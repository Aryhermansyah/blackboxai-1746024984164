<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['image_id'])) {
    header("Location: moodboard.php");
    exit();
}

$image_id = intval($_GET['image_id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch moodboard image info
$stmt = $conn->prepare("SELECT image_path, description FROM moodboard_images WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$stmt->bind_result($image_path, $description);
if (!$stmt->fetch()) {
    $stmt->close();
    $conn->close();
    header("Location: moodboard.php");
    exit();
}
$stmt->close();

$error = "";
$success = "";

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commenter = $_POST['commenter'] ?? '';
    $comment = $_POST['comment'] ?? '';

    if (empty($commenter) || empty($comment)) {
        $error = "Nama dan komentar wajib diisi.";
    } else {
        $stmt = $conn->prepare("INSERT INTO moodboard_comments (moodboard_image_id, commenter, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $image_id, $commenter, $comment);
        if ($stmt->execute()) {
            $success = "Komentar berhasil ditambahkan.";
        } else {
            $error = "Gagal menambahkan komentar.";
        }
        $stmt->close();
    }
}

// Fetch comments
$stmt = $conn->prepare("SELECT commenter, comment, commented_at FROM moodboard_comments WHERE moodboard_image_id = ? ORDER BY commented_at DESC");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Komentar Moodboard - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Komentar untuk Gambar Moodboard</h1>
    <div class="mb-3">
        <img src="../<?= htmlspecialchars($image_path) ?>" alt="Moodboard Image" style="max-width: 300px;" />
        <p><?= nl2br(htmlspecialchars($description)) ?></p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="commenter" class="form-label">Nama *</label>
            <input type="text" name="commenter" id="commenter" class="form-control" required />
        </div>
        <div class="mb-3">
            <label for="comment" class="form-label">Komentar *</label>
            <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Tambah Komentar</button>
        <a href="moodboard.php" class="btn btn-secondary">Kembali ke Moodboard</a>
    </form>

    <hr />

    <h3>Daftar Komentar</h3>
    <?php if ($comments_result && $comments_result->num_rows > 0): ?>
        <?php while ($row = $comments_result->fetch_assoc()): ?>
            <div class="mb-3">
                <strong><?= htmlspecialchars($row['commenter']) ?></strong> <small class="text-muted"><?= $row['commented_at'] ?></small>
                <p><?= nl2br(htmlspecialchars($row['comment'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Belum ada komentar.</p>
    <?php endif; ?>
</div>
</body>
</html>
