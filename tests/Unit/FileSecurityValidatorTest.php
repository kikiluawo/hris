<?php

namespace Tests\Unit;

use App\Services\FileSecurityValidator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileSecurityValidatorTest extends TestCase
{
    private FileSecurityValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FileSecurityValidator();
    }

    public function test_rejects_double_extension_files(): void
    {
        $file = UploadedFile::fake()->create('malicious.php.pdf', 100);

        $result = $this->validator->validateUploadSafety($file);

        $this->assertFalse($result['safe']);
        $this->assertStringContainsString('ekstensi ganda', $result['reason']);
    }

    public function test_rejects_files_containing_php_scripts(): void
    {
        $file = UploadedFile::fake()->createWithContent('resume.pdf', "<?php system('whoami'); ?>");

        $result = $this->validator->validateUploadSafety($file);

        $this->assertFalse($result['safe']);
        $this->assertStringContainsString('kode script', $result['reason']);
    }

    public function test_accepts_clean_pdf_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('curriculum_vitae.pdf', "%PDF-1.4\nClean Content");

        $result = $this->validator->validateUploadSafety($file);

        $this->assertTrue($result['safe']);
        $this->assertNull($result['reason']);
    }

    public function test_inspect_document_type_identifies_text_vs_scan(): void
    {
        // Berkas teks simulasi
        $tempPdf = tempnam(sys_get_temp_dir(), 'test_cv_');
        // Buat PDF sederhana dengan teks dummy
        file_put_contents($tempPdf, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $inspection = $this->validator->inspectDocumentType($tempPdf, 'pdf');

        $this->assertIsArray($inspection);
        $this->assertArrayHasKey('is_digital_text', $inspection);
        $this->assertArrayHasKey('is_scanned_image', $inspection);

        @unlink($tempPdf);
    }
}
