<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];

function respond($success, $message, $extra = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid image type.'];
    }

    $uploadsDir = '../uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }

    $fileName = uniqid('img_') . '_' . basename($file['name']);
    $targetPath = $uploadsDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => 'uploads/' . $fileName];
    }

    return ['success' => false, 'message' => 'Failed to upload image.'];
}

// Add activity
if (isset($_POST['add_activity'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $frequency = trim($_POST['frequency'] ?? '');

    $imagePath = null;

    if (isset($_FILES['memory_image']) && $_FILES['memory_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['memory_image']);
        if ($uploadResult['success']) {
            $imagePath = $uploadResult['path'];
        } else {
            respond(false, $uploadResult['message']);
        }
    }

    if ($name && $frequency) {
        $stmt = $pdo->prepare("INSERT INTO sk_activities (user_id, name, description, category, frequency, memory_image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt->execute([$user_id, $name, $description, $category, $frequency, $imagePath])) {
            $id = $pdo->lastInsertId();
            respond(true, 'Activity added successfully.', [
                'activity' => [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'frequency' => $frequency,
                    'memory_image' => $imagePath,
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

    // Get image path if exists
    $stmt = $pdo->prepare("SELECT memory_image FROM sk_activities WHERE id = ? AND user_id = ?");
    $stmt->execute([$activity_id, $user_id]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete activity
    $stmt = $pdo->prepare("DELETE FROM sk_activities WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$activity_id, $user_id]) && $stmt->rowCount() > 0) {
        // Delete the image file
        if ($activity && !empty($activity['memory_image'])) {
            $imagePath = '../' . $activity['memory_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
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

    $imagePath = null;

    if (isset($_FILES['memory_image']) && $_FILES['memory_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['memory_image']);
        if ($uploadResult['success']) {
            $imagePath = $uploadResult['path'];

            // Delete old image
            $stmt = $pdo->prepare("SELECT memory_image FROM sk_activities WHERE id = ? AND user_id = ?");
            $stmt->execute([$activity_id, $user_id]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($old && !empty($old['memory_image'])) {
                $oldPath = '../' . $old['memory_image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        } else {
            respond(false, $uploadResult['message']);
        }
    }

    if ($name && $frequency) {
        $query = "UPDATE sk_activities SET name = ?, description = ?, category = ?, frequency = ?";
        $params = [$name, $description, $category, $frequency];

        if ($imagePath !== null) {
            $query .= ", memory_image = ?";
            $params[] = $imagePath;
        }

        $query .= " WHERE id = ? AND user_id = ?";
        $params[] = $activity_id;
        $params[] = $user_id;

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params) && $stmt->rowCount() > 0) {
            respond(true, 'Activity updated successfully.');
        } else {
            respond(false, 'Failed to update activity or no changes made.');
        }
    } else {
        respond(false, 'Name and frequency are required for update.');
    }
}

respond(false, 'Invalid request.');