@php
    $candidate = $application->candidate;
    $opening = $application->jobOpening;
    $company = $opening->company?->name ?: 'PT Majesty Group';
    $position = $opening->position?->name ?? $opening->title;
    $schedule = $application->interview_at;
    $hrName = $application->decidedBy?->name ?? 'Human Resources Department';

    $hour = $schedule ? (int) $schedule->format('H') : (int) now()->format('H');
    $timeGreeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

    $gender = $application->jobApplicationForm?->personal_data['gender'] ?? $application->jobApplicationForm?->personal_data['sex'] ?? null;
    $honorific = match (strtoupper((string) $gender)) {
        'M', 'L', 'PRIA', 'LAKI-LAKI' => 'Mr. ',
        'F', 'P', 'WANITA', 'PEREMPUAN' => 'Ms. ',
        default => '',
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interview Invitation - {{ $company }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f3;font-family:'Inter',Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f3;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e5e7eb;box-shadow:0 4px 6px -1px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background-color:#0c1e38;padding:28px 32px;">
                            <p style="margin:0;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#d4af37;font-weight:600;">INTERVIEW INVITATION</p>
                            <p style="margin:8px 0 0;font-size:22px;font-weight:bold;color:#ffffff;letter-spacing:0.5px;">{{ strtoupper($company) }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;font-size:14px;line-height:1.75;color:#374151;">
                            <p style="margin:0 0 16px;font-size:15px;color:#111827;">{{ $timeGreeting }}, {{ $honorific }}<strong>{{ $candidate->name }}</strong>,</p>

                            <p style="margin:0 0 16px;">
                                Greetings from <strong>{{ $company }}</strong>!
                            </p>

                            <p style="margin:0 0 16px;">
                                My name is <strong>{{ $hrName }}</strong> from the Human Resources Department of {{ $company }}.
                            </p>

                            <p style="margin:0 0 24px;">
                                We are pleased to invite you to participate in the next stage of our recruitment process for the <strong>{{ $position }}</strong> position at {{ $company }}.
                            </p>

                            <p style="margin:0 0 12px;font-weight:bold;font-size:14.5px;color:#111827;">
                                1. INTERVIEW SCHEDULE
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:18px 20px;font-size:13.5px;line-height:1.85;">
                                        <strong>Date:</strong> {{ $schedule?->format('l, F d, Y') ?? '—' }}<br>
                                        <strong>Time:</strong> {{ $schedule?->format('H:i') ?? '—' }} WIB<br>
                                        @if ($application->interview_mode === 'online')
                                            <strong>Method:</strong> Online / Video Call<br>
                                            <strong>Platform / Link:</strong> {{ $application->interview_location ?? '—' }}<br>
                                            @if ($application->interview_maps_url)
                                                <strong>Meeting Link:</strong> <a href="{{ $application->interview_maps_url }}" style="color:#0284c7;word-break:break-all;">{{ $application->interview_maps_url }}</a><br>
                                            @endif
                                        @else
                                            <strong>Venue:</strong> {{ $application->interview_location ?? '—' }}<br>
                                            @if ($application->interview_address)
                                                <strong>Address:</strong> {{ $application->interview_address }}<br>
                                            @endif
                                            @if ($application->interview_maps_url)
                                                <div style="margin-top:8px;">
                                                    <strong>📍 Google Maps Location:</strong><br>
                                                    <a href="{{ $application->interview_maps_url }}" target="_blank" style="color:#0284c7;text-decoration:none;font-weight:600;display:inline-block;margin-top:3px;">Buka di Google Maps &rarr;</a>
                                                </div>
                                            @endif
                                        @endif
                                        @if ($application->interview_notes)
                                            <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #cbd5e1;color:#475569;">
                                                <strong>Notes:</strong> {{ $application->interview_notes }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-weight:bold;font-size:14.5px;color:#111827;">
                                2. REQUIRED DOCUMENTS
                            </p>
                            <p style="margin:0 0 10px;font-size:13.5px;">
                                To support our recruitment and evaluation process, kindly submit the following documents:
                            </p>
                            <ol style="margin:0 0 14px 20px;padding:0;font-size:13.5px;line-height:1.75;">
                                <li>Job Application Form</li>
                                <li>Updated Curriculum Vitae (CV)</li>
                                <li>Copy of Educational Certificate</li>
                                <li>Academic Transcript</li>
                                <li>Copy of National ID Card (KTP)</li>
                                <li>Other relevant supporting documents, if applicable..</li>
                            </ol>
                            <p style="margin:0 0 24px;font-size:13px;color:#64748b;font-style:italic;">
                                Kindly bring all the required documents with you on the scheduled interview day for verification purposes.
                            </p>

                            <p style="margin:0 0 8px;font-weight:bold;font-size:14.5px;color:#111827;">
                                3. SELECTION PROCESS
                            </p>
                            <ul style="margin:0 0 24px 20px;padding:0;font-size:13.5px;line-height:1.75;">
                                <li>HR / User Interview</li>
                                <li>Assessment Test*</li>
                            </ul>

                            <p style="margin:0 0 16px;font-size:13.5px;">
                                Please confirm your attendance by replying to this email.
                            </p>

                            <p style="margin:0 0 24px;font-size:13.5px;">
                                We appreciate your interest in pursuing a career with {{ $company }} and look forward to meeting you during the selection process.<br>
                                Thank you for your attention and cooperation.
                            </p>

                            <p style="margin:0;font-size:13.5px;line-height:1.6;">
                                Best regards,<br><br>
                                <strong style="font-size:14px;color:#111827;">{{ $hrName }}</strong><br>
                                <span style="color:#64748b;">Human Resources Department</span><br>
                                <strong>{{ strtoupper($company) }}</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb;font-size:11.5px;line-height:1.6;color:#6b7280;text-align:center;">
                            This is an automated recruitment message from Majesty Group HR System.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
