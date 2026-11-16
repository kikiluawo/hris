<x-layouts.app :title="$item ? 'Ubah soal bank' : 'Tambah soal bank'">
    <x-ui.page-header
        :title="$item ? 'Ubah soal bank' : 'Tambah soal ke bank'"
        description="Soal di bank dipakai berulang kali di tes mana pun. Mengubahnya tidak mengubah tes yang sudah tersusun."
    >
        <x-slot:actions>
            <x-ui.button :href="route('question-bank.index')" variant="secondary" icon="chevron-left">Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($errors->any())
        <x-ui.alert variant="danger" class="mb-4">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    <div
        class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px] xl:items-start"
        x-data="bankForm(
            {{ Illuminate\Support\Js::from($fields) }},
            {{ Illuminate\Support\Js::from([
                'uploadUrl' => route('assessments.image.store'),
                'imageRoot' => App\Support\AssessmentImageStore::urlRoot(),
            ]) }}
        )"
    >
        <form method="POST" action="{{ $item ? route('question-bank.update', $item) : route('question-bank.store') }}" class="space-y-4">
            @csrf
            @if ($item)
                @method('PATCH')
            @endif

            <input type="hidden" name="image_path" :value="image_path">

            <x-ui.card title="Isi soal" description="Pilih jenisnya dulu. Soal skala tidak punya jawaban benar.">
                <div class="space-y-4">
                    <div>
                        <x-ui.label for="type">Jenis soal</x-ui.label>
                        <select
                            name="type"
                            id="type"
                            x-model="type"
                            x-on:change="toType(type)"
                            class="h-11 w-full rounded-xl border border-line bg-surface px-3.5 text-[15px] text-ink shadow-sm outline-none transition focus:border-brand focus:ring-4 focus:ring-brand/15"
                        >
                            @foreach (\App\Models\AssessmentQuestion::typeOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11.5px] text-subtle" x-text="type === 'scale'
                            ? 'Kandidat memilih satu pernyataan yang paling sesuai. HRD menilai per dimensi.'
                            : (type === 'disc'
                                ? 'Soal kepribadian 4 pernyataan (Paling vs Kurang) untuk mengukur profil perilaku DISC & grafik adaptasi.'
                                : (type === 'essay' ? 'Jawaban ditulis bebas dan dinilai manual oleh HRD.' : 'Salah satu pilihan ditandai sebagai jawaban benar dan dinilai otomatis.'))"></p>
                        <x-ui.field-error name="type" />
                    </div>

                    <div>
                        <x-ui.label for="question">Teks soal</x-ui.label>
                        <x-ui.textarea name="question" rows="3" x-model="question" placeholder="Tulis pernyataan atau pertanyaan di sini…" />
                        <x-ui.field-error name="question" />
                    </div>

                    <div>
                        <x-ui.label for="bank-question-image">Gambar soal</x-ui.label>
                        <div class="flex flex-wrap items-center gap-2">
                            <input
                                type="file"
                                id="bank-question-image"
                                accept="image/png,image/jpeg,image/webp"
                                class="h-10 w-full max-w-[260px] cursor-pointer rounded-xl border border-line bg-surface text-[12.5px] text-muted file:mr-2 file:h-10 file:cursor-pointer file:rounded-l-xl file:border-0 file:bg-surface-2 file:px-3 file:text-[12px] file:font-semibold file:text-ink"
                                x-on:change="pickImage('question', $el.files, (name) => image_path = name); $el.value = ''"
                            >
                            <span class="text-[11.5px] text-subtle" x-show="imageBusy === 'question'" x-cloak>Mengunggah…</span>
                            <x-ui.button type="button" variant="ghost" size="sm" x-show="image_path" x-cloak x-on:click="clearImage('question')">Hapus gambar</x-ui.button>
                        </div>
                        <div class="mt-2 flex flex-wrap items-center gap-2" x-show="image_path" x-cloak>
                            <img :src="imageUrl(image_path)" alt="Pratinjau gambar soal" class="h-20 w-20 rounded-lg border border-line bg-surface object-contain">
                            <p class="text-[11.5px] text-subtle">Gambar tampil di atas teks soal untuk kandidat.</p>
                        </div>
                        <p class="mt-1 text-[11.5px] text-subtle" x-show="!image_path && imageBusy !== 'question'" x-cloak>Opsional. Pakai untuk soal deret gambar, diagram, atau bacaan.</p>
                        <div class="mt-2 space-y-3" x-show="imageError" x-cloak>
                            <x-ui.alert variant="danger"><span x-text="imageError"></span></x-ui.alert>
                        </div>
                    </div>

                    <div x-show="type === 'scale' || type === 'disc' || type === 'multiple_choice'" x-cloak>
                        <x-ui.label for="dimension">Dimensi / Domain penilaian</x-ui.label>
                        <x-ui.input name="dimension" x-model="dimension" maxlength="50" list="bank-dimensions" placeholder="Contoh: Verbal, Numerik, Logika, Ketelitian, Kepribadian DISC" />
                        <datalist id="bank-dimensions">
                            @foreach ($dimensions as $dimension)
                                <option value="{{ $dimension }}"></option>
                            @endforeach
                        </datalist>
                        <p class="mt-1 text-[11.5px] text-subtle" x-text="type === 'disc' ? 'Urutan dimensi DISC untuk opsi A, B, C, D (contoh: S,I,D,C)' : (type === 'multiple_choice' ? 'Kategori domain/aspek, misalnya: Verbal, Numerik, Logika, Spasial, Ketelitian' : 'Soal dengan nama dimensi sama digabung jadi satu skor di halaman penilaian.')"></p>
                        <x-ui.field-error name="dimension" />
                    </div>

                    <div x-show="type !== 'essay'" x-cloak>
                        <div class="flex items-center justify-between gap-3">
                            <x-ui.label for="options-0">Pilihan jawaban</x-ui.label>
                            <x-ui.button type="button" variant="ghost" size="sm" icon="refresh" x-on:click="likert()" x-show="type === 'scale'" x-cloak>
                                Skala 5 titik
                            </x-ui.button>
                        </div>

                        <div class="mt-1.5 space-y-2">
                            <template x-for="(option, index) in options" :key="index">
                                <div class="rounded-xl border border-line bg-surface-2/40 p-2">
                                    <div class="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name="correct_option"
                                            :value="String(index)"
                                            x-model="correct"
                                            x-show="type === 'multiple_choice'"
                                            x-cloak
                                            class="size-4 shrink-0 accent-brand"
                                            title="Tandai sebagai jawaban benar"
                                        >
                                        <input
                                            type="text"
                                            :name="'options[' + index + ']'"
                                            x-model="options[index]"
                                            :placeholder="'Pilihan ' + String.fromCharCode(65 + index) + ' (teks, boleh dikosongkan kalau pakai gambar)'"
                                            maxlength="500"
                                            class="h-10 w-full rounded-xl border border-line bg-surface px-3 text-[14px] text-ink shadow-sm outline-none transition placeholder:text-subtle focus:border-brand focus:ring-4 focus:ring-brand/15"
                                        >
                                        <button
                                            type="button"
                                            x-on:click="removeOption(index)"
                                            x-show="options.length > 2"
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-line bg-surface text-subtle transition hover:bg-surface-2 hover:text-ink"
                                            title="Hapus pilihan"
                                        >
                                            <x-icon name="close" class="size-3.5" />
                                        </button>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <input type="hidden" :name="'option_images[' + index + ']'" :value="option_images[index]">
                                        <img
                                            :src="imageUrl(option_images[index])"
                                            :alt="'Gambar pilihan ' + String.fromCharCode(65 + index)"
                                            class="size-10 shrink-0 rounded-lg border border-line bg-surface object-contain"
                                            x-show="option_images[index]"
                                            x-cloak
                                        >
                                        <input
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="h-8 w-full max-w-[210px] cursor-pointer rounded-lg border border-line bg-surface text-[11.5px] text-muted file:mr-2 file:h-8 file:cursor-pointer file:rounded-l-lg file:border-0 file:bg-surface-2 file:px-2 file:text-[11px] file:font-semibold file:text-ink"
                                            x-on:change="pickImage('option:' + index, $el.files, (name) => option_images[index] = name); $el.value = ''"
                                        >
                                        <span class="text-[11px] text-subtle" x-show="imageBusy === 'option:' + index" x-cloak>Mengunggah…</span>
                                        <x-ui.button type="button" variant="ghost" size="sm" x-show="option_images[index]" x-cloak x-on:click="clearImage('option', index)">Hapus</x-ui.button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div class="mt-2.5 flex flex-wrap items-center gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" icon="plus" x-on:click="addOption()" x-show="options.length < 6">
                                Tambah pilihan
                            </x-ui.button>
                            <span class="text-[11.5px] text-subtle" x-show="type === 'multiple_choice'" x-cloak>Klik bulatan untuk menandai jawaban benar.</span>
                            <span class="text-[11.5px] text-subtle">Setiap pilihan boleh berupa gambar saja, teks saja, atau keduanya.</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Poin">
                <div class="space-y-4">
                    <div x-show="type !== 'scale' && type !== 'disc'" x-cloak>
                        <x-ui.label for="points">Poin soal ini</x-ui.label>
                        <x-ui.input type="number" name="points" x-model.number="points" min="0" max="100" inputmode="numeric" />
                        <x-ui.field-error name="points" />
                    </div>

                    <p class="text-[12px] text-subtle" x-show="type === 'scale' || type === 'disc'" x-cloak>Soal skala dan DISC tidak memakai poin nilai (dinilai otomatis lewat skor dimensi & grafik adaptasi).</p>

                    <div class="flex flex-wrap gap-2">
                        <x-ui.button type="submit" icon="check">{{ $item ? 'Simpan perubahan' : 'Simpan ke bank soal' }}</x-ui.button>
                        <x-ui.button type="button" variant="ghost" :href="route('question-bank.index')">Batal</x-ui.button>
                    </div>
                </div>
            </x-ui.card>
        </form>

        <div class="space-y-4 xl:sticky xl:top-6">
            <x-ui.card title="Pratinjau kandidat">
                <p class="whitespace-pre-line text-[14px] font-medium leading-relaxed" x-text="question.trim() || 'Teks soal belum diisi.'"></p>

                <img :src="imageUrl(image_path)" alt="Gambar soal" class="mt-3 max-h-48 w-full rounded-xl border border-line bg-surface object-contain" x-show="image_path" x-cloak>

                <div class="mt-3 space-y-1.5" x-show="type !== 'essay'">
                    <template x-for="(option, index) in options" :key="'p-' + index">
                        <p class="flex items-start gap-2 text-[13px] text-muted">
                            <span
                                class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full border text-[10.5px] font-bold"
                                :class="correct === String(index) && type === 'multiple_choice' ? 'border-brand bg-brand text-brand-ink' : 'border-line bg-surface text-muted'"
                                x-text="String.fromCharCode(65 + index)"
                            ></span>
                            <img :src="imageUrl(option_images[index])" :alt="'Gambar ' + String.fromCharCode(65 + index)" class="size-10 rounded-lg border border-line bg-surface object-contain" x-show="option_images[index]" x-cloak>
                            <span x-text="option.trim() || (option_images[index] ? '' : '…')"></span>
                        </p>
                    </template>
                </div>

                <p class="mt-3 text-[13px] italic text-subtle" x-show="type === 'essay'" x-cloak>Kandidat mengetik jawaban bebas.</p>
            </x-ui.card>

            <x-ui.card title="Cara pakainya">
                <ol class="list-decimal space-y-1.5 pl-4 text-[12.5px] leading-relaxed text-muted">
                    <li>Tambah soal sekali di halaman ini.</li>
                    <li>Buat tes baru, atau edit tes yang masih draf.</li>
                    <li>Klik <span class="font-semibold">Muat dari bank</span>, centang soal yang dibutuhkan, tambahkan.</li>
                    <li>Soal yang sudah masuk tes bisa diedit bebas tanpa mengubah bank.</li>
                </ol>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
