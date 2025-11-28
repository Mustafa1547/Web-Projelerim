<?php
// Oturumu başlat
session_start();



// Veritabanı bağlantı dosyasını dahil et
include("../inc/DB.php");
$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");
if ($mysqli->connect_error) {
    die("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
}

$db = new Database($mysqli);

// POST isteği ile kitap ID'si gönderildiyse
if (isset($_GET["kid"])) {

    $kitapID = intval($_GET["kid"]);

    // Sadece gerekli olan kolonlar alınarak kitap bilgileri çekilir
    $sonuc = $db->kolonVeriCek("tblkitap", "kitap_adi, resim, yazar, tur, sayfa_sayisi", $kitapID);

    // Eğer kitap bulunduysa, bilgiler alınır
    if ($sonuc && $sonuc->num_rows > 0) {
        $kitap = $sonuc->fetch_assoc();
    } else {
        // Kitap bulunamazsa mesaj gösterilir ve çıkılır
        echo "Kitap bulunamadı.";
        exit;
    }
} else {
    // Kitap ID'si gönderilmemişse hata mesajı gösterilir
    echo "Kitap ID belirtilmedi.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <!-- Sayfa ayarları -->
    <meta charset="UTF-8">
    <title>Kitap Detayı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Kitap detay kartı -->
<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-success text-white">
            <!-- Kitap adı başlık olarak gösterilir -->
            <h4><?php echo htmlspecialchars($kitap["kitap_adi"]); ?> - Kitap Detayı</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Kitap görseli -->
                <div class="col-md-4 text-center">
                    <img src="../img/<?php echo htmlspecialchars($kitap["resim"]); ?>" class="img-fluid rounded" alt="Kitap Resmi">
                </div>
                <!-- Kitap bilgileri -->
                <div class="col-md-8">
                    <p><strong>Kitap Adı:</strong> <?php echo htmlspecialchars($kitap["kitap_adi"]); ?></p>
                    <p><strong>Yazar:</strong> <?php echo htmlspecialchars($kitap["yazar"]); ?></p>
                    <p><strong>Tür:</strong> <?php echo htmlspecialchars($kitap["tur"]); ?></p>
                    <p><strong>Sayfa Sayısı:</strong> <?php echo htmlspecialchars($kitap["sayfa_sayisi"]); ?></p>
                </div>
            </div>
            <!-- Geri dön butonu -->
            <a href="customerpage.php" class="btn btn-secondary mt-3">Geri Dön</a>
        </div>
    </div>
</div>

</body>
</html>
