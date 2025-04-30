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
$client_id = 0;
$session_description = "";
$participants = "";
$order_number = 0;
$error = "";
$success = "";

// Fetch clients for dropdown
$clients_result = $conn->query("SELECT id, groom_name, bride_name FROM clients ORDER BY event_date DESC");

if ($id > 0) {
    // Fetch existing photo session data
    $stmt = $conn->prepare("SELECT client_id, session_description, participants, order_number FROM photo_sessions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $client_id = $row['client_id'];
        $session_description = $row['session_description'];
        $participants = $row['participants'];
        $order_number = $row['order_number'];
    } else {
        $error = "Data sesi foto tidak ditemukan.";
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $session_description = $_POST['session_description'] ?? '';
    $participants = $_POST['participants'] ?? '';
    $order_number = intval($_POST['order_number']);

    if ($client_id <= 0 || empty($session_description) || $order_number <= 0) {
        $error = "Pilih klien, isi deskripsi sesi, dan urutan wajib diisi dengan benar.";
    } else {
        if ($id > 0) {
            // Update existing photo session
            $stmt = $conn->prepare("UPDATE photo_sessions SET client_id=?, session_description=?, participants=?, order_number=? WHERE id=?");
            $stmt->bind_param("issii", $client_id, $session_description, $participants, $order_number, $id);
            if ($stmt->execute()) {
                $success = "Data sesi foto berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data sesi foto.";
            }
            $stmt->close();
        } else {
            // Insert new photo session
            $stmt = $conn->prepare("INSERT INTO photo_sessions (client_id, session_description, participants, order_number) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $client_id, $session_description, $participants, $order_number);
            if ($stmt->execute()) {
                $success = "Data sesi foto berhasil disimpan.";
                $id = $stmt->insert_id;
            } else {
                $error = "Gagal menyimpan data sesi foto.";
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
    <title><?= $id > 0 ? 'Edit' : 'Tambah' ?> Sesi Foto - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1><?= $id > 0 ? 'Edit' : 'Tambah' ?> Sesi Foto</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post" action="">
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
            <label for="session_description" class="form-label">Deskripsi Sesi Foto *</label>
            <input type="text" name="session_description" id="session_description" class="form-control" value="<?= htmlspecialchars($session_description) ?>" required />
        </div>
        <div class="mb-3">
            <label for="participants" class="form-label">Nama-nama Peserta</label>
            <textarea name="participants" id="participants" class="form-control" rows="3"><?= htmlspecialchars($participants) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="order_number" class="form-label">Urutan Pengambilan Foto *</label>
            <input type="number" name="order_number" id="order_number" class="form-control" value="<?= htmlspecialchars($order_number) ?>" min="1" required />
        </div>
        <button type="submit" class="btn btn-success"><?= $id > 0 ? 'Update' : 'Simpan' ?></button>
        <a href="photo_sessions.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
