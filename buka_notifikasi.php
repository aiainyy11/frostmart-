<?php
include '../includes/cek_login.php';
include '../koneksi.php';

$id_user = $_SESSION['id'];
$id_notif = $_GET['id'] ?? null;

if ($id_notif) {
    // Tandai dibaca, tapi cuma kalau notifikasi ini emang punya user yang login (keamanan)
    $q = mysqli_query($conn, "SELECT link FROM tb_notifikasi WHERE id = '$id_notif' AND id_user = '$id_user'");
    $notif = mysqli_fetch_assoc($q);

    if ($notif) {
        mysqli_query($conn, "UPDATE tb_notifikasi SET dibaca = 1 WHERE id = '$id_notif'");
        $tujuan = $notif['link'] ?: 'index.php';
        header("Location: $tujuan");
        exit;
    }
}

header("Location: index.php");
exit;