<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AssessmentAttemptController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssessmentImageController;
use App\Http\Controllers\AssessmentTestController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeBiodataController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HiringRequestAdminController;
use App\Http\Controllers\HiringRequestController;
use App\Http\Controllers\JobApplicationFormController;
use App\Http\Controllers\JobOpeningController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionBankController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login-hr-majesty', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login-hr-majesty', [AuthenticatedSessionController::class, 'store']);
});

Route::get('/', [CareerController::class, 'index'])->name('career.index');
Route::redirect('karir', '/');
Route::get('karir/{slug}/terkirim', [CareerController::class, 'thanks'])->name('career.thanks');
Route::get('karir/{slug}', [CareerController::class, 'show'])->name('career.show');
Route::post('karir/{slug}/lamar', [CareerController::class, 'apply'])
    ->middleware('throttle:10,1')
    ->name('career.apply');

Route::get('permintaan-karyawan/{token}', [HiringRequestController::class, 'create'])->name('hiring-requests.create');
Route::get('permintaan-karyawan/{token}/terkirim', [HiringRequestController::class, 'thanks'])->name('hiring-requests.thanks');
Route::post('permintaan-karyawan/{token}', [HiringRequestController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('hiring-requests.store');

Route::get('tes/{token}', [AssessmentTestController::class, 'landing'])->name('assessment.landing');
Route::post('tes/{token}/mulai', [AssessmentTestController::class, 'start'])
    ->middleware('throttle:20,1')
    ->name('assessment.start');
Route::get('tes/{token}/kerjakan', [AssessmentTestController::class, 'take'])->name('assessment.take');
Route::post('tes/{token}/jawab', [AssessmentTestController::class, 'answer'])
    ->middleware('throttle:240,1')
    ->name('assessment.answer');
Route::post('tes/{token}/pelanggaran', [AssessmentTestController::class, 'violation'])
    ->middleware('throttle:120,1')
    ->name('assessment.violation');
Route::post('tes/{token}/snapshot', [AssessmentTestController::class, 'snapshot'])
    ->middleware('throttle:60,1')
    ->name('assessment.snapshot');
Route::get('tes/{token}/status', [AssessmentTestController::class, 'status'])
    ->middleware('throttle:240,1')
    ->name('assessment.status');
Route::post('tes/{token}/selesai', [AssessmentTestController::class, 'submit'])
    ->middleware('throttle:30,1')
    ->name('assessment.submit');
Route::get('tes/{token}/hasil', [AssessmentTestController::class, 'finished'])->name('assessment.finished');

Route::get('formulir-lamaran/{token}', [JobApplicationFormController::class, 'show'])->name('application-forms.show');
Route::post('formulir-lamaran/{token}', [JobApplicationFormController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('application-forms.store');

Route::get('onboarding/{token}/biodata', [EmployeeBiodataController::class, 'show'])->name('employee-biodatas.show');
Route::post('onboarding/{token}/biodata', [EmployeeBiodataController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('employee-biodatas.store');

Route::redirect('admin-hrd', '/admin-hrd/dashboard');

Route::middleware(['auth', 'hrd'])->prefix('admin-hrd')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('karyawan', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('karyawan/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('karyawan/next-staff-number', [EmployeeController::class, 'nextStaffNumber'])->name('employees.next-staff-number');
    Route::get('karyawan/baru', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('karyawan', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('karyawan/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('karyawan/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::patch('karyawan/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

    Route::get('mutasi', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('mutasi/baru', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('mutasi', [TransferController::class, 'store'])->name('transfers.store');

    Route::get('organisasi/perusahaan', [CompanyController::class, 'index'])->name('companies.index');
    Route::post('organisasi/perusahaan', [CompanyController::class, 'store'])->name('companies.store');
    Route::patch('organisasi/perusahaan/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('organisasi/perusahaan/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

    Route::get('organisasi/cabang', [BranchController::class, 'index'])->name('branches.index');
    Route::post('organisasi/cabang', [BranchController::class, 'store'])->name('branches.store');
    Route::patch('organisasi/cabang/{branch}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('organisasi/cabang/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');

    Route::get('organisasi/departemen', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('organisasi/departemen', [DepartmentController::class, 'store'])->name('departments.store');
    Route::patch('organisasi/departemen/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('organisasi/departemen/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

    Route::get('organisasi/jabatan', [PositionController::class, 'index'])->name('positions.index');
    Route::post('organisasi/jabatan', [PositionController::class, 'store'])->name('positions.store');
    Route::patch('organisasi/jabatan/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('organisasi/jabatan/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');

    Route::get('rekrutmen/lowongan', [JobOpeningController::class, 'index'])->name('openings.index');
    Route::post('rekrutmen/lowongan', [JobOpeningController::class, 'store'])->name('openings.store');
    Route::patch('rekrutmen/lowongan/{jobOpening}', [JobOpeningController::class, 'update'])->name('openings.update');
    Route::patch('rekrutmen/lowongan/{jobOpening}/status', [JobOpeningController::class, 'toggleStatus'])->name('openings.toggle');
    Route::delete('rekrutmen/lowongan/{jobOpening}', [JobOpeningController::class, 'destroy'])->name('openings.destroy');

    Route::get('rekrutmen/permintaan', [HiringRequestAdminController::class, 'index'])->name('hiring-requests.index');
    Route::patch('rekrutmen/permintaan/token', [HiringRequestAdminController::class, 'rotateToken'])->name('hiring-requests.token');
    Route::get('rekrutmen/permintaan/{hiringRequest}', [HiringRequestAdminController::class, 'show'])->name('hiring-requests.show');
    Route::patch('rekrutmen/permintaan/{hiringRequest}', [HiringRequestAdminController::class, 'update'])->name('hiring-requests.update');
    Route::patch('rekrutmen/permintaan/{hiringRequest}/terbitkan', [HiringRequestAdminController::class, 'publish'])->name('hiring-requests.publish');
    Route::patch('rekrutmen/permintaan/{hiringRequest}/tolak', [HiringRequestAdminController::class, 'reject'])->name('hiring-requests.reject');

    Route::get('rekrutmen/pelamar', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::patch('rekrutmen/pelamar/{application}/status', [ApplicantController::class, 'updateStatus'])->name('applicants.status');
    Route::post('rekrutmen/pelamar/{application}/kirim-formulir', [ApplicantController::class, 'sendApplicationForm'])->name('applicants.send-form');
    Route::get('rekrutmen/pelamar/{application}/formulir-lamaran', [JobApplicationFormController::class, 'viewAdmin'])->name('applicants.view-form');
    Route::get('rekrutmen/pelamar/{application}/formulir-lamaran/cetak', [JobApplicationFormController::class, 'printAdmin'])->name('applicants.print-form');
    Route::patch('rekrutmen/pelamar/{application}/interview', [ApplicantController::class, 'scheduleInterview'])->name('applicants.interview');
    Route::patch('rekrutmen/pelamar/{application}/selesai-interview', [ApplicantController::class, 'completeInterview'])->name('applicants.complete-interview');
    Route::post('rekrutmen/pelamar/{application}/undang-tes', [ApplicantController::class, 'inviteAssessment'])->name('applicants.invite-assessment');
    Route::post('rekrutmen/pelamar/{application}/keputusan', [ApplicantController::class, 'decideFinal'])->name('applicants.decide-final');
    Route::get('rekrutmen/pelamar/{application}/biodata', [EmployeeBiodataController::class, 'viewAdmin'])->name('applicants.view-biodata');
    Route::get('rekrutmen/pelamar/{application}/biodata/cetak', [EmployeeBiodataController::class, 'printAdmin'])->name('applicants.print-biodata');
    Route::get('rekrutmen/pelamar/{application}/cv', [ApplicantController::class, 'downloadCv'])->name('applicants.cv');

    Route::get('rekrutmen/tes', [AssessmentController::class, 'index'])->name('assessments.index');
    Route::post('rekrutmen/tes/generate-disc', [AssessmentController::class, 'seedDisc'])->name('assessments.seed-disc');
    Route::post('rekrutmen/tes/generate-aptitude', [AssessmentController::class, 'seedAptitude'])->name('assessments.seed-aptitude');
    Route::post('rekrutmen/tes/generate-accuracy', [AssessmentController::class, 'seedAccuracy'])->name('assessments.seed-accuracy');
    Route::post('rekrutmen/tes/generate-comprehensive', [AssessmentController::class, 'seedComprehensive'])->name('assessments.seed-comprehensive');
    Route::get('rekrutmen/tes/baru', [AssessmentController::class, 'create'])->name('assessments.create');
    Route::post('rekrutmen/tes/unggah-gambar', [AssessmentImageController::class, 'store'])->name('assessments.image.store');
    Route::post('rekrutmen/tes', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::get('rekrutmen/tes/{assessment}', [AssessmentController::class, 'show'])->name('assessments.show');
    Route::get('rekrutmen/tes/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
    Route::patch('rekrutmen/tes/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
    Route::delete('rekrutmen/tes/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');
    Route::post('rekrutmen/tes/{assessment}/undangan', [AssessmentController::class, 'invite'])->name('assessments.invitations.store');
    Route::post('rekrutmen/tes/{assessment}/undangan/{invitation}/perbarui', [AssessmentController::class, 'renewInvitation'])->name('assessments.invitations.renew');
    Route::post('rekrutmen/tes/{assessment}/undangan/{invitation}/cabut', [AssessmentController::class, 'revokeInvitation'])->name('assessments.invitations.revoke');
    Route::delete('rekrutmen/tes/{assessment}/undangan/{invitation}', [AssessmentController::class, 'destroyInvitation'])->name('assessments.invitations.destroy');

    Route::get('rekrutmen/percobaan/{attempt}', [AssessmentAttemptController::class, 'show'])->name('assessments.attempt');
    Route::get('rekrutmen/percobaan-snapshot/{snapshot}', [AssessmentAttemptController::class, 'viewSnapshot'])->name('assessments.attempts.snapshot');
    Route::patch('rekrutmen/percobaan/{attempt}', [AssessmentAttemptController::class, 'grade'])->name('assessments.grade');

    Route::get('rekrutmen/bank-soal', [QuestionBankController::class, 'index'])->name('question-bank.index');
    Route::get('rekrutmen/bank-soal/baru', [QuestionBankController::class, 'create'])->name('question-bank.create');
    Route::post('rekrutmen/bank-soal', [QuestionBankController::class, 'store'])->name('question-bank.store');
    Route::post('rekrutmen/bank-soal/sync-standar', [QuestionBankController::class, 'syncStandard'])->name('question-bank.sync-standard');
    Route::post('rekrutmen/bank-soal/masukan', [QuestionBankController::class, 'import'])->name('question-bank.import');
    Route::get('rekrutmen/bank-soal/{questionBank}/edit', [QuestionBankController::class, 'edit'])->name('question-bank.edit');
    Route::patch('rekrutmen/bank-soal/{questionBank}', [QuestionBankController::class, 'update'])->name('question-bank.update');
    Route::delete('rekrutmen/bank-soal/{questionBank}', [QuestionBankController::class, 'destroy'])->name('question-bank.destroy');

    Route::get('cuti', [LeaveRequestController::class, 'index'])->name('leaves.index');
    Route::patch('cuti/{leaveRequest}/setujui', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
    Route::patch('cuti/{leaveRequest}/tolak', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

    Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profil/sandi', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('verifikasi-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verifikasi-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('konfirmasi-sandi', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('konfirmasi-sandi', [ConfirmablePasswordController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
