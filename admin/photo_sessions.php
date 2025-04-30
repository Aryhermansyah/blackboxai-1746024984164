<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Connect to database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch photo sessions with client info
$sql = "SELECT p.id, p.session_description, p.participants, p.order_number, c.groom_name, c.bride_name 
        FROM photo_sessions p 
        LEFT JOIN clients c ON p.client_id = c.id 
        ORDER BY p.order_number ASC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>List Foto Keluarga - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>List Foto Keluarga</h1>
    <a href="photo_session_form.php" class="btn btn-primary mb-3">Tambah Sesi Foto Baru</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Deskripsi Sesi</th>
                <th>Nama Klien</th>
                <th>Peserta</th>
                <th>Urutan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['session_description']) ?></td>
                        <td><?= htmlspecialchars($row['groom_name']) ?> &amp; <?= htmlspecialchars($row['bride_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['participants'])) ?></td>
                        <td><?= htmlspecialchars($row['order_number']) ?></td>
                        <td>
                            <a href="photo_session_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="photo_session_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus sesi foto ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">Tidak ada data sesi foto.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>
</body>
</html>
