<?php
// Fungsi ini mengubah nilai status_pesanan di database
// jadi label yang lebih jelas buat ditampilkan ke pelanggan.
// Nilai di database TETAP: pending, diproses, dikirim, selesai
// (jadi gak perlu ubah struktur tabel), cuma tampilannya yang beda.

function label_status($status) {
    $mapping = [
        'pending'   => 'Menunggu Konfirmasi',
        'diproses'  => 'Sedang Dikemas',
        'dikirim'   => 'Sedang Dalam Perjalanan',
        'selesai'   => 'Pesanan Telah Sampai',
    ];

    return $mapping[$status] ?? ucfirst($status);
}