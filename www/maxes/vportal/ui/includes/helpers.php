<?php
/**
 * Mendapatkan versi cache busting untuk file CSS atau JS.
 * Ini akan menambahkan query string seperti '?v=1678886400' ke URL file.
 *
 * @param string $webPath Path file dari web root (e.g., '/maxtest/vportal/ui/css/style.css')
 * @return string Query string untuk cache busting atau string kosong jika file tidak ditemukan.
 */
function getCacheBustingVersion(string $webPath): string {
    // Dapatkan path absolut di server
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $webPath;

    if (file_exists($fullPath)) {
        // Ambil waktu modifikasi terakhir dari file sebagai versi
        return '?v=' . filemtime($fullPath);
    }

    // Jika file tidak ada, jangan tambahkan apa-apa untuk menghindari error
    return '';
}