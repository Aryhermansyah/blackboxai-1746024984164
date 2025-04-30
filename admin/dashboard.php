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

// Fetch summary data for dashboard
$clientCount = $conn->query("SELECT COUNT(*) as count FROM clients")->fetch_assoc()['count'];
$rundownCount = $conn->query("SELECT COUNT(*) as count FROM rundown_segments")->fetch_assoc()['count'];
$vendorCount = $conn->query("SELECT COUNT(*) as count FROM vendors")->fetch_assoc()['count'];
$teamCount = $conn->query("SELECT COUNT(*) as count FROM team_members")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Admin - Wedding Rundown</title>
    <link href="../assets/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Dashboard Admin</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Klien</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $clientCount ?></h5>
                    <p class="card-text">Jumlah klien terdaftar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Rundown</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $rundownCount ?></h5>
                    <p class="card-text">Segmen rundown</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Vendor</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $vendorCount ?></h5>
                    <p class="card-text">Vendor terdaftar</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-header">Tim WO</div>
                <div class="card-body">
                    <h5 class="card-title"><?= $teamCount ?></h5>
                    <p class="card-text">Anggota tim</p>
                </div>
            </div>
        </div>
    </div>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>
</body>
</html>
