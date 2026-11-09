/**
 * Pencarian live untuk tabel ber-pagination server.
 * Form tetap berupa GET biasa (fallback tanpa JS); dengan JS, setiap perubahan
 * di-debounce lalu hanya region tabel yang diganti lewat fetch partial.
 *
 * Prefetch: saat kursor/fokus berada di link halaman atau header sort, potongan
 * tabelnya diambil lebih dulu sehingga klik terasa instan meski database jauh.
 */
const PREFETCH_TTL = 30_000;
const FILTER_LABELS = { search: 'Pencarian', prodi_id: 'Program studi', jenis_kelamin: 'Jenis kelamin', status: 'Status', angkatan: 'Angkatan' };

export default (debounceMs = 350) => ({
    loading: false,
    timer: null,
    controller: null,
    requestId: 0,
    filtersOpen: true,
    // Query string aktif, dipakai link Cetak/Kirim PDF agar mengikuti filter.
    query: window.location.search,
    cache: new Map(),
    // ID terpilih untuk aksi massal (tetap tersimpan saat pindah halaman/filter).
    selected: [],

    get activeFilters() {
        const params = new URLSearchParams(this.query);

        return Object.entries(FILTER_LABELS).filter(([key]) => params.get(key)?.trim()).map(([key, label]) => {
            const value = params.get(key);
            const field = this.$refs?.form?.elements.namedItem(key);
            const option = field?.options ? [...field.options].find((option) => option.value === value) : null;

            return { key, label, value: option?.textContent.trim() ?? value };
        });
    },

    removeFilter(key) {
        const field = this.$refs.form.elements.namedItem(key);
        if (field) {
            field.value = '';
        }
        this.submit();
    },

    resetFilters() {
        for (const key of Object.keys(FILTER_LABELS)) {
            const field = this.$refs.form.elements.namedItem(key);
            if (field) {
                field.value = '';
            }
        }
        this.submit();
    },

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
        this.filtersOpen = this.activeFilters.some((filter) => filter.key !== 'search')
            || (window.matchMedia?.('(min-width: 1024px)')?.matches ?? true);

        const navLink = (target) => {
            const link = target.closest?.('[data-table-region] a[href]');

            return link && (link.closest('nav[role="navigation"]') || link.hasAttribute('data-sort-link')) ? link : null;
        };

        this.$el.addEventListener('click', (event) => {
            const link = navLink(event.target);

            if (link) {
                event.preventDefault();
                this.load(link.href, true);
            }
        });

        const prefetch = (event) => {
            const link = navLink(event.target);

            if (link) {
                this.fetchPartial(link.href).catch(() => {});
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
        clearTimeout(this.timer);
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
            if (!response.ok || response.redirected) {
                throw new Error(response.statusText);
            }

            return response.text();
        });

        this.cache.set(url, { at: Date.now(), promise });
        promise.catch(() => this.cache.delete(url));

        return promise;
    },

    async load(url, scrollToTable = false) {
        const requestId = ++this.requestId;
        this.controller?.abort();
        this.controller = new AbortController();
        this.loading = true;

        try {
            const html = await this.fetchPartial(url, this.controller.signal);

            if (requestId !== this.requestId) {
                return;
            }

            this.$refs.region.innerHTML = html;
            history.replaceState(null, '', url);
            this.query = new URL(url, window.location.origin).search;
            const params = new URLSearchParams(this.query);
            for (const key of ['sort', 'direction']) {
                const field = this.$refs.form?.elements.namedItem(key);
                if (field) {
                    field.value = params.get(key) ?? '';
                }
            }
            if (scrollToTable) {
                const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)')?.matches;
                this.$refs.region.closest('section')?.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
            }
            window.dispatchEvent(new CustomEvent('table-updated', { detail: { url } }));
        } catch (error) {
            if (requestId === this.requestId && error.name !== 'AbortError') {
                window.location.href = url;
            }
        } finally {
            if (requestId === this.requestId) {
                this.loading = false;
            }
        }
    },
});
