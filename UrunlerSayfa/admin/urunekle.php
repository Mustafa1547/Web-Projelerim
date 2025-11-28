<?php
session_start();
$msg="";
$tip=0;
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Çıkış yapma işlemi
    if (isset($_POST['logout'])) {
        $_SESSION["email"] = null;
        session_destroy();
        header("Location:login.php");
    }

    // Ürün ekleme işlemi
    if (isset($_POST['add_product'])) {
        $product_name = isset($_POST['product_name'])?htmlspecialchars($_POST['product_name']):null;
        $product_description = isset($_POST['product_description'])?htmlspecialchars($_POST['product_description']):null;
        $product_price = isset($_POST['product_price'])?htmlspecialchars($_POST['product_price']):null;

        //ön hazırlık
        $host="localhost";
        $username="root";
        $password="";
        $dbname="dbyurt";

        //bağlan
        $connection=new mysqli($host,$username,$password,$dbname);
        if($connection->connect_error){
          die("HATA".$connection->connect_error);
        }
        $sorgu="INSERT INTO tblurunler (adi,aciklama,fiyat) VALUES(?,?,?)";
        $stmt = $connection->prepare($sorgu);
        $stmt->bind_param("sss", $product_name, $product_description,$product_price);//veriler bağlanıyor
        if($stmt->execute()){
          $msg.="Ürünler başarıyla eklendi.";
          $tip=1;
        }
        else{
          $msg.="Ürün ekleme başarısız!";
          $tip=0;
        }
        
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ürün Ekleme Sayfası</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <form method="post"> 
        <nav class="navbar navbar-expand-sm bg-success navbar-dark">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">İletişim</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Galeri</a>
                    </li>
                    <li class="nav-item">
                       <form method="post">
                               <button type="submit" name="logout" class="btn btn-primary">ÇIKIŞ</button>
                        </form>

                    </li>
                </ul>
            </div>
        </nav>

        <!-- Ürün Ekleme Alanı -->
        <div class="container mt-5">
            <h3>Ürün Ekle</h3>
            <div class="mb-3">
                <label for="product_name" class="form-label">Ürün Adı</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="mb-3">
                <label for="product_description" class="form-label">Ürün Açıklaması</label>
                <textarea class="form-control" id="product_description" name="product_description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="product_price" class="form-label">Ürün Fiyatı</label>
                <input type="number" class="form-control" id="product_price" name="product_price" required>
            </div>
            <button type="submit" name="add_product" class="btn btn-success">Ürünü Ekle</button>

            
            <?php if($tip==0){ ?>
            <div class='alert alert-danger mt-3' id="uyari"><?php echo $msg; ?></div>
            <?php } ?>
            <?php if($tip==1){ ?>
            <div class='alert alert-success mt-3' id="uyari"><?php echo $msg; ?></div>
            <?php } ?>
        </div>
    </form>

</body>
</html>

<script>

    document.getElementById("uyari").style.display='block';

</script>
<?php
if($connection){
// Bağlantıyı kapat
$connection->close();
}
?>


