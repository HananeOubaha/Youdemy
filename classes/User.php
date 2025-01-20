<?php
abstract class User {
    // Changer la visibilité de $db de private à protected
    protected $db;
    protected $id;
    protected $username;
    protected $email;
    protected $passwordHash;
    protected $role;
    protected $isActive;
    protected $isVerified;
    protected $createdAt;

    public function __construct($db, $id = null, $username = null, $email = null, $passwordHash = null, $role = null, $isActive = null, $isVerified = null, $createdAt = null) {
        $this->db = $db;
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->role = $role;
        $this->isActive = $isActive;
        $this->isVerified = $isVerified;
        $this->createdAt = $createdAt;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getIsActive() { return $this->isActive; }
    public function getIsVerified() { return $this->isVerified; }
    public function getCreatedAt() { return $this->createdAt; }

    // Setters
    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    public function setRole($role) { $this->role = $role; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    public function setIsVerified($isVerified) { $this->isVerified = $isVerified; }

    // Password hashing method
    protected function setPasswordHash($password) {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    // Abstract method for login (must be implemented by child classes)
    abstract public function login($email, $password);

    // Abstract method for register (must be implemented by child classes)
    abstract public function register($username, $email, $password, $role);

    // Logout method (concrete implementation)
    public function logout() {
        session_start();
        session_destroy();
    }

    // Save user to the database (concrete implementation)
    public function save() {
        try {
            if ($this->id) {
                // Update user
                $query = "UPDATE users SET username = :username, email = :email, role = :role, is_active = :is_active, is_verified = :is_verified WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            } else {
                // Insert new user
                $query = "INSERT INTO users (username, email, password, role, is_active, is_verified) VALUES (:username, :email, :password, :role, :is_active, :is_verified)";
                $stmt = $this->db->prepare($query);
            }
            $stmt->bindParam(':username', $this->username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $this->passwordHash, PDO::PARAM_STR);
            $stmt->bindParam(':role', $this->role, PDO::PARAM_STR);
            $stmt->bindParam(':is_active', $this->isActive, PDO::PARAM_BOOL);
            $stmt->bindParam(':is_verified', $this->isVerified, PDO::PARAM_BOOL);
            $stmt->execute();

            if (!$this->id) {
                $this->id = $this->db->lastInsertId();
            }
            return $this->id;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("An error occurred while saving the user.");
        }
    }
}
?>