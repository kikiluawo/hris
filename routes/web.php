<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\JobOpeningController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login-hr-majesty', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login-hr-majesty', [AuthenticatedSessionController::class, 'store']);

    // Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);

    // Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    // Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    // Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::get('/', [CareerController::class, 'index'])->name('career.index');
Route::redirect('karir', '/');
Route::get('karir/{slug}/terkirim', [CareerController::class, 'thanks'])->name('career.thanks');
Route::get('karir/{slug}', [CareerController::class, 'show'])->name('career.show');
Route::post('karir/{slug}/lamar', [CareerController::class, 'apply'])
    ->middleware('throttle:10,1')
    ->name('career.apply');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('karyawan', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('karyawan/export', [EmployeeController::class, 'export'])->name('employees.export');
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

    Route::get('rekrutmen/pelamar', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('rekrutmen/pelamar/{application}/cv', [ApplicantController::class, 'downloadCv'])->name('applicants.cv');

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

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
