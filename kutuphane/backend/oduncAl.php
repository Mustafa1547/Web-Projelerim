<?php

// CORS ayarları: Diğer domainlerden gelen isteklerin kabul edilmesi sağlanır
header("Access-Control-Allow-Origin: *"); // Tüm kaynaklardan gelen istekleri kabul et
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // İzin verilen HTTP metodları
header("Access-Control-Allow-Headers: Content-Type"); // Content-Type başlığına izin ver



// Veritabanı bağlantısı için gerekli sınıf dosyasını dahil et
include("inc/DB.php");

// MySQL bağlantısı başlatılır
$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");

// Bağlantı hatası varsa 500 hatası döndürülür ve çıkılır
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "hata" => "Veritabanı bağlantı hatası: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Veritabanı işlemleri için sınıf örneği oluşturulur
$db = new Database($mysqli);

try {
    // İstekle gelen JSON verisi okunur
    $json = file_get_contents("php://input");

    // Hata ayıklamak için gelen JSON dosyaya yazılır (isteğe bağlıdır)
    file_put_contents("debug.json", $json);

    // JSON çözümlemesi yapılır (string -> dizi)
    $data = json_decode($json, true);

    // JSON parse hatası varsa 400 Bad Request dönülür
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            "hata" => "JSON çözümleme hatası: " . json_last_error_msg(),
            "gelen_veri" => $json
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    // Gelen veri boşsa veya beklenen yapıda değilse
    if (!$data || !is_array($data) || empty($data)) {
        http_response_code(400);
        echo json_encode([
            "hata" => "Geçersiz veya boş JSON verisi"
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    // Bugünün tarihi ve 15 gün sonrası hesaplanır
    $today = date("Y-m-d");
    $limit = date("Y-m-d", strtotime("+15 days"));
    $sonuclar = []; // İşlem sonuçlarını tutmak için dizi

    // Her kitap için işlem yapılır
    foreach ($data as $item) {
        // Gerekli alanlar ve sayısal değer kontrolü
        if (!isset($item['kitap_id']) || !isset($item['adet']) || !is_numeric($item['kitap_id']) || !is_numeric($item['adet'])) {
            http_response_code(400);
            echo json_encode([
                "hata" => "Geçersiz kitap_id veya adet bilgisi"
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit();
        }

        // Değerler tamsayıya dönüştürülür
        $kitap_id = (int)$item['kitap_id'];
        $adet = (int)$item['adet'];

        // Kitap adı veritabanından alınır
        $kitap = $db->kolonVeriCek("tblkitaplar", "kitap_adi", $kitap_id);
        $kitap_adi = ($kitap && $kitap->num_rows > 0)
            ? $kitap->fetch_assoc()['kitap_adi']
            : "Bilinmeyen Kitap";

        // Adet kadar kayıt eklenir (örneğin 2 adet ödünç alındıysa 2 kayıt yapılır)
        for ($i = 0; $i < $adet; $i++) {
            $veri = [
                'id' => $kitap_id,
                'borrow_day' => $today,
                'borrow_day_limit' => $limit,
                'price' => 0,
                'state' => 1
            ];

            // Kayıt eklenemezse hata döndürülür
            if (!$db->veriEkle($veri, 'tblkitapodunc')) {
                http_response_code(500);
                echo json_encode([
                    "hata" => "Kitap eklenemedi: kitap_id $kitap_id"
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit();
            }
        }

        // Başarılı işlem sonucu bu diziye eklenir
        $sonuclar[] = [
            'kitap_id' => $kitap_id,
            'kitap_adi' => $kitap_adi,
            'adet' => $adet
        ];
    }

    // Tüm işlemler başarıyla tamamlandıysa 200 OK ve başarı mesajı gönderilir
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "kitaplar" => $sonuclar
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Sunucu tarafında beklenmeyen bir hata olursa burada yakalanır
    http_response_code(500);
    echo json_encode([
        "hata" => "Sunucu hatası: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>
