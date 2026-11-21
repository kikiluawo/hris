<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobOpening;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestJobOpeningsSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::with(['departments.positions'])->get();

        $openings = [
            [
                'title' => 'Service Advisor',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'D3',
                'min_experience' => 2,
                'screening_threshold' => 75,
                'skills' => ['Service Advisor', 'Customer Handling', 'Estimasi Biaya Perbaikan', 'Komunikasi', 'Microsoft Office'],
                'description' => "Bertanggung jawab menyambut dan melayani pelanggan yang datang ke bengkel resmi Majesty Group.\n\nTugas & Tanggung Jawab:\n• Menerima dan mendengarkan keluhan pelanggan terkait kendala kendaraan.\n• Melakukan inspeksi awal bersama pelanggan dan menyusun perkiraan waktu serta estimasi biaya perbaikan.\n• Berkoordinasi dengan Foreman dan Teknisi selama proses servis berlangsung.\n• Menginformasikan perkembangan pekerjaan kepada pelanggan bila terdapat penambahan suku cadang.\n• Memastikan kendaraan telah siap dan menyerahkan kembali kepada pelanggan dengan ramah.",
            ],
            [
                'title' => 'Mekanik Mobil Senior',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'SMA/SMK',
                'min_experience' => 3,
                'screening_threshold' => 70,
                'skills' => ['Engine Overhaul', 'Kelistrikan Mobil', 'Scanning ECU', 'Tune Up', 'Spooring Balancing'],
                'description' => "Melakukan perawatan berkala dan perbaikan berat kendaraan roda empat sesuai SOP.\n\nTugas & Tanggung Jawab:\n• Mendiagnosa gangguan mesin, rem, suspensi, dan transmisi menggunakan scanner diagnostik.\n• Menjalankan pekerjaan turun mesin (overhaul) dan kalibrasi sistem injeksi/ECU.\n• Memastikan kualitas pekerjaan perbaikan memenuhi standar keselamatan dan kebersihan kerja (K3).\n• Menguji coba performa kendaraan pasca servis sebelum diserahkan ke pelanggan.",
            ],
            [
                'title' => 'Accounting Staff',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'S1',
                'min_experience' => 1,
                'screening_threshold' => 75,
                'skills' => ['Jurnal Akuntansi', 'Laporan Keuangan', 'Rekonsiliasi Bank', 'Accurate', 'Pajak PPh/PPn'],
                'description' => "Bertanggung jawab atas pencatatan transaksi keuangan harian dan rekonsiliasi pembukuan perusahaan.\n\nTugas & Tanggung Jawab:\n• Memeriksa kelengkapan dokumen transaksi kas keluar dan kas masuk.\n• Melakukan input jurnal akuntansi harian ke sistem ERP / Accurate.\n• Menyusun rekonsiliasi rekening koran bulanan.\n• Menghitung dan menyiapkan laporan pajak masa (PPh 21, 23, PPN).\n• Membantu penyusunan laporan laba rugi dan neraca berkala.",
            ],
            [
                'title' => 'Digital Marketing & SEO Specialist',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'S1',
                'min_experience' => 2,
                'screening_threshold' => 70,
                'skills' => ['Meta Ads', 'Google Ads', 'SEO/SEM', 'Content Strategy', 'Google Analytics', 'Copywriting'],
                'description' => "Mengelola strategi pemasaran digital terpadu untuk mendongkrak visibilitas merek dan perolehan prospek calon konsumen.\n\nTugas & Tanggung Jawab:\n• Merancang, menjalankan, dan mengevaluasi kampanye berbayar di Meta Ads, Google Ads, dan TikTok Ads.\n• Mengoptimalkan performa SEO on-page dan off-page pada portal web resmi Majesty Group.\n• Menganalisis traffic web, rasio konversi (conversion rate), dan ROI kampanye iklan digital.\n• Berkolaborasi dengan desainer grafis dan videografer dalam memproduksi materi iklan berkualitas tinggi.",
            ],
            [
                'title' => 'Sales Executive Mobil',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'SMA/SMK',
                'min_experience' => 1,
                'screening_threshold' => 65,
                'skills' => ['B2C Sales', 'Negosiasi', 'Komunikasi Persuasif', 'Follow-up Prospek', 'Presentasi Produk', 'SIM A'],
                'description' => "Mencapai target penjualan unit kendaraan baru melalui pendekatan profesional dan relasi yang kuat dengan pelanggan.\n\nTugas & Tanggung Jawab:\n• Menemukan prospek pelanggan baru melalui pameran, event otomotif, dan relasi komunitas.\n• Memberikan konsultasi detail mengenai tipe kendaraan, fitur keunggulan, serta skema pembiayaan/kredit.\n• Mendampingi calon pembeli dalam sesi test drive.\n• Mengurus berkas pembiayaan leasing dan mengawal hingga proses serah terima unit (handover).",
            ],
            [
                'title' => 'IT Support & Network Administrator',
                'job_type' => JobOpening::TYPE_FULL_TIME,
                'min_education' => 'D3',
                'min_experience' => 1,
                'screening_threshold' => 70,
                'skills' => ['Troubleshooting Hardware & Software', 'Mikrotik Router', 'LAN/WAN/WiFi', 'CCTV Setup', 'Windows Server'],
                'description' => "Menjaga kelancaran operasional teknologi informasi, infrastruktur jaringan kabel/nirkabel, dan perangkat keras kantor.\n\nTugas & Tanggung Jawab:\n• Memastikan ketersediaan koneksi internet dan jaringan lokal (LAN/WiFi) di seluruh cabang kantor.\n• Membantu penyelesaian kendala teknis PC, printer kasir, scanner, dan perangkat operasional harian.\n• Melakukan konfigurasi router Mikrotik, firewall, dan sistem keamanan akses intranet.\n• Mengelola perawatan rutin kamera pengawas (CCTV) dan absensi biometrik.",
            ],
            [
                'title' => 'Customer Service / Kasir Showroom',
                'job_type' => JobOpening::TYPE_PART_TIME,
                'min_education' => 'SMA/SMK',
                'min_experience' => 0,
                'screening_threshold' => 65,
                'skills' => ['Pelayanan Pelanggan', 'Kasir & POS System', 'Pembukuan Kas Kecil', 'Ramah & Komunikatif'],
                'description' => "Posisi paruh waktu (part-time) untuk melayani transaksi pembayaran pelanggan di showroom dan bengkel pada jadwal shift akhir pekan atau jam sibuk.\n\nTugas & Tanggung Jawab:\n• Menyambut tamu dan pelanggan showroom dengan ramah dan penuh keramahan.\n• Memproses transaksi pembayaran tunai, kartu debit/kredit, maupun QRIS menggunakan sistem POS.\n• Membuat laporan rekapitulasi penerimaan kas di akhir shift kerja.\n• Menjawab pertanyaan umum pelanggan mengenai jam operasional dan lokasi fasilitas.",
            ],
            [
                'title' => 'Content Creator & Social Media Admin',
                'job_type' => JobOpening::TYPE_PART_TIME,
                'min_education' => 'D3',
                'min_experience' => 1,
                'screening_threshold' => 70,
                'skills' => ['CapCut', 'Premiere Pro', 'Canva', 'TikTok Content', 'Instagram Reels', 'Copywriting'],
                'description' => "Posisi paruh waktu (part-time) fleksibel untuk mengkreasikan konten video kreatif promosi kendaraan dan promo bengkel Majesty Group.\n\nTugas & Tanggung Jawab:\n• Memproduksi 3-4 video pendek mingguan untuk TikTok dan Instagram Reels.\n• Mengambil video dan foto kegiatan di dealer, bengkel, serta liputan serah terima unit kendaraan.\n• Menjawab direct message (DM) dan interaksi komentar follower di media sosial.\n• Memantau tren audio dan topik viral terkini yang relevan dengan industri otomotif.",
            ],
            [
                'title' => 'HR Operations & Recruitment Intern',
                'job_type' => JobOpening::TYPE_INTERNSHIP,
                'min_education' => 'S1',
                'min_experience' => 0,
                'screening_threshold' => 65,
                'skills' => ['Talent Sourcing', 'Administrasi HR', 'Psikotes', 'Microsoft Excel', 'Komunikasi Interpersonal'],
                'description' => "Program magang (internship) berdurasi 3 hingga 6 bulan untuk mahasiswa tingkat akhir atau lulusan baru jurusan Psikologi, Manajemen, atau Administrasi Bisnis.\n\nTugas & Tanggung Jawab:\n• Membantu proses seleksi awal CV pelamar di portal karir perusahaan.\n• Mengkoordinasikan jadwal pelaksanaan psikotes online dan sesi wawancara pelamar.\n• Membantu penyiapan kelengkapan dokumen orientasi onboarding karyawan baru.\n• Berpartisipasi dalam pemutakhiran data absensi dan arsip kepegawaian HRD.",
            ],
            [
                'title' => 'Mechanical Engineering Intern',
                'job_type' => JobOpening::TYPE_INTERNSHIP,
                'min_education' => 'SMA/SMK',
                'min_experience' => 0,
                'screening_threshold' => 60,
                'skills' => ['Dasar Otomotif Kendaraan', 'Membaca Manual Book', 'Diagnosa Komputer', 'Disiplin K3'],
                'description' => "Program magang teknis bagi siswa/mahasiswa jurusan Teknik Otomotif atau Teknik Mesin yang ingin memperdalam pengalaman praktik langsung di industri otomotif.\n\nTugas & Tanggung Jawab:\n• Mengamati dan membantu teknisi senior dalam proses tune-up, servis berkala, dan penggantian oli kendaraan.\n• Mempelajari penggunaan peralatan bengkel modern, tire changer, spooring 3D, dan car scanner.\n• Menerapkan prinsip 5R (Ringkas, Rapi, Resik, Rawat, Rajin) dan keselamatan kerja di bengkel.\n• Menyusun laporan kegiatan magang teknis sesuai arahan Service Manager.",
            ],
        ];

        foreach ($openings as $index => $item) {
            $company = $companies->isNotEmpty() ? $companies->get($index % $companies->count()) : null;
            $department = null;
            $position = null;

            if ($company && $company->departments->isNotEmpty()) {
                $department = $company->departments->first();
                if ($department->positions->isNotEmpty()) {
                    $position = $department->positions->first();
                }
            }

            $baseSlug = Str::slug($item['title']);
            $slug = $baseSlug;
            $suffix = 2;
            while (JobOpening::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            JobOpening::create([
                'title' => $item['title'],
                'job_type' => $item['job_type'],
                'slug' => $slug,
                'company_id' => $company?->id,
                'department_id' => $department?->id,
                'position_id' => $position?->id,
                'description' => $item['description'],
                'required_skills' => $item['skills'],
                'min_education' => $item['min_education'],
                'min_experience' => $item['min_experience'],
                'screening_threshold' => $item['screening_threshold'],
                'is_open' => true,
            ]);
        }
    }
}
