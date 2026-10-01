<?php
// File ini WAJIB di-include di paling atas setiap halaman admin.
// Kalau syarat di bawah gak terpenuhi, user langsung ditendang balik ke login.

session_start();

// Cek 1: apakah dia udah login sama sekali?
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Cek 2: kalau udah login, apakah rolenya admin?
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Kalau lolos dua-duanya, baru boleh lanjut ke isi halaman admin.
?>