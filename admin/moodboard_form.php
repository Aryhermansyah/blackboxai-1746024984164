<?php
session_start();
require_once '../config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = 0;
$description = "";
$error = "";
$success = "";

// Fetch clients for dropdown
$clients_result = $conn->query("SELECT id, groom_name, bride_name FROM clients ORDER BY event_date DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $description = $_POST['description'] ?? '';

    if ($client_id <= 0) {
        $error = "Pilih klien terlebih dahulu.";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
        $error = "Unggah gambar moodboard.";
    } else {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = basename($_FILES['image']['name']);
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Format gambar harus JPG atau PNG.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $error = "Ukuran gambar maksimal 2MB.";
        } else {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $new_name = 'moodboard_' . time() . '.' . $file_ext;
            if (move_uploaded_file($file_tmp, $upload_dir . $new_name)) {
                $image_path = 'uploads/' . $new_name;
                $stmt = $conn->prepare("INSERT INTO moodboard_images (client_id, image_path, description) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $client_id, $image_path, $description);
                if ($stmt->execute()) {
                    $success = "Gambar moodboard berhasil diunggah.";
                    $client_id = 0;
                    $description = "";
                } else {
                    $error = "Gagal menyimpan data gambar.";
                }
                $stmt->close();
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Upload Gambar Moodboard - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Upload Gambar Moodboard</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="client_id" class="form-label">Pilih Klien *</label>
            <select name="client_id" id="client_id" class="form-select" required>
                <option value="">-- Pilih Klien --</option>
                <?php foreach ($clients_result as $client): ?>
                    <option value="<?= $client['id'] ?>" <?= $client['id'] == $client_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($client['groom_name']) ?> &amp; <?= htmlspecialchars($client['bride_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Pilih Gambar (JPG/PNG, max 2MB) *</label>
            <input type="file" name="image" id="image" class="form-control" accept=".jpg,.jpeg,.png" required />
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($description) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Unggah</button>
        <a href="moodboard.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
