<?php
header('Content-Type: application/json');
include("inc/DB.php");

$mysqli = new mysqli("localhost", "root", "", "dbstories");
if ($mysqli->connect_error) {
    echo json_encode(["success" => false, "message" => "Veritabanı bağlantı hatası"]);
    exit;
}

$db = new Database($mysqli);

$data = json_decode(file_get_contents("php://input"), true);
$storyId = intval($data["story_id"] ?? 0);
$action = $data["action"] ?? "like"; // "like" veya "unlike"

if ($storyId <= 0) {
    echo json_encode(["success" => false, "message" => "Geçersiz hikaye ID."]);
    exit;
}

// Mevcut satırı al
$result = $db->kolonVeriCek("tblcommentslikes", "likes", $storyId);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentLikes = (int)$row["likes"];

    if ($action === "like") {
        $newLikes = $currentLikes + 1;
    } else { // unlike
        $newLikes = max($currentLikes - 1, 0);
    }

    $updated = $db->veriGuncelle(["likes" => $newLikes], "tblcommentslikes", $storyId);

    if ($updated) {
        echo json_encode(["success" => true, "newLikes" => $newLikes]);
    } else {
        echo json_encode(["success" => false, "message" => "Beğeni güncellenemedi."]);
    }

} else {
    // Satır yoksa ve like ise yeni satır ekle
    if ($action === "like") {
        $inserted = $db->veriEkle(["story_id" => $storyId, "likes" => 1], "tblcommentslikes");
        if ($inserted) {
            echo json_encode(["success" => true, "newLikes" => 1]);
        } else {
            echo json_encode(["success" => false, "message" => "Beğeni eklenemedi."]);
        }
    } else {
        echo json_encode(["success" => false, "newLikes" => 0]);
    }
}
exit;
