<?php
// JWT kütüphanesini dahil et
require __DIR__ . '/../../vendor/autoload.php';


use Firebase\JWT\JWT;  //JWT işlemlerini yapabilmek için JWT sınıfını kullanmaya hazırla
use Firebase\JWT\Key;  //	JWT decode işlemi için kullanılacak anahtar ve algoritma bilgisini tutan sınıf

// JWT oluşturmak için kullanılacak gizli anahtar
$secretKey = "gizliAnahtar123";

// Hata durumlarını takip etmek için değişkenler
$err = false;
$msg = "";

// Form POST yöntemi ile gönderildiyse işlem yapılır
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    // Formdan gelen e-posta ve şifre kontrol ediliyor
    if (isset($_POST["email"]) && isset($_POST["psw"])) {
        $mail = $_POST['email'];
        $psw = $_POST['psw'];

        // Alanlar boş bırakılmışsa uyarı ver
        if ($mail == "" || $psw == "") {
            $err = true;
            $msg = "Kullanıcı adı ve/veya şifre boş!";
        } else {
            // Veritabanı dosyasını dahil et
            include("../inc/DB.php");

            // Veritabanına bağlan
            $mysqli = new mysqli("localhost", "root", "", "dbkutuphane");
            if ($mysqli->connect_error) {
                die("Veritabanı bağlantı hatası: " . $mysqli->connect_error);
            }

           // Oluşturulan mysqli bağlantısını Database sınıfına enjekte et (Dependency Injection)
            $db = new Database($mysqli);
               // Veritabanı sınıfı üzerinden kullanıcı kontrolü yap
            $sonuc = $db->kullaniciGirisi("tblkullanici", "email", "password", $mail, $psw);

            // Eğer kullanıcı admin (1) ise
            if ($sonuc == 1) {
                // JWT payload (içerik) oluştur
                $payload = [
                    "iss" => "http://localhost",       // Token'ı kim oluşturdu
                    "aud" => "http://localhost",       // Token'ı kim kullanacak
                    "iat" => time(),                   // Token oluşturulma zamanı
                    "exp" => time() + 3600,            // Token geçerlilik süresi (1 saat)
                    "data" => [
                        "email" => $mail               // Kullanıcı bilgisi
                    ]
                ];

                // JWT token'ı oluştur
                $jwt = JWT::encode($payload, $secretKey, 'HS256');

                // Token'ı cookie'ye yaz (1 saat süreli)
                setcookie("jwt_token", $jwt, time() + 3600, "/", "", false, true);

                // Session başlat ve token'ı sakla
                session_start();
                $_SESSION['token'] = $jwt;
                $_SESSION['email'] = $mail;

                // Admin paneline yönlendir
                header("Location: bookadd.php");
                exit;

            // Eğer kullanıcı normal kullanıcı (3) ise
            } elseif ($sonuc == 2) {
                session_start();
                $_SESSION['email'] = $mail;

                // Aynı şekilde JWT oluştur
                $payload = [
                    "iss" => "http://localhost",       // Token'ı kim oluşturdu
                    "aud" => "http://localhost",       // Token'ı kim kullanacak
                    "iat" => time(),                   // Token oluşturulma zamanı
                    "exp" => time() + 3600,            // Token geçerlilik süresi (1 saat)
                    "data" => [
                        "email" => $mail               // Kullanıcı bilgisi
                    ]
                ];

             // Kullanıcı bilgilerini ve oturum süresini içeren JWT payload'u şifreleyip token oluştur
            $jwt = JWT::encode($payload, $secretKey, 'HS256');



            setcookie("jwt_token", $jwt, time() + 3600, "/", "", false, true);

          // Aynı JWT token'ı PHP oturumuna da ekle (isteğe bağlı olarak backend işlemleri için)
          $_SESSION['token'] = $jwt;


                // Kullanıcı paneline yönlendir
                header("Location: customerpage.php");
                exit;

            } else {
                // Geçersiz kullanıcı bilgisi
                $err = true;
                $msg = "Geçersiz e-posta veya şifre!";
            }
        }

      } else {
        // E-posta veya şifre gönderilmemiş
        $err = true;
        $msg = "E-posta ve şifre gönderilmedi!";
      }
   }
?>



<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş Yap</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../../frontend/style.css">
</head>
<body id="loginbody">

<!-- Giriş formu -->
<form method="post" class="login-box">
  <h2>Login Page</h2>

  <!-- E-Posta alanı -->
  <div class="mb-3">
    <label for="email" class="form-label">E-Mail</label>
    <input type="email" name="email" id="email" class="form-control" required>
  </div>

  <!-- Şifre alanı -->
  <div class="mb-3">
    <label for="psw" class="form-label">Şifre</label>
    <input type="password" name="psw" id="psw" class="form-control" required>
  </div>

  <!-- Şifremi unuttum linki -->
  <a href="#" class="forgot-link">Şifrenizi mi unuttunuz?</a>

  <!-- Giriş butonu -->
  <button id="btnm" class="btn btn-gradient mt-3">Giriş</button>

  <!-- Hata veya başarı mesajı -->
  <?php 
    if($msg != "") {
        $alertClass = $err ? 'alert-danger' : 'alert-success';
        echo "<p class='alert $alertClass mt-3' id='uyari'>$msg</p>";
    }
  ?>
</form>

</body>
</html>

<!-- Hata mesajı varsa göster -->
<script>
  const uyari = document.getElementById('uyari');
  if (uyari) {
    uyari.style.display = 'block';
  }
</script>
