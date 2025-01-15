<?php
class Tag {
    private $id;
    private $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public static function addTag($name) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public static function getTagsForCourse($courseId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT t.* FROM tags t JOIN course_tags ct ON t.id = ct.tag_id WHERE ct.course_id = ?");
        $stmt->execute([$courseId]);
        $tags = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = new Tag($row['id'], $row['name']);
        }
        return $tags;
    }
}
