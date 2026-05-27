<?php

namespace App\Exports;

class AcademicYearExport
{
    protected array $data;

    protected array $filters;

    public function __construct(array $data, array $filters)
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return ['Nama', 'Tanggal Mulai', 'Tanggal Selesai', 'Status'];
    }

    public function rows(): array
    {
        return [];
    }
}
