<?php
require_once 'BaseCourse.php';

class CourseText extends BaseCourse {
    // Propriété spécifique aux cours texte
    protected $text_content;
    protected $content_type = 'text';

    // Constructeur pour initialiser les propriétés communes et spécifiques
    public function __construct($db, $id = null, $title = null, $description = null, $content = null, $teacher_id = null, $category_id = null, $created_at = null, $text_content = null) {
        parent::__construct($db, $id, $title, $description, $content, $teacher_id, $category_id, $created_at);
        $this->text_content = $text_content;
    }

    // Getter et Setter pour la propriété spécifique
    public function getTextContent() { return $this->text_content; }
    public function setTextContent($text_content) { $this->text_content = $text_content; }

    // Implémentation de la méthode create pour les cours texte
    public function create($title, $description, $content, $teacher_id, $category_id) {
        $query = "INSERT INTO courses (title, description, content, teacher_id, category_id, text_content) 
                 VALUES (:title, :description, :content, :teacher_id, :category_id, :text_content)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":text_content", $this->text_content);

        return $stmt->execute();
    }

    // Implémentation de la méthode displayCourseDetails pour les cours texte
    public function displayCourseDetails() {
        echo "Course ID: " . $this->id . "<br>";
        echo "Title: " . $this->title . "<br>";
        echo "Description: " . $this->description . "<br>";
        echo "Content: " . $this->content . "<br>";
        echo "Teacher ID: " . $this->teacher_id . "<br>";
        echo "Category ID: " . $this->category_id . "<br>";
        echo "Created At: " . $this->created_at . "<br>";
        echo "Text Content: " . $this->text_content . "<br>";
    }
}
?>