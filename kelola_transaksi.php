<?php
include '../includes/cek_admin.php';
include '../koneksi.php';
include '../includes/status_label.php';


/* ==========================================
   UPDATE STATUS PESANAN
   ========================================== */

if (isset($_POST['update_status'])) {

    $id_transaksi = intval($_POST['id_transaksi']);
    $status_baru = $_POST['status_pesanan'];

    // Status yang diperbolehkan
    $status_valid = [
        'pending',
        'diproses',
        'dikirim',
        'selesai'
    ];

    if (in_array($status_baru, $status_valid)) {

        $update = mysqli_query($conn, "
            UPDATE tb_transaksi
            SET status_pesanan = '$status_baru'
            WHERE id_transaksi = '$id_transaksi'
        ");

        if ($update) {

            header("Location: kelola_transaksi.php?success=Status berhasil diperbarui");
            exit;

        } else {

            header("Location: kelola_transaksi.php?error=Status gagal diperbarui");
            exit;

        }

    } else {

        header("Location: kelola_transaksi.php?error=Status tidak valid");
        exit;

    }
}


/* ==========================================
   AMBIL PESANAN DARI DATABASE
   ========================================== */

$query = mysqli_query($conn, "
    SELECT
        t.*,
        u.nama AS nama_pelanggan,
        u.email AS email_pelanggan
    FROM tb_transaksi t
    JOIN tb_user u
        ON t.id_pelanggan = u.id
    ORDER BY t.id_transaksi DESC
");


if (!$query) {
    die("Query transaksi gagal: " . mysqli_error($conn));
}


/* ==========================================
   NOTIFIKASI
   ========================================== */

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Data Transaksi — FrostMart Admin</title>

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

    <style>

        /* =========================
           ALERT
           ========================= */

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .alert-error {
            background: #f8d7da;
            color: #842029;
        }


        /* =========================
           STATUS SELECT
           ========================= */

        .status-form {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 240px;
        }

        .status-select {
            padding: 8px 10px;
            border: 1px solid #d5dce5;
            border-radius: 8px;
            background: white;
            font-size: 13px;
            cursor: pointer;
            outline: none;
        }

        .status-select:focus {
            border-color: #5b9bd5;
        }


        /* =========================
           BUTTON
           ========================= */

        .btn-save {
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            background: #1677c8;
            color: white;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #0f65ad;
        }

        .btn-detail {
            display: inline-block;
            padding: 8px 12px;
            background: #e8f4ff;
            color: #1677c8;
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .btn-detail:hover {
            background: #d5ebff;
        }


        /* =========================
           STATUS LABEL
           ========================= */

        .status-label {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            color: #777;
        }


        /* =========================
           RESPONSIVE TABLE
           ========================= */

        .table-wrap {
            overflow-x: auto;
        }

    </style>

</head>


<body>


<div class="admin-layout">


    <!-- =================================
         SIDEBAR
         ================================= -->

    <aside class="sidebar">

        <div class="sidebar-brand">
            ❄️ FrostMart Admin
        </div>


        <nav>

            <a href="dashboard.php">
                Dashboard
            </a>


            <a
                href="kelola_transaksi.php"
                class="active"
            >
                Data Transaksi
            </a>


            <a href="kelola_pelanggan.php">
                Data Pelanggan
            </a>


            <a href="profil.php">
                Profil Saya
            </a>


            <a href="../logout.php">
                Logout
            </a>

        </nav>

    </aside>



    <!-- =================================
         MAIN CONTENT
         ================================= -->

    <main class="main-content">


        <h1>
            Data Transaksi
        </h1>


        <p class="welcome">
            Kelola transaksi dan status pesanan pelanggan FrostMart.
        </p>



        <!-- =================================
             NOTIFIKASI
             ================================= -->

        <?php if ($success): ?>

            <div class="alert alert-success">

                ✅
                <?php echo htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>


        <?php if ($error): ?>

            <div class="alert alert-error">

                ❌
                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>



        <!-- =================================
             PANEL TRANSAKSI
             ================================= -->

        <section class="panel">


            <div class="table-wrap">


                <table>


                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Pelanggan
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Tanggal
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Status Pesanan
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>

                    </thead>



                    <tbody>


                    <?php if (mysqli_num_rows($query) > 0): ?>


                        <?php while ($row = mysqli_fetch_assoc($query)): ?>


                            <?php

                            /*
                             * Ambil total transaksi.
                             * Kalau kolom total tidak ada,
                             * sementara ditampilkan 0.
                             */

                            $total = isset($row['total'])
                                ? (int) $row['total']
                                : 0;


                            /*
                             * Status asli dari database
                             */

                            $status = $row['status_pesanan'] ?? 'pending';

                            ?>


                            <tr>


                                <!-- =========================
                                     ID TRANSAKSI
                                     ========================= -->

                                <td>

                                    <strong>
                                        #
                                        <?php
                                        echo htmlspecialchars(
                                            $row['id_transaksi']
                                        );
                                        ?>
                                    </strong>

                                </td>



                                <!-- =========================
                                     PELANGGAN
                                     ========================= -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['nama_pelanggan']
                                    );
                                    ?>

                                </td>



                                <!-- =========================
                                     EMAIL
                                     ========================= -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['email_pelanggan']
                                    );
                                    ?>

                                </td>



                                <!-- =========================
                                     TANGGAL
                                     ========================= -->

                                <td>

                                    <?php

                                    if (!empty($row['tanggal'])) {

                                        echo date(
                                            'd/m/Y',
                                            strtotime($row['tanggal'])
                                        );

                                    } else {

                                        echo '-';

                                    }

                                    ?>

                                </td>



                                <!-- =========================
                                     TOTAL
                                     ========================= -->

                                <td>

                                    Rp
                                    <?php

                                    echo number_format(
                                        $total,
                                        0,
                                        ',',
                                        '.'
                                    );

                                    ?>

                                </td>



                                <!-- =========================
                                     STATUS + EDIT
                                     ========================= -->

                                <td>


                                    <form
                                        method="POST"
                                        action="kelola_transaksi.php"
                                        class="status-form"
                                    >


                                        <!-- ID TRANSAKSI -->

                                        <input
                                            type="hidden"
                                            name="id_transaksi"
                                            value="<?php
                                            echo $row['id_transaksi'];
                                            ?>"
                                        >



                                        <!-- PILIH STATUS -->

                                        <select
                                            name="status_pesanan"
                                            class="status-select"
                                        >


                                            <option
                                                value="pending"
                                                <?php
                                                echo $status == 'pending'
                                                    ? 'selected'
                                                    : '';
                                                ?>
                                            >
                                                Menunggu Konfirmasi
                                            </option>


                                            <option
                                                value="diproses"
                                                <?php
                                                echo $status == 'diproses'
                                                    ? 'selected'
                                                    : '';
                                                ?>
                                            >
                                                Sedang Dikemas
                                            </option>


                                            <option
                                                value="dikirim"
                                                <?php
                                                echo $status == 'dikirim'
                                                    ? 'selected'
                                                    : '';
                                                ?>
                                            >
                                                Sedang Dalam Perjalanan
                                            </option>


                                            <option
                                                value="selesai"
                                                <?php
                                                echo $status == 'selesai'
                                                    ? 'selected'
                                                    : '';
                                                ?>
                                            >
                                                Pesanan Telah Sampai
                                            </option>


                                        </select>



                                        <!-- SIMPAN -->

                                        <button
                                            type="submit"
                                            name="update_status"
                                            class="btn-save"
                                        >
                                            Simpan
                                        </button>


                                    </form>


                                    <!-- STATUS DARI status_label.php -->

                                    <span class="status-label">

                                        Status saat ini:
                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                label_status($status)
                                            );
                                            ?>
                                        </strong>

                                    </span>


                                </td>



                                <!-- =========================
                                     DETAIL
                                     ========================= -->

                                <td>

                                    <a
                                        href="detail_transaksi.php?id=<?php
                                        echo $row['id_transaksi'];
                                        ?>"
                                        class="btn-detail"
                                    >
                                        👁️ Detail
                                    </a>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                style="
                                    text-align:center;
                                    padding:30px;
                                    color:#777;
                                "
                            >

                                Belum ada transaksi pelanggan.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>

                </table>

            </div>


        </section>


    </main>


</div>


</body>

</html>