<?php

namespace App\Exports;

class StudentExport
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
        return ['Nama', 'NISN', 'NIS', 'Kelas', 'Jenis Kelamin', 'Status'];
    }

    public function rows(): array
    {
        return [];
    }
}
