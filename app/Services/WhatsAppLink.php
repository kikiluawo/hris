<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AssessmentInvitation;

class WhatsAppLink
{
    public static function assessmentInvitation(AssessmentInvitation $invitation): string
    {
        $number = self::normalizePhone($invitation->application?->candidate?->phone);

        return 'https://wa.me/' . ($number ?? '') . '?text=' . rawurlencode(self::assessmentMessage($invitation));
    }

    public static function assessmentMessage(AssessmentInvitation $invitation): string
    {
        $invitation->loadMissing(['assessment.questions', 'application.candidate', 'application.jobOpening.company']);
        $assessment = $invitation->assessment;

        $lines = [
            'Halo ' . $invitation->candidateName() . ',',
            '',
            'Kami dari HRD ' . ($invitation->application?->jobOpening?->company?->name ?? 'Majesty Group')
                . ' mengundang Anda mengikuti tes online untuk posisi '
                . ($invitation->application?->jobOpening?->title ?? '-') . '.',
            '',
            'Tes: ' . ($assessment?->title ?? '-'),
            'Durasi: ' . $invitation->durationMinutes() . ' menit',
            'Jumlah soal: ' . ($assessment?->questions?->count() ?? 0),
            'Berlaku sampai: ' . $invitation->expires_at->translatedFormat('d M Y H:i'),
            '',
            'Buka tautan pribadi berikut untuk mengerjakan:',
            $invitation->link(),
            '',
            'Tautan ini hanya untuk Anda dan hanya bisa dipakai satu kali. Waktu mulai berjalan saat Anda menekan tombol mulai, dan berakhir setelah ' . $invitation->durationMinutes() . ' menit.',
            '',
            'Penting: gunakan HP atau laptop dengan kamera aktif. Saat tes berlangsung, jangan berpindah tab atau aplikasi. Pelanggaran pertama akan diberi peringatan; pelanggaran berikutnya membuat tes langsung berakhir.',
            '',
            'Terima kasih.',
        ];

        return implode("\n", $lines);
    }

    public static function interviewInvitation(Application $application): ?string
    {
        $number = self::normalizePhone($application->candidate?->phone);

        if ($number === null || $application->interview_at === null) {
            return null;
        }

        return 'https://wa.me/' . $number . '?text=' . rawurlencode(self::interviewMessage($application));
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        if (! str_starts_with($digits, '62') || strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public static function interviewMessage(Application $application): string
    {
        $application->loadMissing(['candidate', 'jobOpening.company']);

        $schedule = $application->interview_at;

        $lines = [
            'Halo ' . ($application->candidate?->name ?? 'Kandidat') . ',',
            '',
            'Kami dari HRD ' . ($application->jobOpening?->company?->name ?? 'Majesty Group')
                . ' mengundang Anda untuk mengikuti interview posisi '
                . ($application->jobOpening?->title ?? '-') . '.',
            '',
            'Hari/tanggal: ' . $schedule->translatedFormat('l, d F Y'),
            'Waktu: ' . $schedule->format('H:i') . ' WIB',
            'Metode: ' . ($application->interviewModeLabel() ?? '-'),
            'Lokasi/tautan: ' . ($application->interview_location ?? '-'),
        ];

        if (filled($application->interview_notes)) {
            $lines[] = '';
            $lines[] = 'Catatan: ' . trim($application->interview_notes);
        }

        $lines[] = '';
        $lines[] = 'Mohon konfirmasi kehadiran Anda dengan membalas pesan ini. Terima kasih.';

        return implode("\n", $lines);
    }

    public static function jobApplicationFormInvitation(Application $application, string $url): ?string
    {
        $application->loadMissing(['candidate', 'jobOpening.company']);
        $number = self::normalizePhone($application->candidate?->phone);
        if ($number === null) {
            return null;
        }

        $lines = [
            'Halo ' . ($application->candidate?->name ?? 'Kandidat') . ',',
            '',
            'Selamat! Lamaran Anda untuk posisi ' . ($application->jobOpening?->title ?? '-')
                . ' di ' . ($application->jobOpening?->company?->name ?? 'Majesty Group')
                . ' dinyatakan LOLOS seleksi berkas awal.',
            '',
            'Sebagai langkah berikutnya sebelum tahapan interview, mohon kesediaan Anda untuk melengkapi Formulir Data Pelamar resmi kami melalui tautan berikut:',
            $url,
            '',
            'Formulir ini bersifat rahasia dan diperlukan untuk evaluasi tim HRD.',
            '',
            'Terima kasih.',
        ];

        return 'https://wa.me/' . $number . '?text=' . rawurlencode(implode("\n", $lines));
    }

    public static function onboardingInvitation(Application $application, string $url): ?string
    {
        $application->loadMissing(['candidate', 'jobOpening.company']);
        $number = self::normalizePhone($application->candidate?->phone);
        if ($number === null) {
            return null;
        }

        $lines = [
            'Halo ' . ($application->candidate?->name ?? 'Kandidat') . ',',
            '',
            'Selamat! Berdasarkan hasil evaluasi seluruh tahapan seleksi, Anda dinyatakan DITERIMA untuk bergabung di '
                . ($application->jobOpening?->company?->name ?? 'Majesty Group')
                . ' pada posisi ' . ($application->jobOpening?->title ?? '-') . '.',
            '',
            'Sebagai persiapan administrasi dan penomoran karyawan, mohon untuk melengkapi Formulir Biodata Karyawan melalui tautan berikut:',
            $url,
            '',
            'Sebagian data Anda sudah terisi otomatis dari formulir sebelumnya. Anda hanya perlu melengkapi nomor rekening, data keluarga tambahan, serta mengunggah lampiran yang diperlukan.',
            '',
            'Selamat bergabung dan terima kasih!',
        ];

        return 'https://wa.me/' . $number . '?text=' . rawurlencode(implode("\n", $lines));
    }
}
