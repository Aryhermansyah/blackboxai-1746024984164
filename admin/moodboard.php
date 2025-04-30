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

// Fetch moodboard images with client info
$sql = "SELECT m.id, m.image_path, m.description, m.uploaded_at, c.groom_name, c.bride_name 
        FROM moodboard_images m 
        LEFT JOIN clients c ON m.client_id = c.id 
        ORDER BY m.uploaded_at DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Moodboard - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
    <style>
        .moodboard-image {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1>Moodboard</h1>
    <a href="moodboard_form.php" class="btn btn-primary mb-3">Upload Gambar Baru</a>
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="../<?= htmlspecialchars($row['image_path']) ?>" alt="Moodboard Image" class="card-img-top moodboard-image" />
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['groom_name']) ?> &amp; <?= htmlspecialchars($row['bride_name']) ?></h5>
                            <p class="card-text"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                            <p class="card-text"><small class="text-muted">Diunggah: <?= $row['uploaded_at'] ?></small></p>
                            <a href="moodboard_comments.php?image_id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Komentar</a>
                            <a href="moodboard_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus gambar ini?');">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada gambar moodboard.</p>
        <?php endif; ?>
    </div>
    <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
</div>
</body>
</html>
