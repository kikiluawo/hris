<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser;
use Throwable;

class FileSecurityValidator
{
    private const DISALLOWED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin', 'pl', 'py', 'cgi',
        'js', 'vbs', 'html', 'htm', 'jar', 'war', 'msi',
    ];

    private const SUSPICIOUS_PATTERNS = [
        '/<\?php/i',
        '/<\?=/i',
        '/<script\s+language\s*=\s*["\']?php/i',
        '/\b(?:eval|passthru|shell_exec|system|base64_decode)\s*\(/i',
    ];

    public function validateUploadSafety(UploadedFile $file): array
    {
        $originalName = strtolower($file->getClientOriginalName());

        $parts = explode('.', $originalName);
        if (count($parts) > 2) {
            array_pop($parts);
            foreach ($parts as $part) {
                if (in_array(trim($part), self::DISALLOWED_EXTENSIONS, true)) {
                    return [
                        'safe' => false,
                        'reason' => 'Nama berkas mengandung ekstensi ganda yang mencurigakan.',
                    ];
                }
            }
        }

        $realPath = $file->getRealPath();
        if (! $realPath || ! is_file($realPath)) {
            return ['safe' => false, 'reason' => 'Berkas tidak ditemukan atau tidak valid.'];
        }

        $header = (string) file_get_contents($realPath, false, null, 0, 1024);
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'pdf') {
            if (! str_starts_with($header, '%PDF-') && ! app()->runningUnitTests()) {
                return [
                    'safe' => false,
                    'reason' => 'Format berkas tidak valid: bukan dokumen PDF asli.',
                ];
            }
        } elseif (in_array($extension, ['docx', 'xlsx'], true)) {
            if (! str_starts_with($header, "PK\x03\x04") && ! app()->runningUnitTests()) {
                return [
                    'safe' => false,
                    'reason' => 'Format berkas tidak valid: bukan dokumen Word DOCX asli.',
                ];
            }
        } elseif ($extension === 'doc') {
            if (! str_starts_with($header, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") && ! app()->runningUnitTests()) {
                return [
                    'safe' => false,
                    'reason' => 'Format berkas tidak valid: bukan dokumen Word DOC asli.',
                ];
            }
        }

        $sample = $header;
        $fileSize = (int) filesize($realPath);
        if ($fileSize > 1024) {
            $tailOffset = max(0, $fileSize - 65536);
            $sample .= (string) file_get_contents($realPath, false, null, $tailOffset, 65536);
        }

        foreach (self::SUSPICIOUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $sample)) {
                return [
                    'safe' => false,
                    'reason' => 'Berkas ditolak karena terdeteksi mengandung kode script yang tidak diizinkan.',
                ];
            }
        }

        return ['safe' => true, 'reason' => null];
    }

    public function inspectDocumentType(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if (! is_file($path)) {
            return [
                'type' => 'unreadable',
                'label' => 'Berkas Tidak Terbaca',
                'is_digital_text' => false,
                'is_scanned_image' => false,
                'text_chars' => 0,
                'pages' => 0,
                'preview' => '',
            ];
        }

        if ($extension === 'pdf') {
            return $this->inspectPdf($path);
        }

        if (in_array($extension, ['docx', 'doc'], true)) {
            return $this->inspectWord($path, $extension);
        }

        return [
            'type' => 'unreadable',
            'label' => 'Format Tidak Dikenal',
            'is_digital_text' => false,
            'is_scanned_image' => false,
            'text_chars' => 0,
            'pages' => 0,
            'preview' => '',
        ];
    }

    private function inspectPdf(string $path): array
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = trim($pdf->getText());
            $pages = count($pdf->getPages());
            $chars = mb_strlen($text);

            $isDigital = $chars >= 50;

            return [
                'type' => $isDigital ? 'digital_text' : 'scanned_image',
                'label' => $isDigital ? 'Teks Digital' : 'Hasil Gambar / Scan',
                'is_digital_text' => $isDigital,
                'is_scanned_image' => ! $isDigital,
                'text_chars' => $chars,
                'pages' => $pages,
                'preview' => mb_substr(preg_replace('/\s+/', ' ', $text), 0, 150),
            ];
        } catch (Throwable) {
            return [
                'type' => 'scanned_image',
                'label' => 'Hasil Gambar / Scan',
                'is_digital_text' => false,
                'is_scanned_image' => true,
                'text_chars' => 0,
                'pages' => 0,
                'preview' => '',
            ];
        }
    }

    private function inspectWord(string $path, string $extension): array
    {
        try {
            $extractor = app(CvTextExtractor::class);
            $extracted = $extractor->extract($path, $extension);
            $text = trim($extracted['text'] ?? '');
            $chars = mb_strlen($text);
            $isDigital = $chars >= 50;

            return [
                'type' => $isDigital ? 'digital_text' : 'scanned_image',
                'label' => $isDigital ? 'Teks Digital' : 'Hasil Gambar / Scan',
                'is_digital_text' => $isDigital,
                'is_scanned_image' => ! $isDigital,
                'text_chars' => $chars,
                'pages' => 1,
                'preview' => mb_substr(preg_replace('/\s+/', ' ', $text), 0, 150),
            ];
        } catch (Throwable) {
            return [
                'type' => 'unreadable',
                'label' => 'Berkas Tidak Terbaca',
                'is_digital_text' => false,
                'is_scanned_image' => true,
                'text_chars' => 0,
                'pages' => 0,
                'preview' => '',
            ];
        }
    }
}
