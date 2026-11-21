<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\HiringRequest;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HiringRequestApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $hrd;
    private User $ceo;
    private User $cfo;
    private Company $company;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hrd = User::factory()->create([
            'name' => 'Rina Kartika',
            'email' => 'admin@hrd.local',
            'is_hrd' => true,
            'role' => User::ROLE_HRD,
        ]);

        $this->ceo = User::factory()->create([
            'name' => 'Hadi Ong',
            'email' => 'ceo@hrd.local',
            'is_hrd' => true,
            'role' => User::ROLE_CEO,
        ]);

        $this->cfo = User::factory()->create([
            'name' => 'Sri Rahayu',
            'email' => 'cfo@hrd.local',
            'is_hrd' => true,
            'role' => User::ROLE_CFO,
        ]);

        $this->company = Company::create([
            'name' => 'PT Majesty Motor',
            'code' => 'MM',
        ]);

        $this->token = HiringRequest::rotateRequestToken();
    }

    public function test_can_submit_new_hire_request(): void
    {
        $response = $this->post(route('hiring-requests.store', $this->token), [
            'requester_name' => 'Ahmad Suhendra',
            'requester_position' => 'Kepala Bengkel',
            'requester_phone' => '081234567890',
            'requester_email' => 'ahmad@majesty.co.id',
            'requesting_unit' => 'Divisi Aftersales',
            'request_type' => 'baru',
            'title' => 'Mekanik Senior',
            'headcount' => 2,
            'description' => 'Membutuhkan mekanik senior untuk menangani overhaul mesin.',
        ]);

        $response->assertRedirect(route('hiring-requests.thanks', $this->token));

        $this->assertDatabaseHas('hiring_requests', [
            'requester_name' => 'Ahmad Suhendra',
            'request_type' => 'baru',
            'title' => 'Mekanik Senior',
            'headcount' => 2,
            'status' => HiringRequest::STATUS_NEW,
            'replaced_employee_name' => null,
            'replacement_reason' => null,
        ]);
    }

    public function test_replacement_request_validates_required_fields(): void
    {
        $response = $this->from(route('hiring-requests.create', $this->token))
            ->post(route('hiring-requests.store', $this->token), [
                'requester_name' => 'Ahmad Suhendra',
                'requester_position' => 'Kepala Bengkel',
                'requester_phone' => '081234567890',
                'request_type' => 'pergantian',
                'title' => 'Mekanik Senior',
                'headcount' => 1,
                'description' => 'Menggantikan mekanik yang resign.',
            ]);

        $response->assertSessionHasErrors(['replaced_employee_name', 'replacement_reason']);
    }

    public function test_can_submit_replacement_request_with_details(): void
    {
        $response = $this->post(route('hiring-requests.store', $this->token), [
            'requester_name' => 'Ahmad Suhendra',
            'requester_position' => 'Kepala Bengkel',
            'requester_phone' => '081234567890',
            'request_type' => 'pergantian',
            'replaced_employee_name' => 'Budi Santoso',
            'replacement_reason' => 'Mengundurkan diri per 30 November karena pindah domisili.',
            'title' => 'Service Advisor',
            'headcount' => 1,
            'description' => 'Menggantikan Budi Santoso sebagai front liner penerimaan servis.',
        ]);

        $response->assertRedirect(route('hiring-requests.thanks', $this->token));

        $this->assertDatabaseHas('hiring_requests', [
            'request_type' => 'pergantian',
            'replaced_employee_name' => 'Budi Santoso',
            'replacement_reason' => 'Mengundurkan diri per 30 November karena pindah domisili.',
            'title' => 'Service Advisor',
        ]);
    }

    public function test_hrd_can_directly_approve_and_publish_opening(): void
    {
        $hiringRequest = HiringRequest::create([
            'requester_name' => 'Ahmad',
            'requester_position' => 'Kabeng',
            'requester_phone' => '08123',
            'request_type' => 'baru',
            'title' => 'Kasir Bengkel',
            'headcount' => 1,
            'description' => 'Mencatat transaksi servis harian.',
            'company_id' => $this->company->id,
            'status' => HiringRequest::STATUS_NEW,
        ]);

        $response = $this->actingAs($this->hrd)
            ->patch(route('hiring-requests.publish', $hiringRequest), [
                'approval_action' => 'direct',
                'approval_route' => 'direct',
                'title' => 'Kasir Bengkel',
                'company_id' => $this->company->id,
                'headcount' => 1,
                'description' => 'Mencatat transaksi servis harian dan laporan kasir.',
                'request_type' => 'baru',
            ]);

        $response->assertRedirect(route('hiring-requests.show', $hiringRequest));

        $hiringRequest->refresh();
        $this->assertEquals(HiringRequest::STATUS_PUBLISHED, $hiringRequest->status);
        $this->assertNotNull($hiringRequest->job_opening_id);

        $this->assertDatabaseHas('job_openings', [
            'id' => $hiringRequest->job_opening_id,
            'title' => 'Kasir Bengkel',
            'is_open' => true,
        ]);
    }

    public function test_hrd_can_submit_for_ceo_approval_and_ceo_approves(): void
    {
        $hiringRequest = HiringRequest::create([
            'requester_name' => 'Ahmad',
            'requester_position' => 'Kabeng',
            'requester_phone' => '08123',
            'request_type' => 'pergantian',
            'replaced_employee_name' => 'Dodi',
            'replacement_reason' => 'Resign',
            'title' => 'Kepala Regu Mekanik',
            'headcount' => 1,
            'description' => 'Supervisi perbaikan mesin.',
            'company_id' => $this->company->id,
            'status' => HiringRequest::STATUS_NEW,
        ]);

        // HRD sends for CEO approval
        $this->actingAs($this->hrd)
            ->patch(route('hiring-requests.publish', $hiringRequest), [
                'approval_action' => 'executive',
                'approval_route' => 'ceo',
                'title' => 'Kepala Regu Mekanik',
                'company_id' => $this->company->id,
                'headcount' => 1,
                'description' => 'Supervisi perbaikan mesin.',
                'request_type' => 'pergantian',
                'replaced_employee_name' => 'Dodi',
                'replacement_reason' => 'Resign',
            ]);

        $hiringRequest->refresh();
        $this->assertEquals(HiringRequest::STATUS_PENDING_APPROVAL, $hiringRequest->status);
        $this->assertEquals('ceo', $hiringRequest->approval_route);
        $this->assertEquals('pending', $hiringRequest->ceo_approval_status);

        // CEO Hadi Ong approves
        $response = $this->actingAs($this->ceo)
            ->patch(route('hiring-requests.approve', $hiringRequest), [
                'acting_as' => 'ceo',
                'approval_notes' => 'Disetujui untuk penggantian segera.',
            ]);

        $response->assertRedirect(route('hiring-requests.show', $hiringRequest));

        $hiringRequest->refresh();
        $this->assertEquals(HiringRequest::STATUS_PUBLISHED, $hiringRequest->status);
        $this->assertEquals('approved', $hiringRequest->ceo_approval_status);
        $this->assertEquals($this->ceo->id, $hiringRequest->ceo_approved_by);
        $this->assertNotNull($hiringRequest->job_opening_id);
    }

    public function test_approval_both_requires_both_ceo_and_cfo(): void
    {
        $hiringRequest = HiringRequest::create([
            'requester_name' => 'Ahmad',
            'requester_position' => 'Kabeng',
            'requester_phone' => '08123',
            'request_type' => 'baru',
            'title' => 'Branch Manager Trainee',
            'headcount' => 1,
            'description' => 'Pelatihan kepemimpinan cabang.',
            'company_id' => $this->company->id,
            'status' => HiringRequest::STATUS_NEW,
        ]);

        // HRD routes for both CEO & CFO
        $this->actingAs($this->hrd)
            ->patch(route('hiring-requests.publish', $hiringRequest), [
                'approval_action' => 'executive',
                'approval_route' => 'both',
                'title' => 'Branch Manager Trainee',
                'company_id' => $this->company->id,
                'headcount' => 1,
                'description' => 'Pelatihan kepemimpinan cabang.',
                'request_type' => 'baru',
            ]);

        $hiringRequest->refresh();
        $this->assertEquals(HiringRequest::STATUS_PENDING_APPROVAL, $hiringRequest->status);

        // 1. CEO approves first
        $this->actingAs($this->ceo)
            ->patch(route('hiring-requests.approve', $hiringRequest), [
                'acting_as' => 'ceo',
            ]);

        $hiringRequest->refresh();
        // Still pending approval because CFO has not approved yet
        $this->assertEquals(HiringRequest::STATUS_PENDING_APPROVAL, $hiringRequest->status);
        $this->assertEquals('approved', $hiringRequest->ceo_approval_status);
        $this->assertEquals('pending', $hiringRequest->cfo_approval_status);
        $this->assertNull($hiringRequest->job_opening_id);

        // 2. CFO Sri Rahayu approves second
        $this->actingAs($this->cfo)
            ->patch(route('hiring-requests.approve', $hiringRequest), [
                'acting_as' => 'cfo',
            ]);

        $hiringRequest->refresh();
        // Now both satisfied -> published!
        $this->assertEquals(HiringRequest::STATUS_PUBLISHED, $hiringRequest->status);
        $this->assertEquals('approved', $hiringRequest->cfo_approval_status);
        $this->assertNotNull($hiringRequest->job_opening_id);
    }

    public function test_executive_can_reject_hiring_request(): void
    {
        $hiringRequest = HiringRequest::create([
            'requester_name' => 'Ahmad',
            'requester_position' => 'Kabeng',
            'requester_phone' => '08123',
            'request_type' => 'baru',
            'title' => 'Helper Gudang',
            'headcount' => 3,
            'description' => 'Beban gudang melonjak.',
            'status' => HiringRequest::STATUS_PENDING_APPROVAL,
            'approval_route' => 'cfo',
            'cfo_approval_status' => 'pending',
        ]);

        $response = $this->actingAs($this->cfo)
            ->patch(route('hiring-requests.reject', $hiringRequest), [
                'rejection_reason' => 'Anggaran biaya operasional Q4 belum mencukupi.',
            ]);

        $response->assertRedirect(route('hiring-requests.show', $hiringRequest));

        $hiringRequest->refresh();
        $this->assertEquals(HiringRequest::STATUS_REJECTED, $hiringRequest->status);
        $this->assertEquals('rejected', $hiringRequest->cfo_approval_status);
        $this->assertStringContainsString('Anggaran biaya operasional Q4', $hiringRequest->rejection_reason);
    }
}
