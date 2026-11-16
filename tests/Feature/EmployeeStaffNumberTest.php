<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeStaffNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_number_generator_follows_format(): void
    {
        $company = Company::create([
            'code' => 'TEST01',
            'name' => 'PT Test Indonesia',
            'address' => 'Jakarta',
        ]);

        $staffNumber1 = Employee::generateStaffNumber($company, '2026-11-14');
        $this->assertSame('TEST01-2026.11.001', $staffNumber1);
    }

    public function test_employment_status_supports_probation(): void
    {
        $options = Employee::employmentStatusOptions();
        $this->assertArrayHasKey('probation', $options);
        $this->assertSame('Probation', $options['probation']);

        $employee = new Employee(['employment_status' => 'probation']);
        $this->assertSame('Probation', $employee->employmentStatusLabel());
        $this->assertSame('indigo', $employee->employmentStatusTone());
    }

    public function test_can_create_employee_without_salary_and_with_probation(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $company = Company::create(['code' => 'CO01', 'name' => 'Company Test']);
        $department = Department::create(['company_id' => $company->id, 'name' => 'HR']);
        $position = Position::create(['department_id' => $department->id, 'name' => 'Staff']);

        $response = $this->actingAs($user)->post(route('employees.store'), [
            'name' => 'Candidate Tester',
            'email' => 'candidate.tester@example.com',
            'phone' => '08123456789',
            'company_id' => $company->id,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employment_status' => 'probation',
            'joined_at' => '2026-11-14',
            // Notice: salary is omitted!
            // Notice: nik is omitted to test auto-generation!
        ]);

        $response->assertSessionHasNoErrors();
        $employee = Employee::where('email', 'candidate.tester@example.com')->first();
        $this->assertNotNull($employee);
        $this->assertSame('probation', $employee->employment_status);
        $this->assertNull($employee->salary);
        $this->assertSame('CO01-2026.11.001', $employee->nik);
    }

    public function test_next_staff_number_api_returns_json(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);
        $company = Company::create(['code' => 'CO02', 'name' => 'Company Two']);

        $response = $this->actingAs($user)->getJson(route('employees.next-staff-number', [
            'company_id' => $company->id,
            'joined_at' => '2026-11-14',
        ]));

        $response->assertOk();
        $response->assertJsonStructure(['staff_number']);
        $this->assertSame('CO02-2026.11.001', $response->json('staff_number'));
    }

    public function test_can_render_employee_create_form(): void
    {
        $user = User::factory()->create(['is_hrd' => true]);

        $response = $this->actingAs($user)->get(route('employees.create'));

        $response->assertOk();
        $response->assertSee('No. staff');
        $response->assertDontSee('Gaji bulanan');
        $response->assertSee('Probation');
    }
}
