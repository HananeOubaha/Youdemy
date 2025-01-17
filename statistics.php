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

// Get total courses count
$stmt = $db->prepare("SELECT COUNT(*) as total FROM courses WHERE teacher_id = :teacher_id");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$total_courses = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get total students count across all courses
$stmt = $db->prepare("
    SELECT COUNT(DISTINCT e.student_id) as total 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = :teacher_id
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
// Get most popular course
$stmt = $db->prepare("
    SELECT c.title, COUNT(e.id) as enrollments 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = :teacher_id 
    GROUP BY c.id 
    ORDER BY enrollments DESC 
    LIMIT 1
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$most_popular = $stmt->fetch(PDO::FETCH_ASSOC);
// Get courses by category
$stmt = $db->prepare("
    SELECT cat.name, COUNT(c.id) as count 
    FROM categories cat 
    LEFT JOIN courses c ON cat.id = c.category_id 
    WHERE c.teacher_id = :teacher_id 
    GROUP BY cat.id
");
$stmt->execute(['teacher_id' => $_SESSION['user_id']]);
$courses_by_category = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>