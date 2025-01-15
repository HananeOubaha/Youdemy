<?php
require_once '../config/config.php';
require_once '../classes/User.php';

session_start();

$database = new Database();
$db = $database->connect();
$user = new User($db);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($user->login($email, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>