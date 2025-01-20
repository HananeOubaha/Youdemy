<?php
require_once '../config/config.php';
require_once '../classes/User.php'; // Importer la classe User
require_once '../classes/Student.php'; // Importer la classe Student
require_once '../classes/Teacher.php'; // Importer la classe Teacher
require_once '../classes/Admin.php'; // Importer la classe Admin

session_start();

// Utilisation du Singleton pour obtenir l'instance de la base de données
$database = Database::getInstance();
$db = $database->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Récupérer les informations de l'utilisateur depuis la base de données
    $query = "SELECT id, role, password FROM users WHERE email = :email AND is_active = 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier le mot de passe
        if (password_verify($password, $row['password'])) {
            // Déterminer la classe à utiliser en fonction du rôle
            $role = $row['role'];
            switch ($role) {
                case 'student':
                    $user = new Student($db);
                    break;
                case 'teacher':
                    $user = new Teacher($db);
                    break;
                case 'admin':
                    $user = new Admin($db);
                    break;
                default:
                    $error = 'Invalid role';
                    break;
            }

            if (isset($user)) {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $role;

                // Rediriger vers le tableau de bord unique
                header('Location: dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Invalid email or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Youdemy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Background Image and Overlay -->
    <div class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');">
        <div class="absolute inset-0 bg-black bg-opacity-50"></div>
        
        <!-- Login Card -->
        <div class="bg-white p-8 rounded-lg shadow-2xl w-96 relative z-10 transform transition-all duration-500 hover:scale-105">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Welcome Back!</h1>
            
            <!-- Error Message -->
            <?php if($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 animate-fade-in">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        <i class="fas fa-envelope mr-2"></i>Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="email" type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="password" type="password" name="password" required placeholder="Enter your password">
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 w-full transition duration-300 transform hover:scale-105"
                            type="submit">
                        <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                    </button>
                </div>
            </form>

            <!-- Register Link -->
            <p class="text-center mt-4 text-gray-600">
                Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700 font-semibold">Register</a>
            </p>
        </div>
    </div>
</body>
</html>