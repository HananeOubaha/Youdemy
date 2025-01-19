<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = Database::getInstance();
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

             <!-- Courses by Category -->
             <div class="bg-gradient-to-r from-orange-500 to-red-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Courses by Category</h2>
                        <p class="text-3xl font-bold"><?php echo count($stats['courses_by_category']); ?></p>
                    </div>
                    <i class="fas fa-tags text-4xl opacity-50"></i>
                </div>
            </div>
            <!-- Top Teachers -->
            <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-6 rounded-lg shadow-lg text-white transform transition-all duration-300 hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold mb-2">Top Teachers</h2>
                        <p class="text-3xl font-bold"><?php echo count($stats['top_teachers']); ?></p>
                    </div>
                    <i class="fas fa-chalkboard-teacher text-4xl opacity-50"></i>
                </div>
            </div>
        </div>
        <!-- Courses by Category Details -->
        <div class="bg-white p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Courses by Category</h2>
            <div class="space-y-4">
                <?php foreach ($stats['courses_by_category'] as $category): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                        <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($category['name']); ?></span>
                        <span class="text-gray-600"><?php echo $category['count']; ?> courses</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Teachers Details -->
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Top Teachers</h2>
            <div class="space-y-4">
                <?php foreach ($stats['top_teachers'] as $index => $teacher): ?>
                    <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                        <span class="text-lg font-bold mr-4">#<?php echo $index + 1; ?></span>
                        <div>
                            <p class="font-medium"><?php echo htmlspecialchars($teacher['username']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo $teacher['course_count']; ?> courses</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>