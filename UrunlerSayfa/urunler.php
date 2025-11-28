
<?php
// Veritabanı bağlantısı
$host = "localhost";
$username = "root";
$password = "";
$dbname = "dbyurt";
$adet=0;
// Bağlantıyı oluştur
$connection = new mysqli($host, $username, $password, $dbname);

// Bağlantı hatası kontrolü
if ($connection->connect_error) {
    die("Bağlantı hatası: " . $connection->connect_error);
}

// Sorguyu çalıştır
$sorgu = "SELECT * FROM tblurunler";
$stmt = $connection->prepare($sorgu);
$stmt->execute();
$result = $stmt->get_result(); // Sonuçları al

if (isset($_POST['sepet'])) {
    // Çerezde 'adet' değeri varsa, 1 artır. Yoksa, 1 olarak başlat.
    if (isset($_COOKIE['adet'])) {
        // Cookie var, bir artır
        $adet .= $_COOKIE['adet'] + 1;
    } else {
        // Cookie yok, ilk kez eklendi
        $adet .= 1;
    }

    // Çerezi güncelle
    setcookie('adet', $adet, 0, "/");
 
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ürünler Sayfası</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="css/stil.css">
</head>
<body>

  <!-- Navbar (Navigasyon Çubuğu) -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Y</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="#">Anasayfa</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="#">Ürünler</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">İletişim </a>
          </li>
          <li class="nav-item">
             <a class="nav-link" href="#"><span class="badge bg-light text-dark">
                <i class="bi bi-cart-fill"></i> Ürünler<span class="badge bg-danger"><?php echo $adet;?></span>
              </span></a>
          </li>
        </ul>
      </div>
    </div>
</nav>

 
  <!-- Ürünler Kartları -->
  <div class="container my-5">
     <h3 class="text-center">Ürünlerimiz</h3>
    <hr>
    <form method="post">
    <div class="row row-cols-1 row-cols-md-3 g-4">
      <form method="post">
      <?php
      // Veritabanından gelen her ürünü ekrana yazdırma
      while ($urun = $result->fetch_assoc()) {
        echo '<div class="col">
                <div class="card">
                  <img src="img/nike.png" class="card-img-top" alt="' . $urun['adi'] . '">
                  <div class="card-body">
                    <h5 class="card-title">' . $urun['adi'] . '</h5>
                    <h6 class="card-title">' . $urun['fiyat'] . ' TL</h6>
                    <p class="card-text">' . $urun['aciklama'] . '</p>
                    <button href="#" class="btn btn-primary" id="sepet" name="sepet">Sepete Ekle</button>
                  </div>
                </div>
              </div>';
      }
      ?>
     
    </div>
    </form>
  </div>

  <!-- Bootstrap JS ve bağımlılıkları -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Bağlantıyı kapat
$connection->close();
?>
