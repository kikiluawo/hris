<x-layouts.guest title="Konfirmasi sandi">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Konfirmasi sandi</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">
            Ini area yang dilindungi. Masukkan sandi Anda untuk melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-ui.label for="password">Sandi</x-ui.label>
            <x-ui.input name="password" type="password" icon="lock" reveal placeholder="Masukkan sandi" required autofocus autocomplete="current-password" />
            @error('password')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Konfirmasi</x-ui.button>
    </form>
</x-layouts.guest>
