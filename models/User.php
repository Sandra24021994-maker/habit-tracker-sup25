<?php
class User {
    private $conn;
    private $table = "sk_users";

    public $id;
    public $username;
    public $email;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register a new user
    public function register() {
        $query = "INSERT INTO " . $this->table . " (username, email, password) VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // Hash the password before storing
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bind_param("sss", $this->username, $this->email, $hashed_password);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Verify user credentials for login
    public function login() {
        $query = "SELECT id, username, email, password FROM " . $this->table . " WHERE email = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($this->password, $user['password'])) {
                // Set object properties from database
                $this->id = $user['id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                return true;
            }
        }

        return false;
    }
}
?>
