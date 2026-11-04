// Menyalin ikon Lucide yang dipakai aplikasi ke resources/icons/lucide.php
// (array nama => isi <svg>). Jalankan: npm run icons
import { readFileSync, writeFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const dir = require.resolve('lucide-static/package.json').replace('package.json', 'icons/');

const icons = [
    'archive-restore', 'arrow-down', 'arrow-up', 'arrow-up-right', 'bell', 'building-2', 'calendar',
    'check', 'chevron-down', 'chevron-left', 'chevron-right', 'chevrons-up-down', 'circle-alert',
    'circle-check', 'download', 'eye', 'eye-off', 'file-down', 'file-spreadsheet', 'file-text',
    'graduation-cap', 'house', 'inbox', 'info', 'key-round', 'layout-dashboard', 'loader-circle',
    'lock', 'log-out', 'mail', 'map-pin', 'menu', 'minus', 'moon', 'panel-left-close', 'panel-left-open',
    'pencil', 'phone', 'plus', 'printer', 'refresh-cw', 'save', 'search', 'send', 'shield-check',
    'sun', 'trash-2', 'trending-down', 'trending-up', 'triangle-alert', 'upload', 'user', 'user-plus',
    'user-round', 'users', 'x',
];

const entries = icons.map((name) => {
    const svg = readFileSync(`${dir}${name}.svg`, 'utf8');
    const inner = svg.replace(/^[\s\S]*?<svg[^>]*>/, '').replace(/<\/svg>\s*$/, '').replace(/\s*\n\s*/g, '').trim();
    return `    '${name}' => '${inner.replace(/'/g, "\\'")}',`;
});

writeFileSync('resources/icons/lucide.php', `<?php\n\n// Dihasilkan oleh scripts/build-icons.mjs dari lucide-static (ISC License). Jangan edit manual.\n\nreturn [\n${entries.join('\n')}\n];\n`);
console.log(`${icons.length} ikon ditulis ke resources/icons/lucide.php`);
