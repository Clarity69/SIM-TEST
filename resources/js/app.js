import '@fontsource-variable/plus-jakarta-sans';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import angkatanChart from './angkatan-chart';
import academicCalendar from './academic-calendar';
import commandPalette from './command-palette';
import liveTable from './live-table';

const storage = {
    get(key, fallback = null) {
        try {
            return localStorage.getItem(key) ?? fallback;
        } catch {
            return fallback;
        }
    },
    set(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch {
            // Mode privat / storage diblokir: preferensi hanya berlaku di halaman ini.
        }
    },
};

document.addEventListener('alpine:init', () => {
    // Tema gelap/terang: class "dark" di <html>, disimpan di localStorage.
    Alpine.store('theme', {
        dark: document.documentElement.classList.contains('dark'),
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            storage.set('theme', this.dark ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.dark } }));
        },
    });

    // Sidebar: collapse di desktop (diingat), drawer di mobile.
    Alpine.store('sidebar', {
        collapsed: storage.get('sidebar-collapsed') === '1',
        mobileOpen: false,
        isMobile: window.innerWidth < 1024,
        init() {
            const desktop = window.matchMedia('(min-width: 1024px)');
            const sync = () => {
                this.isMobile = !desktop.matches;
                if (desktop.matches) {
                    this.mobileOpen = false;
                }
            };
            sync();
            desktop.addEventListener('change', sync);
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            storage.set('sidebar-collapsed', this.collapsed ? '1' : '0');
        },
    });

    // Toast notifikasi. Bisa dipanggil dari mana saja: $store.toast.show('Pesan', 'success').
    Alpine.store('toast', {
        items: [],
        show(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.items.push({ id, message, type });
            setTimeout(() => this.dismiss(id), 5000);
        },
        dismiss(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
    });

    // Modal konfirmasi tunggal: tombol hapus memanggil $store.confirm.ask({...}).
    Alpine.store('confirm', {
        open: false,
        title: '',
        message: '',
        action: '',
        method: 'DELETE',
        confirmLabel: 'Hapus',
        tone: 'danger',
        // Form milik halaman (mis. aksi massal) yang dikirim saat dikonfirmasi; null = form modal sendiri.
        form: null,
        ask({ title, message, action = '', method = 'DELETE', confirmLabel = 'Hapus', tone = 'danger', form = null }) {
            Object.assign(this, { title, message, action, method, confirmLabel, tone, form, open: true });
        },
        close() {
            this.open = false;
        },
    });

    // Modal akun dari dropdown topbar: 'profile' | 'password' | null.
    Alpine.store('account', {
        modal: null,
        open(name) {
            this.modal = name;
        },
        close() {
            this.modal = null;
        },
    });

    Alpine.data('liveTable', liveTable);
    Alpine.data('commandPalette', commandPalette);
    Alpine.data('angkatanChart', angkatanChart);
    Alpine.data('academicCalendar', academicCalendar);
});

window.Alpine = Alpine;
Alpine.plugin(focus);
Alpine.start();

// Chart.js hanya dimuat (code-split) di halaman yang punya grafik.
if (document.querySelector('[data-chart]')) {
    import('./charts').then(({ default: initCharts }) => initCharts());
}
