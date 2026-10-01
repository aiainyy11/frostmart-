<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id'];
$item_ids = $_POST['item_ids'] ?? [];
$alamat_kirim = $_POST['alamat_kirim'];
$no_telp = $_POST['no_telp'];
$metode_bayar = $_POST['metode_bayar'];
$metode_kirim = $_POST['metode_kirim'];
$kode_voucher = trim($_POST['kode_voucher']);

if (empty($item_ids)) {
    header("Location: keranjang.php");
    exit;
}

$item_ids_aman = array_map('intval', $item_ids);
$id_list_sql = implode(',', $item_ids_aman);

// Ambil HANYA item yang dipilih (bukan semua isi keranjang user)
$keranjang = mysqli_query($conn, "
    SELECT ke.*, p.harga, p.stok
    FROM tb_keranjang ke
    JOIN tb_produk p ON ke.id_produk = p.id
    WHERE ke.id IN ($id_list_sql) AND ke.id_user = '$id_user'
");

$items = [];
$grand_total = 0;
while ($row = mysqli_fetch_assoc($keranjang)) {
    $items[] = $row;
    $grand_total += $row['harga'] * $row['jumlah'];
}

if (empty($items)) {
    header("Location: keranjang.php");
    exit;
}

// ============ CEK VOUCHER ============
$id_voucher = null;
if ($kode_voucher) {
    $q_voucher = mysqli_query($conn, "SELECT * FROM tb_voucher WHERE kode_voucher = '$kode_voucher'");
    $voucher = mysqli_fetch_assoc($q_voucher);

    if ($voucher && $voucher['kuota'] > 0 && $grand_total >= $voucher['min_belanja']
        && strtotime(date('Y-m-d')) >= strtotime($voucher['tanggal_berlaku'])
        && strtotime(date('Y-m-d')) <= strtotime($voucher['tanggal_berakhir'])) {

        $id_voucher = $voucher['id'];

        if ($voucher['jenis_diskon'] == 'persen') {
            $grand_total -= $grand_total * ($voucher['nilai_diskon'] / 100);
        } else {
            $grand_total -= $voucher['nilai_diskon'];
        }
        if ($grand_total < 0) $grand_total = 0;

        mysqli_query($conn, "UPDATE tb_voucher SET kuota = kuota - 1 WHERE id = '$id_voucher'");
    }
}

// ============ UPDATE ALAMAT & HP TERBARU DI PROFIL (opsional, biar konsisten) ============
mysqli_query($conn, "UPDATE tb_user SET alamat = '$alamat_kirim', hp = '$no_telp' WHERE id = '$id_user'");

// ============ SIMPAN TRANSAKSI ============
$id_voucher_sql = $id_voucher ? "'$id_voucher'" : "NULL";
mysqli_query($conn, "
    INSERT INTO tb_transaksi (id_pelanggan, id_voucher, tanggal, total_harga, status_pesanan)
    VALUES ('$id_user', $id_voucher_sql, CURDATE(), '$grand_total', 'pending')
");
$id_transaksi_baru = mysqli_insert_id($conn);

// ============ SIMPAN DETAIL + KURANGI STOK ============
foreach ($items as $item) {
    mysqli_query($conn, "
        INSERT INTO tb_detail (id_transaksi, id_produk, jumlah)
        VALUES ('$id_transaksi_baru', '{$item['id_produk']}', '{$item['jumlah']}')
    ");
    mysqli_query($conn, "
        UPDATE tb_produk SET stok = stok - '{$item['jumlah']}' WHERE id = '{$item['id_produk']}'
    ");
}

// ============ HAPUS CUMA ITEM YANG DIPILIH DARI KERANJANG ============
// (item yang gak dicentang tadi TETAP ada di keranjang, gak ikut kehapus)
mysqli_query($conn, "DELETE FROM tb_keranjang WHERE id IN ($id_list_sql) AND id_user = '$id_user'");

// ============ NOTIFIKASI: PESANAN DITERIMA ============
$judul_notif = "Pesanan Diterima";
$pesan_notif = "Pesanan #$id_transaksi_baru kamu sudah kami terima dan sedang menunggu diproses.";
$link_notif = "invoice.php?id=$id_transaksi_baru";
mysqli_query($conn, "
    INSERT INTO tb_notifikasi (id_user, id_transaksi, judul, pesan, link)
    VALUES ('$id_user', '$id_transaksi_baru', '$judul_notif', '$pesan_notif', '$link_notif')
");

header("Location: invoice.php?id=$id_transaksi_baru");
exit;