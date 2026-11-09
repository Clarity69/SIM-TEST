import assert from 'node:assert/strict';
import test from 'node:test';
import liveTable from './live-table.js';

function setup(t) {
    const pending = new Map();
    const navigation = [];
    const events = [];
    const location = { search: '', origin: 'https://example.test', href: '' };
    t.mock.method(globalThis, 'fetch', (url) => new Promise((resolve, reject) => {
        pending.set(url, {
            resolve: (html) => resolve({ ok: true, text: async () => html }),
            reject,
        });
    }));
    for (const [name, value] of Object.entries({
        window: { location, dispatchEvent: (event) => events.push(event.detail.url) },
        history: { replaceState: (...args) => navigation.push(args[2]) },
        CustomEvent: class { constructor(type, options) { this.detail = options.detail; } },
    })) {
        const original = Object.getOwnPropertyDescriptor(globalThis, name);
        Object.defineProperty(globalThis, name, { value, configurable: true, writable: true });
        t.after(() => {
            if (original) {
                Object.defineProperty(globalThis, name, original);
            } else {
                delete globalThis[name];
            }
        });
    }
    const region = { innerHTML: '', closest: () => null };
    const state = liveTable();
    state.$refs = { region };

    return { pending, navigation, events, location, region, state };
}

test('a late prefetched response cannot replace the latest search or URL', async (t) => {
    const { state, pending, navigation, events, region } = setup(t);
    const older = 'https://example.test/mahasiswa?search=older';
    const newer = 'https://example.test/mahasiswa?search=newer';
    state.fetchPartial(older);
    const first = state.load(older);
    const second = state.load(newer);
    pending.get(newer).resolve('NEWER RESULT');
    await second;
    pending.get(older).resolve('OLDER RESULT');
    await first;

    assert.equal(region.innerHTML, 'NEWER RESULT');
    assert.equal(state.query, '?search=newer');
    assert.deepEqual(navigation, [newer]);
    assert.deepEqual(events, [newer]);
});

test('an outdated failure neither navigates nor clears the current loading state', async (t) => {
    const { state, pending, location } = setup(t);
    const older = 'https://example.test/mahasiswa?search=older';
    const newer = 'https://example.test/mahasiswa?search=newer';
    state.fetchPartial(older);
    const first = state.load(older);
    const second = state.load(newer);
    pending.get(older).reject(new Error('Network error'));
    await first;

    assert.equal(location.href, '');
    assert.equal(state.loading, true);
    pending.get(newer).resolve('NEWER RESULT');
    await second;
    assert.equal(state.loading, false);
});

test('failure of the current search falls back to a normal navigation', async (t) => {
    const { state, pending, location } = setup(t);
    const url = 'https://example.test/mahasiswa?search=current';
    const request = state.load(url);
    pending.get(url).reject(new Error('Network error'));
    await request;

    assert.equal(location.href, url);
    assert.equal(state.loading, false);
});

test('active filter chips show labels and ignore sorting and pagination', (t) => {
    const { state } = setup(t);
    state.query = '?prodi_id=42&status=Aktif&sort=nama&direction=asc&page=2';
    state.$refs.form = { elements: { namedItem: (key) => key === 'prodi_id' ? { options: [{ value: '42', textContent: 'Teknik Informatika' }] } : null } };

    assert.deepEqual(state.activeFilters, [
        { key: 'prodi_id', label: 'Program studi', value: 'Teknik Informatika' },
        { key: 'status', label: 'Status', value: 'Aktif' },
    ]);
    state.query = '?search=Rina';
    assert.deepEqual(state.activeFilters, [{ key: 'search', label: 'Pencarian', value: 'Rina' }]);
});

test('removing one filter keeps the other form values intact', (t) => {
    const { state } = setup(t);
    const fields = { status: { value: 'Aktif' }, prodi_id: { value: '42' }, search: { value: 'Rina' } };
    state.$refs.form = { elements: { namedItem: (key) => fields[key] } };
    let submitted = false;
    state.submit = () => { submitted = true; };

    state.removeFilter('status');

    assert.equal(fields.status.value, '');
    assert.equal(fields.prodi_id.value, '42');
    assert.equal(fields.search.value, 'Rina');
    assert.equal(submitted, true);
});

test('reset clears filters while preserving the chosen sort order', (t) => {
    const { state } = setup(t);
    const fields = { search: { value: 'Rina' }, status: { value: 'Aktif' }, sort: { value: 'nama' }, direction: { value: 'asc' } };
    state.$refs.form = { elements: { namedItem: (key) => fields[key] } };
    state.submit = () => {};

    state.resetFilters();

    assert.equal(fields.search.value, '');
    assert.equal(fields.status.value, '');
    assert.equal(fields.sort.value, 'nama');
    assert.equal(fields.direction.value, 'asc');
});

test('AJAX sorting stays selected when a new filter is applied', async (t) => {
    const { state, pending } = setup(t);
    const fields = { sort: { value: '' }, direction: { value: '' } };
    state.$refs.form = { elements: { namedItem: (key) => fields[key] } };
    const url = 'https://example.test/mahasiswa?sort=nama&direction=asc';
    const request = state.load(url);
    pending.get(url).resolve('SORTED RESULT');
    await request;

    assert.equal(fields.sort.value, 'nama');
    assert.equal(fields.direction.value, 'asc');
});

test('an authentication redirect uses a full navigation instead of nesting a login form', async (t) => {
    const { state, location } = setup(t);
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, redirected: true, statusText: 'Redirected' }));
    const url = 'https://example.test/mahasiswa?search=Rina';

    await state.load(url);

    assert.equal(location.href, url);
});

test('filtering keeps the viewport still while pagination can return to the table', async (t) => {
    const { state, pending, region } = setup(t);
    let scrolls = 0;
    region.closest = () => ({ scrollIntoView: () => { scrolls++; } });
    const filterUrl = 'https://example.test/mahasiswa?status=Aktif';
    const filtered = state.load(filterUrl);
    pending.get(filterUrl).resolve('FILTERED RESULT');
    await filtered;
    assert.equal(scrolls, 0);

    const pageUrl = 'https://example.test/mahasiswa?status=Aktif&page=2';
    const nextPage = state.load(pageUrl, true);
    pending.get(pageUrl).resolve('NEXT PAGE');
    await nextPage;
    assert.equal(scrolls, 1);
});
