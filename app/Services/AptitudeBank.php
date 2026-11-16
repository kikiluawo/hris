<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\User;

class AptitudeBank
{
    
    public static function questions(): array
    {
        return [

            
            1 => [
                'dimension' => 'Verbal',
                'question' => 'Sinonim (Persamaan Kata): INSINUASI = ...',
                'options' => [
                    'Pernyataan terus terang',
                    'Sindiran halus atau tuduhan tersembunyi',
                    'Pemberian penghargaan resmi',
                    'Persetujuan bersama dalam rapat',
                    'Perjanjian hitam di atas putih',
                ],
                'correct_option' => 1, 
            ],
            2 => [
                'dimension' => 'Verbal',
                'question' => 'Antonim (Lawan Kata): NOMADIK >< ...',
                'options' => [
                    'Suka berpindah-pindah tempat',
                    'Menetap atau tinggal di tempat permanen',
                    'Suka berpetualang ke alam bebas',
                    'Mengembara mencari padang rumput',
                    'Hidup berburu di hutan pedalaman',
                ],
                'correct_option' => 1, 
            ],
            3 => [
                'dimension' => 'Verbal',
                'question' => 'Analogi Kata: MOBIL : BENSIN = MANUSIA : ...',
                'options' => [
                    'Makanan',
                    'Minuman dingin',
                    'Rumah tinggal',
                    'Pakaian hangat',
                    'Sepatu kerja',
                ],
                'correct_option' => 0, 
            ],
            4 => [
                'dimension' => 'Verbal',
                'question' => 'Analogi Kata: APOTEKER : OBAT = KOKI : ...',
                'options' => [
                    'Restoran',
                    'Dapur hotel',
                    'Masakan atau hidangan',
                    'Pisau daging',
                    'Buku menu',
                ],
                'correct_option' => 2, 
            ],
            5 => [
                'dimension' => 'Verbal',
                'question' => 'Sinonim (Persamaan Kata): AMBIGUITAS = ...',
                'options' => [
                    'Kejelasan informasi yang terukur',
                    'Keraguan dalam bertindak',
                    'Makna ganda atau ketaksaan arti',
                    'Kesalahan cetak pada dokumen',
                    'Keputusan mutlak pimpinan',
                ],
                'correct_option' => 2, 
            ],
            6 => [
                'dimension' => 'Verbal',
                'question' => 'Antonim (Lawan Kata): SPORADIS >< ...',
                'options' => [
                    'Kerap kali, teratur, atau terus-menerus',
                    'Jarang atau sesekali terjadi',
                    'Menyebar ke segala arah',
                    'Berhenti seketika tanpa tanda',
                    'Tersembunyi di balik layar',
                ],
                'correct_option' => 0, 
            ],
            7 => [
                'dimension' => 'Verbal',
                'question' => 'Analogi Kata: MIKROSKOP : BAKTERI = TELESKOP : ...',
                'options' => [
                    'Kuman patogen',
                    'Benda langit atau bintang',
                    'Kacamata baca',
                    'Laboratorium sains',
                    'Cahaya inframerah',
                ],
                'correct_option' => 1, 
            ],
            8 => [
                'dimension' => 'Verbal',
                'question' => 'Analogi Kata: KORAN : MAJALAH = SURAT KABAR : ...',
                'options' => [
                    'Buletin atau majalah berkala',
                    'Sambungan telepon seluler',
                    'Siaran radio gelombang FM',
                    'Layar monitor komputer',
                    'Kantor pos pemeriksa',
                ],
                'correct_option' => 0, 
            ],

            
            
            9 => [
                'dimension' => 'Numerik',
                'question' => 'Deret Angka: 2, 4, 8, 16, 32, ... Angka berikutnya adalah:',
                'options' => [
                    '48',
                    '60',
                    '64',
                    '72',
                    '128',
                ],
                'correct_option' => 2, 
            ],
            10 => [
                'dimension' => 'Numerik',
                'question' => 'Deret Angka: 3, 5, 8, 12, 17, ... Angka berikutnya adalah:',
                'options' => [
                    '21',
                    '22',
                    '23',
                    '24',
                    '25',
                ],
                'correct_option' => 2, 
            ],
            11 => [
                'dimension' => 'Numerik',
                'question' => 'Deret Angka: 80, 40, 20, 10, ... Angka berikutnya adalah:',
                'options' => [
                    '2',
                    '5',
                    '8',
                    '1',
                    '0',
                ],
                'correct_option' => 1, 
            ],
            12 => [
                'dimension' => 'Numerik',
                'question' => 'Deret Angka Pola Kuadrat: 1, 4, 9, 16, 25, ... Angka berikutnya adalah:',
                'options' => [
                    '30',
                    '35',
                    '36',
                    '49',
                    '40',
                ],
                'correct_option' => 2, 
            ],
            13 => [
                'dimension' => 'Numerik',
                'question' => 'Aritmatika Terapan: Sebuah produk setelah mendapat diskon 20% dijual dengan harga Rp 200.000. Berapakah harga produk tersebut sebelum diskon?',
                'options' => [
                    'Rp 220.000',
                    'Rp 240.000',
                    'Rp 250.000',
                    'Rp 260.000',
                    'Rp 280.000',
                ],
                'correct_option' => 2, 
            ],
            14 => [
                'dimension' => 'Numerik',
                'question' => 'Aritmatika Terapan: 5 orang pekerja dapat menyelesaikan perakitan proyek dalam waktu 12 hari. Berapa hari yang dibutuhkan jika proyek tersebut dikerjakan oleh 6 orang pekerja dengan kecepatan yang sama?',
                'options' => [
                    '8 hari',
                    '10 hari',
                    '11 hari',
                    '14 hari',
                    '15 hari',
                ],
                'correct_option' => 1, 
            ],
            15 => [
                'dimension' => 'Numerik',
                'question' => 'Komputasi Persentase: 15% dari 240 adalah ...',
                'options' => [
                    '30',
                    '32',
                    '36',
                    '40',
                    '42',
                ],
                'correct_option' => 2, 
            ],
            16 => [
                'dimension' => 'Numerik',
                'question' => 'Kecepatan & Jarak: Sebuah armada logistik menempuh jarak 180 km dalam waktu tepat 3 jam. Berapakah kecepatan rata-rata kendaraan tersebut?',
                'options' => [
                    '50 km/jam',
                    '55 km/jam',
                    '60 km/jam',
                    '65 km/jam',
                    '70 km/jam',
                ],
                'correct_option' => 2, 
            ],

            
            
            17 => [
                'dimension' => 'Logika',
                'question' => "Silogisme:\nSemua karyawan PT Maju mengenakan seragam biru pada hari Senin.\nBudi adalah karyawan PT Maju.\nHari ini adalah hari Senin.\nKesimpulan yang sah dan logis adalah:",
                'options' => [
                    'Budi mungkin mengenakan kemeja bebas',
                    'Budi mengenakan seragam biru hari ini',
                    'Budi tidak masuk kantor hari ini',
                    'Hanya staf senior yang wajib mengenakan seragam biru',
                    'Tidak dapat ditarik kesimpulan apa pun',
                ],
                'correct_option' => 1, 
            ],
            18 => [
                'dimension' => 'Logika',
                'question' => "Penalaran Deduktif:\nSemua burung memiliki sayap.\nSebagian unggas berkategori burung tidak dapat terbang.\nPenguin adalah burung.\nKesimpulan yang benar adalah:",
                'options' => [
                    'Penguin pasti dapat terbang tinggi di udara',
                    'Penguin memiliki sayap',
                    'Penguin bukan termasuk kelompok unggas',
                    'Semua hewan bersayap dapat terbang bebas',
                    'Penguin tidak memiliki struktur sayap',
                ],
                'correct_option' => 1, 
            ],
            19 => [
                'dimension' => 'Logika',
                'question' => "Logika Spasial Posisi:\nDi sebuah kantor, ruang direktur berada di sebelah timur ruang rapat.\nRuang keuangan berada di sebelah barat ruang rapat.\nDi manakah posisi ruang keuangan relatif terhadap ruang direktur?",
                'options' => [
                    'Tepat di sebelah timur ruang direktur',
                    'Di sebelah barat ruang direktur',
                    'Tepat berada di atas ruang direktur',
                    'Di antara ruang rapat dan direktur',
                    'Posisinya sama dengan ruang direktur',
                ],
                'correct_option' => 1, 
            ],
            20 => [
                'dimension' => 'Logika',
                'question' => "Silogisme Negasi:\nTidak ada peserta ujian yang diizinkan membuka ponsel selama tes.\nDoni adalah salah satu peserta ujian.\nKesimpulan yang paling tepat adalah:",
                'options' => [
                    'Doni boleh membuka ponsel jika ada panggilan darurat',
                    'Doni tidak diizinkan membuka ponsel selama tes berlangsung',
                    'Doni tidak membawa ponsel ke ruangan',
                    'Semua orang di ruangan selain Doni membuka ponsel',
                    'Doni dapat menggunakan ponsel setelah 10 menit pertama',
                ],
                'correct_option' => 1, 
            ],
            21 => [
                'dimension' => 'Logika',
                'question' => "Penalaran Implikasi:\nJika hari hujan lebat, maka jalanan raya menjadi basah dan licin.\nHari ini jalan raya basah dan licin.\nKesimpulan yang paling logis adalah:",
                'options' => [
                    'Pasti baru saja terjadi hujan lebat',
                    'Belum tentu karena hujan lebat; jalan raya bisa licin karena tumpahan air atau sebab lain',
                    'Hari ini dipastikan cerah terik tanpa awan',
                    'Tidak ada satu pun kendaraan yang melintas di jalan',
                    'Hujan lebat tidak pernah menyebabkan jalanan basah',
                ],
                'correct_option' => 1, 
            ],
            22 => [
                'dimension' => 'Logika',
                'question' => "Penalaran Kategori Biologis:\nSemua mamalia bernapas dengan paru-paru dan melahirkan/menyusui anaknya.\nLumba-lumba adalah hewan mamalia air.\nMaka kesimpulan yang sah adalah:",
                'options' => [
                    'Lumba-lumba bernapas menggunakan insang luar',
                    'Lumba-lumba bernapas dengan paru-paru dan menyusui anaknya',
                    'Lumba-lumba berkembang biak dengan bertelur di pasir',
                    'Lumba-lumba termasuk keluarga ikan bersisik',
                    'Sebagian lumba-lumba bukan mamalia air',
                ],
                'correct_option' => 1, 
            ],
            23 => [
                'dimension' => 'Logika',
                'question' => "Hubungan Sebab - Akibat:\n(1) Tingkat penjualan produk perusahaan melonjak 300% dalam sebulan.\n(2) Perusahaan meluncurkan kampanye diskon besar dan promosi viral di media sosial.\nHubungan yang paling masuk akal antara kedua pernyataan tersebut adalah:",
                'options' => [
                    'Pernyataan 1 adalah penyebab dan pernyataan 2 adalah akibatnya',
                    'Pernyataan 2 adalah penyebab utama dan pernyataan 1 adalah akibatnya',
                    'Kedua pernyataan adalah akibat dari faktor inflasi ekonomi',
                    'Kedua pernyataan tidak memiliki kaitan logis satu sama lain',
                    'Pernyataan 1 mendahului timbulnya penyebab pernyataan 2',
                ],
                'correct_option' => 1, 
            ],

            
            
            24 => [
                'dimension' => 'Spasial',
                'question' => "Rotasi Arah Jarum Jam:\nSebuah jarum penunjuk mula-mula menghadap ke arah Jam 12 (Utara).\nKemudian berputar searah jarum jam sebesar 90° ke arah Jam 3 (Timur).\nLalu berputar lagi 90° ke arah Jam 6 (Selatan).\nJika pola rotasi 90° searah jarum jam dilanjutkan sekali lagi, jarum akan menghadap ke arah:",
                'options' => [
                    'Jam 12 (Utara)',
                    'Jam 9 (Barat)',
                    'Jam 6 (Selatan)',
                    'Jam 3 (Timur)',
                    'Jam 1.30 (Timur Laut)',
                ],
                'correct_option' => 1, 
            ],
            25 => [
                'dimension' => 'Spasial',
                'question' => "Deret Geometri Bentuk:\nPerhatikan urutan bangun datar berikut:\nSegitiga (3 sisi) ➔ Segiempat (4 sisi) ➔ Segilima (5 sisi) ➔ Segienam (6 sisi).\nBangun datar yang logis melanjutkan pola pertambahan sisi tersebut adalah:",
                'options' => [
                    'Lingkaran sempurna',
                    'Segitujuh (Heptagon, 7 sisi)',
                    'Segidelapan (Oktagon, 8 sisi)',
                    'Bintang lima sudut',
                    'Trapesium sama kaki',
                ],
                'correct_option' => 1, 
            ],
            26 => [
                'dimension' => 'Spasial',
                'question' => "Pola Simbolik Bergantian:\nPerhatikan pola simbol visual berikut:\n○ , ● , ○ , ● , ○ , ...\nSimbol berikutnya untuk melengkapi pola adalah:",
                'options' => [
                    '○ (Lingkaran putih)',
                    '● (Lingkaran hitam penuh)',
                    '■ (Kotak hitam penuh)',
                    '▲ (Segitiga hitam)',
                    '□ (Kotak putih)',
                ],
                'correct_option' => 1, 
            ],
            27 => [
                'dimension' => 'Spasial',
                'question' => "Pola Titik Sudut Bangun Ruang:\nTitik (0 dimensi) ➔ Segmen garis (2 titik ujung) ➔ Segitiga datar (3 titik sudut) ➔ Segiempat datar (4 titik sudut).\nBerapakah jumlah titik sudut pada bangun datar berikutnya dalam deret ini?",
                'options' => [
                    '4 titik sudut',
                    '5 titik sudut (Segilima / Pentagon)',
                    '6 titik sudut (Segienam)',
                    '7 titik sudut',
                    '8 titik sudut',
                ],
                'correct_option' => 1, 
            ],
            28 => [
                'dimension' => 'Spasial',
                'question' => "Refleksi Cermin Datar:\nHuruf kecil 'b' jika dicerminkan terhadap cermin vertikal tegak lurus di sisi kanannya akan tampak persis seperti huruf:",
                'options' => [
                    'b (tetap sama)',
                    'd',
                    'p',
                    'q',
                    'c',
                ],
                'correct_option' => 1, 
            ],
            29 => [
                'dimension' => 'Spasial',
                'question' => "Pola Grid Matriks 2x2:\nDalam kotak matriks 2x2:\n- Kotak kiri-atas berwarna PUTIH\n- Kotak kanan-atas berwarna HITAM\n- Kotak kiri-bawah berwarna HITAM\nAgar matriks memiliki simetri diagonal yang konsisten, kotak kanan-bawah seharusnya berwarna:",
                'options' => [
                    'Hitam pekat',
                    'Putih bersih',
                    'Abu-abu bayangan',
                    'Garis-garis silang',
                    'Kosong transparan',
                ],
                'correct_option' => 1, 
            ],
            30 => [
                'dimension' => 'Spasial',
                'question' => "Bangun Ruang Beraturan:\nSebuah kubus memiliki 6 bidang sisi berbentuk persegi.\nSebuah limas segitiga (Tetrahedron beraturan) memiliki berapa bidang sisi?",
                'options' => [
                    '3 bidang sisi segitiga',
                    '4 bidang sisi segitiga',
                    '5 bidang sisi (1 alas + 4 tegak)',
                    '6 bidang sisi segitiga',
                    '8 bidang sisi segitiga',
                ],
                'correct_option' => 1, 
            ],
        ];
    }

