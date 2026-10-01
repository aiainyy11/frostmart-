<?php
session_start();
include '../koneksi.php';

// =========================================
// WAJIB LOGIN
// =========================================
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}


// =========================================
// PROSES TAMBAH KE KERANJANG
// =========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil ID user dari session
    $id_user = intval($_SESSION['id']);

    // Ambil ID produk dan jumlah dari form
    $id_produk = isset($_POST['id_produk']) ? intval($_POST['id_produk']) : 0;
    $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 1;

    // Minimal jumlah 1
    if ($jumlah < 1) {
        $jumlah = 1;
    }


    // =========================================
    // CEK ID PRODUK
    // =========================================
    if ($id_produk <= 0) {
        die("ID produk tidak valid.");
    }


    // =========================================
    // CEK PRODUK DAN STOK
    // =========================================
    $cek_produk = mysqli_query($conn, "
        SELECT id, nama, stok
        FROM tb_produk
        WHERE id = '$id_produk'
    ");

    if (!$cek_produk) {
        die("Query produk error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($cek_produk) == 0) {
        die("Produk tidak ditemukan.");
    }

    $produk = mysqli_fetch_assoc($cek_produk);

    $stok = intval($produk['stok']);


    // =========================================
    // CEK STOK
    // =========================================
    if ($stok <= 0) {
        header("Location: detail_produk.php?id=$id_produk&error=habis");
        exit;
    }

    if ($jumlah > $stok) {
        $jumlah = $stok;
    }


    // =========================================
    // CEK APAKAH PRODUK SUDAH ADA DI KERANJANG
    // =========================================
    $cek_keranjang = mysqli_query($conn, "
        SELECT id, jumlah
        FROM tb_keranjang
        WHERE id_user = '$id_user'
        AND id_produk = '$id_produk'
    ");

    if (!$cek_keranjang) {
        die("Query keranjang error: " . mysqli_error($conn));
    }


    // =========================================
    // JIKA SUDAH ADA DI KERANJANG
    // =========================================
    if (mysqli_num_rows($cek_keranjang) > 0) {

        $existing = mysqli_fetch_assoc($cek_keranjang);

        $jumlah_lama = intval($existing['jumlah']);
        $jumlah_baru = $jumlah_lama + $jumlah;

        // Jangan sampai melebihi stok
        if ($jumlah_baru > $stok) {
            $jumlah_baru = $stok;
        }

        $update = mysqli_query($conn, "
            UPDATE tb_keranjang
            SET jumlah = '$jumlah_baru'
            WHERE id = '{$existing['id']}'
            AND id_user = '$id_user'
        ");

        if (!$update) {
            die("Gagal memperbarui keranjang: " . mysqli_error($conn));
        }


    // =========================================
    // JIKA BELUM ADA DI KERANJANG
    // =========================================
    } else {

        $insert = mysqli_query($conn, "
            INSERT INTO tb_keranjang
            (id_user, id_produk, jumlah)
            VALUES
            ('$id_user', '$id_produk', '$jumlah')
        ");

        if (!$insert) {
            die("Gagal menambahkan ke keranjang: " . mysqli_error($conn));
        }
    }


    // =========================================
    // KEMBALI KE DETAIL PRODUK
    // =========================================
    header("Location: detail_produk.php?id=$id_produk&ditambahkan=1");
    exit;
}


// =========================================
// JIKA FILE DIAKSES LANGSUNG
// =========================================
header("Location: index.php");
exit;

?>