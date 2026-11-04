<x-layouts.guest title="Verifikasi email">
    <div class="mb-8">
        <h1 class="text-[24px] font-semibold tracking-tight">Verifikasi email Anda</h1>
        <p class="mt-1.5 text-[13.5px] text-muted">
            Kami telah mengirim tautan verifikasi ke
            <span class="font-semibold text-ink">{{ auth()->user()->email }}</span>.
            Klik tautan tersebut untuk mengaktifkan akses penuh.
        </p>
    </div>

    @if (session('status') === 'verification-link-sent' || session('status'))
        <x-ui.alert variant="success" class="mb-6">{{ session('status') }}</x-ui.alert>
    @endif

    <div class="space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-ui.button type="submit" size="lg" class="w-full">Kirim ulang email verifikasi</x-ui.button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" size="lg" class="w-full">Keluar dari akun</x-ui.button>
        </form>
    </div>

    <p class="mt-7 text-center text-[12px] text-subtle">
        Anda tetap bisa menjelajahi dashboard, namun beberapa fitur membatasi akses sebelum email terverifikasi.
    </p>
</x-layouts.guest>
