<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: clients.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete client photos from uploads folder
$stmt = $conn->prepare("SELECT groom_photo, bride_photo FROM clients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($groom_photo, $bride_photo);
$stmt->fetch();
$stmt->close();

if ($groom_photo && file_exists('../' . $groom_photo)) {
    unlink('../' . $groom_photo);
}
if ($bride_photo && file_exists('../' . $bride_photo)) {
    unlink('../' . $bride_photo);
}

// Delete client record
$stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: clients.php");
exit();
?>
