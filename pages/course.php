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
// Get course tags
$query = "SELECT t.name FROM tags t 
         JOIN course_tags ct ON t.id = ct.tag_id 
         WHERE ct.course_id = :course_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":course_id", $course_id);
$stmt->execute();
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle enrollment
$error = '';
$success = '';
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'student' && isset($_POST['enroll'])) {
    $student = new Student($db);
    if ($student->enrollCourse($_SESSION['user_id'], $course_id)) {
        $success = 'Successfully enrolled in the course!';
    } else {
        $error = 'You are already enrolled in this course.';
    }
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="../index.php" class="flex items-center space-x-3">
                        <i class="fas fa-chevron-left text-blue-600"></i>
                        <span class="font-semibold text-gray-600">Back to Courses</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="text-gray-600 hover:text-blue-600">Login</a>
                        <a href="register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Start Learning</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>