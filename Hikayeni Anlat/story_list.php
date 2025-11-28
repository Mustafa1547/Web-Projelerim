<?php
header('Content-Type: application/json');
include("inc/DB.php");

// Veritabanı bağlantısı
$mysqli = new mysqli("localhost", "root", "", "dbstories");
if ($mysqli->connect_error) {
    echo json_encode([]);
    exit;
}

$db = new Database($mysqli);

// SQL sorgusu: tblstories ile tblcommentslikes LEFT JOIN
$sql = "
    SELECT 
        s.id, 
        s.author_name, 
        s.story_title, 
        s.story_date, 
        s.category, 
        s.story_content, 
        IFNULL(c.likes, 0) AS likes
    FROM tblstories s
    LEFT JOIN tblcommentslikes c ON s.id = c.story_id
    ORDER BY s.story_date DESC
";

$result = $mysqli->query($sql);

$hikayeler = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hikayeler[] = $row;
    }
}

echo json_encode($hikayeler);
?>
