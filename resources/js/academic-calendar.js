export default function academicCalendar(today) {
    const [year, month] = today.split('-').map(Number);

    return {
        today,
        year,
        month: month - 1,
        get title() {
            return new Date(this.year, this.month, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        },
        get days() {
            const offset = (new Date(this.year, this.month, 1).getDay() + 6) % 7;
            const count = new Date(this.year, this.month + 1, 0).getDate();
            const length = Math.ceil((offset + count) / 7) * 7;

            return Array.from({ length }, (_, index) => {
                const day = index - offset + 1;
                if (day < 1 || day > count) {
                    return { key: `blank-${index}`, date: null, day: '' };
                }
                const date = `${this.year}-${String(this.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                return {
                    key: date,
                    date,
                    day,
                    label: new Date(this.year, this.month, day).toLocaleDateString('id-ID', { dateStyle: 'full' }),
                };
            });
        },
        move(offset) {
            const date = new Date(this.year, this.month + offset, 1);
            this.year = date.getFullYear();
            this.month = date.getMonth();
        },
        reset() {
            this.year = year;
            this.month = month - 1;
        },
    };
}
