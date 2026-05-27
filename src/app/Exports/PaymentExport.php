<?php

namespace App\Exports;

class PaymentExport
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
        return ['Siswa', 'Kelas', 'Judul Pembayaran', 'Jumlah', 'Status', 'Tanggal'];
    }

    public function rows(): array
    {
        return [];
    }
}
