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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$name = $address = $google_maps_link = "";
$error = "";
$success = "";

if ($id > 0) {
    // Fetch existing event location data
    $stmt = $conn->prepare("SELECT * FROM event_locations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $address = $row['address'];
        $google_maps_link = $row['google_maps_link'];
    } else {
        $error = "Data lokasi acara tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $google_maps_link = $_POST['google_maps_link'] ?? '';

    if (empty($name) || empty($address)) {
        $error = "Nama tempat dan alamat wajib diisi.";
    } else {
        if ($id > 0) {
            // Update existing event location
            $stmt = $conn->prepare("UPDATE event_locations SET name=?, address=?, google_maps_link=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $address, $google_maps_link, $id);
            if ($stmt->execute()) {
                $success = "Data lokasi acara berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data lokasi acara.";
            }
            $stmt->close();
        } else {
            // Insert new event location
            $stmt = $conn->prepare("INSERT INTO event_locations (name, address, google_maps_link) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $address, $google_maps_link);
            if ($stmt->execute()) {
                $success = "Data lokasi acara berhasil disimpan.";
                $id = $stmt->insert_id;
            } else {
                $error = "Gagal menyimpan data lokasi acara.";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Lokasi Acara - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Lokasi Acara</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Tempat *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Alamat Lengkap *</label>
            <textarea name="address" id="address" class="form-control" rows="3" required><?= htmlspecialchars($address) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="google_maps_link" class="form-label">Link Google Maps</label>
            <input type="url" name="google_maps_link" id="google_maps_link" class="form-control" value="<?= htmlspecialchars($google_maps_link) ?>" />
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="event_locations.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
