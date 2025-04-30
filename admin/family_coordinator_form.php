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
$name = $role = $phone = "";
$error = "";
$success = "";

if ($id > 0) {
    // Fetch existing family coordinator data
    $stmt = $conn->prepare("SELECT * FROM family_coordinators WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $role = $row['role'];
        $phone = $row['phone'];
    } else {
        $error = "Data koordinator keluarga tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Validate required fields
    if (empty($name) || empty($role)) {
        $error = "Nama dan peran wajib diisi.";
    } else {
        if ($id > 0) {
            // Update existing family coordinator
            $stmt = $conn->prepare("UPDATE family_coordinators SET name=?, role=?, phone=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $role, $phone, $id);
            if ($stmt->execute()) {
                $success = "Data koordinator keluarga berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data koordinator keluarga.";
            }
            $stmt->close();
        } else {
            // Insert new family coordinator
            $stmt = $conn->prepare("INSERT INTO family_coordinators (name, role, phone) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $role, $phone);
            if ($stmt->execute()) {
                $success = "Data koordinator keluarga berhasil disimpan.";
                $id = $stmt->insert_id;
            } else {
                $error = "Gagal menyimpan data koordinator keluarga.";
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
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Koordinator Keluarga - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Koordinator Keluarga</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Nama *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required />
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Peran *</label>
            <input type="text" name="role" id="role" class="form-control" value="<?= htmlspecialchars($role) ?>" required />
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Nomor HP</label>
            <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>" />
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="family_coordinators.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
