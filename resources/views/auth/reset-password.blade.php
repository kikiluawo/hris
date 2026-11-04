<x-layouts.guest title="Atur ulang sandi">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Atur ulang sandi</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">Buat sandi baru untuk akun Anda.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-ui.label for="email">Email</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email', $request->email)" required autocomplete="username" />
            @error('email')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="password">Sandi baru</x-ui.label>
            <x-ui.input name="password" type="password" icon="lock" reveal placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            @error('password')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="password_confirmation">Konfirmasi sandi baru</x-ui.label>
            <x-ui.input name="password_confirmation" type="password" icon="lock" reveal placeholder="Ulangi sandi baru" required autocomplete="new-password" />
        </div>

        <div class="pt-1">
            <x-ui.button type="submit" size="lg" class="w-full">Simpan sandi baru</x-ui.button>
        </div>
    </form>
</x-layouts.guest>
