import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, DoughnutController, ArcElement, Tooltip, Legend);

const palette = () => {
    const dark = document.documentElement.classList.contains('dark');

    return {
        text: dark ? '#cbd5e1' : '#475569',
        grid: dark ? 'rgba(148, 163, 184, 0.15)' : 'rgba(15, 23, 42, 0.06)',
        primary: dark ? '#59b2f2' : '#1079c7',
        primarySoft: dark ? 'rgba(89, 178, 242, 0.55)' : 'rgba(18, 145, 236, 0.55)',
        secondary: '#fe6500',
        surface: dark ? '#0f172a' : '#ffffff',
        status: {
            Aktif: dark ? '#34d399' : '#10b981',
            Cuti: dark ? '#fbbf24' : '#f59e0b',
            Lulus: dark ? '#59b2f2' : '#1079c7',
            Keluar: dark ? '#64748b' : '#94a3b8',
        },
    };
};

const configs = {
    bar({ labels, values, names }, c) {
        const max = Math.max(...values);

        return {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Mahasiswa',
                    data: values,
                    backgroundColor: values.map((v) => (v === max ? c.primary : c.primarySoft)),
                    borderRadius: 8,
                    maxBarThickness: 56,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { title: (items) => names?.[items[0].dataIndex] ?? items[0].label } },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: c.text } },
                    y: { beginAtZero: true, ticks: { color: c.text, precision: 0 }, grid: { color: c.grid }, border: { display: false } },
                },
            },
        };
    },

    // Batang bertumpuk: satu dataset per status (warna mengikuti tema).
    stacked({ labels, datasets }, c) {
        return {
            type: 'bar',
            data: {
                labels,
                datasets: datasets.map(({ status, values }) => ({
                    label: status,
                    data: values,
                    backgroundColor: c.status[status] ?? c.primary,
                    borderColor: c.surface,
                    borderWidth: { top: 2 },
                    borderSkipped: 'bottom',
                    borderRadius: 4,
                    maxBarThickness: 48,
                })),
            },
            options: {
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { title: (items) => `Angkatan ${items[0].label}` } },
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { color: c.text } },
                    y: { stacked: true, beginAtZero: true, ticks: { color: c.text, precision: 0 }, grid: { color: c.grid }, border: { display: false } },
                },
            },
        };
    },

    doughnut({ labels, values }, c) {
        return {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data: values, backgroundColor: [c.primary, c.secondary], borderColor: c.surface, borderWidth: 3 }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: { legend: { display: false } },
            },
        };
    },
};

const draw = (canvas, c) => {
    Chart.getChart(canvas)?.destroy();
    const { type, ...data } = JSON.parse(canvas.dataset.chart);
    new Chart(canvas, configs[type](data, c));
};

export default function initCharts() {
    Chart.defaults.font.family = '"Plus Jakarta Sans Variable", system-ui, sans-serif';
    const render = () => {
        const c = palette();
        document.querySelectorAll('[data-chart]').forEach((canvas) => draw(canvas, c));
    };

    render();
    window.addEventListener('theme-changed', render);

    // Grafik yang datanya diganti di sisi klien (mis. filter rentang angkatan).
    document.addEventListener('chart:update', (event) => draw(event.target, palette()));
}
