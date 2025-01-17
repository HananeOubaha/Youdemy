<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$users = $admin->getAllUsers();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $admin->toggleUserStatus($_POST['user_id']);
    } elseif (isset($_POST['verify_teacher'])) {
        $admin->verifyTeacher($_POST['user_id']);
    }
    header('Location: users.php');
    exit;
}
?>