    public static function createOrUpdateAssessment(?User $creator = null): Assessment
    {
        $assessment = Assessment::firstOrNew(['title' => 'Tes Bakat Kognitif & IQ (30 Soal)']);

        $assessment->fill([
            'description' => 'Tes potensi kognitif dan bakat skolastik standar untuk mengukur estimasi IQ, daya nalar logis, kemampuan numerik, pemahaman verbal, dan visualisasi spasial.',
            'instructions' => "Tes ini terdiri dari 30 soal pilihan ganda yang terbagi ke dalam 4 dimensi kognitif:\n1. Kemampuan Verbal (Soal 1–8)\n2. Kemampuan Numerik (Soal 9–16)\n3. Logika & Penalaran (Soal 17–23)\n4. Spasial & Figural (Soal 24–30)\n\nPetunjuk:\n- Pilih 1 jawaban yang paling benar dari opsi A sampai E.\n- Waktu pengerjaan maksimal 25 menit.\n- Jawablah secara teliti dan gunakan kemampuan terbaik Anda.",
            'duration_minutes' => 25,
            'passing_score' => 60,
            'status' => Assessment::STATUS_PUBLISHED,
            'created_by' => $creator?->id ?? $assessment->created_by ?? User::first()?->id,
        ]);

        $assessment->save();

        $assessment->questions()->delete();

        foreach (self::questions() as $index => $item) {
            $assessment->questions()->create([
                'type' => AssessmentQuestion::TYPE_MULTIPLE_CHOICE,
                'question' => $item['question'],
                'dimension' => $item['dimension'],
                'options' => $item['options'],
                'correct_option' => $item['correct_option'],
                'points' => 1,
                'position' => $index,
            ]);
        }

        QuestionBankSyncService::syncStandardQuestions($creator);

        return $assessment;
    }
}
