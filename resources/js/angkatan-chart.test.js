import assert from 'node:assert/strict';
import test from 'node:test';
import angkatanChart from './angkatan-chart.js';

function setup() {
    const ticks = [];
    const events = [];
    const chart = angkatanChart({
        rows: [
            { angkatan: 2022, status: 'Aktif', jumlah: 8 },
            { angkatan: 2023, status: 'Aktif', jumlah: 5 },
            { angkatan: 2023, status: 'Cuti', jumlah: 2 },
            { angkatan: 2024, status: 'Aktif', jumlah: 9 },
        ],
        statuses: ['Aktif', 'Cuti', 'Lulus', 'Keluar'],
    });
    chart.$watch = () => {};
    chart.$nextTick = (callback) => ticks.push(callback);
    chart.$refs = { canvas: { dataset: {}, dispatchEvent: (event) => events.push(event) } };
    chart.init();
    return { chart, events, flush: () => { while (ticks.length) ticks.shift()(); } };
}

test('range filters keep chart labels, values, and status totals consistent', () => {
    const { chart, flush } = setup();
    chart.dari = 2023;
    chart.sampai = 2023;
    chart.normalize();
    flush();

    const data = JSON.parse(chart.$refs.canvas.dataset.chart);
    assert.deepEqual(data.labels, [2023]);
    assert.deepEqual(data.datasets.map((dataset) => dataset.values), [[5], [2], [0], [0]]);
    assert.equal(chart.total, 7);
});

test('rapid filter updates render only the final range after Alpine has settled', () => {
    const { chart, events, flush } = setup();
    chart.preset(2);
    chart.normalize();
    chart.preset(1);
    chart.normalize();
    flush();

    assert.equal(events.length, 1);
    assert.deepEqual(JSON.parse(chart.$refs.canvas.dataset.chart).labels, [2024]);
    assert.equal(chart.total, 9);
});

test('reversed bounds normalize without losing the range', () => {
    const { chart, flush } = setup();
    chart.dari = 2024;
    chart.sampai = 2023;
    chart.normalize();
    chart.normalize();
    flush();

    assert.deepEqual(chart.range, [2023, 2024]);
    assert.equal(chart.total, 16);
});
