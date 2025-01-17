<?php
require_once '../config/config.php';
require_once '../classes/CourseText.php';
require_once '../classes/CourseVideo.php';

session_start();

$database = Database::getInstance();
$db = $database->connect();
$course = new CourseText($db);
$course = new CourseVideo($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['q']) ? $_GET['q'] : '';

// Get all categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get courses based on search, category, and pagination
if ($search) {
    $courses = $course->searchCourses($search);
} elseif ($category) {
    $courses = $course->getCoursesByCategory($category, $page);
} else {
    $courses = $course->getAllCourses($page);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-indigo-800 to-purple-800 shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="flex items-center space-x-3">
                        <i class="fas fa-chevron-left text-blue-600"></i>
                        <span class="font-semibold text-gray-600">Back to Dashboard</span>
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
    <!-- Header Section -->
    <div class="pt-16 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold mb-4">Browse Our Courses</h1>
            <p class="text-xl opacity-90">Discover courses that match your interests and advance your career</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <!-- Search Form -->
            <form action="courses.php" method="GET" class="mb-8">
                <div class="flex gap-4">
                    <div class="flex-grow relative">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        <input type="text" name="q" placeholder="Search courses..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="w-full pl-10 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                        Search
                    </button>
                </div>
            </form>

            <!-- Category Buttons -->
            <div>
                <h2 class="text-xl font-semibold mb-4">Categories</h2>
                <div class="flex flex-wrap gap-2">
                    <a href="courses.php" 
                       class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-full transition duration-300">
                        All Courses
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="courses.php?category=<?php echo urlencode($cat['id']); ?>" 
                           class="<?php echo $category == $cat['id'] 
                                    ? 'bg-blue-600 text-white' 
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200'; ?> 
                                  font-medium py-2 px-4 rounded-full transition duration-300">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- No Results Message -->
        <?php if (empty($courses)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg mb-8" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <p>No courses found. Try a different search term or category.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Course Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($courses as $course): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 hover:shadow-xl">
                    <img src="https://images.unsplash.com/photo-1501504905252-473c47e087f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($course['title']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
                        <div class="flex flex-col space-y-3">
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                                <span>By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                            </div>
                            <div class="flex items-center text-gray-500">
                                <i class="fas fa-folder mr-2 text-blue-600"></i>
                                <span><?php echo htmlspecialchars($course['category_name']); ?></span>
                            </div>
                            <a href="course.php?id=<?php echo $course['id']; ?>" 
                               class="bg-blue-600 text-white text-center px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                                View Course Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if (!$search && !$category): ?>
            <div class="mt-8 flex justify-center space-x-4">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" 
                       class="flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition duration-300">
                        <i class="fas fa-chevron-left mr-2"></i>
                        Previous
                    </a>
                <?php endif; ?>
                <a href="?page=<?php echo $page + 1; ?>" 
                   class="flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition duration-300">
                    Next
                    <i class="fas fa-chevron-right ml-2"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>