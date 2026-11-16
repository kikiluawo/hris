<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\User;

class DiscBank
{
    
    public static function questions(): array
    {
        return [
            1 => [
                'question' => 'Nomor 1: Gambaran Diri',
                'options' => [
                    'Gampangan, mudah setuju, santai',
                    'Percaya, mudah percaya pada orang lain',
                    'Petualang, suka mengambil risiko, mandiri',
                    'Toleran, penuh rasa hormat, cermat',
                ],
                'dimension' => 'S,I,D,C',
            ],
            2 => [
                'question' => 'Nomor 2: Gambaran Diri',
                'options' => [
                    'Lembut suara, tenang, menjaga harmoni',
                    'Optimis, ceria, penuh harapan baik',
                    'Bertenaga, berani, berinisiatif tinggi',
                    'Cermat, teliti, rapi dan teratur',
                ],
                'dimension' => 'S,I,D,C',
            ],
            3 => [
                'question' => 'Nomor 3: Gambaran Diri',
                'options' => [
                    'Suka menyemangati orang lain, ekspresif',
                    'Menyukai kerapian, teratur, taat aturan',
                    'Tegas, berkemauan keras, pantang menyerah',
                    'Mau mengalah, tidak suka menonjolkan diri',
                ],
                'dimension' => 'I,C,D,S',
            ],
            4 => [
                'question' => 'Nomor 4: Gambaran Diri',
                'options' => [
                    'Suka berhati-hati, memikirkan risiko',
                    'Mudah memutuskan, kompetitif, gigih',
                    'Penuh keyakinan, persuasif, menyenangkan',
                    'Puas dengan keadaan, stabil, sabar',
                ],
                'dimension' => 'C,D,I,S',
            ],
            5 => [
                'question' => 'Nomor 5: Gambaran Diri',
                'options' => [
                    'Menghibur, humoris, memikat orang banyak',
                    'Tepat waktu, teliti, memegang standar tinggi',
                    'Pemberani, tidak takut tantangan besar',
                    'Pendiam, tenang dalam situasi tegang',
                ],
                'dimension' => 'I,C,D,S',
            ],
            6 => [
                'question' => 'Nomor 6: Gambaran Diri',
                'options' => [
                    'Suka membantu orang lain tanpa pamrih',
                    'Sangat analitis, kritis dan menimbang data',
                    'Suka memimpin dan mengambil alih kendali',
                    'Ramah, hangat, cepat akrab dengan siapa pun',
                ],
                'dimension' => 'S,C,D,I',
            ],
            7 => [
                'question' => 'Nomor 7: Gambaran Diri',
                'options' => [
                    'Menuntut kesempurnaan dan kepatuhan SOP',
                    'Cepat bertindak, berorientasi hasil akhir',
                    'Pandai bergaul, luwes dalam komunikasi',
                    'Sabar mendengarkan keluhan orang lain',
                ],
                'dimension' => 'C,D,I,S',
            ],
            8 => [
                'question' => 'Nomor 8: Gambaran Diri',
                'options' => [
                    'Bicara terus terang, lugas, tanpa basa-basi',
                    'Pandai memikat dan mengajak orang lain',
                    'Setia kawan, dapat diandalkan setiap saat',
                    'Berhati-hati dalam membuat keputusan baru',
                ],
                'dimension' => 'D,I,S,C',
            ],
            9 => [
                'question' => 'Nomor 9: Gambaran Diri',
                'options' => [
                    'Antusias, berenergi tinggi, penuh semangat',
                    'Sistematis, terencana secara mendalam',
                    'Percaya diri dan berani menghadapi konflik',
                    'Mudah beradaptasi dan suka kedamaian',
                ],
                'dimension' => 'I,C,D,S',
            ],
            10 => [
                'question' => 'Nomor 10: Gambaran Diri',
                'options' => [
                    'Fokus pada pencapaian target dan prestasi',
                    'Menjaga hubungan baik antar manusia',
                    'Konsisten, menyukai rutinitas yang pasti',
                    'Kritis terhadap kesalahan sekecil apa pun',
                ],
                'dimension' => 'D,I,S,C',
            ],
            11 => [
                'question' => 'Nomor 11: Gambaran Diri',
                'options' => [
                    'Spontan, menyenangkan, suka kejutan',
                    'Rendah hati, tidak suka pamer atau sombong',
                    'Disiplin tinggi, patuh pada etika dan aturan',
                    'Menuntut hasil terbaik dari siapa pun',
                ],
                'dimension' => 'I,S,C,D',
            ],
            12 => [
                'question' => 'Nomor 12: Gambaran Diri',
                'options' => [
                    'Mampu menjaga rahasia, sangat dipercaya',
                    'Akurat dalam mengolah angka dan informasi',
                    'Cepat mengambil inisiatif saat yang lain ragu',
                    'Pandai mencairkan suasana yang kaku',
                ],
                'dimension' => 'S,C,D,I',
            ],
            13 => [
                'question' => 'Nomor 13: Gambaran Diri',
                'options' => [
                    'Mandiri, tidak bergantung pada bantuan orang lain',
                    'Suka menginspirasi dan memotivasi tim',
                    'Menyukai stabilitas dan menghindari perubahan drastis',
                    'Memeriksa kembali pekerjaan agar bebas dari cela',
                ],
                'dimension' => 'D,I,S,C',
            ],
            14 => [
                'question' => 'Nomor 14: Gambaran Diri',
                'options' => [
                    'Menghargai tradisi, tertib, metodis',
                    'Suka memegang kendali atas situasi kerja',
                    'Suka memuji dan memberi apresiasi pada rekan',
                    'Mau berkompromi demi kebaikan bersama',
                ],
                'dimension' => 'C,D,I,S',
            ],
            15 => [
                'question' => 'Nomor 15: Gambaran Diri',
                'options' => [
                    'Optimis menyongsong masa depan',
                    'Sabar menunggu giliran, tidak tergesa-gesa',
                    'Objektif, berpegang pada fakta bukan asumsi',
                    'Cepat bergerak dan bertindak segera',
                ],
                'dimension' => 'I,S,C,D',
            ],
            16 => [
                'question' => 'Nomor 16: Gambaran Diri',
                'options' => [
                    'Berani bersaing dan ingin selalu menang',
                    'Hangat dan mudah tersentuh oleh orang lain',
                    'Menyukai rasa aman dan ketenangan batin',
                    'Ketat mematuhi prosedur operasional baku',
                ],
                'dimension' => 'D,I,S,C',
            ],
            17 => [
                'question' => 'Nomor 17: Gambaran Diri',
                'options' => [
                    'Tidak takut mengambil keputusan yang tidak populer',
                    'Senang berinteraksi dan berjejaring luas',
                    'Tulus dalam mendukung keberhasilan rekan kerja',
                    'Berpikir mendalam sebelum memberikan pendapat',
                ],
                'dimension' => 'D,I,S,C',
            ],
            18 => [
                'question' => 'Nomor 18: Gambaran Diri',
                'options' => [
                    'Penuh pengabdian dan loyalitas pada tim',
                    'Rapi dalam dokumentasi dan arsip kerja',
                    'Ambisius untuk meraih target maksimal',
                    'Mudah memaafkan dan berpikiran positif',
                ],
                'dimension' => 'S,C,D,I',
            ],
            19 => [
                'question' => 'Nomor 19: Gambaran Diri',
                'options' => [
                    'Mengutamakan keharmonisan dan perasaan orang lain',
                    'Menuntut hasil kerja cepat dan tanpa kompromi',
                    'Memiliki selera humor dan menyenangkan',
                    'Menuntut akurasi dan presisi tinggi',
                ],
                'dimension' => 'S,D,I,C',
            ],
            20 => [
                'question' => 'Nomor 20: Gambaran Diri',
                'options' => [
                    'Menimbang segala kemungkinan sebelum memutuskan',
                    'Menghadapi rintangan dengan semangat membara',
                    'Mudah bergaul dengan berbagai kalangan',
                    'Menerima keadaan dengan lapang dada',
                ],
                'dimension' => 'C,D,I,S',
            ],
            21 => [
                'question' => 'Nomor 21: Gambaran Diri',
                'options' => [
                    'Ceria dan membawa energi segar di mana pun berada',
                    'Pendengar setia yang tidak mudah menghakimi',
                    'Berhati-hati dalam menjaga komitmen kerja',
                    'Berani mengambil risiko untuk kemajuan',
                ],
                'dimension' => 'I,S,C,D',
            ],
            22 => [
                'question' => 'Nomor 22: Gambaran Diri',
                'options' => [
                    'Menyukai pekerjaan dengan ritme yang stabil',
                    'Sangat terstruktur, metodis, dan teratur',
                    'Berjiwa pemimpin dan suka membimbing orang lain',
                    'Pandai bernegosiasi dan meyakinkan orang lain',
                ],
                'dimension' => 'S,C,D,I',
            ],
            23 => [
                'question' => 'Nomor 23: Gambaran Diri',
                'options' => [
                    'Senang berbagi cerita dan mengobrol santai',
                    'Siap memperjuangkan target sampai tercapai',
                    'Rela membantu rekan yang sedang kesulitan',
                    'Teliti dan telaten dalam memeriksa kekeliruan',
                ],
                'dimension' => 'I,D,S,C',
            ],
            24 => [
                'question' => 'Nomor 24: Gambaran Diri',
                'options' => [
                    'Memastikan semua langkah kerja sesuai prosedur resmi',
                    'Menyukai suasana kerja yang tenang dan bersahabat',
                    'Menularkan optimisme dan gairah berkarya',
                    'Berani bertanggung jawab atas dampak keputusan besar',
                ],
                'dimension' => 'C,S,I,D',
            ],
        ];
    }

