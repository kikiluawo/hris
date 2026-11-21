<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\HiringRequest;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTypeTest extends TestCase
{
    use RefreshDatabase;

    private User $hrd;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hrd = User::factory()->create(['is_hrd' => true]);
    }

    public function test_can_create_job_opening_with_various_job_types(): void
    {
        foreach ([JobOpening::TYPE_FULL_TIME, JobOpening::TYPE_PART_TIME, JobOpening::TYPE_INTERNSHIP] as $type) {
            $response = $this->actingAs($this->hrd)->post(route('openings.store'), [
                'title' => "Posisi {$type}",
                'job_type' => $type,
                'description' => 'Deskripsi pekerjaan untuk posisi ini.',
                'is_open' => true,
            ]);

            $response->assertRedirect(route('openings.index'));
            $this->assertDatabaseHas('job_openings', [
                'title' => "Posisi {$type}",
                'job_type' => $type,
            ]);
        }
    }

    public function test_career_index_filters_by_job_type(): void
    {
        JobOpening::create([
            'title' => 'Software Engineer Full Time',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'slug' => 'se-full-time',
            'description' => 'Full time role',
            'is_open' => true,
        ]);

        JobOpening::create([
            'title' => 'Barista Part Time',
            'job_type' => JobOpening::TYPE_PART_TIME,
            'slug' => 'barista-part-time',
            'description' => 'Part time role',
            'is_open' => true,
        ]);

        JobOpening::create([
            'title' => 'HR Intern',
            'job_type' => JobOpening::TYPE_INTERNSHIP,
            'slug' => 'hr-intern',
            'description' => 'Internship role',
            'is_open' => true,
        ]);

        // Filter Part Time
        $response = $this->get(route('career.index', ['type' => JobOpening::TYPE_PART_TIME]));
        $response->assertOk();
        $response->assertSee('Barista Part Time');
        $response->assertDontSee('Software Engineer Full Time');
        $response->assertDontSee('HR Intern');

        // Filter Internship
        $response = $this->get(route('career.index', ['type' => JobOpening::TYPE_INTERNSHIP]));
        $response->assertOk();
        $response->assertSee('HR Intern');
        $response->assertDontSee('Barista Part Time');
    }

    public function test_career_index_renders_sidebar_filter_card_and_maintains_inputs(): void
    {
        $company = Company::create(['code' => 'PTY', 'name' => 'PT Y Majesty']);

        JobOpening::create([
            'title' => 'Service Advisor Medan',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'company_id' => $company->id,
            'location' => 'Medan',
            'slug' => 'sa-medan',
            'description' => 'Service advisor di Medan',
            'is_open' => true,
        ]);

        JobOpening::create([
            'title' => 'Accounting Batam',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'location' => 'Batam',
            'slug' => 'acc-batam',
            'description' => 'Staff akuntansi di Batam',
            'is_open' => true,
        ]);

        $response = $this->get(route('career.index', [
            'q' => 'Service',
            'location' => 'Medan',
            'company_id' => $company->id,
            'type' => JobOpening::TYPE_FULL_TIME,
        ]));

        $response->assertOk();
        $response->assertSee('FILTER JOB');
        $response->assertSee('Search Role');
        $response->assertSee('Location');
        $response->assertSee('Business Unit');
        $response->assertSee('Department');
        $response->assertSee('Employment Type');
        $response->assertSee('Clear Filter');
        $response->assertSee('value="Service"', false);
        $response->assertSee('Service Advisor Medan');
        $response->assertDontSee('Accounting Batam');
    }

    public function test_career_index_deduplicates_department_names_and_filters_correctly(): void
    {
        $compA = Company::create(['code' => 'CA', 'name' => 'Company A']);
        $compB = Company::create(['code' => 'CB', 'name' => 'Company B']);

        $deptA = Department::create(['company_id' => $compA->id, 'name' => 'Accounting & Finance']);
        $deptB = Department::create(['company_id' => $compB->id, 'name' => 'Accounting & Finance']);
        $deptC = Department::create(['company_id' => $compA->id, 'name' => 'Human Resources']);

        JobOpening::create([
            'title' => 'Tax Accountant',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'company_id' => $compA->id,
            'department_id' => $deptA->id,
            'slug' => 'tax-accountant',
            'description' => 'Tax accountant role',
            'is_open' => true,
        ]);

        JobOpening::create([
            'title' => 'HR Specialist',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'company_id' => $compA->id,
            'department_id' => $deptC->id,
            'slug' => 'hr-specialist',
            'description' => 'HR role',
            'is_open' => true,
        ]);

        $response = $this->get(route('career.index'));
        $response->assertOk();

        $this->assertEquals(1, substr_count($response->getContent(), '<option value="Accounting &amp; Finance"'));

        $filteredResponse = $this->get(route('career.index', ['department' => 'Accounting & Finance']));
        $filteredResponse->assertOk();
        $filteredResponse->assertSee('Tax Accountant');
        $filteredResponse->assertDontSee('HR Specialist');
    }

    public function test_career_index_matches_openings_without_company_by_department_keywords(): void
    {
        JobOpening::create([
            'title' => 'Admin Accounting',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'company_id' => null,
            'department_id' => null,
            'slug' => 'admin-acc-unlinked',
            'description' => 'Melakukan pencatatan transaksi keuangan perusahaan.',
            'is_open' => true,
        ]);

        JobOpening::create([
            'title' => 'Mekanik Automotive',
            'job_type' => JobOpening::TYPE_FULL_TIME,
            'company_id' => null,
            'department_id' => null,
            'slug' => 'mekanik-auto-unlinked',
            'description' => 'Perawatan rutin mesin kendaraan.',
            'is_open' => true,
        ]);

        $response = $this->get(route('career.index', ['department' => 'Accounting']));
        $response->assertOk();
        $response->assertSee('Admin Accounting');
        $response->assertDontSee('Mekanik Automotive');

        $responseMekanik = $this->get(route('career.index', ['department' => 'Workshop & Maintenance']));
        $responseMekanik->assertOk();
        $responseMekanik->assertSee('Mekanik Automotive');
        $responseMekanik->assertDontSee('Admin Accounting');
    }

    public function test_career_show_displays_job_type_badge(): void
    {
        $opening = JobOpening::create([
            'title' => 'Graphic Designer Intern',
            'job_type' => JobOpening::TYPE_INTERNSHIP,
            'slug' => 'designer-intern',
            'description' => 'Magang desain grafis.',
            'is_open' => true,
        ]);

        $response = $this->get(route('career.show', $opening->slug));
        $response->assertOk();
        $response->assertSee(JobOpening::TYPE_INTERNSHIP);
    }

    public function test_hiring_request_stores_job_type_and_publishes_to_opening(): void
    {
        $company = Company::create(['code' => 'PTX', 'name' => 'PT X']);
        $token = HiringRequest::requestToken();

        $response = $this->post(route('hiring-requests.store', $token), [
            'requester_name' => 'Budi Santoso',
            'requester_position' => 'Manager Bengkel',
            'requester_phone' => '081234567890',
            'request_type' => HiringRequest::TYPE_NEW,
            'company_id' => $company->id,
            'title' => 'Mekanik Part Time',
            'job_type' => JobOpening::TYPE_PART_TIME,
            'headcount' => 2,
            'description' => 'Dibutuhkan mekanik paruh waktu.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('hiring_requests', [
            'title' => 'Mekanik Part Time',
            'job_type' => JobOpening::TYPE_PART_TIME,
        ]);

        $hiringRequest = HiringRequest::where('title', 'Mekanik Part Time')->firstOrFail();

        // Direct approve by HRD
        $reviewResponse = $this->actingAs($this->hrd)->patch(route('hiring-requests.publish', $hiringRequest), [
            'approval_action' => 'direct',
            'title' => 'Mekanik Part Time',
            'job_type' => JobOpening::TYPE_PART_TIME,
            'request_type' => HiringRequest::TYPE_NEW,
            'company_id' => $company->id,
            'headcount' => 2,
            'description' => 'Dibutuhkan mekanik paruh waktu.',
        ]);

        $reviewResponse->assertRedirect();

        $this->assertDatabaseHas('job_openings', [
            'title' => 'Mekanik Part Time',
            'job_type' => JobOpening::TYPE_PART_TIME,
            'is_open' => true,
        ]);
    }
}
