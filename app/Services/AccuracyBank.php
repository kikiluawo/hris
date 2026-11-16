<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\User;

class AccuracyBank
{
    
    public static function questions(): array
    {
        return [

            
            1 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: PT Wijaya Karya Nusantara\n[Kolom B]: PT Wijaya Karya Nusantara",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            2 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Dr. Muhammad Hidayatullah, M.Sc.\n[Kolom B]: Dr. Muhammad Hidayatulloh, M.Sc.",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            3 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Jl. Kebon Sirih No. 42B, Gambir\n[Kolom B]: Jl. Kebon Sirih No. 42B, Gambir",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            4 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: CV Sumber Rejeki Abadi\n[Kolom B]: CV Sumber Rezeki Abadi",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            5 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Prof. Dr. Ir. Setyowati Handayani\n[Kolom B]: Prof. Dr. Ir. Setyowati Handayani",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            6 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Bank Central Asia KCP Thamrin\n[Kolom B]: Bank Sentral Asia KCP Thamrin",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            7 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Gedung Graha Danamon Lt. 18\n[Kolom B]: Gedung Graha Danamon Lt. 18",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            8 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Raden Mas Danang Sutowijoyo\n[Kolom B]: Raden Mas Danang Sutawijoyo",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            9 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Kompleks Perumahan Bumi Asri Blok C4\n[Kolom B]: Kompleks Perumahan Bumi Asri Blok C4",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            10 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: PT Kharisma Inti Persada\n[Kolom B]: PT Karisma Inti Persada",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            11 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Yayasan Bina Talenta Indonesia\n[Kolom B]: Yayasan Bina Talenta Indonesia",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            12 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Menara Sudirman Lantai Mezanin\n[Kolom B]: Menara Sudirman Lantai Mezanine",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            13 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua teks berikut:\n[Kolom A]: Departemen Akuntansi & Keuangan\n[Kolom B]: Departemen Akuntansi & Keuangan",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],

            
            
            14 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor kode berikut:\n[Kolom A]: INV/2026/XI/89472-A\n[Kolom B]: INV/2026/XI/89472-A",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            15 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor kode berikut:\n[Kolom A]: PO-88392018-JKT\n[Kolom B]: PO-88392108-JKT",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            16 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor seri berikut:\n[Kolom A]: SN: 4982-8391-7264\n[Kolom B]: SN: 4982-8391-7264",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            17 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor rekening berikut:\n[Kolom A]: BCA 5271894029\n[Kolom B]: BCA 5271894029",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            18 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor kode berikut:\n[Kolom A]: KTP-3171041908920003\n[Kolom B]: KTP-3171041908920008",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            19 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor barcode berikut:\n[Kolom A]: 8992761829013\n[Kolom B]: 8992761829013",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            20 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor kode berikut:\n[Kolom A]: TRX_98471_XY9\n[Kolom B]: TRX-98471_XY9",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            21 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor resi pengiriman berikut:\n[Kolom A]: JNE-CGK01-9928374\n[Kolom B]: JNE-CGK01-9928374",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            22 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor polis asuransi berikut:\n[Kolom A]: PLS-2026-9812-34\n[Kolom B]: PLS-2026-9821-34",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            23 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua kode batch produksi berikut:\n[Kolom A]: BATCH#26K-9082\n[Kolom B]: BATCH#26K-9082",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            24 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua kode alfanumerik berikut:\n[Kolom A]: 10O8B-4927X\n[Kolom B]: 1008B-4927X",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            25 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua kode aset inventaris berikut:\n[Kolom A]: AST/IT/2026/DELL-7490\n[Kolom B]: AST/IT/2026/DELL-7490",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            26 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor SK berikut:\n[Kolom A]: SK.DIR/049/HRD/XI/2026\n[Kolom B]: SK.DIR/049/HRD/IX/2026",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            27 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor NPWP berikut:\n[Kolom A]: 01.384.920.4-012.000\n[Kolom B]: 01.384.920.4-012.000",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],

            
            
            28 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nilai nominal berikut:\n[Kolom A]: Rp 48.750.000,00\n[Kolom B]: Rp 48.750.000,00",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            29 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nilai nominal berikut:\n[Kolom A]: Rp 125.480.000,00\n[Kolom B]: Rp 125.840.000,00",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            30 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua alamat email berikut:\n[Kolom A]: hendra.kurniawan@perusahaan.co.id\n[Kolom B]: hendra.kurniawan@perusahaan.co.id",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            31 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua alamat email berikut:\n[Kolom A]: customer_service@bankmandiri.co.id\n[Kolom B]: customer-service@bankmandiri.co.id",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            32 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua angka persentase berikut:\n[Kolom A]: 18,75%\n[Kolom B]: 18,75%",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            33 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua angka persentase berikut:\n[Kolom A]: 9,805%\n[Kolom B]: 9,850%",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            34 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor telepon berikut:\n[Kolom A]: +62 21 5299-4800\n[Kolom B]: +62 21 5299-4800",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            35 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor telepon berikut:\n[Kolom A]: 0812-8924-1189\n[Kolom B]: 0812-8942-1189",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            36 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua tautan URL berikut:\n[Kolom A]: https://portal.hrd-system.com/login\n[Kolom B]: https://portal.hrd-system.com/login",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            37 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua tautan URL berikut:\n[Kolom A]: https://api.payment-gateway.id/v1/auth\n[Kolom B]: https://api.paymet-gateway.id/v1/auth",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            38 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nomor akta notaris berikut:\n[Kolom A]: Akta No. 48 Tgl 14-08-2026\n[Kolom B]: Akta No. 48 Tgl 14-08-2026",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
            39 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua nilai tukar kurs berikut:\n[Kolom A]: USD 1 = IDR 15.685,00\n[Kolom B]: USD 1 = IDR 15.658,00",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 1, 
            ],
            40 => [
                'dimension' => 'Ketelitian',
                'question' => "Bandingkan kedua kode transfer SWIFT berikut:\n[Kolom A]: SWIFT: CENAIDJAXXX\n[Kolom B]: SWIFT: CENAIDJAXXX",
                'options' => ['SAMA', 'BEDA'],
                'correct_option' => 0, 
            ],
        ];
    }

    public static function createOrUpdateAssessment(?User $creator = null): Assessment
    {
        $assessment = Assessment::firstOrNew(['title' => 'Tes Ketelitian Kerja & Verifikasi Data (40 Soal)']);

        $assessment->fill([
            'description' => 'Tes ketelitian dan sikap kerja (clerical accuracy) untuk mengukur kecepatan, ketepatan identifikasi detail, daya tahan konsentrasi, dan kestabilan kerja.',
            'instructions' => "Tes ini terdiri dari 40 butir pencocokan data [Kolom A] dan [Kolom B].\n\nPetunjuk Pengerjaan:\n1. Periksa kedua teks/kode dengan seksama dan cepat.\n2. Pilih SAMA jika kedua kolom 100% identik tanpa perbedaan sedikit pun.\n3. Pilih BEDA jika ada perbedaan huruf, angka, tanda baca, simbol, atau urutan.\n4. Waktu pengerjaan maksimal 10 menit (tes kecepatan & ketepatan).\n5. Jawablah secepat dan seteliti mungkin.",
            'duration_minutes' => 10,
            'passing_score' => 75,
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
