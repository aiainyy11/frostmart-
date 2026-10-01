<?php
include '../includes/cek_login.php';
include '../koneksi.php';

$id_user = $_SESSION['id'];
$id_transaksi = $_GET['id'] ?? null;

if (!$id_transaksi) {
    header("Location: index.php");
    exit;
}

$q_transaksi = mysqli_query($conn, "SELECT t.*, u.nama, u.email, u.hp, u.alamat FROM tb_transaksi t JOIN tb_user u ON t.id_pelanggan = u.id 
WHERE t.id_transaksi = '$id_transaksi' AND t.id_pelanggan = '$id_user'");
$t = mysqli_fetch_assoc($q_transaksi);

if (!$t) {
    header("Location: index.php");
    exit;
}

$detail = mysqli_query($conn, "SELECT d.*, p.nama AS nama_produk, p.harga FROM tb_detail d JOIN tb_produk p ON d.id_produk = p.id WHERE
d.id_transaksi = '$id_transaksi'");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $id_transaksi; ?> — FrostMart</title>
    <link rel="stylesheet" href="../assets/css/pelanggan.css">
    <style>
        .invoice-wrap { max-width: 700px; margin: 30px auto; padding: 0 24px 60px; }
        .invoice-box { background: var(--ice-panel); border: 1px solid var(--line); border-radius: 15px; padding: 30px; }
        .invoice-succes { text-align: center; margin-bottom: 24px; }
        .invoice-succes .icon { font-size: 44px; }
        .invoice-succes h1 { font-size: 20px; margin-top: 10px; }
        .invoice-head { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 13.5px; color: var(--frost-dim); }
        table { width:100%; border-collapse:collapse; font-size:13.5px; margin-bottom:20px; }
        thead th { text-align: left; padding:8px; border-bottom: 1px solid var(--line); color: var(--frost-dim); font-size:15px; }
        tbody td { padding:8px; border-bottom: 1px solid var(--line); }
        .invoice-total { text-align: right; font-size: 17px; font-weight:700; margin-bottom: 25px; }
        .invoice-actions { display:flex; gap: 12px; justify-content: center; }
        @media print {
            .navbar, .no-print { display: none !important; }
            body { background:white; color:black; }
            .invoice-box { background:white; border: 1px solid #ccc; color:black; }
            table thead th, table tbody td { color:black; border-color: #ccc; }
        }
    </style>
</head>
<body>
    
<header class="navbar no-print">
    <div class="navbar-inner">
        <a href="index.php" class="brand">❄️ FrostMart</a>
        <div class="navbar-actions">
            <a href="profile.php" class="nav-link">Profil Saya</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>
</header>

<main class="invoice-wrap">
<div class="invoice-succes no-print">
    <div class="icon">✅</div>
    <h1>Pesanan Berhasil Dibuat!</h1>
</div>

<div class="invoice-box">
    <div class="invoice-head">
        <div>
            <strong style="color:var(--frost);">Invoice #<?php echo $t['id_transaksi']; ?></strong><br>
            <?php echo date('d/m/Y', strtotime($t['tanggal'])); ?>
        </div>
        <div style="text-align:right;">
            <?php echo htmlspecialchars($t['nama']); ?><br>
            <?php echo htmlspecialchars($t['alamat']); ?>
    </div>
</div>

<table>
    <thead>
        <tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr>
    </thead>
    <tbody>
        <?php while ($d = mysqli_fetch_assoc($detail)): ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nama_produk']); ?></td>
                <td>Rp <?php echo number_format($d['harga'],0,',', '.'); ?></td>
                <td><?php echo $d['jumlah']; ?></td>
                <td>Rp <?php echo number_format($d['harga'] * $d['jumlah'],0,',', '.'); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="invoice-total">Total: Rp <?php echo number_format($t['total_harga'],0,',', '.'); ?></div>

    <div class="invoice-actions no-print">
        <button onclick="window.print()" class="btn-solid"> Cetak Invoice</button>
        <a href="index.php" class="btn-outline">Lanjut Berbelanja</a>
    </div>
    </div>
</main>

</body>
</html>