<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$admin = new Admin($db);

$stats = $admin->getStatistics();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Youdemy</title>
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Platform Statistics</h1>
         <!-- Statistics Grid -->
         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Courses -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Total Courses</h2>
                        <p class="text-3xl font-bold"><?php echo $stats['total_courses']; ?></p>
                    </div>
                    <i class="fas fa-book-open text-4xl opacity-50"></i>
                </div>
            </div>
             <!-- Most Popular Course -->
             <div class="bg-gradient-to-r from-green-500 to-teal-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Most Popular Course</h2>
                        <?php if ($stats['most_popular_course']): ?>
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($stats['most_popular_course']['title']); ?></p>
                            <p class="text-gray-200"><?php echo $stats['most_popular_course']['enrollments']; ?> enrollments</p>
                        <?php else: ?>
                            <p class="text-gray-200">No enrollments yet</p>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-star text-4xl opacity-50"></i>
                </div>
            </div>
