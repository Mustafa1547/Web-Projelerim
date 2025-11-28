
<?php
header('Content-Type: application/json');
include("inc/DB.php");

$mysqli = new mysqli("localhost", "root", "", "dbstories");
if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "message" => "Veritabanı bağlantı hatası"]);
    exit;
}

$db = new Database($mysqli);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $storyData = [
        "author_name" => trim($data["authorName"] ?? ''),
        "story_title" => trim($data["storyTitle"] ?? ''),
        "category" => trim($data["storyCategory"] ?? ''),
        "story_content" => trim($data["storyContent"] ?? '')
    ];

    if (in_array('', $storyData, true)) {
        echo json_encode(["success" => false, "message" => "Tüm alanları doldurun."]);
        exit;
    }

    $result = $db->veriEkle($storyData, "tblstories");
    echo json_encode([
        "success" => $result,
        "message" => $result ? "Hikaye başarıyla eklendi." : "Ekleme sırasında hata oluştu."
    ]);
}
?>
