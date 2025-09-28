<?php
session_start();

header('Content-Type: application/json'); // All responses are JSON

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Helper function to send JSON response and stop script
function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

// Add activity
if (isset($_POST['add_activity'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');

    if ($name && $frequency) {
        $stmt = $pdo->prepare("INSERT INTO sk_activities (user_id, name, description, category, frequency, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $name, $description, $category, $frequency])) {
            $id = $pdo->lastInsertId();
            respond(true, 'Activity added successfully.', [
                'activity' => [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'frequency' => $frequency,
                    'created_at' => date('Y-m-d')
                ]
            ]);
        } else {
            respond(false, 'Failed to add activity.');
        }
    } else {
        respond(false, 'Name and frequency are required.');
    }
}

// Delete activity
if (isset($_POST['delete_activity']) && isset($_POST['delete_activity_id'])) {
    $activity_id = intval($_POST['delete_activity_id']);
    $stmt = $pdo->prepare("DELETE FROM sk_activities WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$activity_id, $user_id]) && $stmt->rowCount() > 0) {
        respond(true, 'Activity deleted successfully.');
    } else {
        respond(false, 'Failed to delete activity or activity not found.');
    }
}

// Update activity
if (isset($_POST['update_activity']) && isset($_POST['activity_id'])) {
    $activity_id = intval($_POST['activity_id']);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');

    if ($name && $frequency) {
        $stmt = $pdo->prepare("UPDATE sk_activities SET name = ?, description = ?, category = ?, frequency = ? WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$name, $description, $category, $frequency, $activity_id, $user_id]) && $stmt->rowCount() > 0) {
            respond(true, 'Activity updated successfully.');
        } else {
            respond(false, 'Failed to update activity or no changes made.');
        }
    } else {
        respond(false, 'Name and frequency are required for update.');
    }
}

// If none of the above conditions are met
respond(false, 'Invalid request.');