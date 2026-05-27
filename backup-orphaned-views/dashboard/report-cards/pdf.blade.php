<!DOCTYPE html>
<html>

<head>
    <title>Report Card - {{ $student->name }}</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            color: #3b82f6;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 5px 0;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .grade-table th,
        .grade-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }

        .grade-table th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
        }

        .footer-sign {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #000;
            margin-top: 60px;
            text-align: center;
        }

        .page-break {
            page-break-after: always;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    @php
        $normalizedPeriod = strtolower((string) $period);
        $periodLabel =
            $normalizedPeriod === 'ganjil'
                ? __('Ganjil')
                : ($normalizedPeriod === 'genap'
                    ? __('Genap')
                    : $period);
    @endphp

    <div class="header">
        <h2>{{ __('Laporan Pencapaian Kompetensi') }}</h2>
        <p>{{ $year }} - {{ __('Semester') }} {{ $periodLabel }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>{{ __('Siswa') }}</strong></td>
            <td width="35%">: {{ $student->name }}</td>
            <td width="15%"><strong>{{ __('Kelas') }}</strong></td>
            <td width="35%">: {{ $classroom->name }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('NISN') }}</strong></td>
            <td>: {{ $student->nisn }}</td>
            <td><strong>{{ __('Periode') }}</strong></td>
            <td>: {{ $periodLabel }}</td>
        </tr>
    </table>

    <table class="grade-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="45%">{{ __('Mata Pelajaran') }}</th>
                <th width="15%">{{ __('Nilai') }}</th>
                <th width="35%">{{ __('Detail') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grades as $index => $grade)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $grade->subject->name ?? '-' }}</td>
                    <td><strong>{{ $grade->score }}</strong></td>
                    <td>
                        @if ($grade->score >= 80)
                            <span class="badge badge-success">{{ __('Sangat Memuaskan') }}</span>
                        @elseif($grade->score >= 70)
                            <span class="badge badge-warning">{{ __('Sangat Baik') }}</span>
                        @else
                            <span class="badge badge-danger">{{ __('Perlu Perbaikan') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <strong>{{ __('Catatan Wali Kelas') }}:</strong>
        <p style="border: 1px solid #e5e7eb; padding: 10px; min-height: 50px; border-radius: 4px; color: #6b7280;">
            {{ $notes ?? __('Catatan_default') }}
        </p>
    </div>

    <div class="footer">
        <p>{{ __('Dicetak pada') }}: {{ $date }}</p>
        <div class="footer-sign">
            {{ __('Wali Kelas') }} {{ $classroom->name }}
        </div>
    </div>
</body>

</html>
