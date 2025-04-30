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

// Fetch clients
$result = $conn->query("SELECT * FROM clients ORDER BY event_date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Data Klien - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Data Klien</h1>
    <a href="client_form.php" class="btn btn-primary mb-3">Tambah Klien Baru</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama Pengantin Pria</th>
                <th>Nama Pengantin Wanita</th>
                <th>Tanggal Acara</th>
                <th>Waktu Acara</th>
                <th>Lokasi Acara</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['groom_name']) ?></td>
                        <td><?= htmlspecialchars($row['bride_name']) ?></td>
                        <td><?= htmlspecialchars($row['event_date']) ?></td>
                        <td><?= htmlspecialchars($row['event_time']) ?></td>
                        <td><?= htmlspecialchars($row['event_location']) ?></td>
                        <td>
                            <a href="client_form.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="client_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus klien ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Tidak ada data klien.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>
</body>
</html>
