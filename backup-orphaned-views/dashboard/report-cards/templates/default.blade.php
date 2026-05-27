<div class="rapor-container" style="font-family: Arial, sans-serif; padding: 20px;">
    {{-- Header --}}
    <div class="rapor-header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid {{ $template->header_color ?? '#000' }}; padding-bottom: 20px;">
        @if ($template->logo_path && \Illuminate\Support\Facades\Storage::exists($template->logo_path))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($template->logo_path) }}" alt="Logo" style="height: 60px; margin-bottom: 10px;">
        @endif
        <h1 style="margin: 0; color: {{ $template->header_color ?? '#000' }};">LAPORAN HASIL BELAJAR SISWA</h1>
        <p style="margin: 5px 0; font-size: 12px;">Semester {{ $studentReportCard->student?->classroom?->academic_year?->year ?? 'N/A' }}</p>
    </div>

    {{-- Student Information --}}
    <div class="student-info" style="margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <tr>
                <td style="width: 20%; padding: 5px;">Nama Siswa</td>
                <td style="width: 50%; padding: 5px;">: {{ $studentReportCard->student->name ?? 'N/A' }}</td>
                <td style="width: 30%; padding: 5px;"></td>
            </tr>
            <tr>
                <td style="padding: 5px;">NISN</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student->nisn ?? 'N/A' }}</td>
                <td style="padding: 5px;"></td>
            </tr>
            <tr>
                <td style="padding: 5px;">Kelas</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student?->classroom?->name ?? 'N/A' }}</td>
                <td style="padding: 5px;"></td>
            </tr>
            <tr>
                <td style="padding: 5px;">Sekolah</td>
                <td style="padding: 5px;">: {{ $school->name ?? 'N/A' }}</td>
                <td style="padding: 5px;"></td>
            </tr>
        </table>
    </div>

    {{-- Grades Table --}}
    <div class="grades-section" style="margin-bottom: 20px;">
        <h3 style="font-size: 13px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">NILAI MATA PELAJARAN</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="border: 1px solid #999; padding: 8px; text-align: left;">No</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: left;">Mata Pelajaran</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: center;">Nilai</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                {{-- Grades would be iterated here --}}
                <tr>
                    <td colspan="4" style="border: 1px solid #999; padding: 8px; text-align: center; color: #999;">Tidak ada data nilai</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Attendance Summary --}}
    @if ($template->config['show_attendance_summary'] ?? false)
        <div class="attendance-section" style="margin-bottom: 20px;">
            <h3 style="font-size: 13px; margin-bottom: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">RINGKASAN KEHADIRAN</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <tr>
                    <td style="width: 25%; padding: 8px; border: 1px solid #999;">Hadir</td>
                    <td style="width: 25%; padding: 8px; border: 1px solid #999; text-align: center;">-</td>
                    <td style="width: 25%; padding: 8px; border: 1px solid #999;">Izin</td>
                    <td style="width: 25%; padding: 8px; border: 1px solid #999; text-align: center;">-</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #999;">Sakit</td>
                    <td style="padding: 8px; border: 1px solid #999; text-align: center;">-</td>
                    <td style="padding: 8px; border: 1px solid #999;">Alpa</td>
                    <td style="padding: 8px; border: 1px solid #999; text-align: center;">-</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- Footer --}}
    <div class="rapor-footer" style="margin-top: 40px; text-align: center; font-size: 11px; border-top: 1px solid #ccc; padding-top: 20px;">
        <p>{{ $template->config['footer_text'] ?? 'Laporan ini adalah dokumen resmi sekolah' }}</p>
        <p style="margin-top: 30px;">Jakarta, {{ now()->translatedFormat('d F Y') }}</p>
    </div>
</div>
