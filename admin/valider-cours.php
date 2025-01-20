<?php
require_once '../config/config.php';
require_once '../classes/User.php';
require_once '../classes/CourseText.php';
require_once '../classes/CourseVideo.php';

session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$database = Database::getInstance();
$db = $database->connect();

// Instancier les classes de cours
$courseText = new CourseText($db);
$courseVideo = new CourseVideo($db);

// Récupérer tous les cours (pending et active)
$textCourses = $courseText->getAllCoursesByStatus(); // Pas de filtre de statut
$videoCourses = $courseVideo->getAllCoursesByStatus(); // Pas de filtre de statut

// Fusionner les résultats
$courses = array_merge($textCourses, $videoCourses);

// Traitement des actions (valider, suspendre, supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['course_id'])) {
        $courseId = $_POST['course_id'];
        $action = $_POST['action'];

        switch ($action) {
            case 'validate':
                $courseText->validateCourse($courseId);
                $courseVideo->validateCourse($courseId);
                break;
            case 'suspend':
                $courseText->suspendCourse($courseId);
                $courseVideo->suspendCourse($courseId);
                break;
            case 'delete':
                $courseText->deleteCourse($courseId);
                $courseVideo->deleteCourse($courseId);
                break;
        }

        // Rediriger pour éviter la soumission multiple du formulaire
        header('Location: valider-cours.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Courses</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="../pages/dashboard.php" class="flex items-center py-4 px-2">
                        <i class="fas fa-arrow-left text-blue-600 mr-2"></i>
                        <span class="font-semibold text-gray-700 text-lg">Back to Dashboard</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">
            <i class="fas fa-tasks text-blue-600 mr-2"></i>Manage Courses
        </h1>

        <!-- Liste des cours -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">
                <i class="fas fa-book-open text-blue-600 mr-2"></i>All Courses
            </h2>
            <?php if (empty($courses)): ?>
                <p class="text-gray-600">No courses found.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($courses as $course): ?>
                        <div class="border p-6 rounded-lg flex justify-between items-center bg-gradient-to-r from-gray-50 to-white hover:shadow-lg transition-shadow duration-300">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-book text-blue-500 mr-2"></i><?php echo htmlspecialchars($course['title']); ?>
                                </h3>
                                <p class="text-gray-600 mt-1">
                                    <i class="fas fa-user-tie text-blue-500 mr-2"></i>By <?php echo htmlspecialchars($course['teacher_name']); ?>
                                </p>
                                <p class="text-gray-500 mt-2">
                                    <i class="fas fa-align-left text-blue-500 mr-2"></i><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...
                                </p>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-tag text-blue-500 mr-2"></i>Type: <?php echo htmlspecialchars($course['content_type']); ?> | 
                                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>Status: <span class="font-semibold"><?php echo htmlspecialchars($course['status']); ?></span>
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <!-- Formulaire pour valider un cours -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to validate this course?');">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="action" value="validate">
                                    <button type="submit" class="bg-gradient-to-r from-green-400 to-green-600 text-white px-4 py-2 rounded-lg hover:from-green-500 hover:to-green-700 transition-all duration-300 flex items-center shadow-md hover:shadow-lg">
                                        <i class="fas fa-check-circle mr-2"></i> Validate
                                    </button>
                                </form>
                                <!-- Formulaire pour suspendre un cours -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to suspend this course?');">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="action" value="suspend">
                                    <button type="submit" class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-white px-4 py-2 rounded-lg hover:from-yellow-500 hover:to-yellow-700 transition-all duration-300 flex items-center shadow-md hover:shadow-lg">
                                        <i class="fas fa-pause-circle mr-2"></i> Suspend
                                    </button>
                                </form>
                                <!-- Formulaire pour supprimer un cours -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="bg-gradient-to-r from-red-400 to-red-600 text-white px-4 py-2 rounded-lg hover:from-red-500 hover:to-red-700 transition-all duration-300 flex items-center shadow-md hover:shadow-lg">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>