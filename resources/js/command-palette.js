/**
 * Palet perintah (Ctrl/⌘ + K, atau "/"): navigasi, aksi cepat, dan pencarian
 * mahasiswa/prodi lewat endpoint JSON. Hasil pencarian di-cache per kata kunci.
 */
const normalize = (text) => text.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').trim();

const isTyping = (element) => element instanceof HTMLElement
    && (element.isContentEditable || ['INPUT', 'TEXTAREA', 'SELECT'].includes(element.tagName));

export default ({ commands = [], searchUrl = '', icons = {} } = {}) => ({
    open: false,
    query: '',
    results: [],
    active: 0,
    loading: false,
    timer: null,
    controller: null,
    cache: new Map(),
    icons,
    isMac: /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent),

    init() {
        window.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.open ? this.close() : this.show();
            } else if (event.key === '/' && !this.open && !isTyping(event.target)) {
                event.preventDefault();
                this.show();
            }
        });
        window.addEventListener('open-command-palette', () => this.show());
    },

    show() {
        this.open = true;
        this.query = '';
        this.results = [];
        this.active = 0;
        this.$nextTick(() => this.$refs.input?.focus());
    },

    close() {
        this.open = false;
        this.controller?.abort();
    },

    get items() {
        const query = normalize(this.query);
        const matches = query === ''
            ? commands
            : commands.filter((command) => normalize(`${command.title} ${command.keywords ?? ''}`).includes(query));

        return [...this.results, ...matches];
    },

    /** Judul grup ditampilkan pada item pertama setiap grup. */
    startsGroup(index) {
        return index === 0 || this.items[index - 1].group !== this.items[index].group;
    },

    search() {
        clearTimeout(this.timer);
        this.active = 0;
        const query = this.query.trim();

        if (query.length < 2) {
            this.results = [];
            this.loading = false;

            return;
        }

        if (this.cache.has(query)) {
            this.results = this.cache.get(query);

            return;
        }

        this.loading = true;
        this.timer = setTimeout(async () => {
            this.controller?.abort();
            this.controller = new AbortController();

            try {
                const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: this.controller.signal,
                });
                const { results } = await response.json();
                const mapped = results.map((result) => ({
                    ...result,
                    group: result.type === 'prodi' ? 'Program studi' : 'Mahasiswa',
                    icon: result.type === 'prodi' ? 'prodi' : 'mahasiswa',
                }));
                this.cache.set(query, mapped);

                if (this.query.trim() === query) {
                    this.results = mapped;
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    this.results = [];
                }
            } finally {
                this.loading = false;
            }
        }, 180);
    },

    move(step) {
        const total = this.items.length;

        if (total === 0) {
            return;
        }

        this.active = (this.active + step + total) % total;
        this.$nextTick(() => document.getElementById(`cmd-item-${this.active}`)?.scrollIntoView({ block: 'nearest' }));
    },

    choose(item = this.items[this.active]) {
        if (!item) {
            return;
        }

        this.close();

        if (item.url) {
            window.location.href = item.url;
        } else if (item.action === 'theme') {
            this.$store.theme.toggle();
        } else if (item.action === 'password') {
            this.$store.account.open('password');
        } else if (item.action === 'logout') {
            document.getElementById('logout-form')?.submit();
        }
    },
});
