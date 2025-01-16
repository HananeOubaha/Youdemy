<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$teacher = new Teacher($db);

$courses = $teacher->getMyCourses($_SESSION['user_id']);
?>