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
            'Kami dari HRD ' . ($invitation->application?->jobOpening?->company?->name ?: 'PT Majesty Group')
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
        $application->loadMissing(['candidate', 'jobOpening.company', 'jobOpening.position', 'decidedBy', 'jobApplicationForm']);

        $schedule = $application->interview_at;
        $candidateName = $application->candidate?->name ?? 'Candidate';
        $companyName = $application->jobOpening?->company?->name ?: 'PT Majesty Group';
        $positionTitle = $application->jobOpening?->position?->name ?? $application->jobOpening?->title ?? 'the applied position';
        $hrName = $application->decidedBy?->name ?? auth()->user()?->name ?? 'HR Department';

        $hour = $schedule ? (int) $schedule->format('H') : (int) now()->format('H');
        $timeGreeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $gender = $application->jobApplicationForm?->personal_data['gender'] ?? $application->jobApplicationForm?->personal_data['sex'] ?? null;
        $honorific = match (strtoupper((string) $gender)) {
            'M', 'L', 'PRIA', 'LAKI-LAKI' => 'Mr. ',
            'F', 'P', 'WANITA', 'PEREMPUAN' => 'Ms. ',
            default => '',
        };

        $greetingLine = "{$timeGreeting}, {$honorific}{$candidateName}";

        $dateFormatted = $schedule ? $schedule->format('l, F d, Y') : '-';
        $timeFormatted = $schedule ? $schedule->format('H:i') . ' (WIB)' : '-';

        $venue = $application->interview_location;
        $address = $application->interview_address;
        $mapsUrl = $application->interview_maps_url;

        $lines = [
            'INTERVIEW INVITATION',
            strtoupper($companyName),
            '',
            $greetingLine,
            '',
            "Greetings from {$companyName}!",
            '',
            "My name is {$hrName} from the Human Resources Department of {$companyName}.",
            '',
            "We are pleased to invite you to participate in the next stage of our recruitment process for the {$positionTitle} position at {$companyName}.",
            '',
            'Please find the interview details below:',
            '1. INTERVIEW SCHEDULE',
            "* Date: {$dateFormatted}",
            "* Time: {$timeFormatted}",
        ];

        if ($application->interview_mode === 'online') {
            $lines[] = '* Method: Online / Video Call';
            if (filled($venue)) {
                $lines[] = "* Platform / Link: {$venue}";
            }
            if (filled($mapsUrl)) {
                $lines[] = "* Meeting Link: {$mapsUrl}";
            }
        } else {
            if (filled($venue)) {
                $lines[] = "* Venue: {$venue}";
            }
            if (filled($address)) {
                $lines[] = "* Address: {$address}";
            }
            if (filled($mapsUrl)) {
                $lines[] = '';
                $lines[] = '📍 *Google Maps Location:*';
                $lines[] = $mapsUrl;
            }
        }

        if (filled($application->interview_notes)) {
            $lines[] = '';
            $lines[] = 'Notes: ' . trim($application->interview_notes);
        }

        $lines[] = '';
        $lines[] = '2. REQUIRED DOCUMENTS';
        $lines[] = '';
        $lines[] = 'To support our recruitment and evaluation process, kindly submit the following documents:';
        $lines[] = '';
        $lines[] = '1. Job Application Form';
        $lines[] = '2. Updated Curriculum Vitae (CV)';
        $lines[] = '3. Copy of Educational Certificate';
        $lines[] = '4. Academic Transcript';
        $lines[] = '5. Copy of National ID Card (KTP)';
        $lines[] = '6. Other relevant supporting documents, if applicable..';
        $lines[] = '';
        $lines[] = 'Kindly bring all the required documents with you on the scheduled interview day for verification purposes.';
        $lines[] = '';
        $lines[] = '3. SELECTION PROCESS';
        $lines[] = '';
        $lines[] = 'The selection process will consist of:';
        $lines[] = '* HR/User Interview';
        $lines[] = '* Assessment Test*';
        $lines[] = '';
        $lines[] = 'Please confirm your attendance by replying to this message.';
        $lines[] = '';
        $lines[] = "We appreciate your interest in pursuing a career with {$companyName} and look forward to meeting you during the selection process.";
        $lines[] = 'Thank you for your attention and cooperation.';
        $lines[] = '';
        $lines[] = 'Best regards,';
        $lines[] = '';
        $lines[] = $hrName;
        $lines[] = 'Human Resources Department';
        $lines[] = strtoupper($companyName);

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
                . ' di ' . ($application->jobOpening?->company?->name ?: 'PT Majesty Group')
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
                . ($application->jobOpening?->company?->name ?: 'PT Majesty Group')
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
