<?php

namespace Database\Seeders;

use App\Models\AssessmentQuestion;
use App\Models\QuestionBank;
use Illuminate\Database\Seeder;

class PsychotestBankSeeder extends Seeder
{
    private const LIKERT = [
        'Sangat tidak sesuai',
        'Tidak sesuai',
        'Cukup sesuai',
        'Sesuai',
        'Sangat sesuai',
    ];

    private const ITEMS = [
        'Kepemimpinan' => [
            'Saya lebih suka mengambil alih tanggung jawab saat tim bingung harus bertindak.',
            'Saya mampu memberi masukan yang jujur tanpa membuat orang merasa dihakimi.',
            'Saya tenang mengambil keputusan ketika informasi yang tersedia belum lengkap.',
            'Saya dengan sengaja memberi kesempatan anggota yang pendiam untuk berbicara.',
        ],
        'Kerja Sama' => [
            'Saya rela menyesuaikan cara kerja saya agar tim lebih mudah berjalan.',
            'Pendapat rekan kerja sering saya jadikan bahan pertimbangan, walau saya tidak setuju.',
            'Saya lebih mementingkan hasil bersama daripada membuktikan cara saya yang benar.',
            'Saya mau membantu pekerjaan yang bukan tugas saya jika rekan sedang kewalahan.',
        ],
        'Komunikasi' => [
            'Saya menjelaskan hal rumit dengan bahasa yang bisa dipahami orang awam.',
            'Saya lebih banyak mendengar daripada memotong pembicaraan saat orang menyampaikan masalah.',
            'Saya menyampaikan kabar buruk langsung, tidak menunggu sampai orang lain bertanya.',
            'Saya memastikan orang yang saya ajak bicara benar-benar memahami maksud saya.',
        ],
        'Integritas' => [
            'Saya tetap mengikuti aturan walau tidak ada yang mengawasi.',
            'Saya mengakui kesalahan sendiri walaupun berdampak pada penilaian orang terhadap saya.',
            'Saya tidak menyampaikan informasi yang saya belum yakin kebenarannya.',
            'Saya menolak tawaran yang menguntungkan saya tapi merugikan perusahaan.',
        ],
        'Ketahanan Kerja' => [
            'Saya tetap fokus bekerja saat deadline datang bersamaan.',
            'Kritik pedas tidak membuat saya kehilangan semangat untuk waktu lama.',
            'Saya bisa kembali produktif setelah menghadapi kegagalan.',
            'Tekanan tinggi tidak membuat saya membalas dengan emosi.',
        ],
        'Motivasi & Disiplin' => [
            'Saya menyelesaikan pekerjaan lebih dulu sebelum hal yang saya nikmati.',
            'Saya menetapkan target pribadi tanpa harus diminta atasan.',
            'Saya tetap menjalankan rutinitas walau sedang tidak bersemangat.',
            'Saya mencari sendiri hal baru yang perlu saya pelajari di pekerjaan ini.',
        ],
    ];

    public function run(): void
    {
        foreach (self::ITEMS as $dimension => $questions) {
            foreach ($questions as $question) {
                QuestionBank::query()->firstOrCreate(
                    ['question' => $question],
                    [
                        'type' => AssessmentQuestion::TYPE_SCALE,
                        'dimension' => $dimension,
                        'options' => self::LIKERT,
                        'points' => 1,
                    ]
                );
            }
        }
    }
}
