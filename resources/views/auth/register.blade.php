<x-layouts.guest title="Daftar">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Buat akun baru</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">Isi data berikut untuk mulai mengelola tim Anda.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-ui.label for="name">Nama lengkap</x-ui.label>
            <x-ui.input name="name" icon="user" :value="old('name')" placeholder="Nama lengkap Anda" required autofocus autocomplete="name" />
            @error('name')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="email">Email</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email')" placeholder="nama@perusahaan.com" required autocomplete="username" />
            @error('email')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="password">Sandi</x-ui.label>
            <x-ui.input name="password" type="password" icon="lock" reveal placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            @error('password')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label for="password_confirmation">Konfirmasi sandi</x-ui.label>
            <x-ui.input name="password_confirmation" type="password" icon="lock" reveal placeholder="Ulangi sandi" required autocomplete="new-password" />
        </div>

        <div class="pt-1">
            <x-ui.button type="submit" size="lg" class="w-full">Daftar</x-ui.button>
        </div>
    </form>

    <p class="mt-7 text-center text-[13px] text-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand transition hover:underline">Masuk di sini</a>
    </p>
</x-layouts.guest>
