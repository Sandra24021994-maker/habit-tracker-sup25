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

// Fetch all activities
$stmt = $pdo->prepare("SELECT * FROM sk_activities WHERE user_id = :user_id ORDER BY created_at DESC");
$stmt->execute(['user_id' => $user_id]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$edit_id = $_GET['edit_id'] ?? null;
$activity_to_edit = null;
if ($edit_id) {
    foreach ($activities as $a) {
        if ($a['id'] == $edit_id) {
            $activity_to_edit = $a;
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
        padding: 20px 0;
    }
    .container {
        max-width: 900px;
        margin: auto;
        background: white;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
    }
    h1, h2 {
        color: #3366ff;
        font-weight: 700;
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
    .btn-delete {
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
    }
    .btn-delete:hover {
        background: #c0392b;
    }
    .btn-edit {
        background: #3498db;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        text-decoration: none;
    }
    .btn-edit:hover {
        background: #2980b9;
        color: white;
        text-decoration: none;
    }
    .message {
        padding: 10px;
        margin-top: 15px;
        border-radius: 6px;
        display: none;
        font-weight: 600;
    }
    .message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    table th, table td {
        vertical-align: middle !important;
    }
    .memory-img-thumb {
        max-width: 60px;
        max-height: 40px;
        cursor: pointer;
        border-radius: 6px;
        object-fit: cover;
    }
    /* Modal styles */
    #imageModal .modal-dialog {
        max-width: 600px;
    }
    #imageModal img {
        width: 100%;
        border-radius: 8px;
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4" style="border-radius: 12px; max-width: 900px; margin: 0 auto 30px auto; padding: 0 20px;">
  <div class="container-fluid p-0">
    <a class="navbar-brand fw-bold" href="#">HOBILO</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  
    <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle fw-semibold" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo htmlspecialchars($_SESSION['username']); ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../controllers/UserController.php?logout=1">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container">
  <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>

  <div id="message" class="message"></div>

  <?php
  // Helper function for image preview in form
  function memoryImagePreview($filename) {
    if ($filename) {
      $url = "../" . htmlspecialchars($filename);
      return '<img src="' . $url . '" alt="Memory Image" style="max-width:120px; max-height:80px; border-radius:6px; margin-top:5px;" />';
    }
    return '';
  }
  ?>

  <?php if ($activity_to_edit): ?>
  <h2>Edit Activity</h2>
  <form id="activityForm" class="mb-4" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $activity_to_edit['id']; ?>" />
    <div class="mb-3">
      <label>Activity Name</label>
      <input type="text" name="name" required maxlength="100" class="form-control" value="<?php echo htmlspecialchars($activity_to_edit['name']); ?>" />
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" maxlength="255" class="form-control"><?php echo htmlspecialchars($activity_to_edit['description']); ?></textarea>
    </div>
    <div class="mb-3">
      <label>Category</label>
      <input type="text" name="category" maxlength="50" class="form-control" value="<?php echo htmlspecialchars($activity_to_edit['category']); ?>" />
    </div>
    <div class="mb-3">
      <label>Frequency</label>
      <select name="frequency" required class="form-select">
        <option value="">Select frequency</option>
        <option value="Daily" <?php if($activity_to_edit['frequency']=='Daily') echo 'selected'; ?>>Daily</option>
        <option value="Weekly" <?php if($activity_to_edit['frequency']=='Weekly') echo 'selected'; ?>>Weekly</option>
        <option value="Monthly" <?php if($activity_to_edit['frequency']=='Monthly') echo 'selected'; ?>>Monthly</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Memory Image (optional)</label>
      <input type="file" name="memory_image" accept="image/*" class="form-control" />
      <?php echo memoryImagePreview($activity_to_edit['memory_image']); ?>
    </div>
    <button type="submit" class="btn btn-primary me-2">Update Activity</button>
    <button type="button" id="cancelEdit" class="btn btn-secondary">Cancel</button>
  </form>
  <?php else: ?>
  <h2>Add Activity</h2>
  <form id="activityForm" class="mb-4" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Activity Name</label>
      <input type="text" name="name" required maxlength="100" class="form-control" />
    </div>
    <div class="mb-3">
      <label>Description</label>
      <textarea name="description" maxlength="255" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label>Category</label>
      <input type="text" name="category" maxlength="50" class="form-control" />
    </div>
    <div class="mb-3">
      <label>Frequency</label>
      <select name="frequency" required class="form-select">
        <option value="">Select frequency</option>
        <option value="Daily">Daily</option>
        <option value="Weekly">Weekly</option>
        <option value="Monthly">Monthly</option>
      </select>
    </div>
    <div class="mb-3">
      <label>Memory Image (optional)</label>
      <input type="file" name="memory_image" accept="image/*" class="form-control" />
    </div>
    <button type="submit" class="btn btn-primary">Add Activity</button>
  </form>
  <?php endif; ?>

  <h2>Your Activities</h2>
  <table id="activitiesTable" class="table table-striped table-bordered align-middle">
    <thead>
      <tr>
        <th>Name</th><th>Description</th><th>Category</th><th>Frequency</th><th>Created At</th><th>Memory Image</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($activities as $a): ?>
      <tr data-id="<?php echo $a['id']; ?>">
        <td><?php echo htmlspecialchars($a['name']); ?></td>
        <td><?php echo htmlspecialchars($a['description']); ?></td>
        <td><?php echo htmlspecialchars($a['category']); ?></td>
        <td><?php echo htmlspecialchars($a['frequency']); ?></td>
        <td><?php echo date('Y-m-d', strtotime($a['created_at'])); ?></td>
        <td>
          <?php if ($a['memory_image']): ?>
            <img src="../<?php echo htmlspecialchars($a['memory_image']); ?>" alt="Memory Image" class="memory-img-thumb" />
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td>
          <button class="btn-delete btn btn-sm me-2">Delete</button>
          <a href="dashboard.php?edit_id=<?php echo $a['id']; ?>" class="btn-edit btn btn-sm">Edit</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Modal for image preview -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">Memory Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" alt="Memory Image" id="modalImage" />
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
const form = document.getElementById('activityForm');
const messageDiv = document.getElementById('message');
const cancelEditBtn = document.getElementById('cancelEdit');
const activitiesTable = document.getElementById('activitiesTable').querySelector('tbody');

function showMessage(msg, type = 'success'){
  messageDiv.textContent = msg;
  messageDiv.className = 'message ' + type;
  messageDiv.style.display = 'block';
  setTimeout(() => { messageDiv.style.display = 'none'; }, 4000);
}

if(form){
  form.addEventListener('submit', function(e){
    e.preventDefault();

    // Using FormData to send text fields + file
    const formData = new FormData(form);

    if(formData.get('id')){
      formData.append('update_activity', '1');
      formData.append('activity_id', formData.get('id'));
    } else {
      formData.append('add_activity', '1');
    }

    // Remove id because we already appended activity_id for update
    formData.delete('id');

    fetch('../controllers/ActivityController.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if(data.success){
        showMessage(data.message);

        if(formData.get('activity_id')){
          // On update, reload page after short delay
          setTimeout(() => window.location.href = 'dashboard.php', 1000);
        } else {
          // On add, update table with new activity
          const a = data.activity;
          const tr = document.createElement('tr');
          tr.setAttribute('data-id', a.id);
          tr.innerHTML = `
            <td>${a.name}</td>
            <td>${a.description}</td>
            <td>${a.category}</td>
            <td>${a.frequency}</td>
            <td>${a.created_at}</td>
            <td>${a.memory_image ? `<img src="../${a.memory_image}" alt="Memory Image" class="memory-img-thumb" />` : '-'}</td>
            <td>
              <button class="btn-delete btn btn-sm me-2">Delete</button>
              <a href="dashboard.php?edit_id=${a.id}" class="btn-edit btn btn-sm">Edit</a>
            </td>
          `;
          activitiesTable.prepend(tr);
          form.reset();
        }
      } else {
        showMessage(data.message || 'Operation failed', 'error');
      }
    })
    .catch(() => showMessage('Network error', 'error'));
  });
}

if(cancelEditBtn){
  cancelEditBtn.addEventListener('click', () => {
    window.location.href = 'dashboard.php';
  });
}

// Delete button handler
activitiesTable.addEventListener('click', e => {
  if(e.target.classList.contains('btn-delete')){
    const row = e.target.closest('tr');
    const id = row.getAttribute('data-id');
    if(confirm('Are you sure you want to delete this activity?')){
      const postData = new URLSearchParams();
      postData.append('delete_activity', '1');
      postData.append('delete_activity_id', id);

      fetch('../controllers/ActivityController.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: postData.toString()
      })
      .then(res => res.json())
      .then(data => {
        if(data.success){
          showMessage(data.message);
          row.remove();
        } else {
          showMessage(data.message || 'Failed to delete', 'error');
        }
      })
      .catch(() => showMessage('Network error', 'error'));
    }
  }
});

// Image thumbnail click to show modal
document.querySelector('#activitiesTable tbody').addEventListener('click', e => {
  if(e.target.classList.contains('memory-img-thumb')){
    const src = e.target.getAttribute('src');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = src;
    const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
  }
});
</script>

</body>
</html>