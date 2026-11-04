{{-- Satu modal konfirmasi untuk seluruh aplikasi. Pemicu: $store.confirm.ask({ title, message, action }) --}}
<div x-data x-show="$store.confirm.open" x-cloak
    class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
    role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message"
    x-on:keydown.escape.window="$store.confirm.close()">
    <div class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm" x-show="$store.confirm.open"
        x-transition.opacity x-on:click="$store.confirm.close()" aria-hidden="true"></div>

    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900"
        x-show="$store.confirm.open" x-transition x-trap.noscroll.inert="$store.confirm.open">
        <div class="flex gap-4">
            <span class="grid size-11 shrink-0 place-items-center rounded-full bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-300">
                <x-icon name="triangle-alert" />
            </span>
            <div>
                <h2 id="confirm-title" class="text-base font-semibold text-slate-900 dark:text-white" x-text="$store.confirm.title"></h2>
                <p id="confirm-message" class="mt-1.5 text-sm text-slate-600 dark:text-slate-300" x-text="$store.confirm.message"></p>
            </div>
        </div>

        <form method="POST" x-bind:action="$store.confirm.action" class="mt-6 flex justify-end gap-3"
            x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="_method" x-bind:value="$store.confirm.method">
            <x-button variant="secondary" x-on:click="$store.confirm.close()">Batal</x-button>
            <x-button type="submit" variant="danger" icon="trash-2" :loading="true">
                <span x-text="$store.confirm.confirmLabel"></span>
            </x-button>
        </form>
    </div>
</div>
