<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\ImportProgressRequest;
use App\Models\Student;
use App\Models\Task;
use App\Services\ProcessStudentsDapodikService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportController extends ResourceController
{
    use ApiResponse;

    protected static string $permissionResource = 'students';

    /**
     * POST /students/import
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        // ✅ Create a Task for this import to track in Task Management
        $task = Task::create([
            'title' => 'Import Siswa DAPODIK: '.$request->file('file')->getClientOriginalName(),
            'status' => 'in_progress',
            'category' => 'student-analytics',
            'priority' => 2, // Normal
            'progress_percentage' => 0,
            'created_by' => auth()->id(),
            'assigned_to' => auth()->id(),
            'description' => 'Proses import data siswa dari file DAPODIK sedang berjalan secara background.',
        ]);

        $service = new ProcessStudentsDapodikService((string) $request->user()->id);
        $result = $service->dispatchFromUpload($request->file('file'), $task->id);

        return $this->success([
            'batch_id' => $result['batch_id'],
            'import_id' => $result['import_id'],   // ✅ FIX: kirim import_id ke frontend
            'task_id' => $task->id,
            'total_rows' => $result['total_rows'],
            'progress_url' => route('dashboard.students.import.progress', [
                'batchId' => $result['batch_id'],
                'import_id' => $result['import_id'], // ✅ FIX: sertakan di URL progress
                'task_id' => $task->id,
                'total_rows' => $result['total_rows'],
            ]),
        ], 'Import DAPODIK dijadwalkan. Pantau progres secara realtime.');
    }

    /**
     * GET /students/import/progress/{batchId}
     */
    public function progress(ImportProgressRequest $request)
    {
        $batch = Bus::findBatch($request->batchId);
        $importId = $request->query('import_id', '');
        $cachedProgress = cache()->get("import_progress_{$importId}");

        // Fetch already imported students for this batch
        $importedStudents = [];
        if ($importId) {
            $importedStudents = Student::where('import_id', $importId)
                ->with(['classrooms' => function ($q) {
                    $q->wherePivot('status', 'active');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get()
                ->map(function ($s) {
                    return [
                        'name' => $s->name,
                        'nisn' => $s->nisn,
                        'gender' => $s->gender,
                        'rombel' => $s->classrooms->first()?->name ?? '-',
                    ];
                })->toArray();
        }

        return view('dashboard.students.import.progress', [
            'batchId' => $request->batchId,
            'importId' => $importId,
            'totalRows' => (int) $request->total_rows,
            'initialData' => $batch ? [
                'progress' => $batch->progress(),
                'is_finished' => $batch->finished(),
                'is_cancelled' => $batch->cancelled(),
                'total_jobs' => $batch->totalJobs,
                'processed_jobs' => $batch->processedJobs(),
                'cached' => $cachedProgress,
                'imported_students' => $importedStudents,
                // Get actual counts from database for accuracy
                'actual_success' => $importId ? Student::where('import_id', $importId)->count() : 0,
                'actual_failed' => $cachedProgress['failed'] ?? 0,
            ] : null,
        ]);
    }

    /**
     * GET /students/import/{batchId}/status
     */
    public function status(string $batchId, Request $request): JsonResponse
    {
        $batch = Bus::findBatch($batchId);
        $importId = $request->query('import_id');
        $cachedProgress = $importId ? cache()->get("import_progress_{$importId}") : null;

        if (! $batch) {
            return $this->error('Batch tidak ditemukan.', null, 404);
        }

        return $this->success([
            'id' => $batch->id,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'processed_jobs' => $batch->processedJobs(),
            'progress' => $batch->progress(),
            'is_finished' => $batch->finished(),
            'has_failures' => $batch->hasFailures(),
            'cancelled_at' => $batch->cancelledAt,
            'finished_at' => $batch->finishedAt,
            'cached' => $cachedProgress,
            // Use actual database count for accuracy
            'actual_success' => $importId ? Student::where('import_id', $importId)->count() : 0,
            'actual_failed' => $cachedProgress['failed'] ?? 0,
        ]);
    }

    /**
     * DELETE /students/import/{batchId}
     */
    public function cancel(string $batchId): JsonResponse
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return $this->error('Batch tidak ditemukan.', null, 404);
        }

        $batch->cancel();

        // Optional: clear cache or mark as cancelled in cache if needed

        return $this->success(null, 'Import dibatalkan.');
    }

    /**
     * GET /students/import/data
     */
    public function data(Request $request): JsonResponse
    {
        $importId = $request->query('importId');

        if (! $importId) {
            return $this->error('Import ID required', null, 400);
        }

        $students = Student::where('import_id', $importId)
            ->orderBy('name')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'gender' => $student->gender,
                    'rombel' => $student->currentClassroom()?->name ?? '-',
                ];
            });

        $cachedProgress = cache()->get("import_progress_{$importId}");

        return $this->success([
            'students' => $students,
            'imported_success' => $students->count(),
            'imported_failed' => $cachedProgress['failed'] ?? 0,
            'errors' => $cachedProgress['errors'] ?? [],
        ]);
    }

    /**
     * Download DAPODIK import template
     * Based on ProcessStudentsDapodikService COL_MAP
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $lastColLetter = 'BD';
        $lastColNum = 56;

        // --- Row 1: Title ---
        $sheet->mergeCells('A1:'.$lastColLetter.'1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT SISWA (DAPODIK)');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // --- Row 2: Instructions ---
        $sheet->mergeCells('A2:'.$lastColLetter.'2');
        $sheet->setCellValue('A2', 'Petunjuk: Isi data mulai baris ke-7 sesuai format DAPODIK. Baris ke-7 adalah contoh data. Kolom header dibagi dalam 3 kelompok: Data Siswa (biru), Data Orang Tua (hijau), dan Data Sekolah (ungu).');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '4B5563']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
        ]);

        // --- Row 3: Group Labels ---
        $sheet->mergeCells('A3:V3');
        $sheet->setCellValue('A3', '▌ DATA SISWA');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('W3:AN3');
        $sheet->setCellValue('W3', '▌ DATA ORANG TUA / WALI');
        $sheet->getStyle('W3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('AO3:'.$lastColLetter.'3');
        $sheet->setCellValue('AO3', '▌ DATA SEKOLAH');
        $sheet->getStyle('AO3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(22);

        // --- Row 4: Legend ---
        $sheet->mergeCells('A4:'.$lastColLetter.'4');
        $sheet->setCellValue('A4', 'Baris ke-5 & 6 = Header  |  Baris ke-7 = Contoh data  |  Baris ke-8+ = Data yang akan diimport  |  Kolom dengan latar hijau = Wajib diisi');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '6B7280']],
        ]);

        // --- Row 5: Main Headers (Student Data) ---
        $mainHeaders = [
            1 => 'Nama Siswa *',
            3 => 'L/P *',
            4 => 'NISN *',
            5 => 'Tempat Lahir',
            6 => 'Tanggal Lahir',
            8 => 'Agama',
            9 => 'Alamat Jalan',
            10 => 'RT',
            11 => 'RW',
            13 => 'Desa/Kelurahan',
            14 => 'Kecamatan',
            16 => 'Jenis Tinggal',
            19 => 'Telepon',
            20 => 'HP',
            21 => 'Email',
        ];

        foreach ($mainHeaders as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 5);
            $cell->setValue($header);
        }

        // --- Row 6: Parent/School Headers ---
        $parentHeaders = [
            24 => 'Nama Ayah *',
            26 => 'Pendidikan Ayah',
            27 => 'Pekerjaan Ayah',
            30 => 'Nama Ibu *',
            32 => 'Pendidikan Ibu',
            33 => 'Pekerjaan Ibu',
            36 => 'Nama Wali',
            38 => 'Pendidikan Wali',
            39 => 'Pekerjaan Wali',
            42 => 'Rombel *',
            56 => 'Sekolah Asal',
        ];

        foreach ($parentHeaders as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col, 6);
            $cell->setValue($header);
        }

        // Style Row 5 headers (student info) - Blue
        $studentHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        foreach ($mainHeaders as $col => $header) {
            $cellRef = $this->getColumnLetter($col).'5';
            $sheet->getStyle($cellRef)->applyFromArray($studentHeaderStyle);
        }

        // Highlight required columns in green for student headers
        $requiredStudentCols = [1, 3, 4];
        foreach ($requiredStudentCols as $col) {
            $cellRef = $this->getColumnLetter($col).'5';
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new Color('FF059669'));
        }

        // Style Row 6 headers (parent/school) - Green for parent, Purple for school
        $parentCols = [24, 26, 27, 30, 32, 33, 36, 38, 39];
        $schoolCols = [42, 56];

        $parentHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF047857']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        foreach ($parentCols as $col) {
            $cellRef = $this->getColumnLetter($col).'6';
            $sheet->getStyle($cellRef)->applyFromArray($parentHeaderStyle);
        }

        $schoolHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6D28D9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        foreach ($schoolCols as $col) {
            $cellRef = $this->getColumnLetter($col).'6';
            $sheet->getStyle($cellRef)->applyFromArray($schoolHeaderStyle);
        }

        // Highlight required parent/school cols
        $requiredParentCols = [24, 30, 42];
        foreach ($requiredParentCols as $col) {
            $cellRef = $this->getColumnLetter($col).'6';
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->setStartColor(new Color('FF059669'));
        }

        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->getRowDimension(6)->setRowHeight(22);

        // --- Row 7: Sample data ---
        $sampleData = [
            1 => 'Ahmad Budi Santoso',
            3 => 'L',
            4 => '1234567890',
            5 => 'Jakarta',
            6 => '2010-05-15',
            8 => 'Islam',
            9 => 'Jl. Merdeka No. 123',
            10 => '01',
            11 => '02',
            13 => 'Wonoayu',
            14 => 'Sidoarjo',
            16 => 'Milik Sendiri',
            19 => '0211234567',
            20 => '081234567890',
            21 => 'ahmad@email.com',
            24 => 'Bapak Ahmad',
            26 => 'SMA',
            27 => 'Wiraswasta',
            30 => 'Ibu Siti',
            32 => 'SMA',
            33 => 'Ibu Rumah Tangga',
            42 => 'I Baghdad',
            56 => 'SD Negeri 1 Jakarta',
        ];

        foreach ($sampleData as $col => $value) {
            $sheet->getCellByColumnAndRow($col, 7)->setValue($value);
        }

        $sheet->getStyle('A7:'.$lastColLetter.'7')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3F4F6']],
            'font' => ['color' => ['rgb' => '9CA3AF'], 'italic' => true, 'size' => 10],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_DASHED, 'color' => ['rgb' => 'D1D5DB']]],
        ]);

        // --- Row 8+: Data area ---
        $dataStartRow = 8;
        $dataEndRow = 1048576;

        // Apply borders to data area
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A'.$dataStartRow.':'.$lastColLetter.$dataEndRow)->applyFromArray($dataStyle);

        // Data validation: L/P (dropdown)
        $genderValidation = $sheet->getDataValidation(
            $this->getColumnLetter(3).$dataStartRow.':'.$this->getColumnLetter(3).$dataEndRow
        );
        $genderValidation->setType(DataValidation::TYPE_LIST);
        $genderValidation->setFormula1('"L,P"');
        $genderValidation->setAllowBlank(true);
        $genderValidation->setShowDropDown(true);
        $genderValidation->setShowInputMessage(true);
        $genderValidation->setPromptTitle('Jenis Kelamin');
        $genderValidation->setPrompt('Pilih L (Laki-laki) atau P (Perempuan)');
        $genderValidation->setShowErrorMessage(true);
        $genderValidation->setErrorStyle(DataValidation::STYLE_STOP);
        $genderValidation->setError('Jenis kelamin harus L atau P.');

        // Freeze pane below headers
        $sheet->freezePane('A'.$dataStartRow);

        // Column widths (only for columns that have headers)
        $allHeaderCols = array_keys($mainHeaders + $parentHeaders);
        foreach ($allHeaderCols as $col) {
            $colLetter = $this->getColumnLetter($col);
            $sheet->getColumnDimension($colLetter)->setWidth(20);
        }

        // Wider columns for important fields
        $sheet->getColumnDimension($this->getColumnLetter(1))->setWidth(30);
        $sheet->getColumnDimension($this->getColumnLetter(4))->setWidth(18);
        $sheet->getColumnDimension($this->getColumnLetter(9))->setWidth(32);
        $sheet->getColumnDimension($this->getColumnLetter(24))->setWidth(25);
        $sheet->getColumnDimension($this->getColumnLetter(30))->setWidth(25);
        $sheet->getColumnDimension($this->getColumnLetter(42))->setWidth(16);
        $sheet->getColumnDimension($this->getColumnLetter(56))->setWidth(28);

        $sheet->setSelectedCell('A'.$dataStartRow);

        $writer = new Xlsx($spreadsheet);

        $fileName = 'template_dapodik_siswa.xlsx';
        $tempPath = storage_path('app/temp/'.$fileName);

        if (! Storage::disk('local')->exists('temp')) {
            Storage::disk('local')->makeDirectory('temp');
        }

        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }
}
