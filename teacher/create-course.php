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