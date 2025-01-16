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

    <!-- Course Header -->
    <div class="pt-16 bg-gradient-to-r from-indigo-800 to-purple-800 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="max-w-3xl">
                <h1 class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($course['title']); ?></h1>
                <div class="flex items-center space-x-4 mb-6">
                    <div class="flex items-center">
                        <i class="fas fa-user-tie mr-2"></i>
                        <span>By <?php echo htmlspecialchars($course['teacher_name']); ?></span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-folder mr-2"></i>
                        <span><?php echo htmlspecialchars($course['category_name']); ?></span>
                    </div>
                </div>
                <?php if (!empty($tags)): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <span class="bg-white/20 px-3 py-1 rounded-full text-sm">
                                <?php echo htmlspecialchars($tag['name']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Course Content -->
            <div class="lg:col-span-2">
                <?php if($error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Error</p>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>

                <?php if($success): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Success</p>
                        <p><?php echo htmlspecialchars($success); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Course Description -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">About This Course</h2>
                        <div class="prose max-w-none text-gray-600">
                            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Course Content -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Course Content</h2>
                        <div class="bg-gray-50 rounded-lg p-6">
                            <?php if ($course['content_type'] === 'video'): ?>
                                <!-- Afficher la vidéo -->
                                <video controls width="100%" class="rounded-lg">
                                    <source src="<?php echo htmlspecialchars($course['content']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <!-- Afficher le contenu texte -->
                                <?php echo nl2br(htmlspecialchars($course['content'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-24">
                    <div class="p-6">
                        <div class="mb-6">
                            <img src="https://images.unsplash.com/photo-1501504905252-473c47e087f8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Course Preview" class="w-full rounded-lg">
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['role'] === 'student'): ?>
                                <form method="POST" action="" class="mb-4">
                                    <button type="submit" name="enroll" 
                                            class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center justify-center">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        Enroll in Course
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <p class="text-gray-600 mb-4">Please login as a student to enroll in this course.</p>
                                <a href="login.php" 
                                   class="block w-full bg-blue-600 text-white text-center px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-300">
                                    Login to Enroll
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Course Features -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold mb-4">Course Features</h3>
                            <ul class="space-y-3 text-gray-600">
                                <li class="flex items-center">
                                    <i class="fas fa-clock mr-3 text-blue-600"></i>
                                    <span>Self-paced learning</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-certificate mr-3 text-blue-600"></i>
                                    <span>Certificate of completion</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-infinity mr-3 text-blue-600"></i>
                                    <span>Full lifetime access</span>
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-mobile-alt mr-3 text-blue-600"></i>
                                    <span>Access on mobile and TV</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>