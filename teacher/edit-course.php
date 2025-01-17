<?php
require_once '../config/config.php';
require_once '../classes/Teacher.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->connect();
$teacher = new Teacher($db);

$error = '';
$success = '';

// Get course ID from URL
$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: courses.php');
    exit;
}
// Get course ID from URL
$course_id = $_GET['id'] ?? null;
if (!$course_id) {
    header('Location: courses.php');
    exit;
}

// Get categories and tags for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current course data
$stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id AND teacher_id = :teacher_id");
$stmt->execute([
    'course_id' => $course_id,
    'teacher_id' => $_SESSION['user_id']
]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: courses.php');
    exit;
}

// Get current course tags
$stmt = $db->prepare("SELECT tag_id FROM course_tags WHERE course_id = :course_id");
$stmt->execute(['course_id' => $course_id]);
$current_tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $content = $_POST['content'] ?? '';
    $content_type = $_POST['content_type'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $selected_tags = $_POST['tags'] ?? [];

    if (empty($title) || empty($description) || empty($content_type) || empty($category_id)) {
        $error = 'All fields are required';
    } else {
        // Handle video upload
        if ($content_type === 'video') {
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
                // Validate file type and size
                $allowed_types = ['video/mp4', 'video/webm', 'video/ogg'];
                $max_size = 100 * 1024 * 1024; // 100 MB

                $file_type = $_FILES['video_file']['type'];
                $file_size = $_FILES['video_file']['size'];

                if (!in_array($file_type, $allowed_types)) {
                    $error = 'Invalid file type. Only MP4, WebM, and Ogg are allowed.';
                } elseif ($file_size > $max_size) {
                    $error = 'File size exceeds the maximum limit of 100 MB.';
                } else {
                    // Upload the video
                    $upload_dir = '../uploads/videos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $video_name = uniqid() . '_' . basename($_FILES['video_file']['name']);
                    $video_path = $upload_dir . $video_name;
                    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_path)) {
                        $content = $video_path; // Store the video path as content
                    } else {
                        $error = 'Failed to upload video';
                    }
                }
            } elseif ($course['content_type'] === 'text') {
                // If switching from text to video, a new video file is required
                $error = 'Video file is required for video content type';
            } else {
                // Keep the existing video if no new file is uploaded
                $content = $course['content'];
            }
        } else {
            // If content type is text, clear any existing video path
            $content = $_POST['content'];
        }

        if (empty($error)) {
            if ($teacher->updateCourse($course_id, $title, $description, $content, $content_type, $category_id, $selected_tags)) {
                $success = 'Course updated successfully!';
                // Refresh course data
                $stmt = $db->prepare("SELECT * FROM courses WHERE id = :course_id");
                $stmt->execute(['course_id' => $course_id]);
                $course = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update course';
            }
        }
    }
}
?>