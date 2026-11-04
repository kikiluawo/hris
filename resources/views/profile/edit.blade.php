<x-layouts.app title="Profil">
    <x-ui.page-header
        title="Profil & Pengaturan"
        description="Perbarui informasi akun, sandi, dan preferensi tampilan Anda."
    />

    <div class="grid gap-4 xl:grid-cols-5">
        <div class="space-y-4 xl:col-span-3">
            <x-ui.card title="Informasi profil" description="Nama dan email yang digunakan untuk masuk.">
                <x-slot:actions>
                    @if (auth()->user()->hasVerifiedEmail())
                        <x-ui.badge variant="success">Email terverifikasi</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning">Belum terverifikasi</x-ui.badge>
                    @endif
                </x-slot:actions>

                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="flex items-center gap-3 rounded-xl border border-line bg-surface-2/50 p-3">
                        <x-ui.avatar :name="$user->name" size="lg" />
                        <div class="min-w-0">
                            <p class="truncate text-[13.5px] font-semibold">{{ $user->name }}</p>
                            <p class="truncate text-[11.5px] text-subtle">Administrator · {{ $user->email }}</p>
                        </div>
                    </div>

                    <div>
                        <x-ui.label for="name">Nama lengkap</x-ui.label>
                        <x-ui.input name="name" :value="$user->name" required autocomplete="name" />
                        @error('name')
                            <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-ui.label for="email">Email</x-ui.label>
                        <x-ui.input name="email" type="email" icon="mail" :value="$user->email" required autocomplete="username" />
                        @error('email')
                            <p class="mt-1.5 text-[12.5px] text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-1">
                        <x-ui.button type="submit">Simpan perubahan</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            @if (! auth()->user()->hasVerifiedEmail())
                <x-ui.alert variant="warning">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span>Email Anda belum diverifikasi. Periksa kotak masuk untuk tautan verifikasi.</span>
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="font-semibold underline underline-offset-2">Kirim ulang</button>
                        </form>
                    </div>
                </x-ui.alert>
            @endif
        </div>

        <div class="space-y-4 xl:col-span-2">
            <x-ui.card title="Ubah sandi" description="Gunakan kombinasi yang kuat dan mudah Anda ingat.">
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-ui.label for="current_password">Sandi saat ini</x-ui.label>
                        <x-ui.input name="current_password" type="password" icon="lock" reveal placeholder="Masukkan sandi saat ini" required autocomplete="current-password" />
                        @error('current_password')
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
                        <x-ui.button type="submit" variant="secondary">Perbarui sandi</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <x-ui.card title="Tampilan" description="Pilih tema yang paling nyaman untuk Anda.">
                <div class="grid grid-cols-3 gap-2">
                    @foreach ([['light', 'Terang', 'sun'], ['dark', 'Gelap', 'moon'], ['system', 'Sistem', 'settings']] as [$mode, $label, $icon])
                        <button
                            type="button"
                            @click="$store.theme.set('{{ $mode }}')"
                            :class="$store.theme.mode === '{{ $mode }}' ? 'border-brand-line bg-brand-soft text-brand-soft-ink' : 'border-line bg-surface text-muted hover:bg-surface-2'"
                            class="flex flex-col items-center gap-2 rounded-xl border px-3 py-3.5 text-[12px] font-semibold transition"
                        >
                            <x-icon :name="$icon" class="size-5" />
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
