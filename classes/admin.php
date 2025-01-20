<?php
require_once 'User.php';

class Admin extends User {
    public function __construct($db, $id = null, $username = null, $email = null, $passwordHash = null, $role = 'admin', $isActive = true, $isVerified = true, $createdAt = null) {
        // Appel du constructeur de la classe parente (User) pour initialiser les propriétés héritées
        parent::__construct($db, $id, $username, $email, $passwordHash, $role, $isActive, $isVerified, $createdAt);
    }

    // Implémentation de la méthode abstraite login
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email AND is_active = 1 AND role = 'admin'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                return true;
            }
        }
        return false;
    }

    // Implémentation de la méthode abstraite register
    public function register($username, $email, $password, $role = 'admin') {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setRole($role);
        $this->setPasswordHash($password);
        $this->setIsActive(true);
        $this->setIsVerified(true); // Les admins sont vérifiés par défaut

        return $this->save();
    }

    // Récupérer tous les cours
    public function getAllCourses() {
        $query = "SELECT c.*, cat.name as category_name 
                  FROM courses c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer tous les utilisateurs (sauf les admins)
    public function getAllUsers() {
        $query = "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Activer/désactiver un utilisateur
    public function toggleUserStatus($user_id) {
        $query = "UPDATE users SET is_active = NOT is_active WHERE id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Vérifier un enseignant
    public function verifyTeacher($user_id) {
        $query = "UPDATE users SET is_verified = 1 WHERE id = :user_id AND role = 'teacher'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Supprimer un utilisateur
    public function deleteUser($user_id) {
        try {
            $this->db->beginTransaction();

            // Supprimer les inscriptions de l'utilisateur
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE student_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Supprimer les cours de l'utilisateur (s'il est enseignant)
            $stmt = $this->db->prepare("DELETE FROM courses WHERE teacher_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            // Supprimer l'utilisateur
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :user_id");
            $stmt->execute(['user_id' => $user_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les statistiques
    public function getStatistics() {
        $stats = [];

        // Nombre total de cours
        $query = "SELECT COUNT(*) as total FROM courses";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total_courses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Cours par catégorie
        $query = "SELECT c.name, COUNT(co.id) as count 
                 FROM categories c 
                 LEFT JOIN courses co ON c.id = co.category_id 
                 GROUP BY c.id";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['courses_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cours le plus populaire
        $query = "SELECT c.title, COUNT(e.id) as enrollments 
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 GROUP BY c.id 
                 ORDER BY enrollments DESC 
                 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['most_popular_course'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Top 3 enseignants
        $query = "SELECT u.username, COUNT(c.id) as course_count 
                 FROM users u 
                 LEFT JOIN courses c ON u.id = c.teacher_id 
                 WHERE u.role = 'teacher' 
                 GROUP BY u.id 
                 ORDER BY course_count DESC 
                 LIMIT 3";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['top_teachers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}
?>