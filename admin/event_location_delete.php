<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: event_locations.php");
    exit();
}

$id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Delete event location record
$stmt = $conn->prepare("DELETE FROM event_locations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$conn->close();

header("Location: event_locations.php");
exit();
?>
