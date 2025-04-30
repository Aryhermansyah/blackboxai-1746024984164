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
$groom_name = $bride_name = $event_date = $event_time = $event_location = $event_theme = "";
$groom_photo = $bride_photo = "";
$error = "";
$success = "";

if ($id > 0) {
    // Fetch existing client data
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $groom_name = $row['groom_name'];
        $bride_name = $row['bride_name'];
        $event_date = $row['event_date'];
        $event_time = $row['event_time'];
        $event_location = $row['event_location'];
        $event_theme = $row['event_theme'];
        $groom_photo = $row['groom_photo'];
        $bride_photo = $row['bride_photo'];
    } else {
        $error = "Data klien tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groom_name = $_POST['groom_name'] ?? '';
    $bride_name = $_POST['bride_name'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $event_location = $_POST['event_location'] ?? '';
    $event_theme = $_POST['event_theme'] ?? '';

    // Validate required fields
    if (empty($groom_name) || empty($bride_name) || empty($event_date) || empty($event_time) || empty($event_location)) {
        $error = "Semua field wajib diisi kecuali tema acara.";
    } else {
        // Handle file uploads
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Groom photo upload
        if (isset($_FILES['groom_photo']) && $_FILES['groom_photo']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['groom_photo']['tmp_name'];
            $file_name = basename($_FILES['groom_photo']['name']);
            $file_size = $_FILES['groom_photo']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Foto pengantin pria harus berformat JPG atau PNG.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = "Ukuran foto pengantin pria maksimal 2MB.";
            } else {
                $new_name = 'groom_' . time() . '.' . $file_ext;
                move_uploaded_file($file_tmp, $upload_dir . $new_name);
                $groom_photo = 'uploads/' . $new_name;
            }
        }

        // Bride photo upload
        if (isset($_FILES['bride_photo']) && $_FILES['bride_photo']['error'] == UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['bride_photo']['tmp_name'];
            $file_name = basename($_FILES['bride_photo']['name']);
            $file_size = $_FILES['bride_photo']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (!in_array($file_ext, $allowed_ext)) {
                $error = "Foto pengantin wanita harus berformat JPG atau PNG.";
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = "Ukuran foto pengantin wanita maksimal 2MB.";
            } else {
                $new_name = 'bride_' . time() . '.' . $file_ext;
                move_uploaded_file($file_tmp, $upload_dir . $new_name);
                $bride_photo = 'uploads/' . $new_name;
            }
        }

        if (empty($error)) {
            if ($id > 0) {
                // Update existing client
                $stmt = $conn->prepare("UPDATE clients SET groom_name=?, bride_name=?, groom_photo=?, bride_photo=?, event_date=?, event_time=?, event_location=?, event_theme=? WHERE id=?");
                $stmt->bind_param("ssssssssi", $groom_name, $bride_name, $groom_photo, $bride_photo, $event_date, $event_time, $event_location, $event_theme, $id);
                if ($stmt->execute()) {
                    $success = "Data klien berhasil diperbarui.";
                } else {
                    $error = "Gagal memperbarui data klien.";
                }
                $stmt->close();
            } else {
                // Insert new client
                $stmt = $conn->prepare("INSERT INTO clients (groom_name, bride_name, groom_photo, bride_photo, event_date, event_time, event_location, event_theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $groom_name, $bride_name, $groom_photo, $bride_photo, $event_date, $event_time, $event_location, $event_theme);
                if ($stmt->execute()) {
                    $success = "Data klien berhasil disimpan.";
                    $id = $stmt->insert_id;
                } else {
                    $error = "Gagal menyimpan data klien.";
                }
                $stmt->close();
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
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Klien - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Klien</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="groom_name" class="form-label">Nama Pengantin Pria *</label>
            <input type="text" name="groom_name" id="groom_name" class="form-control" value="<?= htmlspecialchars($groom_name) ?>" required />
        </div>
        <div class="mb-3">
            <label for="bride_name" class="form-label">Nama Pengantin Wanita *</label>
            <input type="text" name="bride_name" id="bride_name" class="form-control" value="<?= htmlspecialchars($bride_name) ?>" required />
        </div>
        <div class="mb-3">
            <label for="groom_photo" class="form-label">Foto Pengantin Pria (JPG/PNG, max 2MB)</label>
            <?php if ($groom_photo): ?>
                <div><img src="../<?= htmlspecialchars($groom_photo) ?>" alt="Foto Pengantin Pria" style="max-width: 150px;"/></div>
            <?php endif; ?>
            <input type="file" name="groom_photo" id="groom_photo" class="form-control" accept=".jpg,.jpeg,.png" />
        </div>
        <div class="mb-3">
            <label for="bride_photo" class="form-label">Foto Pengantin Wanita (JPG/PNG, max 2MB)</label>
            <?php if ($bride_photo): ?>
                <div><img src="../<?= htmlspecialchars($bride_photo) ?>" alt="Foto Pengantin Wanita" style="max-width: 150px;"/></div>
            <?php endif; ?>
            <input type="file" name="bride_photo" id="bride_photo" class="form-control" accept=".jpg,.jpeg,.png" />
        </div>
        <div class="mb-3">
            <label for="event_date" class="form-label">Tanggal Acara *</label>
            <input type="date" name="event_date" id="event_date" class="form-control" value="<?= htmlspecialchars($event_date) ?>" required />
        </div>
        <div class="mb-3">
            <label for="event_time" class="form-label">Waktu Acara *</label>
            <input type="time" name="event_time" id="event_time" class="form-control" value="<?= htmlspecialchars($event_time) ?>" required />
        </div>
        <div class="mb-3">
            <label for="event_location" class="form-label">Lokasi Acara *</label>
            <input type="text" name="event_location" id="event_location" class="form-control" value="<?= htmlspecialchars($event_location) ?>" required />
        </div>
        <div class="mb-3">
            <label for="event_theme" class="form-label">Tema Acara</label>
            <input type="text" name="event_theme" id="event_theme" class="form-control" value="<?= htmlspecialchars($event_theme) ?>" />
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="clients.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
