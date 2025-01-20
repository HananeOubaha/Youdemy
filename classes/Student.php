<?php
require_once 'User.php';

class Student extends User {
    public function __construct($db, $id = null, $username = null, $email = null, $passwordHash = null, $role = 'student', $isActive = true, $isVerified = false, $createdAt = null) {
        // Appel du constructeur de la classe parente (User) pour initialiser les propriétés héritées
        parent::__construct($db, $id, $username, $email, $passwordHash, $role, $isActive, $isVerified, $createdAt);
    }

    // Implémentation de la méthode abstraite login
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email AND is_active = 1 AND role = 'student'";
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
    public function register($username, $email, $password, $role = 'student') {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setRole($role);
        $this->setPasswordHash($password);
        $this->setIsActive(true);
        $this->setIsVerified(false); // Les étudiants ne sont pas vérifiés par défaut

        return $this->save();
    }

    // S'inscrire à un cours
    public function enrollCourse($student_id, $course_id) {
        // Vérifier si déjà inscrit
        $query = "SELECT id FROM enrollments WHERE student_id = :student_id AND course_id = :course_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false; // Déjà inscrit
        }

        // S'inscrire au cours
        $query = "INSERT INTO enrollments (student_id, course_id) VALUES (:student_id, :course_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Récupérer les cours auxquels l'étudiant est inscrit
    public function getEnrolledCourses($student_id) {
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM enrollments e 
                 JOIN courses c ON e.course_id = c.id 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE e.student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>