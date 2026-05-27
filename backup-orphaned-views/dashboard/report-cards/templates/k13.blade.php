<div class="rapor-k13" style="font-family: Arial, sans-serif; padding: 20px;">
    {{-- Header --}}
    <div class="header" style="text-align: center; margin-bottom: 25px; border-bottom: 3px solid {{ $template->header_color ?? '#1a3a52' }}; padding-bottom: 15px;">
        @if ($template->logo_path && \Illuminate\Support\Facades\Storage::exists($template->logo_path))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($template->logo_path) }}" alt="Logo" style="height: 70px; margin-bottom: 10px;">
        @endif
        <h1 style="margin: 0; color: {{ $template->header_color ?? '#1a3a52' }}; font-size: 18px;">LAPORAN HASIL BELAJAR PESERTA DIDIK</h1>
        <p style="margin: 5px 0; font-size: 12px; color: {{ $template->header_color ?? '#1a3a52' }};">KURIKULUM 2013</p>
    </div>

    {{-- Identity & Period --}}
    <div class="identity-section" style="margin-bottom: 20px; font-size: 12px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 5px; width: 15%;">Nama Peserta Didik</td>
                <td style="padding: 5px; width: 50%;">: <strong>{{ $studentReportCard->student->name ?? 'N/A' }}</strong></td>
                <td style="padding: 5px; width: 15%;">Nomor Induk</td>
                <td style="padding: 5px; width: 20%;">: {{ $studentReportCard->student->nisn ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">Sekolah</td>
                <td style="padding: 5px;">: {{ $school->name ?? 'N/A' }}</td>
                <td style="padding: 5px;">Tahun Pelajaran</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student?->classroom?->academic_year?->year ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">Kelas/Semester</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student?->classroom?->name ?? 'N/A' }}</td>
                <td style="padding: 5px;">Tanggal Rapor</td>
                <td style="padding: 5px;">: {{ now()->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    {{-- A. Academic Achievement --}}
    <div class="academic-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#1a3a52' }}; color: white; padding: 8px; border-radius: 3px;">A. PENGETAHUAN DAN KETERAMPILAN</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #e8eef6;">
                    <th style="border: 1px solid #666; padding: 8px; text-align: left;">No</th>
                    <th style="border: 1px solid #666; padding: 8px; text-align: left;">Mata Pelajaran</th>
                    <th style="border: 1px solid #666; padding: 8px; text-align: center;">KKM</th>
                    <th style="border: 1px solid #666; padding: 8px; text-align: center;">Nilai</th>
                    <th style="border: 1px solid #666; padding: 8px; text-align: center;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5" style="border: 1px solid #666; padding: 8px; text-align: center; color: #999;">Tidak ada data nilai</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- B. Competencies / Sikap --}}
    <div class="sikap-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#1a3a52' }}; color: white; padding: 8px; border-radius: 3px;">B. SIKAP</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr style="background-color: #e8eef6;">
                <td style="border: 1px solid #666; padding: 8px; width: 50%;">Sikap Spiritual</td>
                <td style="border: 1px solid #666; padding: 8px; width: 50%; font-weight: bold;">-</td>
            </tr>
            <tr style="background-color: #e8eef6;">
                <td style="border: 1px solid #666; padding: 8px;">Sikap Sosial</td>
                <td style="border: 1px solid #666; padding: 8px; font-weight: bold;">-</td>
            </tr>
        </table>
    </div>

    {{-- Rank & Average --}}
    @if ($template->config['show_rank'] ?? true)
        <div class="rank-section" style="margin-bottom: 25px; padding: 12px; background-color: #f5f5f5; border: 1px solid #ddd; border-radius: 3px;">
            <div style="font-size: 12px;">
                <p style="margin: 0 0 8px 0;">
                    <strong>Rata-rata Nilai Pengetahuan:</strong>
                    <span style="float: right;">-</span>
                </p>
                <p style="margin: 0;">
                    <strong>Rangking:</strong>
                    <span style="float: right;">-</span>
                </p>
            </div>
        </div>
    @endif

    {{-- C. Ekstrakurikuler --}}
    <div class="extracurricular-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#1a3a52' }}; color: white; padding: 8px; border-radius: 3px;">C. EKSTRAKURIKULER</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #e8eef6;">
                    <th style="border: 1px solid #666; padding: 8px; text-align: left;">Kegiatan Ekstrakurikuler</th>
                    <th style="border: 1px solid #666; padding: 8px; text-align: center;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="border: 1px solid #666; padding: 8px; text-align: center; color: #999;">Tidak ada data ekstrakurikuler</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- D. Attendance --}}
    @if ($template->config['show_attendance_summary'] ?? true)
        <div class="attendance-section" style="margin-bottom: 25px;">
            <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#1a3a52' }}; color: white; padding: 8px; border-radius: 3px;">D. KETIDAKHADIRAN</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <tr>
                    <td style="border: 1px solid #666; padding: 8px; width: 25%; text-align: center;">Sakit</td>
                    <td style="border: 1px solid #666; padding: 8px; width: 25%; text-align: center;">-</td>
                    <td style="border: 1px solid #666; padding: 8px; width: 25%; text-align: center;">Izin</td>
                    <td style="border: 1px solid #666; padding: 8px; width: 25%; text-align: center;">-</td>
                </tr>
                <tr>
                    <td style="border: 1px solid #666; padding: 8px; text-align: center;">Tanpa Keterangan</td>
                    <td style="border: 1px solid #666; padding: 8px; text-align: center;">-</td>
                    <td style="border: 1px solid #666; padding: 8px; text-align: center;">Jumlah Hari Efektif</td>
                    <td style="border: 1px solid #666; padding: 8px; text-align: center;">-</td>
                </tr>
            </table>
        </div>
    @endif

    {{-- E. Notes --}}
    <div class="notes-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#1a3a52' }}; color: white; padding: 8px; border-radius: 3px;">E. CATATAN WALI KELAS</h3>
        <div style="border: 1px solid #666; padding: 12px; min-height: 60px; font-size: 11px; line-height: 1.5;">-</div>
    </div>

    {{-- Signatures --}}
    <div class="signatures" style="margin-top: 40px;">
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 50%; text-align: center; padding-top: 30px;">
                    <p style="margin: 0;">Orang Tua/Wali</p>
                    <p style="margin: 50px 0 0 0; font-weight: bold;">__________________</p>
                </td>
                <td style="width: 50%; text-align: center; padding-top: 30px;">
                    <p style="margin: 0;">Guru Wali Kelas</p>
                    <p style="margin: 50px 0 0 0; font-weight: bold;">__________________</p>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 30px;">
                    <p style="margin: 0;">Kepala Sekolah</p>
                    <p style="margin: 50px 0 0 0; font-weight: bold;">__________________</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer" style="margin-top: 30px; text-align: center; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px; color: #666;">
        <p>{{ $template->config['footer_text'] ?? 'Laporan ini merupakan dokumen resmi sekolah sebagai bukti hasil belajar peserta didik' }}</p>
    </div>
</div>
