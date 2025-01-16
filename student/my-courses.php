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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="../pages/dashboard.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to Dashboard</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../pages/courses.php" class="text-blue-500 hover:text-blue-700">Browse More Courses</a>
                    <span class="text-gray-400">|</span>
                    <div class="relative">
                        <button id="categoryDropdown" class="text-blue-500 hover:text-blue-700">
                            Filter by Category
                        </button>
                        <div id="categoryMenu" class="hidden absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-20">
                            <?php
                            $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categories as $category) {
                                echo "<a href='../pages/courses.php?category={$category['id']}' class='block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100'>" . htmlspecialchars($category['name']) . "</a>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <!-- Page Header -->
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">My Enrolled Courses</h1>
            <p class="text-gray-600">Continue your learning journey with these amazing courses.</p>
        </div>