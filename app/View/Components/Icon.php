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
        self::$icons ??= require resource_path('icons/lucide.php');

        if (! isset(self::$icons[$this->name])) {
            throw new InvalidArgumentException("Ikon [{$this->name}] belum ada di resources/icons/lucide.php; tambahkan di scripts/build-icons.mjs.");
        }

        $paths = self::$icons[$this->name];

        return <<<BLADE
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ \$attributes->merge(['class' => 'size-5 shrink-0']) }}>{$paths}</svg>
            BLADE;
    }
}
