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
        if ($stmt->execute([$user_id, $name, $description, $category, $frequency])) {
            $_SESSION['flash_success'] = "Activity added successfully.";
        } else {
            $_SESSION['flash_error'] = "Failed to add activity.";
        }
    } else {
        $_SESSION['flash_error'] = "Name and frequency are required.";
    }
    header('Location: ../views/dashboard.php');
    exit();
}

if (isset($_POST['delete_activity']) && isset($_POST['delete_activity_id'])) {
    $activity_id = intval($_POST['delete_activity_id']);
    $stmt = $pdo->prepare("DELETE FROM sk_activities WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$activity_id, $user_id]) && $stmt->rowCount() > 0) {
        $_SESSION['flash_success'] = "Activity deleted successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to delete activity or activity not found.";
    }
    header('Location: ../views/dashboard.php');
    exit();
}

if (isset($_POST['update_activity']) && isset($_POST['activity_id'])) {
    $activity_id = intval($_POST['activity_id']);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');

    if ($name && $frequency) {
        $stmt = $pdo->prepare("UPDATE sk_activities SET name = ?, description = ?, category = ?, frequency = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$name, $description, $category, $frequency, $activity_id, $user_id]) && $stmt->rowCount() > 0) {
            $_SESSION['flash_success'] = "Activity updated successfully.";
        } else {
            $_SESSION['flash_error'] = "Failed to update activity or no changes made.";
        }
    } else {
        $_SESSION['flash_error'] = "Name and frequency are required for update.";
    }
    header('Location: ../views/dashboard.php');
    exit();
}

header('Location: ../views/dashboard.php');
exit();