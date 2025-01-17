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

// Handle category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (:name)");
        if ($stmt->execute(['name' => $category_name])) {
            $success = 'Category added successfully!';
        } else {
            $error = 'Failed to add category.';
        }
    } else {
        $error = 'Category name cannot be empty.';
    }
}
// Handle category deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
    if ($stmt->execute(['id' => $delete_id])) {
        $success = 'Category deleted successfully!';
    } else {
        $error = 'Failed to delete category.';
    }
}