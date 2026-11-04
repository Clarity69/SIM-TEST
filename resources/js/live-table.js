/**
 * Pencarian live untuk tabel ber-pagination server.
 * Form tetap berupa GET biasa (fallback tanpa JS); dengan JS, setiap perubahan
 * di-debounce lalu hanya region tabel yang diganti lewat fetch partial.
 */
export default (debounceMs = 350) => ({
    loading: false,
    timer: null,
    controller: null,
    // Query string aktif, dipakai link Cetak/Kirim PDF agar mengikuti filter.
    query: window.location.search,

    init() {
        this.$el.addEventListener('click', (event) => {
            const link = event.target.closest('[data-table-region] a[href]');

            if (link && (link.closest('nav[role="navigation"]') || link.hasAttribute('data-sort-link'))) {
                event.preventDefault();
                this.load(link.href);
            }
        });
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

    async load(url) {
        this.controller?.abort();
        this.controller = new AbortController();
        this.loading = true;

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                signal: this.controller.signal,
            });

            if (!response.ok) {
                throw new Error(response.statusText);
            }

            this.$refs.region.innerHTML = await response.text();
            history.replaceState(null, '', url);
            this.query = new URL(url, window.location.origin).search;
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
