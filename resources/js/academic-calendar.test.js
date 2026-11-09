import assert from 'node:assert/strict';
import test from 'node:test';
import academicCalendar from './academic-calendar.js';

test('calendar aligns Sunday-first months to a Monday-first week', () => {
    const calendar = academicCalendar('2026-11-10');

    assert.equal(calendar.days.slice(0, 6).every((day) => day.date === null), true);
    assert.equal(calendar.days[6].date, '2026-11-01');
    assert.equal(calendar.days.filter((day) => day.date).length, 30);
    assert.equal(calendar.days[35].date, '2026-11-30');
});

test('calendar includes leap day and omits it in a normal year', () => {
    const leap = academicCalendar('2024-02-10');
    const normal = academicCalendar('2025-02-10');

    assert.equal(leap.days.some((day) => day.date === '2024-02-29'), true);
    assert.equal(normal.days.filter((day) => day.date).length, 28);
});

test('month navigation crosses year boundaries and today restores the original date', () => {
    const calendar = academicCalendar('2026-12-10');

    calendar.move(1);
    assert.equal(calendar.year, 2027);
    assert.equal(calendar.month, 0);
    calendar.move(-1);
    assert.equal(calendar.year, 2026);
    assert.equal(calendar.month, 11);
    calendar.move(-12);
    calendar.reset();
    assert.equal(calendar.year, 2026);
    assert.equal(calendar.month, 11);
    assert.equal(calendar.today, '2026-12-10');
});
