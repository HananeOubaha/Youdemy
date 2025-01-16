<?php
require_once '../config/config.php';
require_once '../classes/Student.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$student = new Student($db);

$courses = $student->getEnrolledCourses($_SESSION['user_id']);
?>