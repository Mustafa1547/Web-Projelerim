<?php
// kitaplar.php - Kitap verilerini JSON formatında dönen API dosyası
// CORS (Cross-Origin Resource Sharing) ayarı: Her yerden erişime izin ver
header("Access-Control-Allow-Origin: *");

// Dönen içeriğin tipini ve karakter setini JSON olarak ayarla
header("Content-Type: application/json; charset=UTF-8");

// Database sınıfının tanımlı olduğu dosyayı dahil et
include("inc/DB.php");

// 1. MySQL sunucusuna bağlanmak için mysqli nesnesi oluştur
$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");

// Eğer bağlantı sırasında hata varsa
if ($mysqli->connect_error) {
    // HTTP yanıt kodu 500 (Sunucu hatası) olarak ayarla
    http_response_code(500);

    // JSON formatında hata mesajı gönder ve bağlantı hatasını belirt
    echo json_encode(["hata" => "Veritabanı bağlantı hatası: " . $mysqli->connect_error]);

    exit();
}

// 2. Oluşturulan mysqli bağlantısını Database sınıfına enjekte et (Dependency Injection)
$db = new Database($mysqli);

try {
    // Eğer GET isteğinde 'tur' parametresi varsa ve boş değilse
    if (isset($_GET['tur']) && !empty($_GET['tur'])) {
        // 'tur' parametresini al
        $tur = $_GET['tur'];

        // Database sınıfındaki veriCek fonksiyonunu kullanarak
        // tblkitap tablosundan tur kolonu belirtilen $tur değerine eşit olan kayıtları çek
        $result = $db->veriCek("tblkitap", "*", $tur, "tur");
    } else {
        // Eğer 'tur' parametresi yoksa tüm kitapları çek
        $result = $db->tumVeriCek();
    }

    // Kitapların tutulacağı boş bir dizi oluştur
    $kitaplar = [];

    // Eğer sorgudan sonuç geldiyse ve sonuç satır sayısı sıfırdan büyükse
    if ($result && $result->num_rows > 0) {
        // Sonuç kümesindeki her satırı tek tek döngü ile al
        while ($row = $result->fetch_assoc()) {
            // Her satırı diziye ekle
            $kitaplar[] = $row;
        }

        // JSON olarak, Türkçe karakterler Unicode olarak korunsun ve okunabilir şekilde yazılsın
        echo json_encode($kitaplar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        // Eğer sonuç yoksa, HTTP yanıt kodu 404 (Bulunamadı) olarak ayarla
        http_response_code(404);

        // JSON formatında "Kitap bulunamadı" mesajı döndür
        echo json_encode(["mesaj" => "Kitap bulunamadı."]);
    }
} catch (Exception $e) {
    // Eğer try bloğunda hata yakalanırsa, HTTP 500 hatası dön
    http_response_code(500);

    // JSON formatında hata mesajı gönder
    echo json_encode(["hata" => "Sunucu hatası: " . $e->getMessage()]);
}
?>
