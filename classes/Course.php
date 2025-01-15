<?php
abstract class BaseCourse {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Méthode abstraite pour créer un cours
    abstract public function create($title, $description, $content, $teacher_id, $category_id);

    // Méthode abstraite pour obtenir tous les cours
    abstract public function getAllCourses($page = 1, $limit = 9);
}
class Course extends BaseCourse {

    // Implémentation de la méthode create
    public function create($title, $description, $content, $teacher_id, $category_id) {
        $query = "INSERT INTO courses (title, description, content, teacher_id, category_id) 
                 VALUES (:title, :description, :content, :teacher_id, :category_id)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->bindParam(":category_id", $category_id);

        return $stmt->execute();
    }

    // Implémentation de la méthode getAllCourses
    public function getAllCourses($page = 1, $limit = 9) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 ORDER BY c.created_at DESC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Méthode pour rechercher des cours par mot-clé
    public function searchCourses($keyword) {
        $keyword = "%$keyword%";
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.title LIKE :keyword OR c.description LIKE :keyword
                 ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Méthode pour obtenir les cours par catégorie
    public function getCoursesByCategory($category_id, $page = 1, $limit = 9) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT c.*, u.username as teacher_name, cat.name as category_name 
                 FROM courses c 
                 LEFT JOIN users u ON c.teacher_id = u.id 
                 LEFT JOIN categories cat ON c.category_id = cat.id 
                 WHERE c.category_id = :category_id
                 ORDER BY c.created_at DESC
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>