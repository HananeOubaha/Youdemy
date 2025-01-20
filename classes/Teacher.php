<?php
require_once 'User.php';

class Teacher extends User {
    public function __construct($db, $id = null, $username = null, $email = null, $passwordHash = null, $role = 'teacher', $isActive = true, $isVerified = false, $createdAt = null) {
        // Appel du constructeur de la classe parente (User) pour initialiser les propriétés héritées
        parent::__construct($db, $id, $username, $email, $passwordHash, $role, $isActive, $isVerified, $createdAt);
    }

    // Implémentation de la méthode abstraite login
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email AND is_active = 1 AND role = 'teacher'";
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
    public function register($username, $email, $password, $role = 'teacher') {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setRole($role);
        $this->setPasswordHash($password);
        $this->setIsActive(true);
        $this->setIsVerified(false); // Les enseignants ne sont pas vérifiés par défaut

        return $this->save();
    }

    // Récupérer les cours de l'enseignant
    public function getMyCourses($teacher_id) {
        $query = "SELECT c.*, COUNT(e.id) as student_count 
                 FROM courses c 
                 LEFT JOIN enrollments e ON c.id = e.course_id 
                 WHERE c.teacher_id = :teacher_id 
                 GROUP BY c.id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour un cours
    public function updateCourse($course_id, $title, $description, $content, $content_type, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le cours
            $query = "UPDATE courses 
                     SET title = :title, description = :description, content = :content, content_type = :content_type, category_id = :category_id 
                     WHERE id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);
            $stmt->bindParam(":content", $content, PDO::PARAM_STR);
            $stmt->bindParam(":content_type", $content_type, PDO::PARAM_STR);
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
            $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
            $stmt->execute();

            // Supprimer les anciens tags
            $query = "DELETE FROM course_tags WHERE course_id = :course_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
            $stmt->execute();

            // Ajouter les nouveaux tags
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
                    $stmt->bindParam(":tag_id", $tag_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to update course: " . $e->getMessage());
            return false;
        }
    }

    // Supprimer un cours
    public function deleteCourse($course_id) {
        try {
            $this->db->beginTransaction();

            // Supprimer les tags du cours
            $stmt = $this->db->prepare("DELETE FROM course_tags WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            // Supprimer les inscriptions au cours
            $stmt = $this->db->prepare("DELETE FROM enrollments WHERE course_id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            // Supprimer le cours
            $stmt = $this->db->prepare("DELETE FROM courses WHERE id = :course_id");
            $stmt->execute(['course_id' => $course_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to delete course: " . $e->getMessage());
            return false;
        }
    }

    // Récupérer les étudiants d'un cours
    public function getCourseStudents($course_id, $teacher_id) {
        $query = "SELECT u.username, u.email, e.enrolled_at 
                 FROM enrollments e 
                 JOIN users u ON e.student_id = u.id 
                 JOIN courses c ON e.course_id = c.id 
                 WHERE c.id = :course_id AND c.teacher_id = :teacher_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
        $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer un cours avec type de contenu (texte ou vidéo)
    public function createCourse($title, $description, $content, $content_type, $teacher_id, $category_id, $tags) {
        try {
            $this->db->beginTransaction();

            // Insérer le cours
            $query = "INSERT INTO courses (title, description, content, content_type, teacher_id, category_id) 
                     VALUES (:title, :description, :content, :content_type, :teacher_id, :category_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);
            $stmt->bindParam(":content", $content, PDO::PARAM_STR);
            $stmt->bindParam(":content_type", $content_type, PDO::PARAM_STR);
            $stmt->bindParam(":teacher_id", $teacher_id, PDO::PARAM_INT);
            $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
            $stmt->execute();

            $course_id = $this->db->lastInsertId();

            // Ajouter les tags
            if (!empty($tags)) {
                foreach ($tags as $tag_id) {
                    $query = "INSERT INTO course_tags (course_id, tag_id) VALUES (:course_id, :tag_id)";
                    $stmt = $this->db->prepare($query);
                    $stmt->bindParam(":course_id", $course_id, PDO::PARAM_INT);
                    $stmt->bindParam(":tag_id", $tag_id, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to create course: " . $e->getMessage());
            return false;
        }
    }
}
?>