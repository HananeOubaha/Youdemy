<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = Database::getInstance();
$db = $database->connect();
$teacher = new Teacher($db);

$courses = $teacher->getMyCourses($_SESSION['user_id']);
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
    <nav class="bg-gradient-to-r from-indigo-800 to-purple-800 shadow-lg mb-8">
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
        <!-- Success and Error Messages -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 animate-fade-in" role="alert">';
            echo '<span class="block sm:inline">' . $_SESSION['success_message'] . '</span>';
            echo '</div>';
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 animate-fade-in" role="alert">';
            echo '<span class="block sm:inline">' . $_SESSION['error_message'] . '</span>';
            echo '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
         <!-- Page Header -->
         <div class="flex justify-between items-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">My Courses</h1>
            <a href="create-course.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300">
                <i class="fas fa-plus mr-2"></i>Create New Course
            </a>
        </div>

        <!-- Course Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($courses as $course): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-lg">
                    <!-- Course Image -->
                    <div class="h-48 bg-cover bg-center" style="background-image: url('<?php echo htmlspecialchars($course['image_url'] ?? 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); ?>');">
                    </div>
                    <!-- Course Details -->
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-500 mr-2"></i>
                                <span class="text-sm text-gray-500"><?php echo $course['student_count']; ?> students</span>
                            </div>
                            <div class="space-x-2">
                                <a href="edit-course.php?id=<?php echo $course['id']; ?>" 
                                   class="text-blue-500 hover:text-blue-700 transition duration-300">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="course-students.php?id=<?php echo $course['id']; ?>" 
                                   class="text-green-500 hover:text-green-700 transition duration-300">
                                    <i class="fas fa-users"></i>
                                </a>
                                <button onclick="confirmDelete(<?php echo $course['id']; ?>)" 
                                        class="text-red-500 hover:text-red-700 transition duration-300">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Courses Message -->
        <?php if (empty($courses)): ?>
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <i class="fas fa-book-open fa-3x text-gray-400 mb-4"></i>
                <p class="text-gray-600 text-xl mb-4">You haven't created any courses yet.</p>
                <a href="create-course.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300">
                    Create Your First Course
                </a>
            </div>
        <?php endif; ?>
    </div>
     <!-- JavaScript for Delete Confirmation -->
     <script>
        function confirmDelete(courseId) {
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                window.location.href = 'delete-course.php?id=' + courseId;
            }
        }
    </script>
    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>
