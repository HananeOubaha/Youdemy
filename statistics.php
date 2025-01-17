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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Statistics - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-indigo-800 to-purple-800  shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="courses.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to My Courses</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Course Statistics</h1>
            <p class="text-gray-600">Track your teaching progress and course performance.</p>
        </div>
        
        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Total Courses</h2>
                        <p class="text-3xl font-bold"><?php echo $total_courses; ?></p>
                    </div>
                    <i class="fas fa-book-open text-4xl opacity-50"></i>
                </div>
            </div>
             <!-- Total Students -->
             <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Total Students</h2>
                        <p class="text-3xl font-bold"><?php echo $total_students; ?></p>
                    </div>
                    <i class="fas fa-users text-4xl opacity-50"></i>
                </div>
            </div>