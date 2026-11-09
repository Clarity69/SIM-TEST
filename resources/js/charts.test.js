import assert from 'node:assert/strict';
import test from 'node:test';
import { Chart } from 'chart.js';
import { draw } from './charts.js';

test('filtering updates the existing Chart.js instance without destroying its canvas', (t) => {
    const updates = [];
    let destroyed = false;
    const instance = {
        config: { type: 'bar' },
        update: (mode) => updates.push(mode),
        destroy: () => { destroyed = true; },
    };
    t.mock.method(Chart, 'getChart', () => instance);
    const canvas = {
        dataset: { chart: JSON.stringify({ type: 'stacked', labels: [2024], datasets: [{ status: 'Aktif', values: [9] }] }) },
    };
    const palette = { status: { Aktif: '#10b981' }, surface: '#fff', text: '#000', grid: '#eee' };

    draw(canvas, palette);
    canvas.dataset.chart = JSON.stringify({ type: 'stacked', labels: [2023, 2024], datasets: [{ status: 'Aktif', values: [5, 9] }] });
    draw(canvas, palette);

    assert.equal(destroyed, false);
    assert.deepEqual(instance.data.labels, [2023, 2024]);
    assert.deepEqual(instance.data.datasets[0].data, [5, 9]);
    assert.deepEqual(updates, ['none', 'none']);
});
