<?php

namespace App\View\Components;

use Illuminate\View\Component;
use InvalidArgumentException;

/**
 * Ikon Lucide sebagai SVG inline: <x-icon name="users" class="size-5" />.
 * Daftar ikon dihasilkan oleh `npm run icons` (resources/icons/lucide.php).
 */
class Icon extends Component
{
    /** @var array<string, string>|null */
    private static ?array $icons = null;

    public function __construct(public string $name) {}

    public function render(): string
    {
        $paths = self::paths($this->name);

        return <<<BLADE
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ \$attributes->merge(['class' => 'size-5 shrink-0']) }}>{$paths}</svg>
            BLADE;
    }

    /**
     * SVG lengkap sebagai string, untuk ikon yang dirender JavaScript (mis. palet perintah).
     */
    public static function svg(string $name, string $class = 'size-5 shrink-0'): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" class="'.e($class).'">'.self::paths($name).'</svg>';
    }

    private static function paths(string $name): string
    {
        self::$icons ??= require resource_path('icons/lucide.php');

        if (! isset(self::$icons[$name])) {
            throw new InvalidArgumentException("Ikon [{$name}] belum ada di resources/icons/lucide.php; tambahkan di scripts/build-icons.mjs.");
        }

        return self::$icons[$name];
    }
}
