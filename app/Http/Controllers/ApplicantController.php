<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicantController extends Controller
{
    public function index(Request $request): View
    {
        $applications = Application::with(['candidate', 'jobOpening'])
            ->when($request->filled('job'), fn ($query) => $query->where('job_opening_id', $request->integer('job')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->string('q')->trim() . '%';

                $query->whereHas('candidate', fn ($candidate) => $candidate
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->latest()
            ->get();

        return view('recruitment.applicants', [
            'applications' => $applications,
            'openings' => JobOpening::orderBy('title')->pluck('title', 'id'),
        ]);
    }

    public function downloadCv(Application $application): StreamedResponse
    {
        $candidate = $application->candidate;
        $disk = Storage::disk('local');

        abort_unless($disk->exists($candidate->cv_path), 404);

        return $disk->download($candidate->cv_path, $candidate->cv_original_name);
    }
}
