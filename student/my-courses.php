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
        <!-- No Courses Message -->
        <?php if (empty($courses)): ?>
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <i class="fas fa-book-open fa-3x text-gray-400 mb-4"></i>
                <p class="text-gray-600 text-xl mb-4">You haven't enrolled in any courses yet.</p>
                <a href="../pages/courses.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition duration-300">
                    Browse Available Courses
                </a>
            </div>
        <?php else: ?>
            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-lg">
                        <!-- Course Image -->
                        <div class="h-48 bg-cover bg-center relative" style="background-image: url('<?php echo htmlspecialchars($course['image_url'] ?? 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); ?>');">
                            <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                                <span class="text-white text-lg font-semibold"><?php echo htmlspecialchars($course['category_name']); ?></span>
                            </div>
                        </div>
                        <!-- Course Details -->
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2 text-gray-800"><?php echo htmlspecialchars($course['title']); ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 150)) . '...'; ?></p>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-gray-500">By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                                    <span class="text-sm text-gray-500 block">Progress: <span class="font-semibold"><?php echo rand(0, 100); ?>%</span></span>
                                </div>
                                <a href="../pages/course.php?id=<?php echo $course['id']; ?>" 
                                   class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                                    Continue Learning
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- JavaScript for Dropdown -->
    <script>
        const categoryDropdown = document.getElementById('categoryDropdown');
        const categoryMenu = document.getElementById('categoryMenu');

        categoryDropdown.addEventListener('click', () => {
            categoryMenu.classList.toggle('hidden');
        });

        window.addEventListener('click', (e) => {
            if (!categoryDropdown.contains(e.target)) {
                categoryMenu.classList.add('hidden');
            }
        });
    </script>
    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>