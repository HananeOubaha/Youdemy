<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = Database::getInstance();
$db = $database->connect();
$admin = new Admin($db);

$error = '';
$success = '';

// Handle tag addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tags'])) {
        $tags = array_map('trim', explode(',', $_POST['tags']));
        $tags = array_filter($tags); // Remove empty values
        
        try {
            $db->beginTransaction();
            
            foreach ($tags as $tag) {
                $stmt = $db->prepare("INSERT IGNORE INTO tags (name) VALUES (:name)");
                $stmt->execute(['name' => $tag]);
            }
            
            $db->commit();
            $success = 'Tags added successfully!';
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Error adding tags';
        }
    }
}
// Handle tag deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM tags WHERE id = :id");
    if ($stmt->execute(['id' => $delete_id])) {
        $success = 'Tag deleted successfully!';
    } else {
        $error = 'Failed to delete tag.';
    }
}
// Get all tags
$stmt = $db->query("SELECT * FROM tags ORDER BY name");
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tags - Youdemy</title>
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Tags</h1>

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

         <!-- Add Multiple Tags Form -->
         <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 rounded-lg shadow-lg text-white mb-8 transform transition-all duration-300 hover:shadow-xl">
            <h2 class="text-xl font-semibold mb-4"><i class="fas fa-tags mr-2"></i>Add Multiple Tags</h2>
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-sm font-bold mb-2" for="tags">
                        Enter tags (comma-separated)
                    </label>
                    <textarea
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                        id="tags"
                        name="tags"
                        rows="3"
                        placeholder="web development, javascript, react"
                        required
                    ></textarea>
                </div>
                <button type="submit" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition duration-300">
                    <i class="fas fa-plus-circle mr-2"></i>Add Tags
                </button>
            </form>
        </div>
   
         <!-- Existing Tags List -->
         <div class="bg-white shadow-md rounded-lg overflow-hidden transform transition-all duration-300 hover:shadow-xl">
            <h2 class="text-xl font-semibold p-6 border-b"><i class="fas fa-list-alt mr-2"></i>Existing Tags</h2>
            <div class="p-6">
                <div class="space-y-2">
                    <?php foreach ($tags as $tag): ?>
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($tag['name']); ?></span>
                            <a href="?delete_id=<?php echo $tag['id']; ?>" 
                               class="text-red-500 hover:text-red-700 transition duration-300">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once '../pages/footer.php';
    ?>
</body>
</html>