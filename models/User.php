<?php
class User {
    private $conn;

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /**
     * Check if a user with the given username or email already exists.
     * @param string $username
     * @param string $email
     * @return bool True if user exists, false otherwise.
     */
    public function existsByUsernameOrEmail(string $username, string $email): bool {
        $query = "SELECT COUNT(*) as count FROM sk_users WHERE username = :username OR email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':username' => $username,
            ':email' => $email
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && $row['count'] > 0);
    }

    /**
     * Register a new user.
     * Expects the password to be already hashed.
     * @param string $username
     * @param string $email
     * @param string $hashedPassword
     * @return bool True on success, false on failure.
     */
    public function register(string $username, string $email, string $hashedPassword): bool {
        $query = "INSERT INTO sk_users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($query);

        try {
            return $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);
        } catch (PDOException $e) {
            error_log('User::register error - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Attempt to log in a user by username or email and password.
     * @param string $usernameOrEmail
     * @param string $password Plain text password to verify.
     * @return array|false User data array on success, false on failure.
     */
    public function login(string $usernameOrEmail, string $password) {
        $query = "SELECT * FROM sk_users WHERE username = :input OR email = :input LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':input' => $usernameOrEmail]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Remove password hash before returning user data
            unset($user['password']);
            return $user;
        }

        return false;
    }
}







