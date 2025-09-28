<?php
session_start();

// Include database and user model
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Create User model instance
$user = new User($db);

if (isset($_POST['register'])) {
    // Registration logic
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic input validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['message'] = "All fields are required!";
        header('Location: ../views/register.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Invalid email format!";
        header('Location: ../views/register.php');
        exit();
    }

    // Check if user already exists
    if ($user->existsByUsernameOrEmail($username, $email)) {
        $_SESSION['message'] = "Username or email already taken!";
        header('Location: ../views/register.php');
        exit();
    }

    // Hash the password before saving
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Attempt to register user
    $result = $user->register($username, $email, $passwordHash);

    if ($result) {
        $_SESSION['message'] = "Registration successful! Please login.";
        header('Location: ../views/login.php');
        exit();
    } else {
        $_SESSION['message'] = "Registration failed! Please try again.";
        header('Location: ../views/register.php');
        exit();
    }

} elseif (isset($_POST['login'])) {
    // Login logic
    $usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($usernameOrEmail) || empty($password)) {
        $_SESSION['message'] = "All fields are required!";
        header('Location: ../views/login.php');
        exit();
    }

    $userData = $user->login($usernameOrEmail, $password);

    if ($userData) {
        // Set session variables
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['message'] = "Login successful! Welcome, " . htmlspecialchars($userData['username']);
        header('Location: ../views/dashboard.php');
        exit();
    } else {
        $_SESSION['message'] = "Login failed! Invalid credentials.";
        header('Location: ../views/login.php');
        exit();
    }

} else {
    // Access without POST or unknown action
    header('Location: ../views/login.php');
    exit();
}