<?php
require_once '../config/config.php';
require_once '../classes/Admin.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
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
