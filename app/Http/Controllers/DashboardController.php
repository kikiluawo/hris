<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\HiringRequest;
use App\Models\LeaveRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now();

        $totalEmployees = Employee::where('is_active', true)->count();
        $newEmployees = Employee::where('joined_at', '>=', $today->copy()->startOfMonth())->count();

        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $attendanceRate = $totalEmployees > 0
            ? round((($totalEmployees - $onLeaveToday) / $totalEmployees) * 100, 1)
            : 0.0;

        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();

        $newHiringRequests = HiringRequest::where('status', HiringRequest::STATUS_NEW)->count();

        $monthlyPayroll = (int) Employee::where('is_active', true)->sum('salary');

        $companyTotals = Company::query()
            ->withCount(['employees' => fn ($query) => $query->where('is_active', true)])
            ->orderByDesc('employees_count')
            ->take(6)
            ->get();

        $companyMax = max(1, (int) $companyTotals->max('employees_count'));

        $recentLeaves = LeaveRequest::with(['employee.company', 'employee.department'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(6)
            ->get();

        $activity = LeaveRequest::with(['employee.company', 'employee.department'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $attendanceLabels = collect(range(11, 0))
            ->map(fn (int $weeksAgo) => $today->copy()->subWeeks($weeksAgo)->startOfWeek()->translatedFormat('d M'));

        $attendanceValues = [96.4, 97.1, 96.8, 98.2, 97.6, 98.9, 97.9, 98.4, 96.9, 97.8, 98.6, 98.1];

        return view('dashboard', [
            'totalEmployees' => $totalEmployees,
            'newEmployees' => $newEmployees,
            'attendanceRate' => $attendanceRate,
            'onLeaveToday' => $onLeaveToday,
            'pendingLeaves' => $pendingLeaves,
            'newHiringRequests' => $newHiringRequests,
            'monthlyPayroll' => $monthlyPayroll,
            'companyTotals' => $companyTotals,
            'companyMax' => $companyMax,
            'recentLeaves' => $recentLeaves,
            'activity' => $activity,
            'attendanceLabels' => $attendanceLabels,
            'attendanceValues' => $attendanceValues,
        ]);
    }
}
