<?php

namespace App\Services;

class QrCodeService
{
    public function generateCode(string $category, int $id, ?int $year = null): string
    {
        $year = $year ?? (int)date('Y');
        $noUrut = str_pad($id, 4, '0', STR_PAD_LEFT);
        $kat = strtoupper($category);

        return "SMKN2-LAB-{$kat}-{$year}-{$noUrut}";
    }

    public function generateQrImage(string $code): string
    {
        // Langsung gunakan fallback SVG agar proses pembuatan QR instan tanpa dependensi API eksternal
        return 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="white"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="12" fill="black">QR: ' . $code . '</text></svg>';
    }
}
