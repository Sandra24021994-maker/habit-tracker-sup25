<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/db.php';

$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];
$message = '';

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_email = trim($_POST['email'] ?? '');

    // Basic validation
    if (empty($new_username) || empty($new_email)) {
        $message = 'Please fill in all fields.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        // Check if username or email already exists for another user
        $stmt = $pdo->prepare("SELECT id FROM sk_users WHERE (username = :username OR email = :email) AND id != :id");
        $stmt->execute([
            ':username' => $new_username,
            ':email' => $new_email,
            ':id' => $user_id
        ]);
        if ($stmt->rowCount() > 0) {
            $message = 'Username or email already taken.';
        } else {
            // Update user info
            $stmt = $pdo->prepare("UPDATE sk_users SET username = :username, email = :email WHERE id = :id");
            $stmt->execute([
                ':username' => $new_username,
                ':email' => $new_email,
                ':id' => $user_id
            ]);
            $message = 'Profile updated successfully.';
            // Update session username
            $_SESSION['username'] = $new_username;
        }
    }
}

// Fetch current user data to populate form
$stmt = $pdo->prepare("SELECT username, email, created_at FROM sk_users WHERE id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User not found (should not happen if logged in)
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Profile - Hobilo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb, #fc5c7d);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            min-height: 100vh;
        }
        .profile-container {
            max-width: 480px;
            margin: 60px auto;
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        h1 {
            font-weight: 700;
            color: #3366ff;
            margin-bottom: 1rem;
            user-select: none;
        }
        label {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #3366ff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #274bdb;
        }
        .message {
            margin-bottom: 1rem;
        }
        .created-date {
            color: #777;
            font-style: italic;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        a.back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #3366ff;
            font-weight: 600;
            text-decoration: none;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1>Your Profile</h1>

        <?php if ($message): ?>
            <div class="alert alert-info message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <p class="created-date">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>

        <form method="POST" action="profile.php" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-control"
                    required
                    value="<?php echo htmlspecialchars($user['username']); ?>"
                />
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    required
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                />
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Profile</button>
        </form>

        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
    </div>
</body>
</html>