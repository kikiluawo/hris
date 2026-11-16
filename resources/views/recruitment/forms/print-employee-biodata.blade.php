<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Biodata Karyawan - {{ $personal['full_name'] ?? $candidate->name }}</title>
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
        .section-num { font-weight: bold; font-size: 11px; margin-top: 10px; margin-bottom: 4px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 2px; }
        table.form-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.form-table td, table.form-table th { border: 1px solid #999; padding: 4px 6px; vertical-align: top; }
        table.form-table th { background: #f3f4f6; font-weight: bold; font-size: 10px; text-align: left; }
        .label { font-weight: 600; color: #222; font-size: 10px; }
        .val { font-size: 11px; color: #000; }
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
            <strong>Pratinjau Biodata Karyawan: {{ $personal['full_name'] ?? $candidate->name }}</strong>
            <span style="margin-left: 10px; color: #94a3b8;">PT. {{ $company->name ?? '—' }}</span>
        </div>
        <div>
            <button onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="{{ route('applicants.view-biodata', $application) }}" style="margin-left: 15px;">Tutup</a>
        </div>
    </div>

    <div class="sheet">
        <div class="header">
            <h1>FORMULIR BIODATA KARYAWAN</h1>
            <h2>PT. {{ $company->name ?? '________________________' }}</h2>
        </div>

        <div class="section-num">1. IDENTITAS PRIBADI</div>
        <table class="form-table">
            <tr>
                <td style="width: 25%;"><span class="label">Nama Lengkap:</span></td>
                <td style="width: 40%;"><strong class="val">{{ $personal['full_name'] ?? $candidate->name }}</strong></td>
                <td style="width: 15%;"><span class="label">Nama Panggilan:</span></td>
                <td style="width: 20%;"><span class="val">{{ $personal['nickname'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Tempat / Tanggal Lahir:</span></td>
                <td><span class="val">{{ $personal['birth_place'] ?? '' }}, {{ $personal['birth_date'] ?? '' }}</span></td>
                <td><span class="label">Jenis Kelamin:</span></td>
                <td><span class="val">{{ ($personal['gender'] ?? '') === 'pria' || ($personal['gender'] ?? '') === 'M' ? 'Pria' : 'Wanita' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Suku / Kewarganegaraan:</span></td>
                <td><span class="val">{{ $personal['race'] ?? '—' }} / {{ $personal['nationality'] ?? 'Indonesia' }}</span></td>
                <td><span class="label">Agama:</span></td>
                <td><span class="val">{{ $personal['religion'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Status Pernikahan:</span></td>
                <td><span class="val">{{ $personal['marital_status'] ?? '—' }}</span></td>
                <td><span class="label">Golongan Darah:</span></td>
                <td><span class="val">{{ $personal['blood_type'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Anak Ke / Dari:</span></td>
                <td colspan="3"><span class="val">Anak ke-{{ $personal['birth_order'] ?? '—' }} dari {{ $personal['siblings_count'] ?? '—' }} bersaudara</span></td>
            </tr>
            <tr>
                <td><span class="label">Alamat Tempat Tinggal:</span></td>
                <td colspan="3"><span class="val">{{ $personal['current_address'] ?? $candidate->address }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Email & No HP:</span></td>
                <td><span class="val">{{ $personal['email'] ?? $candidate->email }} · {{ $personal['phone_mobile'] ?? $candidate->phone }}</span></td>
                <td><span class="label">No. Telepon Rumah:</span></td>
                <td><span class="val">{{ $personal['phone_home'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Nomor KTP:</span></td>
                <td><strong class="val">{{ $personal['ktp_no'] ?? '—' }}</strong></td>
                <td><span class="label">Nomor NPWP:</span></td>
                <td><span class="val">{{ $personal['npwp_no'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">BPJS Ketenagakerjaan:</span></td>
                <td><span class="val">{{ $personal['bpjs_tk_no'] ?? '—' }}</span></td>
                <td><span class="label">Hobi / Kegemaran:</span></td>
                <td><span class="val">{{ $personal['hobby'] ?? '—' }}</span></td>
            </tr>
        </table>

        <div class="section-num">2. INFORMASI REKENING BANK</div>
        <table class="form-table">
            <tr>
                <td style="width: 25%;"><span class="label">No. Rekening Bank BCA:</span></td>
                <td style="width: 35%;"><strong class="val">{{ $bankAccounts['bca']['number'] ?? '—' }}</strong></td>
                <td style="width: 15%;"><span class="label">Cabang:</span></td>
                <td style="width: 25%;"><span class="val">{{ $bankAccounts['bca']['branch'] ?? '—' }}</span></td>
            </tr>
            <tr>
                <td><span class="label">No. Rekening Bank Lain:</span></td>
                <td><span class="val">{{ $bankAccounts['other']['bank_name'] ?? '—' }} ({{ $bankAccounts['other']['number'] ?? '—' }})</span></td>
                <td><span class="label">Cabang:</span></td>
                <td><span class="val">{{ $bankAccounts['other']['branch'] ?? '—' }}</span></td>
            </tr>
        </table>

        <div class="section-num">3. RIWAYAT PENDIDIKAN</div>
        <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">a. PENDIDIKAN FORMAL</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Nama & Alamat Sekolah</th>
                    <th style="width: 20%;">Jurusan</th>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 10%;">Tamat</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($eduFormal as $idx => $f)
                    @if (!empty($f['school']))
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $f['school'] }}</td>
                            <td>{{ $f['major'] ?? '—' }}</td>
                            <td>{{ $f['period'] ?? '—' }}</td>
                            <td>{{ $f['graduated'] ?? 'Tamat' }}</td>
                            <td>{{ $f['notes'] ?? '—' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" style="text-align: center; color: #888;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-num">4. PENGALAMAN KERJA</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Perusahaan</th>
                    <th style="width: 20%;">Jabatan</th>
                    <th style="width: 15%;">Tahun</th>
                    <th style="width: 20%;">Alamat Kantor</th>
                    <th style="width: 15%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workExperiences as $idx => $w)
                    @if (!empty($w['company']))
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $w['company'] }}</td>
                            <td>{{ $w['position'] ?? '—' }}</td>
                            <td>{{ $w['period'] ?? '—' }}</td>
                            <td>{{ $w['address'] ?? '—' }}</td>
                            <td>{{ $w['notes'] ?? '—' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" style="text-align: center; color: #888;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-num">5. TEMPAT TINGGAL SEJAK DILAHIRKAN (CHRONOLOGIS)</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Tahun s/d Tahun</th>
                    <th style="width: 55%;">Nama Jalan & Nomor Rumah</th>
                    <th style="width: 20%;">Kota</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($residences as $idx => $r)
                    @if (!empty($r['address']) || !empty($r['period']))
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $r['period'] ?? '—' }}</td>
                            <td>{{ $r['address'] ?? '—' }}</td>
                            <td>{{ $r['city'] ?? '—' }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="4" style="text-align: center; color: #888;">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-num">6. KETERANGAN KELUARGA</div>
        <table class="form-table">
            <tr>
                <td style="width: 20%;"><span class="label">Bapak:</span></td>
                <td style="width: 30%;"><span class="val">{{ $family['father']['name'] ?? '—' }}</span></td>
                <td style="width: 20%;"><span class="label">Pekerjaan & HP:</span></td>
                <td style="width: 30%;"><span class="val">{{ $family['father']['job'] ?? '—' }} ({{ $family['father']['phone'] ?? '—' }})</span></td>
            </tr>
            <tr>
                <td><span class="label">Ibu:</span></td>
                <td><span class="val">{{ $family['mother']['name'] ?? '—' }}</span></td>
                <td><span class="label">Pekerjaan & HP:</span></td>
                <td><span class="val">{{ $family['mother']['job'] ?? '—' }} ({{ $family['mother']['phone'] ?? '—' }})</span></td>
            </tr>
            @if (!empty($family['spouse']['name']))
                <tr>
                    <td><span class="label">Istri / Suami:</span></td>
                    <td><span class="val">{{ $family['spouse']['name'] }}</span></td>
                    <td><span class="label">Pekerjaan & HP:</span></td>
                    <td><span class="val">{{ $family['spouse']['job'] ?? '—' }} ({{ $family['spouse']['phone'] ?? '—' }})</span></td>
                </tr>
            @endif
        </table>

        <div style="font-size: 10px; font-weight: bold; margin-top: 6px; margin-bottom: 2px;">KAWAN-KAWAN TERDEKAT / SAHABAT KARIB (3 ORANG YG MASIH HIDUP)</div>
        <table class="form-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Nama Lengkap</th>
                    <th style="width: 20%;">No Telepon</th>
                    <th style="width: 25%;">Pekerjaan & Alamat</th>
                    <th style="width: 20%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($family['friends'] ?? [] as $idx => $fr)
                    @if (!empty($fr['name']))
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $fr['name'] }}</strong></td>
                            <td>{{ $fr['phone'] ?? '—' }}</td>
                            <td>{{ $fr['job_school'] ?? '—' }}</td>
                            <td>{{ $fr['notes'] ?? '—' }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: flex-end; padding: 0 20px;">
            <div style="font-size: 9.5px; color: #666; max-width: 60%;">
                Keterangan tersebut di atas saya buat dengan sebenarnya dengan penuh kesadaran dan rasa tanggung jawab.
            </div>
            <div style="text-align: center; width: 180px;">
                <p>{{ $biodata->signed_city ?? 'Kota' }}, {{ $biodata->submitted_at?->format('d/m/Y') ?? date('d/m/Y') }}</p>
                <div style="height: 50px;"></div>
                <p style="border-top: 1px solid #000; font-weight: bold;">( {{ $biodata->signed_name ?? $personal['full_name'] ?? $candidate->name }} )</p>
                <span style="font-size: 9px; color: #555;">Tanda Tangan Karyawan</span>
            </div>
        </div>
    </div>
</body>
</html>