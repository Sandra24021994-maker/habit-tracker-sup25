<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];

if (isset($_POST['add_activity'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');

    if ($name && $frequency) {
        $stmt = $pdo->prepare("INSERT INTO sk_activities (user_id, name, description, category, frequency, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $name, $description, $category, $frequency]);
    }
    header('Location: ../views/dashboard.php');
    exit();
}

if (isset($_POST['delete_activity']) && isset($_POST['delete_activity_id'])) {
    $activity_id = intval($_POST['delete_activity_id']);
    $stmt = $pdo->prepare("DELETE FROM sk_activities WHERE id = ? AND user_id = ?");
    $stmt->execute([$activity_id, $user_id]);
    header('Location: ../views/dashboard.php');
    exit();
}

header('Location: ../views/dashboard.php');
exit();