    public static function createOrUpdateAssessment(?User $creator = null): Assessment
    {
        $assessment = Assessment::firstOrNew(['title' => 'Tes Kepribadian DISC (24 Soal)']);

        $assessment->fill([
            'description' => 'Tes profil perilaku dan kepribadian standar DISC untuk memetakan karakter kerja, gaya komunikasi, serta grafik adaptasi lingkungan kerja.',
            'instructions' => "Tes ini terdiri dari 24 nomor pernyataan.\nPada setiap nomor, terdapat 4 pilihan pernyataan.\n\nPetunjuk:\n1. Pilih 1 pernyataan yang PALING (P / Most) menggambarkan diri Anda.\n2. Pilih 1 pernyataan yang PALING TIDAK / KURANG (K / Least) menggambarkan diri Anda.\n3. Jangan memilih pernyataan yang sama untuk P dan K.\n4. Tidak ada jawaban benar atau salah. Jawablah secara spontan dan jujur sesuai diri Anda yang sebenarnya.",
            'duration_minutes' => 15,
            'passing_score' => 0,
            'status' => Assessment::STATUS_PUBLISHED,
            'created_by' => $creator?->id ?? $assessment->created_by ?? User::first()?->id,
        ]);

        $assessment->save();

        $assessment->questions()->delete();

        foreach (self::questions() as $index => $item) {
            $assessment->questions()->create([
                'type' => AssessmentQuestion::TYPE_DISC,
                'question' => $item['question'],
                'dimension' => $item['dimension'] ?? 'S,I,D,C',
                'options' => $item['options'],
                'correct_option' => null,
                'points' => 0,
                'position' => $index,
            ]);
        }

        QuestionBankSyncService::syncStandardQuestions($creator);

        return $assessment;
    }
}
