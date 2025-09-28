<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

// Include database connection
require_once '../config/db.php'; 

// Get PDO connection
$db = new Database();
$pdo = $db->getConnection();

$user_id = $_SESSION['user_id'];

// Fetch existing activities for this user
$stmt = $pdo->prepare("SELECT * FROM sk_activities WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if edit mode for activity
$edit_id = $_GET['edit_id'] ?? null;
$activity_to_edit = null;

if ($edit_id) {
    foreach ($activities as $act) {
        if ($act['id'] == $edit_id) {
            $activity_to_edit = $act;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard - Hobilo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb, #fc5c7d);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .dashboard-container {
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 100%;
            margin: 80px auto 0;
        }

        h1 {
            font-weight: 700;
            color: #3366ff;
            margin-bottom: 1rem;
            user-select: none;
            text-align: center;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            z-index: 1000;
        }

        .header-title {
            font-size: 24px;
            font-weight: 700;
            color: black;
            user-select: none;
        }

        .dropdown-toggle {
            border: none;
            background: transparent;
            font-size: 28px;
            cursor: pointer;
            color: #333;
        }

        .dropdown-menu {
            right: 0;
            left: auto;
            min-width: 150px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .dropdown-menu a {
            color: #333;
            font-weight: 600;
        }

        .dropdown-menu a:hover {
            background-color: #f1f1f1;
            color: #3366ff;
        }

        /* Form styling */
        form {
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        label {
            font-weight: 600;
        }

        /* Activities table */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #3366ff;
            color: white;
        }

        /* Delete button */
        .btn-delete {
            color: white;
            background-color: #ff4b5c;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-delete:hover {
            background-color: #d93648;
        }
        /* Edit button styling */
        .btn-edit {
            color: white;
            background-color: #17a2b8;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 5px;
            font-size: 0.9rem;
            text-align: center;
        }
        .btn-edit:hover {
            background-color: #138496;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header>
    <div class="header-title" aria-label="Dashboard title">Dashboard</div>

    <div class="dropdown">
        <button class="dropdown-toggle" type="button" id="menuToggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open menu">
            &#9776;
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuToggle">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../controllers/logout.php">Log Out</a></li>
        </ul>
    </div>
</header>

<div class="dashboard-container" role="main">

    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

    <?php if ($activity_to_edit): ?>
        <h2>Edit Activity</h2>
        <form action="../controllers/ActivityController.php" method="post" novalidate>
            <input type="hidden" name="activity_id" value="<?php echo $activity_to_edit['id']; ?>">
            <div class="mb-3">
                <label for="name">Activity Name</label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="100" 
                       value="<?php echo htmlspecialchars($activity_to_edit['name']); ?>" />
            </div>

            <div class="mb-3">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" maxlength="255"><?php echo htmlspecialchars($activity_to_edit['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="category">Category</label>
                <input type="text" class="form-control" id="category" name="category" maxlength="50" 
                       value="<?php echo htmlspecialchars($activity_to_edit['category']); ?>" />
            </div>

            <div class="mb-3">
                <label for="frequency">Frequency</label>
                <select class="form-select" id="frequency" name="frequency" required>
                    <option value="">Select frequency</option>
                    <option value="Daily" <?php if ($activity_to_edit['frequency'] == 'Daily') echo 'selected'; ?>>Daily</option>
                    <option value="Weekly" <?php if ($activity_to_edit['frequency'] == 'Weekly') echo 'selected'; ?>>Weekly</option>
                    <option value="Monthly" <?php if ($activity_to_edit['frequency'] == 'Monthly') echo 'selected'; ?>>Monthly</option>
                </select>
            </div>

            <button type="submit" name="update_activity" class="btn btn-warning w-100">Update Activity</button>
            <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    <?php else: ?>
        <h2>Add Activity</h2>
        <form action="../controllers/ActivityController.php" method="post" novalidate>
            <div class="mb-3">
                <label for="name">Activity Name</label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="100" />
            </div>

            <div class="mb-3">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" maxlength="255"></textarea>
            </div>

            <div class="mb-3">
                <label for="category">Category</label>
                <input type="text" class="form-control" id="category" name="category" maxlength="50" />
            </div>

            <div class="mb-3">
                <label for="frequency">Frequency</label>
                <select class="form-select" id="frequency" name="frequency" required>
                    <option value="">Select frequency</option>
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                </select>
            </div>

            <button type="submit" name="add_activity" class="btn btn-primary w-100">Add Activity</button>
        </form>
    <?php endif; ?>

    <h2>Your Activities</h2>

    <?php if (count($activities) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Frequency</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($activities as $activity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($activity['name']); ?></td>
                        <td><?php echo htmlspecialchars($activity['description']); ?></td>
                        <td><?php echo htmlspecialchars($activity['category']); ?></td>
                        <td><?php echo htmlspecialchars($activity['frequency']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($activity['created_at'])); ?></td>
                        <td>
                            <form action="../controllers/ActivityController.php" method="post" onsubmit="return confirm('Are you sure you want to delete this activity?');" style="display:inline-block;">
                                <input type="hidden" name="delete_activity_id" value="<?php echo $activity['id']; ?>" />
                                <button type="submit" name="delete_activity" class="btn-delete">Delete</button>
                            </form>
                            <a href="dashboard.php?edit_id=<?php echo $activity['id']; ?>" class="btn-edit">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No activities added yet. Start by adding one above!</p>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>