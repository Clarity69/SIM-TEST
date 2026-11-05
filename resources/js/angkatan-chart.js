/**
 * Grafik mahasiswa per angkatan × status dengan filter rentang angkatan.
 * Data agregat (dari cache dashboard) sudah ada di halaman, jadi memfilter
 * rentang tidak perlu request ke server sama sekali.
 */
export default ({ rows = [], statuses = [] } = {}) => ({
    rows,
    statuses,
    years: [...new Set(rows.map((row) => row.angkatan))].sort((a, b) => a - b),
    dari: null,
    sampai: null,

    init() {
        this.dari = this.years[0] ?? null;
        this.sampai = this.years.at(-1) ?? null;
        this.$watch('dari', () => this.normalize());
        this.$watch('sampai', () => this.normalize());
    },

    normalize() {
        if (this.dari !== null && this.sampai !== null && Number(this.dari) > Number(this.sampai)) {
            [this.dari, this.sampai] = [this.sampai, this.dari];

            return;
        }

        this.render();
    },

    get range() {
        return this.years.filter((year) => year >= Number(this.dari) && year <= Number(this.sampai));
    },

    jumlah(status, year = null) {
        return this.rows
            .filter((row) => row.status === status && (year === null ? this.range.includes(row.angkatan) : row.angkatan === year))
            .reduce((total, row) => total + row.jumlah, 0);
    },

    get total() {
        return this.statuses.reduce((total, status) => total + this.jumlah(status), 0);
    },

    persen(status) {
        return this.total ? (this.jumlah(status) / this.total) * 100 : 0;
    },

    preset(count) {
        this.sampai = this.years.at(-1);
        this.dari = this.years[Math.max(0, this.years.length - count)];
    },

    isPreset(count) {
        return Number(this.sampai) === this.years.at(-1) && Number(this.dari) === this.years[Math.max(0, this.years.length - count)];
    },

    chartData() {
        return JSON.stringify({
            type: 'stacked',
            labels: this.range,
            datasets: this.statuses.map((status) => ({ status, values: this.range.map((year) => this.jumlah(status, year)) })),
        });
    },

    render() {
        const canvas = this.$refs.canvas;

        if (canvas) {
            canvas.dataset.chart = this.chartData();
            canvas.dispatchEvent(new CustomEvent('chart:update', { bubbles: true }));
        }
    },
});
