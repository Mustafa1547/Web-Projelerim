<?php

header("Content-Type: application/json; charset=UTF-8");  // JSON çıktısı için içerik tipi ayarla
header("Access-Control-Allow-Origin: *");                 // Herhangi bir kaynaktan erişime izin ver

// Database sınıfını dahil et (inc/DB.php dosyasındaki Database sınıfını kullanacağız)
include("inc/DB.php");

// MySQL bağlantısını başlat (localhost'ta root kullanıcı, şifresiz, dbkutuphane veritabanı)
$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");

// Bağlantı kontrolü yap, hata varsa 500 hatası ile JSON olarak hata mesajı gönder ve çık
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "hata" => "Veritabanı bağlantı hatası: " . $mysqli->connect_error
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

// Database nesnesi oluştur, $mysqli bağlantısını sınıfa geçir
$db = new Database($mysqli);

try {
    // 'tblkitapodunc' tablosundan id ve borrow_day_limit sütunlarını çek (aktif kayıt filtresi yoksa tüm kayıtlar gelir)
    $result = $db->kolonVeriCek('tblkitapodunc', 'id, borrow_day_limit', null);

    // Eğer veri çekme başarısızsa veya hiç kayıt yoksa 404 hatası ve mesaj döndür
    if ($result === false || $result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            "mesaj" => "Aktif ödünç kaydı bulunamadı"
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    $today = new DateTime(); // Bugünün tarihi DateTime nesnesi olarak alınır
    $cezaAlanKitaplar = [];  // Ceza alan kitapların bilgilerini tutacak boş dizi

    // Tüm ödünç kayıtlarını döngü ile tek tek kontrol et
    while ($row = $result->fetch_assoc()) {
        $limit_date = new DateTime($row["borrow_day_limit"]); // Ödünç verme süresi bitiş tarihi

        // Eğer bugünün tarihi ödünç bitiş tarihinden büyükse (geç kalındıysa)
        if ($today > $limit_date) {
            // Gecikilen gün sayısını hesapla
            $days_late = $today->diff($limit_date)->days;

            // Gecikilen gün sayısı ile ceza tutarını hesapla (10 TL/gün)
            $price = $days_late * 10;

            // price sütununu güncellemek için veriyi hazırla
            $formData = ['price' => $price];

            // Veritabanında ilgili ödünç kaydını güncelle (id bazlı)
            $db->veriGuncelle($formData, 'tblkitapodunc', $row["id"]);

            // Ceza alan kitaplar dizisine bu kaydın bilgilerini ekle
            $cezaAlanKitaplar[] = [
                'kitap_odunc_id' => $row["id"],
                'ceza_gunu' => $days_late,
                'ceza_tutari' => $price
            ];
        }
    }

    // İşlem tamamlandı, HTTP 200 başarılı kodu gönder
    http_response_code(200);

    // Eğer ceza alan kitap varsa onları JSON formatında döndür
    if (count($cezaAlanKitaplar) > 0) {
        echo json_encode([
            $cezaAlanKitaplar
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Ceza alan kitap yoksa bilgilendirme mesajı döndür
    } else {
        echo json_encode([
            "mesaj" => "Ceza kontrolü tamamlandı, güncelleme gerekmedi"
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

} catch (Exception $e) {
    // Eğer bir hata oluşursa 500 hata kodu ve hata mesajını JSON formatında gönder
    http_response_code(500);
    echo json_encode([
        "hata" => "Sunucu hatası: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


?>
