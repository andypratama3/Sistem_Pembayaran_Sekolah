<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi format Rupiah Indonesia.
     *
     * Contoh:
     *   rupiah(200)           → "Rp 200"
     *   rupiah(100000)        → "Rp 100.000"
     *   rupiah(1000000.5)     → "Rp 1.000.001"  (default tanpa desimal)
     *   rupiah(1000000.5, 2)  → "Rp 1.000.000,50"
     *   rupiah(null)          → "-"
     */
    function rupiah(mixed $angka, int $desimal = 0): string
    {
        if ($angka === null || $angka === '') {
            return '-';
        }

        return 'Rp '.number_format((float) $angka, $desimal, ',', '.');
    }
}

if (! function_exists('angka')) {
    /**
     * Format angka biasa (tanpa prefix Rp), separator ribuan titik.
     *
     * Contoh:
     *   angka(1234.567)      → "1.234,567"
     *   angka(1234.567, 0)   → "1.235"
     *   angka(null)          → "-"
     */
    function angka(mixed $nilai, int $desimal = 3): string
    {
        if ($nilai === null || $nilai === '') {
            return '-';
        }

        return number_format((float) $nilai, $desimal, ',', '.');
    }
}
