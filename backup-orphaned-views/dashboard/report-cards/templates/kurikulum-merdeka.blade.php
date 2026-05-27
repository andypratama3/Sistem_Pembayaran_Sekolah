<div class="rapor-kurikulum-merdeka" style="font-family: Arial, sans-serif; padding: 20px;">
    {{-- Header --}}
    <div class="header" style="text-align: center; margin-bottom: 25px; border-bottom: 3px solid {{ $template->header_color ?? '#2c5282' }}; padding-bottom: 15px;">
        @if ($template->logo_path && \Illuminate\Support\Facades\Storage::exists($template->logo_path))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($template->logo_path) }}" alt="Logo" style="height: 70px; margin-bottom: 10px;">
        @endif
        <h1 style="margin: 0; color: {{ $template->header_color ?? '#2c5282' }}; font-size: 18px;">RAPOR PESERTA DIDIK</h1>
        <p style="margin: 5px 0; font-size: 12px; color: {{ $template->header_color ?? '#2c5282' }};">KURIKULUM MERDEKA</p>
    </div>

    {{-- Student & School Information --}}
    <div class="info-section" style="margin-bottom: 25px; font-size: 12px;">
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
                <td style="padding: 5px;">Tahun Ajaran</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student?->classroom?->academic_year?->year ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 5px;">Kelas</td>
                <td style="padding: 5px;">: {{ $studentReportCard->student?->classroom?->name ?? 'N/A' }}</td>
                <td style="padding: 5px;">Fase</td>
                <td style="padding: 5px;">: -</td>
            </tr>
        </table>
    </div>

    {{-- Capaian Kompetensi (Learning Achievements) --}}
    @if ($template->config['show_competencies'] ?? true)
        <div class="competencies-section" style="margin-bottom: 25px;">
            <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#2c5282' }}; color: white; padding: 8px; border-radius: 3px;">CAPAIAN KOMPETENSI</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <thead>
                    <tr style="background-color: #e8f1f8; border: 1px solid #999;">
                        <th style="border: 1px solid #999; padding: 8px; text-align: left;">Mata Pelajaran</th>
                        <th style="border: 1px solid #999; padding: 8px; text-align: center;">Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" style="border: 1px solid #999; padding: 8px; text-align: center; color: #999;">Tidak ada data capaian kompetensi</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    {{-- Nilai Mata Pelajaran --}}
    <div class="grades-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#2c5282' }}; color: white; padding: 8px; border-radius: 3px;">NILAI MATA PELAJARAN</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #e8f1f8; border: 1px solid #999;">
                    <th style="border: 1px solid #999; padding: 8px; text-align: left;">No</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: left;">Mata Pelajaran</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: center;">Nilai Akhir</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: center;">Predikat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" style="border: 1px solid #999; padding: 8px; text-align: center; color: #999;">Tidak ada data nilai</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Attendance & Behavior --}}
    <div class="conduct-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#2c5282' }}; color: white; padding: 8px; border-radius: 3px;">PERILAKU & KEHADIRAN</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <tr>
                <td style="width: 50%; border: 1px solid #999; padding: 8px;">
                    <strong>Kehadiran:</strong>
                    <div style="margin-top: 5px;">
                        Hadir: - | Izin: - | Sakit: - | Alpa: -
                    </div>
                </td>
                <td style="width: 50%; border: 1px solid #999; padding: 8px;">
                    <strong>Catatan Perilaku:</strong>
                    <div style="margin-top: 5px; font-size: 10px;">-</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Ekstrakurikuler (Extracurriculars) --}}
    <div class="extracurricular-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#2c5282' }}; color: white; padding: 8px; border-radius: 3px;">KEGIATAN EKSTRAKURIKULER</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background-color: #e8f1f8; border: 1px solid #999;">
                    <th style="border: 1px solid #999; padding: 8px; text-align: left;">Kegiatan</th>
                    <th style="border: 1px solid #999; padding: 8px; text-align: center;">Prestasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="border: 1px solid #999; padding: 8px; text-align: center; color: #999;">Tidak ada data ekstrakurikuler</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Notes --}}
    <div class="notes-section" style="margin-bottom: 25px;">
        <h3 style="font-size: 13px; margin-bottom: 12px; background-color: {{ $template->header_color ?? '#2c5282' }}; color: white; padding: 8px; border-radius: 3px;">CATATAN KHUSUS</h3>
        <div style="border: 1px solid #999; padding: 12px; min-height: 50px; font-size: 11px;">-</div>
    </div>

    {{-- Signatures --}}
    <div class="signatures" style="margin-top: 40px;">
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 50%; text-align: center; padding-top: 30px;">
                    <p style="margin: 0;">Guru Wali Kelas</p>
                    <p style="margin: 50px 0 0 0; font-weight: bold;">__________________</p>
                </td>
                <td style="width: 50%; text-align: center; padding-top: 30px;">
                    <p style="margin: 0;">Kepala Sekolah</p>
                    <p style="margin: 50px 0 0 0; font-weight: bold;">__________________</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer" style="margin-top: 30px; text-align: center; font-size: 10px; border-top: 1px solid #ccc; padding-top: 10px; color: #666;">
        <p>{{ $template->config['footer_text'] ?? 'Dokumen ini adalah catatan resmi prestasi belajar peserta didik' }}</p>
    </div>
</div>
