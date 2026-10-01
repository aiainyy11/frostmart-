<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "db_frostmart";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");