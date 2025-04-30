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
$name = $position = $contact = "";
$error = "";
$success = "";

if ($id > 0) {
    // Fetch existing team member data
    $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $position = $row['position'];
        $contact = $row['contact'];
    } else {
        $error = "Data anggota tim tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $contact = $_POST['contact'] ?? '';

    // Validate required fields
    if (empty($name) || empty($position)) {
        $error = "Nama dan jabatan wajib diisi.";
    } else {
        if ($id > 0) {
            // Update existing team member
            $stmt = $conn->prepare("UPDATE team_members SET name=?, position=?, contact=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $position, $contact, $id);
            if ($stmt->execute()) {
                $success = "Data anggota tim berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data anggota tim.";
            }
            $stmt->close();
        } else {
            // Insert new team member
            $stmt = $conn->prepare("INSERT INTO team_members (name, position, contact) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $position, $contact);
            if ($stmt->execute()) {
                $success = "Data anggota tim berhasil disimpan.";
                $id = $stmt->insert_id;
            } else {
                $error = "Gagal menyimpan data anggota tim.";
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
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Anggota Tim - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Anggota Tim</h1>
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
            <label for="position" class="form-label">Jabatan *</label>
            <input type="text" name="position" id="position" class="form-control" value="<?= htmlspecialchars($position) ?>" required />
        </div>
        <div class="mb-3">
            <label for="contact" class="form-label">Nomor Kontak</label>
            <input type="text" name="contact" id="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>" />
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="team_members.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
