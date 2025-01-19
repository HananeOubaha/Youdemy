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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-indigo-800 to-purple-800 shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <a href="courses.php" class="flex items-center py-4 px-2">
                        <span class="font-semibold text-gray-500 text-lg">← Back to My Courses</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Edit Course</h1>
            <p class="text-gray-600">Update your course details and share your knowledge with the world.</p>
        </div>

        <!-- Success and Error Messages -->
        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 animate-fade-in">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Edit Course Form -->
        <form method="POST" action="" enctype="multipart/form-data" class="relative bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-2xl p-8 transform transition-all duration-300 hover:shadow-xl">
            <!-- Background Animation -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 opacity-75 rounded-lg animate-pulse"></div>
            <div class="relative z-10">
                <!-- Course Title -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="title">
                        <i class="fas fa-book mr-2"></i>Course Title
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="title" type="text" name="title" value="<?php echo htmlspecialchars($course['title']); ?>" required>
                </div>

                <!-- Course Description -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="description">
                        <i class="fas fa-align-left mr-2"></i>Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                              id="description" name="description" rows="4" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>

                <!-- Course Content Type -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="content_type">
                        <i class="fas fa-file-alt mr-2"></i>Content Type
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="content_type" name="content_type" required>
                        <option value="">Select content type</option>
                        <option value="text" <?php echo $course['content_type'] === 'text' ? 'selected' : ''; ?>>Text</option>
                        <option value="video" <?php echo $course['content_type'] === 'video' ? 'selected' : ''; ?>>Video</option>
                    </select>
                </div>

                <!-- Course Content (Text) -->
                <div class="mb-6 <?php echo $course['content_type'] === 'text' ? '' : 'hidden'; ?>" id="text_content_field">
                    <label class="block text-white text-sm font-bold mb-2" for="content">
                        <i class="fas fa-file-alt mr-2"></i>Course Content (Text)
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                              id="content" name="content" rows="10"><?php echo $course['content_type'] === 'text' ? htmlspecialchars($course['content']) : ''; ?></textarea>
                </div>

                <!-- Course Content (Video) -->
                <div class="mb-6 <?php echo $course['content_type'] === 'video' ? '' : 'hidden'; ?>" id="video_content_field">
                    <label class="block text-white text-sm font-bold mb-2" for="video_file">
                        <i class="fas fa-video mr-2"></i>Course Content (Video)
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="video_file" type="file" name="video_file" accept="video/*">
                    <?php if ($course['content_type'] === 'video'): ?>
                        <p class="text-white mt-2">Current Video: <a href="<?php echo htmlspecialchars($course['content']); ?>" target="_blank" class="underline">View Video</a></p>
                    <?php endif; ?>
                </div>

                <!-- Course Category -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2" for="category">
                        <i class="fas fa-tag mr-2"></i>Category
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                            id="category" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $course['category_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Course Tags -->
                <div class="mb-6">
                    <label class="block text-white text-sm font-bold mb-2">
                        <i class="fas fa-tags mr-2"></i>Tags
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($tags as $tag): ?>
                            <label class="inline-flex items-center bg-white bg-opacity-20 p-3 rounded-lg hover:bg-opacity-30 transition duration-200">
                                <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>"
                                       <?php echo in_array($tag['id'], $current_tags) ? 'checked' : ''; ?>
                                       class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-3 text-white"><?php echo htmlspecialchars($tag['name']); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-center">
                    <button class="bg-white text-blue-500 font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 transform hover:scale-105"
                            type="submit">
                        <i class="fas fa-save mr-2"></i>Update Course
                    </button>
                </div>
            </div>
        </form>
    </div>

     <!-- JavaScript to toggle content fields -->
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

    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>