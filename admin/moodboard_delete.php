<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: moodboard.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete moodboard image file
$stmt = $conn->prepare("SELECT image_path FROM moodboard_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($image_path);
$stmt->fetch();
$stmt->close();

if ($image_path && file_exists('../' . $image_path)) {
    unlink('../' . $image_path);
}

// Delete moodboard image record
$stmt = $conn->prepare("DELETE FROM moodboard_images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: moodboard.php");
exit();
?>
