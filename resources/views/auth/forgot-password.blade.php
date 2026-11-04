<x-layouts.guest title="Lupa sandi">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Lupa sandi?</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">
            Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang sandi.
        </p>
    </div>

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <x-ui.label for="email">Email</x-ui.label>
            <x-ui.input name="email" type="email" icon="mail" :value="old('email')" placeholder="nama@perusahaan.com" required autofocus autocomplete="username" />
            @error('email')
                <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <x-ui.button type="submit" size="lg" class="w-full">Kirim tautan reset</x-ui.button>
    </form>

    <p class="mt-7 text-center text-[13px] text-muted">
        Ingat sandi Anda?
        <a href="{{ route('login') }}" class="font-semibold text-brand transition hover:underline">Kembali masuk</a>
    </p>
</x-layouts.guest>
