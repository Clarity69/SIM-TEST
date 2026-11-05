// Menyalin ikon Lucide yang dipakai aplikasi ke resources/icons/lucide.php
// (array nama => isi <svg>). Jalankan: npm run icons
import { readFileSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const dir = require.resolve('lucide-static/package.json').replace('package.json', 'icons/');

const icons = [
    'activity', 'archive-restore', 'arrow-down', 'arrow-right', 'arrow-up', 'arrow-up-right',
    'badge-check', 'bell', 'building-2', 'calendar', 'calendar-range', 'chart-column', 'check',
    'chevron-down', 'chevron-left', 'chevron-right', 'chevrons-up-down', 'circle-alert',
    'circle-check', 'command', 'corner-down-left', 'download', 'eye', 'eye-off', 'file-down',
    'file-spreadsheet', 'file-text', 'graduation-cap', 'history', 'house', 'id-card', 'inbox', 'info',
    'key-round', 'layout-dashboard', 'list-checks', 'loader-circle', 'lock', 'log-in', 'log-out',
    'mail', 'map-pin', 'menu', 'minus', 'moon', 'panel-left-close', 'panel-left-open', 'pencil',
    'phone', 'plus', 'printer', 'qr-code', 'refresh-cw', 'rotate-ccw', 'save', 'search', 'send',
    'shield-check', 'sun', 'trash-2', 'trending-down', 'trending-up', 'triangle-alert', 'upload',
    'user', 'user-cog', 'user-plus', 'user-round', 'users', 'x',
];

const entries = icons.map((name) => {
    const svg = readFileSync(`${dir}${name}.svg`, 'utf8');
    const inner = svg.replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '').replace(/\s*\n\s*/g, '').trim();
    return `    '${name}' => '${inner.replace(/'/g, "\\'")}',`;
});

writeFileSync('resources/icons/lucide.php', `<?php\n\n// Dihasilkan oleh scripts/build-icons.mjs dari lucide-static (ISC License). Jangan edit manual.\n\nreturn [\n${entries.join('\n')}\n];\n`);
console.log(`${icons.length} ikon ditulis ke resources/icons/lucide.php`);
