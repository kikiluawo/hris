<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'company', 'department', 'status']);

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $employees = Employee::with(['company', 'branch', 'department', 'position'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'filters' => $filters,
            'perPage' => $perPage,
            'companies' => Company::orderBy('name')->get(),
            'departments' => Department::with('company')->orderBy('company_id')->orderBy('name')->get(),
            'statuses' => Employee::employmentStatusOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $employee = null;
        $applicationId = $request->query('application_id');

        if ($applicationId) {
            $application = Application::with([
                'candidate',
                'jobOpening.company',
                'jobOpening.department',
                'jobOpening.position',
                'jobApplicationForm',
                'employeeBiodata',
            ])->find($applicationId);

            if ($application) {
                $candidate = $application->candidate;
                $biodata = $application->employeeBiodata;
                $jobForm = $application->jobApplicationForm;
                $personal = $biodata?->personal_data ?? [];
                $formPersonal = $jobForm?->personal_details ?? [];
                $family = $biodata?->family_details ?? [];
                $formFamily = $jobForm?->family_data ?? [];

                $rawGender = $personal['gender'] ?? $formPersonal['gender'] ?? null;
                $gender = match ($rawGender) {
                    'pria', 'L', 'M', 'laki-laki' => 'pria',
                    'wanita', 'P', 'F', 'perempuan' => 'wanita',
                    default => null,
                };

                $children = $biodata?->children ?? $jobForm?->family_data['children'] ?? [];
                $daughters = 0;
                $sons = 0;
                $daughterNames = [];
                $sonNames = [];
                if (is_array($children)) {
                    foreach ($children as $c) {
                        $cGender = strtolower((string) ($c['gender'] ?? ''));
                        $cName = trim((string) ($c['name'] ?? ''));
                        if (in_array($cGender, ['p', 'wanita', 'perempuan', 'f'], true)) {
                            $daughters++;
                            if ($cName !== '') {
                                $daughterNames[] = $cName;
                            }
                        } elseif (in_array($cGender, ['l', 'pria', 'laki-laki', 'm'], true)) {
                            $sons++;
                            if ($cName !== '') {
                                $sonNames[] = $cName;
                            }
                        }
                    }
                }

                $employee = new Employee([
                    'name' => $candidate->name,
                    'email' => $candidate->email,
                    'phone' => $candidate->phone,
                    'company_id' => $application->jobOpening?->company_id,
                    'branch_id' => null,
                    'department_id' => $application->jobOpening?->department_id,
                    'position_id' => $application->jobOpening?->position_id,
                    'employment_status' => 'probation',
                    'joined_at' => now(),
                    'birth_place' => $personal['birth_place'] ?? $formPersonal['birth_place'] ?? null,
                    'birth_date' => $personal['birth_date'] ?? $formPersonal['birth_date'] ?? null,
                    'gender' => $gender,
                    'religion' => $personal['religion'] ?? $formPersonal['religion'] ?? null,
                    'marital_status' => $personal['marital_status'] ?? $formPersonal['marital_status'] ?? null,
                    'daughters_count' => $daughters,
                    'daughters_names' => ! empty($daughterNames) ? implode(', ', $daughterNames) : null,
                    'sons_count' => $sons,
                    'sons_names' => ! empty($sonNames) ? implode(', ', $sonNames) : null,
                    'address' => $personal['address'] ?? $formPersonal['address'] ?? null,
                    'ktp_no' => $personal['ktp_no'] ?? $formPersonal['ktp_no'] ?? null,
                    'npwp_no' => $personal['npwp_no'] ?? null,
                    'bpjs_tk_no' => $personal['bpjs_tk_no'] ?? null,
                    'spouse_name' => $family['spouse']['name'] ?? $formFamily['spouse']['name'] ?? null,
                    'father_name' => $family['father']['name'] ?? $formFamily['father']['name'] ?? null,
                    'mother_name' => $family['mother']['name'] ?? $formFamily['mother']['name'] ?? null,
                    'emergency_phone' => $personal['phone_home'] ?? $candidate->phone,
                ]);
            }
        }

        return view('employees.form', [
            'employee' => $employee,
            'organization' => $this->organizationTree(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = Employee::create($this->validated($request));

        return to_route('employees.show', $employee)->with('status', "{$employee->name} berhasil ditambahkan.");
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'company', 'branch', 'department', 'position',
            'transfers.fromCompany', 'transfers.fromBranch', 'transfers.fromDepartment', 'transfers.fromPosition',
            'transfers.toCompany', 'transfers.toBranch', 'transfers.toDepartment', 'transfers.toPosition',
        ]);

        return view('employees.show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee): View
    {
        return view('employees.form', [
            'employee' => $employee,
            'organization' => $this->organizationTree(),
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request, $employee));

        return to_route('employees.show', $employee)->with('status', "Data {$employee->name} berhasil diperbarui.");
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['q', 'company', 'department', 'status']);
        $employees = Employee::with(['company', 'branch', 'department', 'position'])
            ->filter($filters)
            ->orderBy('name')
            ->cursor();

        return response()->streamDownload(function () use ($employees) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nama PT', 'Nama Cabang', 'Tgl Masuk', 'Nama', 'No. Staff', 'Jabatan', 'Status',
                'Kontrak I Mulai', 'Kontrak I Berakhir', 'Kontrak II Mulai', 'Kontrak II Berakhir', 'Ket',
                'No Handphone', 'Tempat Tanggal Lahir', 'Pria/Wanita', 'Agama', 'Status Pernikahan',
                'Anak Perempuan', 'Anak Laki-Laki', 'Alamat Tempat Tinggal', 'No KTP', 'No NPWP',
                'No BPJS TK', 'BPJS Kes', 'Nama Istri / Suami', 'Nama Ayah', 'Nama Ibu',
                'No Tlp Darurat', 'Email',
            ]);

            foreach ($employees as $employee) {
                fputcsv($handle, [
                    $employee->company?->name,
                    $employee->branch?->name,
                    $employee->joined_at->format('d/m/Y'),
                    $employee->name,
                    $employee->nik,
                    $employee->position?->name,
                    $employee->employmentStatusLabel(),
                    $employee->contract_1_start?->format('d/m/Y'),
                    $employee->contract_1_end?->format('d/m/Y'),
                    $employee->contract_2_start?->format('d/m/Y'),
                    $employee->contract_2_end?->format('d/m/Y'),
                    $employee->note,
                    $employee->phone,
                    $employee->birthLabel(),
                    $employee->genderLabel(),
                    $employee->religion,
                    $employee->marital_status,
                    $employee->daughters_names ?: $employee->daughters_count,
                    $employee->sons_names ?: $employee->sons_count,
                    $employee->address,
                    $employee->ktp_no,
                    $employee->npwp_no,
                    $employee->bpjs_tk_no,
                    $employee->bpjs_kes_no,
                    $employee->spouse_name,
                    $employee->father_name,
                    $employee->mother_name,
                    $employee->emergency_phone,
                    $employee->email,
                ]);
            }

            fclose($handle);
        }, 'data-karyawan-' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function nextStaffNumber(Request $request): \Illuminate\Http\JsonResponse
    {
        $companyId = $request->query('company_id');
        $joinedAt = $request->query('joined_at') ?: now()->format('Y-m-d');

        if (! $companyId) {
            return response()->json(['staff_number' => '']);
        }

        $staffNumber = Employee::generateStaffNumber($companyId, $joinedAt);

        return response()->json([
            'staff_number' => $staffNumber,
        ]);
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        $data = $request->validate([
            'nik' => ['nullable', 'string', 'max:40', Rule::unique('employees', 'nik')->ignore($employee?->id)],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:120', Rule::unique('employees', 'email')->ignore($employee?->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_id' => ['required', 'exists:companies,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'employment_status' => ['required', 'in:permanent,contract,probation,intern'],
            'joined_at' => ['required', 'date'],
            'contract_1_start' => ['nullable', 'date'],
            'contract_1_end' => ['nullable', 'date', 'after_or_equal:contract_1_start'],
            'contract_2_start' => ['nullable', 'date'],
            'contract_2_end' => ['nullable', 'date', 'after_or_equal:contract_2_start'],
            'note' => ['nullable', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:80'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:pria,wanita'],
            'religion' => ['nullable', 'string', 'max:30'],
            'marital_status' => ['nullable', 'string', 'max:30'],
            'daughters_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'daughters_names' => ['nullable', 'string', 'max:500'],
            'sons_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'sons_names' => ['nullable', 'string', 'max:500'],
            'address' => ['nullable', 'string', 'max:500'],
            'ktp_no' => ['nullable', 'string', 'max:32'],
            'npwp_no' => ['nullable', 'string', 'max:32'],
            'bpjs_tk_no' => ['nullable', 'string', 'max:32'],
            'bpjs_kes_no' => ['nullable', 'string', 'max:32'],
            'spouse_name' => ['nullable', 'string', 'max:120'],
            'father_name' => ['nullable', 'string', 'max:120'],
            'mother_name' => ['nullable', 'string', 'max:120'],
            'emergency_phone' => ['nullable', 'string', 'max:32'],
        ], [], [
            'nik' => 'No. staff',
            'name' => 'nama',
            'email' => 'email',
            'phone' => 'no handphone',
            'company_id' => 'PT',
            'branch_id' => 'cabang',
            'department_id' => 'departemen',
            'position_id' => 'jabatan',
            'employment_status' => 'status kepegawaian',
            'joined_at' => 'tanggal masuk',
            'salary' => 'gaji',
            'contract_1_start' => 'kontrak I mulai',
            'contract_1_end' => 'kontrak I berakhir',
            'contract_2_start' => 'kontrak II mulai',
            'contract_2_end' => 'kontrak II berakhir',
            'birth_place' => 'tempat lahir',
            'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'religion' => 'agama',
            'marital_status' => 'status pernikahan',
            'daughters_count' => 'jumlah anak perempuan',
            'daughters_names' => 'nama anak perempuan',
            'sons_count' => 'jumlah anak laki-laki',
            'sons_names' => 'nama anak laki-laki',
            'address' => 'alamat tempat tinggal',
            'ktp_no' => 'no KTP',
            'npwp_no' => 'no NPWP',
            'bpjs_tk_no' => 'no BPJS TK',
            'bpjs_kes_no' => 'no BPJS Kes',
            'spouse_name' => 'nama istri/suami',
            'father_name' => 'nama ayah',
            'mother_name' => 'nama ibu',
            'emergency_phone' => 'no telepon darurat',
        ]);

        $company = Company::findOrFail($data['company_id']);
        $branch = ! empty($data['branch_id']) ? Branch::find($data['branch_id']) : null;
        $department = Department::findOrFail($data['department_id']);
        $position = Position::findOrFail($data['position_id']);

        $conflicts = [];

        if ($branch && (int) $branch->company_id !== (int) $company->id) {
            $conflicts['branch_id'] = 'Cabang yang dipilih bukan milik PT tersebut.';
        }

        if ((int) $department->company_id !== (int) $company->id) {
            $conflicts['department_id'] = 'Departemen yang dipilih bukan milik PT tersebut.';
        }

        if ((int) $position->department_id !== (int) $department->id) {
            $conflicts['position_id'] = 'Jabatan yang dipilih bukan milik departemen tersebut.';
        }

        if ($conflicts !== []) {
            throw ValidationException::withMessages($conflicts);
        }

        if (empty($data['nik'])) {
            $data['nik'] = Employee::generateStaffNumber($data['company_id'], $data['joined_at']);
        }

        $data['salary'] = $request->filled('salary') ? (int) $request->input('salary') : ($employee?->salary ?? null);
        $data['is_active'] = $request->boolean('is_active');
        $data['daughters_count'] = (int) ($data['daughters_count'] ?? 0);
        $data['sons_count'] = (int) ($data['sons_count'] ?? 0);

        return $data;
    }

    private function organizationTree(): array
    {
        return Company::with([
            'branches' => fn ($query) => $query->orderBy('name'),
            'departments' => fn ($query) => $query->orderBy('name'),
            'departments.positions' => fn ($query) => $query->orderBy('name'),
        ])
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company) => [
                'id' => $company->id,
                'code' => $company->code,
                'name' => $company->name,
                'branches' => $company->branches->map(fn (Branch $branch) => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ])->values()->all(),
                'departments' => $company->departments->map(fn (Department $department) => [
                    'id' => $department->id,
                    'name' => $department->name,
                    'positions' => $department->positions->map(fn (Position $position) => [
                        'id' => $position->id,
                        'name' => $position->name,
                    ])->values()->all(),
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function statusOptions(): array
    {
        return Employee::employmentStatusOptions();
    }
}
