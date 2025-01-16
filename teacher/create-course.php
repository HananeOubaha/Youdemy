<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

// Vérifier si l'utilisateur est connecté et a le rôle d'enseignant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$teacher = new Teacher($db);

$error = '';
$success = '';

// Récupérer les catégories et les tags pour le formulaire
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $content_type = $_POST['content_type'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $selected_tags = $_POST['tags'] ?? [];

    // Validation des champs obligatoires
    if (empty($title) || empty($description) || empty($content_type) || empty($category_id)) {
        $error = 'All fields are required';
    } else {
        // Gestion du téléchargement de vidéo si le type de contenu est "video"
        if ($content_type === 'video') {
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/videos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $video_name = basename($_FILES['video_file']['name']);
                $video_path = $upload_dir . $video_name;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_path)) {
                    $content = $video_path; // Stocker le chemin de la vidéo comme contenu
                } else {
                    $error = 'Failed to upload video';
                }
            } else {
                $error = 'Video file is required';
            }
        }

        // Si aucune erreur, créer le cours
        if (empty($error)) {
            if ($teacher->createCourse($title, $description, $content, $content_type, $_SESSION['user_id'], $category_id, $selected_tags)) {
                $success = 'Course created successfully!';
            } else {
                $error = 'Failed to create course';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Barre de navigation -->
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
<!-- Contenu principal -->
<div class="max-w-4xl mx-auto px-4">
        <!-- En-tête de la page -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Create New Course</h1>
            <p class="text-gray-600">Fill out the form below to create a new course and share your knowledge with the world.</p>
        </div>
        <!-- Formulaire de création de cours -->
        <form method="POST" action="" enctype="multipart/form-data" class="relative bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-2xl p-8 transform transition-all duration-300 hover:shadow-xl">
            <!-- Animation de fond -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 opacity-75 rounded-lg animate-pulse"></div>
            <div class="relative z-10">
                <!-- Titre du cours -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="title">
                        <i class="fas fa-book mr-2"></i>Course Title
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="title" type="text" name="title" required placeholder="Enter course title">
                </div>

                <!-- Description du cours -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="description">
                        <i class="fas fa-align-left mr-2"></i>Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                              id="description" name="description" rows="4" required placeholder="Enter course description"></textarea>
                </div>

                <!-- Type de contenu -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="content_type">
                        <i class="fas fa-file-alt mr-2"></i>Content Type
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="content_type" name="content_type" required>
                        <option value="">Select content type</option>
                        <option value="text">Text</option>
                        <option value="video">Video</option>
                    </select>
                </div>

                <!-- Contenu du cours (texte) -->
                <div class="mb-6" id="text_content_field">
                    <label class="block text-white text-sm font-bold mb-2" for="content">
                        <i class="fas fa-file-alt mr-2"></i>Course Content (Text)
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                              id="content" name="content" rows="10" placeholder="Enter course content"></textarea>
                </div>

                <!-- Contenu du cours (vidéo) -->
                <div class="mb-6 hidden" id="video_content_field">
                    <label class="block text-white text-sm font-bold mb-2" for="video_file">
                        <i class="fas fa-video mr-2"></i>Course Content (Video)
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="video_file" type="file" name="video_file" accept="video/*">
                </div>

                <!-- Catégorie du cours -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="category">
                        <i class="fas fa-tag mr-2"></i>Category
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="category" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tags du cours -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2">
                        <i class="fas fa-tags mr-2"></i>Tags
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($tags as $tag): ?>
                            <label class="inline-flex items-center bg-white bg-opacity-20 p-3 rounded-lg hover:bg-opacity-30 transition duration-200">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                       class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-3 text-white"><?php echo htmlspecialchars($tag['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Bouton de soumission -->
                <div class="flex items-center justify-center">
                    <button class="bg-white text-blue-500 font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 transform hover:scale-105"
                            type="submit">
                        <i class="fas fa-plus mr-2"></i>Create Course
                    </button>
                </div>
            </div>
        </form>
    </div>

        <!-- Script pour afficher/masquer les champs en fonction du type de contenu -->
        <script>
        document.getElementById('content_type').addEventListener('change', function() {
            const textContentField = document.getElementById('text_content_field');
            const videoContentField = document.getElementById('video_content_field');

            if (this.value === 'text') {
                textContentField.classList.remove('hidden');
                videoContentField.classList.add('hidden');
            } else if (this.value === 'video') {
                textContentField.classList.add('hidden');
                videoContentField.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>