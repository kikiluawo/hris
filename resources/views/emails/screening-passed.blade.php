@php
    $candidate = $application->candidate;
    $opening = $application->jobOpening;
    $company = $opening->company?->name ?? config('app.name');
    $position = $opening->position?->name ?? $opening->title;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lolos Screening</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f3;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f3;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color:#06231b;padding:28px 32px;">
                            <p style="margin:0;font-size:11px;letter-spacing:3px;text-transform:uppercase;color:#d4af37;">Rekrutmen</p>
                            <p style="margin:8px 0 0;font-size:20px;font-weight:bold;color:#ffffff;">{{ config('app.name') }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;font-size:15px;">Halo <strong>{{ $candidate->name }}</strong>,</p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;">
                                Terima kasih telah melamar posisi <strong>{{ $position }}</strong> di
                                <strong>{{ $company }}</strong>.
                            </p>

                            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;">
                                Kami senang menginformasikan bahwa CV Anda <strong>lolos tahap screening awal</strong>.
                                Tim HR akan menghubungi Anda untuk mengatur jadwal interview.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;background-color:#f0f7f2;border:1px solid #d7e8dd;border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 20px;font-size:14px;line-height:1.8;">
                                        <strong>Posisi:</strong> {{ $position }}<br>
                                        <strong>Perusahaan:</strong> {{ $company }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;font-size:14px;line-height:1.7;">
                                Mohon pastikan email dan nomor telepon Anda aktif agar kami mudah menghubungi Anda.
                            </p>

                            <p style="margin:24px 0 0;font-size:14px;line-height:1.7;">
                                Salam hangat,<br>
                                <strong>Tim Rekrutmen {{ $company }}</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 32px;background-color:#f9fafb;border-top:1px solid #e5e7eb;font-size:11.5px;line-height:1.6;color:#6b7280;">
                            Email ini dikirim otomatis. Mohon tidak membalas email ini.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
