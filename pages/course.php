<?php
require_once '../config/config.php';
require_once '../classes/Course.php';
require_once '../classes/Student.php';

session_start();

$database = new Database();
$db = $database->connect();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$course_id = $_GET['id'];

// Get course details
$query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
         FROM courses c 
         LEFT JOIN users u ON c.teacher_id = u.id 
         LEFT JOIN categories cat ON c.category_id = cat.id 
         WHERE c.id = :course_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":course_id", $course_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: index.php');
    exit;
}