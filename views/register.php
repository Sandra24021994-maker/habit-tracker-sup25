<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register - Hobilo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #6a82fb, #fc5c7d);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-container {
            background: white;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            width: 380px;
            text-align: center;
        }
        .brand {
            font-weight: 700;
            font-size: 40px;
            color: #3366ff;
            letter-spacing: 8px;
            margin-bottom: 0.3rem;
            user-select: none;
        }
        .slogan {
            font-weight: 600;
            font-size: 16px;
            color: #a992cc;
            margin-bottom: 1.8rem;
            font-style: normal;
        }
        .alert {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        label {
            font-weight: 600;
        }
        button.btn-primary {
            background-color: #3366ff;
            border: none;
        }
        button.btn-primary:hover {
            background-color: #274bdb;
        }
        a {
            color: #3366ff;
            font-weight: 600;
            text-decoration: underline;
            text-underline-offset: 2px;
            text-decoration-thickness: 1px;
        }
        a:hover {
            text-decoration-color: #274bdb;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand" aria-label="Hobilo">
            HOBILO
        </div>
        <div class="slogan">
            Your space for joyful moments.
        </div>

        <h2 class="mb-4">Register</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <form action="../controllers/UserController.php" method="post">
            <div class="mb-3 text-start">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required autofocus />
            </div>
            <div class="mb-3 text-start">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" id="email" class="form-control" required />
            </div>
            <div class="mb-4 text-start">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required />
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
        </form>

        <p class="mt-3 mb-0">
            Already have an account? <a href="login.php">Login here.</a>
        </p>
    </div>
</body>
</html>