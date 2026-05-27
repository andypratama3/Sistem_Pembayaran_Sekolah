<?php

namespace App\Exports;

class ClassroomExport
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
        return ['Nama Kelas', 'Kode', 'Tipe', 'Tahun Akademik'];
    }

    public function rows(): array
    {
        return [];
    }
}
