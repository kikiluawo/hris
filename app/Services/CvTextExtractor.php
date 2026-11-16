<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class CvTextExtractor
{
    public const MIN_TEXT_LENGTH = 40;

    private ?array $toolPaths = [];

    private ?array $ocrLanguages = null;

    public function extract(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if (! is_file($path)) {
            return $this->failed('Berkas CV tidak ditemukan di server.');
        }

        $result = match ($extension) {
            'pdf' => $this->fromPdf($path),
            'docx' => $this->fromDocx($path),
            'doc' => $this->fromDoc($path),
            default => $this->failed('Format CV tidak dikenali. Tinjau manual.'),
        };

        if ($result['text'] === null) {
            return $result;
        }

        $text = $this->clean($result['text']);

        if (mb_strlen($text) < self::MIN_TEXT_LENGTH) {
            return $this->failed($extension === 'pdf'
                ? 'Teks CV tidak terbaca otomatis. Kemungkinan PDF hasil scan dan OCR belum tersedia. Tinjau manual.'
                : 'Teks CV terlalu sedikit untuk dinilai otomatis. Tinjau manual.');
        }

        return [
            'text' => $text,
            'source' => $result['source'],
            'reason' => null,
        ];
    }

    private function fromPdf(string $path): array
    {
        $best = null;

        try {
            $text = trim((new Parser())->parseFile($path)->getText());

            if (mb_strlen($text) >= self::MIN_TEXT_LENGTH) {
                return ['text' => $text, 'source' => 'pdfparser'];
            }

            $best = ['text' => $text, 'source' => 'pdfparser'];
        } catch (Throwable) {
            $best = null;
        }

        if ($binary = $this->tool('pdftotext')) {
            try {
                $text = trim($this->run([$binary, '-q', '-enc', 'UTF-8', $path, '-'], 30));

                if (mb_strlen($text) >= self::MIN_TEXT_LENGTH) {
                    return ['text' => $text, 'source' => 'pdftotext'];
                }

                if (mb_strlen($text) > mb_strlen((string) ($best['text'] ?? ''))) {
                    $best = ['text' => $text, 'source' => 'pdftotext'];
                }
            } catch (Throwable) {
                
            }
        }

        $ocr = $this->ocrPdf($path);

        if ($ocr !== null) {
            return ['text' => $ocr, 'source' => 'ocr'];
        }

        if ($best !== null && $best['text'] !== '') {
            return $best;
        }

        return $this->failed('PDF tidak bisa dibaca. Tinjau manual.');
    }

    private function ocrPdf(string $path): ?string
    {
        if (! config('recruitment.ocr.enabled', true)) {
            return null;
        }

        $tesseract = $this->tool('tesseract');

        if ($tesseract === null) {
            return null;
        }

        $renderer = $this->renderer();

        if ($renderer === null) {
            return null;
        }

        $directory = storage_path('app/ocr-' . uniqid());

        try {
            File::ensureDirectoryExists($directory);
            $prefix = $directory . DIRECTORY_SEPARATOR . 'page';

            $this->run($renderer($path, $prefix), 60);

            $pages = glob($prefix . '*.png') ?: [];
            sort($pages);
            $pages = array_slice($pages, 0, max(1, (int) config('recruitment.ocr.max_pages', 3)));
            $chunks = [];

            foreach ($pages as $page) {
                try {
                    $chunks[] = trim($this->run(
                        [$tesseract, $page, 'stdout', '-l', $this->languageArgument(), '--psm', '3'],
                        60
                    ));
                } catch (Throwable) {
                    
                }
            }

            $text = trim(implode("\n", array_filter($chunks)));

            return $text !== '' ? $text : null;
        } catch (Throwable) {
            return null;
        } finally {
            File::deleteDirectory($directory);
        }
    }

    private function renderer(): ?callable
    {
        $dpi = (string) max(100, (int) config('recruitment.ocr.dpi', 200));
        $lastPage = (string) max(1, (int) config('recruitment.ocr.max_pages', 3));

        if ($binary = $this->tool('pdftoppm')) {
            return fn (string $path, string $prefix) => [
                $binary, '-r', $dpi, '-png', '-f', '1', '-l', $lastPage, $path, $prefix,
            ];
        }

        $ghostscript = $this->tool('gswin64c') ?? $this->tool('gs');

        if ($ghostscript !== null) {
            return fn (string $path, string $prefix) => [
                $ghostscript, '-q', '-dNOPAUSE', '-dBATCH', '-dSAFER',
                '-sDEVICE=png16m', '-r' . $dpi,
                '-dFirstPage=1', '-dLastPage=' . $lastPage,
                '-sOutputFile=' . $prefix . '%d.png', $path,
            ];
        }

        return null;
    }

    private function fromDocx(string $path): array
    {
        try {
            $document = IOFactory::load($path);
            $lines = [];

            foreach ($document->getSections() as $section) {
                $this->collectText($section, $lines);
            }

            $text = trim(implode("\n", array_filter($lines, fn ($line) => trim((string) $line) !== '')));

            if (mb_strlen($text) >= self::MIN_TEXT_LENGTH) {
                return ['text' => $text, 'source' => 'phpword'];
            }
        } catch (Throwable) {
            
        }

        $text = $this->docxXml($path);

        if ($text !== null) {
            return ['text' => $text, 'source' => 'docx-xml'];
        }

        return $this->failed('File DOCX tidak bisa dibaca. Tinjau manual.');
    }

    private function docxXml(string $path): ?string
    {
        if (! class_exists(ZipArchive::class)) {
            return null;
        }

        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || $xml === '') {
            return null;
        }

        $xml = str_replace(['</w:p>', '<w:tab/>', '<w:br/>', '<w:br />'], ["\n", "\t", "\n", "\n"], $xml);
        $text = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim($text) !== '' ? $text : null;
    }

    private function fromDoc(string $path): array
    {
        $soffice = $this->tool('soffice');

        if ($soffice !== null) {
            $directory = storage_path('app/doc-' . uniqid());

            try {
                File::ensureDirectoryExists($directory);
                $this->run([$soffice, '--headless', '--convert-to', 'docx', '--outdir', $directory, $path], 90);

                $converted = glob($directory . DIRECTORY_SEPARATOR . '*.docx') ?: [];

                if ($converted !== []) {
                    return $this->fromDocx($converted[0]);
                }
            } catch (Throwable) {
                
            } finally {
                File::deleteDirectory($directory);
            }
        }

        if ($binary = $this->tool('antiword')) {
            try {
                $text = trim($this->run([$binary, '-w', '0', $path], 30));

                if (mb_strlen($text) >= self::MIN_TEXT_LENGTH) {
                    return ['text' => $text, 'source' => 'antiword'];
                }
            } catch (Throwable) {
                
            }
        }

        return $this->failed('Format .doc lama belum bisa dibaca otomatis di server ini. Simpan ulang sebagai PDF atau DOCX, atau tinjau manual.');
    }

    private function collectText(object $node, array &$lines): void
    {
        if (! method_exists($node, 'getElements')) {
            return;
        }

        foreach ($node->getElements() as $element) {
            if (method_exists($element, 'getRows')) {
                foreach ($element->getRows() as $row) {
                    foreach ($row->getCells() as $cell) {
                        $this->collectText($cell, $lines);
                    }
                }

                continue;
            }

            if (method_exists($element, 'getText')) {
                $lines[] = (string) $element->getText();

                continue;
            }

            if (method_exists($element, 'getElements')) {
                $this->collectText($element, $lines);
            }
        }
    }

    private function clean(string $text): string
    {
        $text = str_replace(["\u{00AD}", "\u{FEFF}"], '', $text);
        $text = str_replace(['ﬁ', 'ﬂ', 'ﬀ', 'ﬃ', 'ﬄ'], ['fi', 'fl', 'ff', 'ffi', 'ffl'], $text);
        $text = preg_replace('/(\p{L})-\s*\n\s*(\p{Ll})/u', '$1$2', $text) ?? $text;
        $text = str_replace(["\u{00A0}", "\u{2013}", "\u{2014}"], [' ', '-', '-'], $text);
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $text) ?? $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/ *\n */u', "\n", $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function languageArgument(): string
    {
        $languages = $this->ocrLanguages();
        $wanted = array_values(array_filter(
            array_map('trim', explode('+', (string) config('recruitment.ocr.languages', 'ind+eng'))),
            fn ($language) => $language !== '' && in_array($language, $languages, true)
        ));

        return $wanted !== [] ? implode('+', $wanted) : 'eng';
    }

    private function ocrLanguages(): array
    {
        if ($this->ocrLanguages !== null) {
            return $this->ocrLanguages;
        }

        $tesseract = $this->tool('tesseract');

        if ($tesseract === null) {
            return $this->ocrLanguages = [];
        }

        try {
            $output = $this->run([$tesseract, '--list-langs'], 15);
            $lines = array_map('trim', preg_split('/\r\n|\r|\n/', $output) ?: []);
            $languages = array_values(array_filter($lines, fn ($line) => preg_match('/^[a-z_]{2,}$/', $line) === 1));

            return $this->ocrLanguages = $languages;
        } catch (Throwable) {
            return $this->ocrLanguages = [];
        }
    }

    private function tool(string $name): ?string
    {
        if (array_key_exists($name, $this->toolPaths)) {
            return $this->toolPaths[$name];
        }

        $configured = config('recruitment.ocr.binaries.' . $name);

        if (is_string($configured) && $configured !== '') {
            return $this->toolPaths[$name] = is_file($configured) ? $configured : null;
        }

        if (! function_exists('proc_open')) {
            return $this->toolPaths[$name] = null;
        }

        $command = PHP_OS_FAMILY === 'Windows' ? ['where.exe', $name] : ['sh', '-c', 'command -v ' . escapeshellarg($name)];

        try {
            $process = $this->process($command);
            $process->setTimeout(5);
            $process->run();

            if (! $process->isSuccessful()) {
                return $this->toolPaths[$name] = null;
            }

            $output = trim(strtok($process->getOutput(), "\r\n"));

            return $this->toolPaths[$name] = $output !== '' ? $output : null;
        } catch (Throwable) {
            return $this->toolPaths[$name] = null;
        }
    }

    private function run(array $command, int $timeout): string
    {
        $process = $this->process($command);
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'Perintah gagal dijalankan.');
        }

        return $process->getOutput();
    }

    private function process(array $command): Process
    {
        $process = new Process($command);
        $path = getenv('PATH');
        $env = [];

        if (is_string($path) && $path !== '') {
            $env['PATH'] = $path;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $env['SystemRoot'] = (string) (getenv('SystemRoot') ?: 'C:\\Windows');
        }

        if ($env !== []) {
            $process->setEnv($env);
        }

        return $process;
    }

    private function failed(string $reason): array
    {
        return [
            'text' => null,
            'source' => null,
            'reason' => $reason,
        ];
    }
}
