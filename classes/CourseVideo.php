<?php
require_once 'BaseCourse.php';

class CourseVideo extends BaseCourse {
    // Propriété spécifique aux cours vidéo
    protected $video_url;

    // Constructeur pour initialiser les propriétés communes et spécifiques
    public function __construct($db, $id = null, $title = null, $description = null, $content = null, $teacher_id = null, $category_id = null, $created_at = null, $video_url = null) {
        parent::__construct($db, $id, $title, $description, $content, $teacher_id, $category_id, $created_at);
        $this->video_url = $video_url;
    }

    // Getter et Setter pour la propriété spécifique
    public function getVideoUrl() { return $this->video_url; }
    public function setVideoUrl($video_url) { $this->video_url = $video_url; }

    // Implémentation de la méthode create pour les cours vidéo
    public function create($title, $description, $content, $teacher_id, $category_id) {
        $query = "INSERT INTO courses (title, description, content, teacher_id, category_id, video_url) 
                 VALUES (:title, :description, :content, :teacher_id, :category_id, :video_url)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":content", $content);
        $stmt->bindParam(":teacher_id", $teacher_id);
        $stmt->bindParam(":category_id", $category_id);
        $stmt->bindParam(":video_url", $this->video_url);

        return $stmt->execute();
    }

    // Implémentation de la méthode displayCourseDetails pour les cours vidéo
    public function displayCourseDetails() {
        echo "Course ID: " . $this->id . "<br>";
        echo "Title: " . $this->title . "<br>";
        echo "Description: " . $this->description . "<br>";
        echo "Content: " . $this->content . "<br>";
        echo "Teacher ID: " . $this->teacher_id . "<br>";
        echo "Category ID: " . $this->category_id . "<br>";
        echo "Created At: " . $this->created_at . "<br>";
        echo "Video URL: " . $this->video_url . "<br>";
    }
}
?>