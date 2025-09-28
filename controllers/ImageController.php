<?php
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/db.php';

$user_id = $_SESSION['user_id'];

// Check if activity_id is provided
if (!isset($_POST['activity_id']) || empty($_POST['activity_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing activity ID']);
    exit();
}

$activity_id = $_POST['activity_id'];

// Check if a file is uploaded
if (!isset($_FILES['memory_image'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

// Set upload directory
$uploadDir = __DIR__ . '/../uploads/memories/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$file = $_FILES['memory_image'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Validate file extension
if (!in_array(strtolower($ext), $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Verify the activity belongs to the logged-in user
$db = new Database();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT * FROM sk_activities WHERE id = ? AND user_id = ?");
$stmt->execute([$activity_id, $user_id]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activity) {
    echo json_encode(['success' => false, 'message' => 'Activity not found or unauthorized']);
    exit();
}

// Generate a unique filename
$newFileName = uniqid('memory_', true) . '.' . $ext;
$destination = $uploadDir . $newFileName;

// Move uploaded file to destination
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Save relative path to DB
    $imageUrl = 'uploads/memories/' . $newFileName;

    $updateStmt = $pdo->prepare("UPDATE sk_activities SET memory_image = ? WHERE id = ? AND user_id = ?");
    if ($updateStmt->execute([$imageUrl, $activity_id, $user_id])) {
        echo json_encode(['success' => true, 'message' => 'Image uploaded successfully', 'image_url' => $imageUrl]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update activity with image']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}