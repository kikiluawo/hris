<x-layouts.guest title="Masuk">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Selamat datang kembali</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">Masuk untuk melanjutkan ke ruang kerja HRD Anda.</p>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <x-ui.label for="email">Email</x-ui.label>
            <x-ui.input
                name="email"
                type="email"
                icon="mail"
                :value="old('email')"
                placeholder="nama@perusahaan.com"
                required
                autofocus
                autocomplete="username"
            />
            @error('email')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="password">Sandi</x-ui.label>
            <x-ui.input
                name="password"
                type="password"
                icon="lock"
                reveal
                placeholder="Masukkan sandi"
                required
                autocomplete="current-password"
            />
            @error('password')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-1">
            <x-ui.checkbox name="remember" label="Ingat saya di perangkat ini" />
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Masuk</x-ui.button>
    </form>
</x-layouts.guest>
