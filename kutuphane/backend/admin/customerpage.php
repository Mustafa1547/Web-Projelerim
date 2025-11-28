<?php
session_start(); // Oturumu başlat

require __DIR__ . '/../../vendor/autoload.php'; // JWT kütüphanesini dahil et
use Firebase\JWT\JWT; //JWT işlemlerini yapabilmek için JWT sınıfını kullanmaya hazırla
use Firebase\JWT\Key; //JWT decode işlemi için kullanılacak anahtar ve algoritma bilgisini tutan sınıf

$secretKey = "gizliAnahtar123"; // JWT imzalama anahtarı

// Eğer JWT cookie'si yoksa, kullanıcı login sayfasına yönlendirilir
if (!isset($_COOKIE['jwt_token'])) {
    header("Location: login.php");
    exit;
}

$token = $_COOKIE['jwt_token'];

try {
    // JWT token çözülüyor ve doğrulanıyor
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
    // Eğer geçerliyse devam edilir
} catch (Exception $e) {
    // Hatalı veya süresi geçmiş token varsa login sayfasına gönder
    header("Location: login.php");
    exit;
}

// Veritabanı bağlantısını başlat
include("../inc/DB.php");

$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");
if ($mysqli->connect_error) {
    die("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
}

$db = new Database($mysqli); // DB sınıfı ile çalış

// Token içerisinden e-posta adresini al
$userEmail = $decoded->data->email; 

// Örnek veri çekme (şu an sabit kitap ID: 3 için veri çekiliyor)
$sonuc = $db->kolonVeriCek("tblkitap", "id, kitap_adi, resim", 3);

// ÇIKIŞ butonuna basıldıysa: Cookie'yi ve oturumu sil, giriş sayfasına yönlendir
if (isset($_POST['cikis'])) {
    setcookie("jwt_token", "", time() - 3600, "/", "", false, true); // JWT cookie'yi iptal et
    session_destroy(); // Oturumu sonlandır
    header("Location: login.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Ödünç Aldığınız Kitaplar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../frontend/style.css">
</head>
<body id="customerpagebody">

<!-- Navbar ve Çıkış Butonu -->
<form method="post"> 
    <nav class="navbar navbar-expand-sm bg-success navbar-dark">
        <div class="container-fluid">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-3">
                    <a class="nav-link text-white fw-semibold" href="studentpage.php">📚 Ödünç Listesi</a>
                </li>
                <li class="nav-item">
                    <button class="btn btn-outline-light" id="cikis" name="cikis">ÇIKIŞ</button>
                </li>
            </ul>
        </div>
    </nav>
</form>

<!-- Başlık Kartı -->
<div class="container">
    <div class="header-card shadow-sm mt-4 p-4 bg-white rounded">
        <h2>📖 Ödünç Aldığınız Kitaplar</h2>
        <p>Ödünç aldığınız kitapları görüntüleyebilir, detaylarına ulaşabilirsiniz.</p>
    </div>
</div>

<!-- Kitaplar Tablosu -->
<div class="container table-container mt-4">
    <h4 class="mb-4 text-center fw-bold">📘 Mevcut Kitaplar</h4>
    <table class="table table-striped table-hover align-middle">
        <thead class="table-success">
            <tr>
                <th scope="col">Kitap Adı</th>
                <th scope="col">Resim</th>
                <th scope="col">İşlem</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Kitaplar varsa tabloya yazdır
            if ($sonuc && $sonuc->num_rows > 0) {
                while ($kitap = $sonuc->fetch_assoc()) {
                    echo '
                    <tr>
                        <td>' . htmlspecialchars($kitap["kitap_adi"]) . '</td>
                        <td><img src="../img/' . htmlspecialchars($kitap["resim"]) . '" style="width:50px; height:auto;"></td>
                        <td>
                            <form method="post"  style="display:inline;">
                                <input type="hidden" name="kitap_id" value="' . htmlspecialchars($kitap["id"]) . '">
                                <a href="detay.php?kid='. htmlspecialchars($kitap["id"]) . '" class="btn btn-warning btn-sm" >Detay</a>
                            </form>
                        </td>
                    </tr>';
                }
            } else {
                // Kitap yoksa mesaj yazdır
                echo '<tr><td colspan="3" class="text-center">Hiç kitap bulunamadı.</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
