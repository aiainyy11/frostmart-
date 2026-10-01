<?php 
session_start();

//Hapus semua data session
session_unset();

//Hancurkan session sepenuhnya
session_destroy();

//Kembali ke halaman login
header("Location: login.php");
exit;