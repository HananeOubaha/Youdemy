<?php
require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$course = new Course($db);

$role = $_SESSION['role'];
?>