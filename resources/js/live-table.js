/**
 * Pencarian live untuk tabel ber-pagination server.
 * Form tetap berupa GET biasa (fallback tanpa JS); dengan JS, setiap perubahan
 * di-debounce lalu hanya region tabel yang diganti lewat fetch partial.
 *
 * Prefetch: saat kursor/fokus berada di link halaman atau header sort, potongan
 * tabelnya diambil lebih dulu sehingga klik terasa instan meski database jauh.
 */
const PREFETCH_TTL = 30_000;

export default (debounceMs = 350) => ({
    loading: false,
    timer: null,
    controller: null,
    // Query string aktif, dipakai link Cetak/Kirim PDF agar mengikuti filter.
    query: window.location.search,
    cache: new Map(),
    // ID terpilih untuk aksi massal (tetap tersimpan saat pindah halaman/filter).
    selected: [],

    /** ID baris pada halaman yang sedang tampil (dibaca dari potongan tabel). */
    pageIds() {
        try {
            return JSON.parse(this.$refs.region.querySelector('[data-page-ids]')?.dataset.pageIds ?? '[]');
        } catch {
            return [];
        }
    },

    allOnPageSelected() {
        const ids = this.pageIds();

        return ids.length > 0 && ids.every((id) => this.selected.includes(id));
    },

    someOnPageSelected() {
        const ids = this.pageIds();

        return ids.some((id) => this.selected.includes(id)) && !this.allOnPageSelected();
    },

    toggleAllOnPage(checked) {
        const ids = this.pageIds();
        this.selected = checked
            ? [...new Set([...this.selected, ...ids])]
            : this.selected.filter((id) => !ids.includes(id));
    },

    init() {
        const navLink = (target) => {
            const link = target.closest?.('[data-table-region] a[href]');

            return link && (link.closest('nav[role="navigation"]') || link.hasAttribute('data-sort-link')) ? link : null;
        };

        this.$el.addEventListener('click', (event) => {
            const link = navLink(event.target);

            if (link) {
                event.preventDefault();
                this.load(link.href);
            }
        });

        const prefetch = (event) => {
            const link = navLink(event.target);

            if (link) {
                this.fetchPartial(link.href);
            }
        };
        this.$el.addEventListener('mouseover', prefetch);
        this.$el.addEventListener('focusin', prefetch);
    },

    queue() {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.submit(), debounceMs);
    },

    submit() {
        const form = this.$refs.form;
        const params = new URLSearchParams(new FormData(form));

        for (const [key, value] of [...params.entries()]) {
            if (value === '') {
                params.delete(key);
            }
        }

        this.load(`${form.action}?${params.toString()}`);
    },

    /** Ambil potongan tabel; hasil disimpan sebentar agar prefetch bisa dipakai ulang. */
    fetchPartial(url, signal) {
        const cached = this.cache.get(url);

        if (cached && Date.now() - cached.at < PREFETCH_TTL) {
            return cached.promise;
        }

        const promise = fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
            signal,
        }).then((response) => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            return response.text();
        });

        this.cache.set(url, { at: Date.now(), promise });
        promise.catch(() => this.cache.delete(url));

        return promise;
    },

    async load(url) {
        this.controller?.abort();
        this.controller = new AbortController();
        this.loading = true;

        try {
            const html = await this.fetchPartial(url, this.controller.signal);

            this.$refs.region.innerHTML = html;
            history.replaceState(null, '', url);
            this.query = new URL(url, window.location.origin).search;
            this.$refs.region.closest('section')?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            window.dispatchEvent(new CustomEvent('table-updated', { detail: { url } }));
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = url;
            }
        } finally {
            this.loading = false;
        }
    },
});
