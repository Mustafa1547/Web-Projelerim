<?php
session_start(); 
// Oturum başlatılır. JWT doğrulaması için oturum kullanılabilir.

require __DIR__ . '/../../vendor/autoload.php';
// Composer ile yüklenen paketlerin otomatik yükleyicisi dahil edilir.
// Burada Firebase JWT kütüphanesi yüklü.

// Firebase JWT isim alanını kullanmak için:
use Firebase\JWT\JWT;  //JWT işlemlerini yapabilmek için JWT sınıfını kullanmaya hazırla
use Firebase\JWT\Key;  //	JWT decode işlemi için kullanılacak anahtar ve algoritma bilgisini tutan sınıf

$secretKey = "gizliAnahtar123";
// JWT'yi doğrulamak ve decode etmek için kullanılan gizli anahtar

// Eğer JWT token çerezi (cookie) yoksa kullanıcı login sayfasına yönlendirilir
if (!isset($_COOKIE['jwt_token'])) {
    header("Location: login.php");
    exit; // yönlendirmeden sonra script sonlandırılır
}

$token = $_COOKIE['jwt_token'];
// Kullanıcının tarayıcısından JWT token alınır

try {
    // JWT token decode edilir, doğrulanır
    $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
} catch (Exception $e) {
    // Token geçersiz veya süresi dolmuşsa login sayfasına yönlendir
    header("Location: login.php");
    exit;
}

include("../inc/DB.php");
// Veritabanı işlemleri için Database sınıfının bulunduğu dosya dahil edilir

$mysqli = new mysqli("localhost", "root", "", "dbkutuphane");
// MySQL bağlantısı oluşturulur

if ($mysqli->connect_error) {
    // Bağlantı hatası varsa script burada durur ve hata mesajı gösterir
    die("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
}


 // Oluşturulan mysqli bağlantısını Database sınıfına enjekte et (Dependency Injection)
 $db = new Database($mysqli);


$mesaj = ""; 
// Form işlemlerinden sonra kullanıcıya gösterilecek mesaj için boş değişken

// API ile JSON formatında kitap ekleme işlemi yapılacaksa:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {
    // POST isteği ve içerik tipi JSON ise:
    $input = json_decode(file_get_contents('php://input'), true);
    // JSON içeriği okunup diziye dönüştürülür

    if (json_last_error() === JSON_ERROR_NONE) {
        // JSON decode hatası yoksa
        $kitapData = [
            // Gelen veriler alınır, yoksa boş string veya sıfır atanır
            "kitap_adi" => $input['kitap_adi'] ?? '',
            "yazar" => $input['yazar'] ?? '',
            "tur" => $input['tur'] ?? '',
            "sayfa_sayisi" => intval($input['sayfa_sayisi']) ?? 0
        ];

        // Zorunlu alanların boş olup olmadığı kontrol edilir
        if (empty($kitapData['kitap_adi']) || empty($kitapData['yazar']) || empty($kitapData['tur']) || $kitapData['sayfa_sayisi'] <= 0) {
            // Eksik bilgi varsa JSON formatında hata mesajı döndürülür
            echo json_encode(['success' => false, 'message' => 'Lütfen tüm alanları doldurun.']);
            exit;
        }

        // Veritabanına kitap verisi eklenir
        $eklemeSonuc = $db->veriEkle($kitapData, "tblkitap");

        // İşlem sonucu JSON olarak döndürülür
        echo json_encode([
            'success' => $eklemeSonuc,
            'message' => $eklemeSonuc ? 'Kitap başarıyla eklendi.' : 'Kitap eklenirken hata oluştu.'
        ]);
        exit;
    } else {
        // JSON parse hatası varsa hata mesajı döndürülür
        echo json_encode(['success' => false, 'message' => 'Geçersiz JSON']);
        exit;
    }
}

// Eğer form ile (normal POST) kitap ekleme işlemi yapılmışsa:
if (isset($_POST['add_book'])) {
    // Formdan gelen veriler alınır, yoksa boş veya 0 atanır
    $kitapData = [
        "kitap_adi" => $_POST['book_name'] ?? '',
        "yazar" => $_POST['author_name'] ?? '',
        "tur" => $_POST['book_type'] ?? '',
        "sayfa_sayisi" => intval($_POST['page_number']) ?? 0
    ];

    // Kitap veritabanına eklenir
    $eklemeSonuc = $db->veriEkle($kitapData, "tblkitap");

    // Sonuca göre bootstrap alert mesajı oluşturulur
    $mesaj = $eklemeSonuc
        ? '<div class="alert alert-success mt-3">Kitap başarıyla eklendi.</div>'
        : '<div class="alert alert-danger mt-3">Kitap eklenirken hata oluştu.</div>';
}

// Eğer çıkış butonuna basılmışsa:
if (isset($_POST['cikis'])) {
    // JWT token cookie süresi geçmiş olarak ayarlanır, yani silinir
    setcookie("jwt_token", "", time() - 3600, "/", "", false, true);
    session_destroy(); // Oturum sonlandırılır
    header("Location: login.php"); // Login sayfasına yönlendirilir
    exit;
}
?>

<!-- HTML kısmı başlıyor -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitap Ekle</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Özel CSS -->
    <link rel="stylesheet" href="../../frontend/style.css">
</head>
<body id="bookaddbody">

<!-- Navigasyon menüsü formun içinde, çünkü çıkış butonu da form ile gönderiliyor -->
<form method="post"> 
    <nav class="navbar navbar-expand-sm bg-success navbar-dark">
        <div class="container-fluid">
            <ul class="navbar-nav d-flex align-items-center w-100">
                <li class="nav-item me-3"><a class="nav-link active" href="bookadd.php">Kitap Ekle</a></li>
                <li class="nav-item me-3"><a class="nav-link" href="customerpage.php">Ödünç Listesi</a></li>
                <li class="nav-item ms-auto">
                    <!-- Çıkış butonu -->
                    <button class="btn btn-outline-light" name="cikis">ÇIKIŞ</button>
                </li>
            </ul>
        </div>
    </nav>
</form>

<!-- Kitap ekleme formu -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card p-4 bg-white">
                <h3 class="text-center text-success mb-4">Kitap Ekle</h3>

                <!-- Kitap ekleme formu, JS kullanmak istemeyenler için -->
                <form method="post">
                    <div class="mb-3">
                        <label for="book_name" class="form-label">Kitap Adı</label>
                        <input type="text" class="form-control" id="book_name" name="book_name" required>
                        <!-- Kitap adı zorunlu alan -->
                    </div>

                    <div class="mb-3">
                        <label for="author_name" class="form-label">Yazar</label>
                        <input type="text" class="form-control" id="author_name" name="author_name" required>
                        <!-- Yazar adı zorunlu alan -->
                    </div>

                    <div class="mb-3">
                        <label for="book_type" class="form-label">Tür</label>
                        <select class="form-select" id="book_type" name="book_type">
                            <option value="Roman">Roman</option>
                            <option value="Bilim Kurgu">Bilim Kurgu</option>
                            <option value="Macera">Macera</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="page_number" class="form-label">Sayfa Sayısı</label>
                        <input type="number" class="form-control" id="page_number" name="page_number" required>
                        <!-- Sayfa sayısı zorunlu, sayı tipi -->
                    </div>

                    <button type="submit" name="add_book" class="btn btn-success mt-2">Kitap Ekle</button>
                </form>

                <!-- İşlem sonrası gösterilecek mesaj -->
                <div id="sonuc"><?= $mesaj ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Özel JS dosyası -->
<script src="../../frontend/script2.js"></script>
</body>
</html>
