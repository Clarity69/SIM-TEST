{{-- Toast notifikasi: pesan session (success/error/status) + yang dipicu JS lewat $store.toast.show(). --}}
<div x-data x-init="
        @if (session('success')) $store.toast.show(@js(session('success')), 'success'); @endif
        @if (session('error')) $store.toast.show(@js(session('error')), 'error'); @endif
    "
    class="pointer-events-none fixed inset-x-4 bottom-4 z-[60] flex flex-col items-end gap-3 sm:inset-x-auto sm:right-6 sm:top-6 sm:bottom-auto"
    aria-live="polite" role="status">
    <template x-for="toast in $store.toast.items" :key="toast.id">
        <div x-transition class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border bg-white p-4 shadow-lg dark:bg-slate-900"
            :class="toast.type === 'success' ? 'border-emerald-200 dark:border-emerald-500/30' : 'border-red-200 dark:border-red-500/30'">
            <span :class="toast.type === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                <template x-if="toast.type === 'success'"><x-icon name="circle-check" /></template>
                <template x-if="toast.type !== 'success'"><x-icon name="circle-alert" /></template>
            </span>
            <p class="flex-1 text-sm text-slate-700 dark:text-slate-200" x-text="toast.message"></p>
            <button type="button" class="rounded-lg p-0.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" x-on:click="$store.toast.dismiss(toast.id)">
                <span class="sr-only">Tutup notifikasi</span>
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
