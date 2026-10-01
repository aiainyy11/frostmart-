<?php
include '../includes/cek_admin.php';
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Ambil data teks dari form
    $nama = $_POST['nama'];
    $id_kategori = $_POST['id_kategori'];
    $sub_kategori = $_POST['sub_kategori'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $tanggal_kadaluwarsa = $_POST['tanggal_kadaluwarsa'] ?: NULL;
    $deskripsi = $_POST['deskripsi'];

    // 2. Insert data produk dulu ke tb_produk
    $insert_produk = mysqli_query($conn, "
        INSERT INTO tb_produk (nama, id_kategori, sub_kategori, harga, stok, deskripsi, tanggal_kadaluwarsa, status)
        VALUES ('$nama', '$id_kategori', '$sub_kategori', '$harga', '$stok', '$deskripsi', " . 
        ($tanggal_kadaluwarsa ? "'$tanggal_kadaluwarsa'" : "NULL") . ", 'aktif')
    ");

    if ($insert_produk) {
        // 3. Ambil id produk yang baru aja dibikin
        $id_produk_baru = mysqli_insert_id($conn);

        // 4. Proses upload foto (bisa lebih dari 1 file)
        $folder_upload = "../uploads/produk/";

        // Pastikan folder upload ada
        if (!is_dir($folder_upload)) {
            mkdir($folder_upload, 0777, true);
        }

        $jumlah_foto = count($_FILES['foto']['name']);

        for ($i = 0; $i < $jumlah_foto; $i++) {
            $nama_file_asli = $_FILES['foto']['name'][$i];
            $tmp_name = $_FILES['foto']['tmp_name'][$i];

            // Bikin nama file unik biar gak bentrok antar produk
            $nama_file_baru = time() . '_' . $i . '_' . $nama_file_asli;
            $path_tujuan = $folder_upload . $nama_file_baru;

            // Pindahin file dari lokasi sementara ke folder uploads
            if (move_uploaded_file($tmp_name, $path_tujuan)) {
                // Simpan path foto ke tb_foto_produk
                $url_foto = "uploads/produk/" . $nama_file_baru;
                mysqli_query($conn, "
                    INSERT INTO tb_foto_produk (id_produk, url_foto, urutan)
                    VALUES ('$id_produk_baru', '$url_foto', '$i')
                ");
            }
        }

        header("Location: dashboard.php?sukses=1");
        exit;
    } else {
        echo "Gagal menyimpan produk: " . mysqli_error($conn);
    }
}
?>