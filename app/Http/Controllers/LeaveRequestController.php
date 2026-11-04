<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'type']);

        $leaves = LeaveRequest::with(['employee.company', 'employee.department'])
            ->filter($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        return view('leaves.index', [
            'leaves' => $leaves,
            'filters' => $filters,
            'counts' => $counts,
        ]);
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => 'approved']);

        return back()->with('status', "Pengajuan {$leaveRequest->typeLabel()} dari {$leaveRequest->employee->name} telah disetujui.");
    }

    public function reject(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => 'rejected']);

        return back()->with('status', "Pengajuan {$leaveRequest->typeLabel()} dari {$leaveRequest->employee->name} telah ditolak.");
    }
}
