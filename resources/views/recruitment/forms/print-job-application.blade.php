<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Application Form - {{ $candidate->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=1">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=1">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; font-size: 11px; line-height: 1.35; color: #111; background: #f3f4f6; }
        .no-print { background: #1e293b; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50; }
        .no-print button { background: #059669; color: white; border: none; padding: 8px 18px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; }
        .no-print a { color: #94a3b8; text-decoration: none; font-size: 13px; }
        .sheet { max-width: 210mm; margin: 15mm auto; background: white; padding: 15mm; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header h1 { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
        .header h2 { font-size: 13px; font-weight: normal; margin-top: 2px; }
        .confidential { border: 1px solid #666; padding: 6px; font-size: 9.5px; margin-bottom: 12px; background: #fafafa; }
        .section-title { background: #e5e7eb; font-weight: bold; font-size: 11px; padding: 4px 6px; border: 1px solid #000; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase; }
        table.form-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.form-table td, table.form-table th { border: 1px solid #999; padding: 4px 6px; vertical-align: top; }
        table.form-table th { background: #f3f4f6; font-weight: bold; font-size: 10px; text-align: left; }
        .label { font-weight: 600; color: #222; font-size: 10px; }
        .val { font-size: 11px; color: #000; }
        .sub-label { font-size: 9px; color: #555; font-style: italic; }
        .photo-box { width: 30mm; height: 40mm; border: 1px dashed #666; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 9px; color: #666; margin: 0 auto; overflow: hidden; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .office-use { margin-top: 15px; border: 2px solid #000; padding: 8px; }
        .office-use h3 { font-size: 11px; text-align: center; border-bottom: 1px solid #000; padding-bottom: 4px; margin-bottom: 6px; }
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .sheet { margin: 0; padding: 0; box-shadow: none; max-width: 100%; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <div>
            <strong>Pratinjau Formulir Lamaran: {{ $candidate->name }}</strong>
            <span style="margin-left: 10px; color: #94a3b8;">Posisi: {{ $jobOpening->title }}</span>
        </div>
        <div>
            <button onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="{{ route('applicants.view-form', $application) }}" style="margin-left: 15px;">Tutup</a>
        </div>
    </div>

    @php
        $personal = $form->personal_data ?? [];
        $family = $form->family_data ?? [];
        $education = $form->education_data ?? [];
        $work = $form->work_history ?? [];
        $additional = $form->additional_info ?? [];
    @endphp

    <div class="sheet">
        <div class="header">
            <h1>JOB APPLICATION FORM / FORMULIR LAMARAN KERJA</h1>
            <h2>{{ strtoupper($jobOpening->company?->name ?: 'PT MAJESTY GROUP') }}</h2>
        </div>

        <div class="confidential">
            <strong>CONFIDENTIAL:</strong> Informasi dalam formulir ini akan diperlakukan dengan sangat rahasia dan tidak akan diungkapkan kepada pihak luar tanpa izin anda.<br>
            <em>The information included in this form will be treated in the strictest confidence and will not be revealed to any outside party without your permission.</em>
        </div>

        <table class="form-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">Posisi yang Dilamar / Position applied:</span><br>
                    <strong class="val">{{ $jobOpening->title }}</strong>
                </td>
                <td style="width: 25%;">
                    <span class="label">Tanggal / Date:</span><br>
                    <span class="val">{{ $form->submitted_at?->format('d/m/Y') ?? date('d/m/Y') }}</span>
                </td>
                <td rowspan="4" style="width: 25%; text-align: center; vertical-align: middle;">
                    <div class="photo-box">
                        @if ($form->photo_path)
                            <img src="{{ asset('storage/' . $form->photo_path) }}" alt="Foto Pelamar">
                        @else
                            Foto Pelamar<br>(3x4 / 4x6)
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Nama / Name:</span><br>
                    <strong class="val">{{ $personal['name'] ?? $candidate->name }}</strong>
                </td>
                <td>
                    <span class="label">Alias (If any):</span><br>
                    <span class="val">{{ $personal['alias'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Alamat / Address:</span><br>
                    <span class="val">{{ $personal['address'] ?? $candidate->address }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Email Address:</span><br>
                    <span class="val">{{ $personal['email'] ?? $candidate->email }}</span>
                </td>
                <td>
                    <span class="label">Mobile Phone:</span><br>
                    <strong class="val">{{ $personal['phone_mobile'] ?? $candidate->phone }}</strong>
                </td>
            </tr>
        </table>

        <table class="form-table">
            <tr>
                <td style="width: 20%;">
                    <span class="label">Jenis Kelamin / Sex:</span><br>
                    <span class="val">{{ ($personal['gender'] ?? '') === 'pria' || ($personal['gender'] ?? '') === 'M' ? 'L (M)' : 'P (F)' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Suku / Race:</span><br>
                    <span class="val">{{ $personal['race'] ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Usia / Age:</span><br>
                    <span class="val">{{ $personal['age'] ?? '—' }} Thn</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Agama / Religion:</span><br>
                    <span class="val">{{ $personal['religion'] ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Status Pernikahan:</span><br>
                    <span class="val">{{ $personal['marital_status'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Place & Date of Birth:</span><br>
                    <span class="val">{{ $personal['birth_place'] ?? '' }}, {{ $personal['birth_date'] ?? '' }}</span>
                </td>
                <td colspan="2">
                    <span class="label">No KTP / NIRC:</span><br>
                    <strong class="val">{{ $personal['ktp_no'] ?? '—' }}</strong>
                </td>
                <td>
                    <span class="label">Kewarganegaraan:</span><br>
                    <span class="val">{{ $personal['nationality'] ?? 'Indonesia' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Penyakit Serius:</span><br>
                    <span class="val">{{ $personal['serious_illness'] ?? 'Tidak ada' }}</span>
                </td>
                <td colspan="2">
                    <span class="label">Cacat Fisik:</span><br>
                    <span class="val">{{ $personal['disabilities'] ?? 'Tidak ada' }}</span>
                </td>
                <td>
                    <span class="label">Kondisi Kesehatan:</span><br>
                    <span class="val">{{ $personal['present_health'] ?? 'Sehat' }}</span>
                </td>
            </tr>
        </table>

        <div class="section-title">DATA KELUARGA / FAMILY PARTICULAR</div>
        <table class="form-table">
            <tr>
                <td style="width: 35%;">
                    <span class="label">Nama Pasangan / Spouse's Name:</span><br>
                    <span class="val">{{ $family['spouse']['name'] ?? '—' }}</span>
                </td>
                <td style="width: 25%;">
                    <span class="label">Pekerjaan / Occupation:</span><br>
                    <span class="val">{{ $family['spouse']['occupation'] ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Tanggal Lahir:</span><br>
                    <span class="val">{{ $family['spouse']['dob'] ?? '—' }}</span>
                </td>
                <td style="width: 20%;">
                    <span class="label">Kewarganegaraan:</span><br>
                    <span class="val">{{ $family['spouse']['nationality'] ?? '—' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Orang Tua (Ayah):</span> {{ $family['father']['name'] ?? '—' }} ({{ $family['father']['occupation'] ?? '—' }})
                </td>
                <td colspan="2">
                    <span class="label">Orang Tua (Ibu):</span> {{ $family['mother']['name'] ?? '—' }} ({{ $family['mother']['occupation'] ?? '—' }})
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Nama Anak:</span><br>
                    @foreach ($family['children'] ?? [] as $idx => $c)
                        @if (!empty($c['name']))
                            <div>{{ $idx + 1 }}) {{ $c['name'] }} ({{ $c['gender'] ?? '' }} - {{ $c['dob'] ?? '' }})</div>
                        @endif
                    @endforeach
                </td>
                <td colspan="2">
                    <span class="label">Nama Saudara:</span><br>
                    @foreach ($family['siblings'] ?? [] as $idx => $s)
                        @if (!empty($s['name']))
                            <div>{{ $idx + 1 }}) {{ $s['name'] }} ({{ $s['job'] ?? '' }})</div>
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Gaji Minimal yang Diharapkan:</span><br>
                    <strong class="val">Rp {{ number_format((float)($family['expected_salary'] ?? 0), 0, ',', '.') }}</strong>
                </td>
                <td colspan="2">
                    <span class="label">Kapan Dapat Bergabung:</span><br>
                    <strong class="val">{{ $family['notice_period'] ?? 'Segera' }}</strong>
                </td>
            </tr>
        </table>

        <div class="section-title">PENDIDIKAN / EDUCATION & SKILLS</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Tingkat</th>
                    <th style="width: 45%;">Nama Sekolah / Universitas</th>
                    <th style="width: 15%;">Periode</th>
                    <th style="width: 15%;">Pencapaian / Jurusan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Senior High School</td>
                    <td>{{ $education['schools']['sma']['name'] ?? '—' }}</td>
                    <td>{{ $education['schools']['sma']['from'] ?? '' }} - {{ $education['schools']['sma']['to'] ?? '' }}</td>
                    <td>{{ $education['schools']['sma']['achievement'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Universitas / University</td>
                    <td>{{ $education['schools']['university']['name'] ?? '—' }}</td>
                    <td>{{ $education['schools']['university']['from'] ?? '' }} - {{ $education['schools']['university']['to'] ?? '' }}</td>
                    <td>{{ $education['schools']['university']['achievement'] ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Lain-lain / Kursus</td>
                    <td>{{ $education['schools']['courses']['name'] ?? '—' }}</td>
                    <td>{{ $education['schools']['courses']['from'] ?? '' }} - {{ $education['schools']['courses']['to'] ?? '' }}</td>
                    <td>{{ $education['schools']['courses']['achievement'] ?? '—' }}</td>
                </tr>
            </tbody>
        </table>

        <table class="form-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">3 Kelebihan Anda / Strengths:</span>
                    <ol style="padding-left: 15px; margin-top: 3px;">
                        @foreach ($education['strengths'] ?? [] as $st)
                            <li>{{ $st }}</li>
                        @endforeach
                    </ol>
                </td>
                <td style="width: 50%;">
                    <span class="label">3 Kekurangan Anda / Weaknesses:</span>
                    <ol style="padding-left: 15px; margin-top: 3px;">
                        @foreach ($education['weaknesses'] ?? [] as $wk)
                            <li>{{ $wk }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Penguasaan Komputer / Computer Skills:</span><br>
                    <span class="val">{{ $education['computer_skills'] ?? '—' }}</span>
                </td>
            </tr>
        </table>

        <div class="section-title">RIWAYAT PEKERJAAN / CAREER HISTORY</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Perusahaan & Jabatan</th>
                    <th style="width: 15%;">Periode</th>
                    <th style="width: 15%;">Melapor Ke</th>
                    <th style="width: 25%;">Tanggung Jawab</th>
                    <th style="width: 15%;">Alasan Keluar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($work['jobs'] ?? [] as $job)
                    @if (!empty($job['company']))
                        <tr>
                            <td><strong>{{ $job['position'] ?? '—' }}</strong><br>{{ $job['company'] }}</td>
                            <td>{{ $job['period_from'] ?? '' }} - {{ $job['period_to'] ?? '' }}</td>
                            <td>{{ $job['report_to'] ?? '—' }}</td>
                            <td>{{ $job['responsibilities'] ?? '—' }}</td>
                            <td>{{ $job['reason_leaving'] ?? '—' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #888;">Tidak ada data riwayat pekerjaan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="form-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">Pernah Dihukum Pidana:</span> {{ $additional['criminal_record'] ?? 'Tidak' }}<br>
                    <span class="label">Kepemilikan SIM:</span> {{ $additional['driving_license'] ?? '—' }}<br>
                    <span class="label">Kendaraan Pribadi:</span> {{ $additional['vehicle_owned'] ?? '—' }}
                </td>
                <td style="width: 50%;">
                    <span class="label">Bisnis Lain:</span> {{ $additional['other_business'] ?? 'Tidak ada' }}<br>
                    <span class="label">Pertimbangan Kesesuaian:</span> {{ $additional['suitability_statement'] ?? '—' }}
                </td>
            </tr>
        </table>

        <div style="margin-top: 15px; display: flex; justify-content: space-between; align-items: flex-end; padding: 0 20px;">
            <div style="font-size: 9.5px; color: #666; max-width: 60%;">
                Dengan ini saya menyatakan bahwa keterangan yang saya berikan adalah benar dan lengkap.
            </div>
            <div style="text-align: center; width: 180px;">
                <p>{{ $personal['birth_place'] ?? 'Tempat' }}, {{ $form->submitted_at?->format('d/m/Y') ?? date('d/m/Y') }}</p>
                <div style="height: 50px;"></div>
                <p style="border-top: 1px solid #000; font-weight: bold;">( {{ $form->signature_name ?? $candidate->name }} )</p>
                <span class="sub-label">Tanda Tangan Pelamar</span>
            </div>
        </div>

        <div class="office-use">
            <h3>FOR OFFICE USE / UNTUK KEGUNAAN KANTOR</h3>
            <table style="width: 100%; font-size: 10px;">
                <tr>
                    <td style="width: 33%;">Interview Oleh: __________________</td>
                    <td style="width: 33%;">Tanggal: __________________</td>
                    <td style="width: 34%;">Keputusan: [  ] Diterima  [  ] Belum Sesuai</td>
                </tr>
                <tr>
                    <td colspan="3" style="padding-top: 8px;">Catatan Pewawancara: __________________________________________________________________________________</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>