<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/Course.php';

session_start();

$database = new Database();
$db = $database->connect();
$course = new Course($db);

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

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
    <title>Youdemy - Online Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Enhanced Navigation -->
    <nav class="bg-white shadow-lg fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-3">
                        <i class="fas fa-graduation-cap text-blue-600 text-2xl"></i>
                        <span class="font-bold text-xl text-gray-800">Youdemy</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="pages/login.php" class="text-gray-600 hover:text-blue-600">Login</a>
                        <a href="pages/register.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">Start Learning</a>
                    <?php else: ?>
                        <a href="pages/dashboard.php" class="text-gray-600 hover:text-blue-600">Dashboard</a>
                        <a href="pages/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition duration-300">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <!-- Hero Section -->
    <div class="pt-20 bg-gradient-to-r from-indigo-800 to-purple-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-16">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">Learn Skills for Your Future Career</h1>
                    <p class="text-xl mb-8">Access over 1000+ courses from professional instructors in design, development, and more.</p>
                    <div class="space-x-4">
                        <a href="#courses" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Explore Courses</a>
                        <a href="#" class="border border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">Learn More</a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1951&q=80" alt="Online Learning" class="rounded-lg shadow-xl">
                </div>
            </div>
        </div>
    </div>
     <!-- Features Section -->
     <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Why Choose Youdemy?</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6">
                    <i class="fas fa-laptop-code text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-3">Expert Instructors</h3>
                    <p class="text-gray-600">Learn from industry professionals with years of experience.</p>
                </div>
                <div class="text-center p-6">
                    <i class="fas fa-clock text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-3">Learn at Your Pace</h3>
                    <p class="text-gray-600">Access course content 24/7 and learn on your schedule.</p>
                </div>
                <div class="text-center p-6">
                    <i class="fas fa-certificate text-4xl text-blue-600 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-3">Certificates</h3>
                    <p class="text-gray-600">Earn certificates upon completion to showcase your skills.</p>
                </div>
            </div>
        </div>
    </div>
<!-- Courses Section -->
<div id="courses" class="max-w-7xl mx-auto px-4 py-16">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Explore Our Courses</h2>
            <p class="text-gray-600">Discover the perfect course to advance your skills</p>
        </div>

        <!-- Search Form -->
        <form action="index.php" method="GET" class="mb-8">
            <div class="flex gap-4">
                <input type="text" name="search" placeholder="Search for courses..." value="<?php echo htmlspecialchars($search); ?>"
                       class="flex-grow shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-300">
                    Search
                </button>
            </div>
        </form>

        <!-- Category Buttons -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Categories</h3>
            <div class="flex flex-wrap gap-2">
                <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-300">
                    All Courses
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?category=<?php echo urlencode($cat['id']); ?>" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded transition duration-300 <?php echo $category == $cat['id'] ? 'bg-blue-600 text-white' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($courses)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-8" role="alert">
                <p>No courses found. Try a different search term or category.</p>
            </div>
        <?php endif; ?>
