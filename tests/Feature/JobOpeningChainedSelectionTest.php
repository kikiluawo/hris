<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\JobOpening;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOpeningChainedSelectionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Company $companyA;
    private Company $companyB;
    private Department $deptA;
    private Department $deptB;
    private Position $posA;
    private Position $posB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_hrd' => true]);

        $this->companyA = Company::create(['code' => 'PTA', 'name' => 'PT Alpha Majesty']);
        $this->companyB = Company::create(['code' => 'PTB', 'name' => 'PT Beta Majesty']);

        $this->deptA = Department::create(['company_id' => $this->companyA->id, 'name' => 'IT & Software']);
        $this->deptB = Department::create(['company_id' => $this->companyB->id, 'name' => 'Finance & Accounting']);

        $this->posA = Position::create(['department_id' => $this->deptA->id, 'name' => 'Senior Developer']);
        $this->posB = Position::create(['department_id' => $this->deptB->id, 'name' => 'Accounting Officer']);
    }

    public function test_openings_page_renders_with_organization_tree_and_chained_attributes(): void
    {
        $response = $this->actingAs($this->user)->get(route('openings.index'));

        $response->assertOk();
        $response->assertViewHas('organization');
        $response->assertSee('x-model="companyId"', false);
        $response->assertSee('x-model="departmentId"', false);
        $response->assertSee('x-model="positionId"', false);
        $response->assertSee(':disabled="!companyId || departmentOptions.length === 0"', false);
        $response->assertSee(':disabled="!companyId || !departmentId || positionOptions.length === 0"', false);
    }

    public function test_can_create_job_opening_with_valid_matching_hierarchy(): void
    {
        $response = $this->actingAs($this->user)->post(route('openings.store'), [
            'title' => 'Senior Developer',
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptA->id,
            'position_id' => $this->posA->id,
            'description' => 'Bertanggung jawab atas pengembangan arsitektur sistem HRD.',
            'screening_threshold' => 75,
            'is_open' => true,
        ]);

        $response->assertRedirect(route('openings.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_openings', [
            'title' => 'Senior Developer',
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptA->id,
            'position_id' => $this->posA->id,
            'screening_threshold' => 75,
        ]);
    }

    public function test_rejects_department_belonging_to_another_company(): void
    {
        $response = $this->actingAs($this->user)->post(route('openings.store'), [
            'title' => 'Cross Company Developer',
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptB->id, // belongs to companyB!
            'description' => 'Deskripsi testing.',
        ]);

        $response->assertSessionHasErrors(['department_id']);
        $this->assertSame(
            'Departemen yang dipilih tidak sesuai dengan PT yang dipilih.',
            session('errors')->first('department_id')
        );
    }

    public function test_rejects_position_belonging_to_another_department(): void
    {
        $response = $this->actingAs($this->user)->post(route('openings.store'), [
            'title' => 'Cross Department Role',
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptA->id,
            'position_id' => $this->posB->id, // belongs to deptB!
            'description' => 'Deskripsi testing.',
        ]);

        $response->assertSessionHasErrors(['position_id']);
        $this->assertSame(
            'Jabatan yang dipilih tidak sesuai dengan departemen yang dipilih.',
            session('errors')->first('position_id')
        );
    }

    public function test_can_update_job_opening_with_matching_hierarchy(): void
    {
        $opening = JobOpening::create([
            'title' => 'Initial Title',
            'slug' => 'initial-title',
            'company_id' => $this->companyA->id,
            'department_id' => $this->deptA->id,
            'position_id' => $this->posA->id,
            'description' => 'Deskripsi awal.',
            'is_open' => true,
        ]);

        $response = $this->actingAs($this->user)->patch(route('openings.update', $opening), [
            'title' => 'Updated Title',
            'company_id' => $this->companyB->id,
            'department_id' => $this->deptB->id,
            'position_id' => $this->posB->id,
            'description' => 'Deskripsi yang diperbarui.',
            'is_open' => true,
        ]);

        $response->assertRedirect(route('openings.index'));
        $response->assertSessionHasNoErrors();

        $opening->refresh();
        $this->assertSame('Updated Title', $opening->title);
        $this->assertSame($this->companyB->id, $opening->company_id);
        $this->assertSame($this->deptB->id, $opening->department_id);
        $this->assertSame($this->posB->id, $opening->position_id);
    }

    public function test_can_create_job_opening_without_company(): void
    {
        $response = $this->actingAs($this->user)->post(route('openings.store'), [
            'title' => 'General Holding Opening',
            'company_id' => null,
            'department_id' => null,
            'position_id' => null,
            'description' => 'Lowongan umum holding tanpa penempatan PT awal.',
            'screening_threshold' => 70,
            'is_open' => true,
        ]);

        $response->assertRedirect(route('openings.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('job_openings', [
            'title' => 'General Holding Opening',
            'company_id' => null,
            'department_id' => null,
            'position_id' => null,
        ]);
    }
}
