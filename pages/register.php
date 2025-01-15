<?php
require_once '../config/config.php';
require_once '../classes/User.php';

session_start();

$database = new Database();
$db = $database->connect();
$user = new User($db);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required';
    } else {
        try {
            if ($user->register($username, $email, $password, $role)) {
                $success = 'Registration successful! Please login.';
            } else {
                $error = 'Registration failed';
            }
        } catch (PDOException $e) {
            $error = 'Email or username already exists';
        }
    }
}
?>
