<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Candidate;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeBiodata;
use App\Models\JobApplicationForm;
use App\Models\JobOpening;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentAndOnboardingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $hrdUser;
    private Company $company;
    private Department $department;
    private Position $position;
    private JobOpening $opening;
    private Candidate $candidate;
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hrdUser = User::factory()->create(['is_hrd' => true]);

        $this->company = Company::create(['name' => 'PT Majesty Sukses Mandiri', 'code' => 'MSM']);
        $this->department = Department::create(['name' => 'Operasional', 'company_id' => $this->company->id]);
        $this->position = Position::create(['name' => 'Staff HRD', 'department_id' => $this->department->id]);

        $this->opening = JobOpening::create([
            'title' => 'Staff HRD',
            'slug' => 'staff-hrd-msm',
            'description' => 'Dibutuhkan segera Staff HRD untuk Majesty Group.',
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'is_open' => true,
            'screening_threshold' => 70,
        ]);

        $this->candidate = Candidate::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'city' => 'Jakarta Barat',
            'address' => 'Jl. Kebon Jeruk No. 12, Jakarta',
            'cv_path' => 'cvs/dummy.pdf',
            'cv_original_name' => 'CV_Budi.pdf',
        ]);

        $this->application = Application::create([
            'job_opening_id' => $this->opening->id,
            'candidate_id' => $this->candidate->id,
            'status' => Application::STATUS_SCREENING_PASSED,
            'screening_score' => 85,
        ]);
    }

    public function test_hrd_can_create_and_send_job_application_form(): void
    {
        $response = $this->actingAs($this->hrdUser)
            ->post(route('applicants.send-form', $this->application));

        $response->assertRedirect();
        $response->assertSessionHas('wa_job_form_url');

        $this->assertDatabaseHas('job_application_forms', [
            'application_id' => $this->application->id,
        ]);

        $this->assertEquals(Application::STATUS_FORM_SENT, $this->application->fresh()->status);
    }

    public function test_candidate_can_view_and_submit_job_application_form(): void
    {
        $form = JobApplicationForm::create([
            'application_id' => $this->application->id,
            'token' => 'test-token-123456',
            'status' => 'draft',
        ]);

        // Candidate views public form
        $viewResponse = $this->get(route('application-forms.show', $form->token));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Job Application Form');
        $viewResponse->assertSee('Budi Santoso');

        // Candidate submits form
        $postData = [
            'name' => 'Budi Santoso',
            'alias' => 'Budi',
            'address' => 'Jl. Kebon Jeruk No. 12 RT 01 RW 02, Jakarta Barat',
            'email' => 'budi.santoso@example.com',
            'mobile_phone' => '081234567890',
            'gender' => 'pria',
            'religion' => 'Islam',
            'marital_status' => 'Belum Menikah',
            'birth_place' => 'Jakarta',
            'birth_date' => '1998-05-15',
            'ktp_no' => '3171011505980001',
            'expected_salary' => 6000000,
            'notice_period' => 'Segera',
            'applicant_signature' => 'Budi Santoso',
            'father' => ['name' => 'Sutrisno', 'occupation' => 'Wiraswasta'],
            'mother' => ['name' => 'Siti Aminah', 'occupation' => 'Ibu Rumah Tangga'],
            'education' => [
                'sma' => ['name' => 'SMAN 1 Jakarta', 'from' => '2013', 'to' => '2016', 'achievement' => 'IPA'],
                'university' => ['name' => 'Universitas Indonesia', 'from' => '2016', 'to' => '2020', 'achievement' => '3.65'],
            ],
            'careers' => [
                [
                    'company' => 'PT Maju Terus',
                    'position' => 'Junior HR',
                    'period_from' => '2021',
                    'period_to' => '2023',
                    'report_to' => 'HR Manager',
                    'responsibilities' => 'Recruitment & payroll admin',
                    'salary_end' => '5000000',
                    'reason_leaving' => 'Ingin berkembang',
                ],
            ],
        ];

        $submitResponse = $this->post(route('application-forms.store', $form->token), $postData);
        $submitResponse->assertRedirect(route('application-forms.show', $form->token));

        $freshForm = $form->fresh();
        $this->assertTrue($freshForm->isSubmitted());
        $this->assertNotNull($freshForm->submitted_at);
        $this->assertEquals('3171011505980001', $freshForm->personal_data['ktp_no']);

        // Application status becomes form_completed
        $this->assertEquals(Application::STATUS_FORM_COMPLETED, $this->application->fresh()->status);
    }

    public function test_form_submission_does_not_downgrade_advanced_application_status(): void
    {
        $this->application->update(['status' => Application::STATUS_ASSESSMENT_DONE]);

        $form = JobApplicationForm::create([
            'application_id' => $this->application->id,
            'token' => 'advanced-status-token',
            'status' => 'draft',
        ]);

        $postData = [
            'personal' => [
                'name' => 'Budi Santoso',
                'nickname' => 'Budi',
                'pob' => 'Jakarta',
                'dob' => '1998-05-15',
                'gender' => 'pria',
                'blood_type' => 'O',
                'religion' => 'Islam',
                'ktp_no' => '3171011505980001',
                'address' => 'Jl. Sudirman No 123, Jakarta Barat',
                'address_domicile' => 'Jl. Sudirman No 123, Jakarta Barat',
                'phone_mobile' => '081234567890',
                'marital_status' => 'Belum Menikah',
            ],
            'confirmation_truth' => '1',
            'confirmation_agree' => '1',
            'signature_name' => 'Budi Santoso',
        ];

        $response = $this->post(route('application-forms.store', $form->token), $postData);
        $response->assertRedirect(route('application-forms.show', $form->token));

        $this->assertEquals(Application::STATUS_ASSESSMENT_DONE, $this->application->fresh()->status);
    }

    public function test_hrd_can_view_and_print_job_application_form(): void
    {
        $form = JobApplicationForm::create([
            'application_id' => $this->application->id,
            'token' => 'test-view-token',
            'status' => 'submitted',
            'submitted_at' => now(),
            'personal_data' => [
                'name' => 'Budi Santoso',
                'address' => 'Jakarta Barat',
                'phone_mobile' => '081234567890',
                'ktp_no' => '3171011505980001',
                'religion' => 'Islam',
                'gender' => 'pria',
            ],
            'signature_name' => 'Budi Santoso',
        ]);

        $adminView = $this->actingAs($this->hrdUser)
            ->get(route('applicants.view-form', $this->application));
        $adminView->assertOk();
        $adminView->assertSee('Budi Santoso');
        $adminView->assertSee('3171011505980001');

        $printView = $this->actingAs($this->hrdUser)
            ->get(route('applicants.print-form', $this->application));
        $printView->assertOk();
        $printView->assertSee('JOB APPLICATION FORM');
        $printView->assertSee('Budi Santoso');
    }

    public function test_hrd_can_accept_candidate_and_generate_onboarding_biodata(): void
    {
        $response = $this->actingAs($this->hrdUser)
            ->post(route('applicants.decide-final', $this->application), ['decision' => 'hire']);

        $response->assertRedirect();
        $response->assertSessionHas('wa_onboarding_url');

        $this->assertEquals(Application::STATUS_HIRED, $this->application->fresh()->status);

        $this->assertDatabaseHas('employee_biodatas', [
            'application_id' => $this->application->id,
        ]);
    }

    public function test_biodata_prepopulates_from_job_application_form(): void
    {
        // 1. Create job application form with submitted data
        $form = JobApplicationForm::create([
            'application_id' => $this->application->id,
            'token' => 'form-token-prefill',
            'status' => 'submitted',
            'personal_data' => [
                'name' => 'Budi Santoso',
                'alias' => 'Budi',
                'birth_place' => 'Jakarta',
                'birth_date' => '1998-05-15',
                'gender' => 'pria',
                'religion' => 'Islam',
                'marital_status' => 'Tidak Kawin',
                'address' => 'Jl. Panjang No 9, Kebon Jeruk',
                'email' => 'budi.santoso@example.com',
                'phone_mobile' => '081234567890',
                'ktp_no' => '3171011505980001',
            ],
            'family_data' => [
                'father' => ['name' => 'Sutrisno', 'occupation' => 'PNS'],
                'mother' => ['name' => 'Siti Aminah', 'occupation' => 'Guru'],
            ],
        ]);

        // 2. Create biodata token
        $biodata = EmployeeBiodata::create([
            'application_id' => $this->application->id,
            'token' => 'biodata-token-abc',
            'status' => 'draft',
        ]);

        // 3. Candidate opens onboarding biodata form
        $response = $this->get(route('employee-biodatas.show', $biodata->token));
        $response->assertOk();
        $response->assertSee('Formulir Biodata Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('3171011505980001');
        $response->assertSee('Sutrisno');
        $response->assertSee('Siti Aminah');

        // 4. Candidate submits onboarding biodata
        $submitData = [
            'personal' => [
                'full_name' => 'Budi Santoso',
                'nickname' => 'Budi',
                'birth_place' => 'Jakarta',
                'birth_date' => '1998-05-15',
                'gender' => 'pria',
                'nationality' => 'Indonesia',
                'religion' => 'Islam',
                'marital_status' => 'Tidak Kawin',
                'blood_type' => 'O',
                'birth_order' => 1,
                'siblings_count' => 3,
                'current_address' => 'Jl. Panjang No 9, Kebon Jeruk',
                'email' => 'budi.santoso@example.com',
                'phone_mobile' => '081234567890',
                'ktp_no' => '3171011505980001',
                'npwp_no' => '98.765.432.1-012.000',
                'bpjs_tk_no' => '2201928374',
            ],
            'bank_accounts' => [
                'bca' => [
                    'number' => '1234567890',
                    'branch' => 'KCP Kebon Jeruk',
                    'holder' => 'Budi Santoso',
                ],
            ],
            'signed_name' => 'Budi Santoso',
            'signed_city' => 'Jakarta',
            'digital_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ];

        $postRes = $this->post(route('employee-biodatas.store', $biodata->token), $submitData);
        $postRes->assertRedirect(route('employee-biodatas.show', $biodata->token));

        $freshBio = $biodata->fresh();
        $this->assertEquals('submitted', $freshBio->status);
        $this->assertEquals('1234567890', $freshBio->bank_accounts['bca']['number']);
        $this->assertNotNull($freshBio->digital_signature);
    }

    public function test_employee_creation_form_prepopulates_from_accepted_application(): void
    {
        $this->application->update(['status' => Application::STATUS_HIRED]);

        $biodata = EmployeeBiodata::create([
            'application_id' => $this->application->id,
            'token' => 'biodata-token-emp-create',
            'status' => 'submitted',
            'personal_data' => [
                'full_name' => 'Budi Santoso',
                'birth_place' => 'Jakarta',
                'birth_date' => '1998-05-15',
                'gender' => 'pria',
                'religion' => 'Islam',
                'marital_status' => 'Tidak Kawin',
                'current_address' => 'Jl. Panjang No 9, Kebon Jeruk',
                'email' => 'budi.santoso@example.com',
                'phone_mobile' => '081234567890',
                'ktp_no' => '3171011505980001',
                'npwp_no' => '98.765.432.1-012.000',
                'bpjs_tk_no' => '2201928374',
            ],
            'family_details' => [
                'father' => ['name' => 'Sutrisno'],
                'mother' => ['name' => 'Siti Aminah'],
            ],
        ]);

        $response = $this->actingAs($this->hrdUser)
            ->get(route('employees.create', ['application_id' => $this->application->id]));

        $response->assertOk();
        $response->assertSee('Tambah karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('3171011505980001');
        $response->assertSee('98.765.432.1-012.000');
        $response->assertSee('2201928374');
        $response->assertSee('Sutrisno');
        $response->assertSee('Siti Aminah');
    }
}
