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
$name = $contact = $service_type = $facilities = "";
$error = "";
$success = "";

if ($id > 0) {
    // Fetch existing vendor data
    $stmt = $conn->prepare("SELECT * FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $contact = $row['contact'];
        $service_type = $row['service_type'];
        $facilities = $row['facilities'];
    } else {
        $error = "Data vendor tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $service_type = $_POST['service_type'] ?? '';
    $facilities = $_POST['facilities'] ?? '';

    // Validate required fields
    if (empty($name) || empty($service_type)) {
        $error = "Nama dan tipe layanan wajib diisi.";
    } else {
        if ($id > 0) {
            // Update existing vendor
            $stmt = $conn->prepare("UPDATE vendors SET name=?, contact=?, service_type=?, facilities=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $contact, $service_type, $facilities, $id);
            if ($stmt->execute()) {
                $success = "Data vendor berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data vendor.";
            }
            $stmt->close();
        } else {
            // Insert new vendor
            $stmt = $conn->prepare("INSERT INTO vendors (name, contact, service_type, facilities) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $contact, $service_type, $facilities);
            if ($stmt->execute()) {
                $success = "Data vendor berhasil disimpan.";
                $id = $stmt->insert_id;
            } else {
                $error = "Gagal menyimpan data vendor.";
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
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Vendor - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Vendor</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Nama Vendor *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
        </div>
        <div class="mb-3">
            <label for="contact" class="form-label">Kontak</label>
            <input type="text" name="contact" id="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>" />
        </div>
        <div class="mb-3">
            <label for="service_type" class="form-label">Tipe Layanan *</label>
            <input type="text" name="service_type" id="service_type" class="form-control" value="<?= htmlspecialchars($service_type) ?>" required />
        </div>
        <div class="mb-3">
            <label for="facilities" class="form-label">Fasilitas</label>
            <textarea name="facilities" id="facilities" class="form-control" rows="4"><?= htmlspecialchars($facilities) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="vendors.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
