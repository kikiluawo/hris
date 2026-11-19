<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_and_filter_companies(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        Company::create(['code' => 'MJT', 'name' => 'PT Majesty Otomotif', 'address' => 'Batam', 'is_active' => true]);
        Company::create(['code' => 'SNS', 'name' => 'PT Sinar Sentosa', 'address' => 'Jakarta', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('companies.index', ['q' => 'Majesty']))
            ->assertOk()
            ->assertSee('PT Majesty Otomotif')
            ->assertDontSee('PT Sinar Sentosa');

        $this->actingAs($user)
            ->get(route('companies.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('PT Sinar Sentosa')
            ->assertDontSee('PT Majesty Otomotif');
    }

    public function test_can_search_and_filter_branches(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $coA = Company::create(['code' => 'MJT', 'name' => 'PT Majesty Otomotif', 'is_active' => true]);
        $coB = Company::create(['code' => 'NUD', 'name' => 'PT Nusantara Digital', 'is_active' => true]);

        Branch::create(['company_id' => $coA->id, 'code' => 'MJT-BTM', 'name' => 'Cabang Batam', 'city' => 'Batam', 'brand' => 'Chery', 'is_active' => true]);
        Branch::create(['company_id' => $coB->id, 'code' => 'NUD-JKT', 'name' => 'Cabang Jakarta', 'city' => 'Jakarta', 'brand' => 'Tech', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('branches.index', ['q' => 'Chery']))
            ->assertOk()
            ->assertSee('Cabang Batam')
            ->assertDontSee('Cabang Jakarta');

        $this->actingAs($user)
            ->get(route('branches.index', ['company' => $coB->id, 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Cabang Jakarta')
            ->assertDontSee('Cabang Batam');
    }

    public function test_can_search_and_filter_departments(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $coA = Company::create(['code' => 'MJT', 'name' => 'PT Majesty Otomotif', 'is_active' => true]);
        $coB = Company::create(['code' => 'NUD', 'name' => 'PT Nusantara Digital', 'is_active' => true]);

        Department::create(['company_id' => $coA->id, 'name' => 'Sales & Marketing', 'is_active' => true]);
        Department::create(['company_id' => $coB->id, 'name' => 'Engineering', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('departments.index', ['q' => 'Sales']))
            ->assertOk()
            ->assertSee('Sales & Marketing')
            ->assertDontSee('Engineering');

        $this->actingAs($user)
            ->get(route('departments.index', ['company' => $coB->id]))
            ->assertOk()
            ->assertSee('Engineering')
            ->assertDontSee('Sales & Marketing');
    }

    public function test_can_search_and_filter_positions(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $coA = Company::create(['code' => 'MJT', 'name' => 'PT Majesty Otomotif', 'is_active' => true]);
        $coB = Company::create(['code' => 'NUD', 'name' => 'PT Nusantara Digital', 'is_active' => true]);

        $deptA = Department::create(['company_id' => $coA->id, 'name' => 'Sales', 'is_active' => true]);
        $deptB = Department::create(['company_id' => $coB->id, 'name' => 'IT', 'is_active' => true]);

        Position::create(['department_id' => $deptA->id, 'name' => 'Sales Executive', 'is_active' => true]);
        Position::create(['department_id' => $deptB->id, 'name' => 'Backend Engineer', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('positions.index', ['q' => 'Backend']))
            ->assertOk()
            ->assertSee('Backend Engineer')
            ->assertDontSee('Sales Executive');

        $this->actingAs($user)
            ->get(route('positions.index', ['company' => $coA->id, 'department' => $deptA->id, 'status' => 'active']))
            ->assertOk()
            ->assertSee('Sales Executive')
            ->assertDontSee('Backend Engineer');
    }

    public function test_pagination_and_per_page_work_on_organization_pages(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $companies = collect();
        for ($i = 1; $i <= 15; $i++) {
            $companies->push(Company::create([
                'code' => sprintf('CO%02d', $i),
                'name' => sprintf('PT Contoh %02d', $i),
                'is_active' => true,
            ]));
        }

        $firstCompany = $companies->first();
        $departments = collect();
        for ($i = 1; $i <= 15; $i++) {
            Branch::create([
                'company_id' => $firstCompany->id,
                'code' => sprintf('BR%02d', $i),
                'name' => sprintf('Cabang %02d', $i),
                'is_active' => true,
            ]);

            $dept = Department::create([
                'company_id' => $firstCompany->id,
                'name' => sprintf('Departemen %02d', $i),
                'is_active' => true,
            ]);
            $departments->push($dept);
        }

        $firstDepartment = $departments->first();
        for ($i = 1; $i <= 15; $i++) {
            Position::create([
                'department_id' => $firstDepartment->id,
                'name' => sprintf('Jabatan %02d', $i),
                'is_active' => true,
            ]);
        }

        $this->actingAs($user)
            ->get(route('companies.index'))
            ->assertOk()
            ->assertSee('PT Contoh 01')
            ->assertSee('PT Contoh 10')
            ->assertDontSee('PT Contoh 11')
            ->assertSee('per_page');

        $this->actingAs($user)
            ->get(route('companies.index', ['per_page' => 25]))
            ->assertOk()
            ->assertSee('PT Contoh 15');

        $this->actingAs($user)
            ->get(route('companies.index', ['per_page' => 999]))
            ->assertOk()
            ->assertSee('PT Contoh 10')
            ->assertDontSee('PT Contoh 11');

        $this->actingAs($user)
            ->get(route('branches.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Cabang 11')
            ->assertDontSee('Cabang 01');

        $this->actingAs($user)
            ->get(route('departments.index', ['per_page' => 10, 'page' => 2]))
            ->assertOk()
            ->assertSee('Departemen 15')
            ->assertDontSee('Departemen 01');

        $this->actingAs($user)
            ->get(route('positions.index', ['per_page' => 50]))
            ->assertOk()
            ->assertSee('Jabatan 01')
            ->assertSee('Jabatan 15');
    }
}
