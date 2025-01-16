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

$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: courses.php');
    exit;
}

// Get course details
$stmt = $db->prepare("SELECT title FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
$stmt->execute([
    'course_id' => $course_id,
    'teacher_id' => $_SESSION['user_id']
]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get enrolled students
$students = $teacher->getCourseStudents($course_id, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Students - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-indigo-800 to-purple-800 shadow-lg mb-8">
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
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="text-gray-600">View and manage students enrolled in your course.</p>
        </div>

        <!-- No Students Message -->
        <?php if (empty($students)): ?>
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <i class="fas fa-users fa-3x text-gray-400 mb-4"></i>
                <p class="text-gray-600 text-xl mb-4">No students enrolled in this course yet.</p>
            </div>
        <?php else: ?>