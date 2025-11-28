<?php
$err = false;
$msg = "";
$errCode = "";

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$dbname = "dbyurt";

// Database connection prepare start
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST["email"]) && isset($_POST["psw"])) {
        $mail = isset($_POST['email']) ? $_POST['email'] : "";
        $psw = isset($_POST['psw']) ? $_POST['psw'] : "";
        $ban=1;
        if ($mail == "" || $psw == "") {
            // Input validation failure
            $err = true;
            $msg = "Kullanıcı adı ve/veya şifre boş!";
        } else {
            // Database connection start
            $conn = new mysqli($host, $username, $password, $dbname);

            if ($conn->connect_error) {
                $err = true;
                $msg = "HATA, Code:1002 " . $conn->connect_error;
            } else {
                // SQL query for checking user login
                $sql = "SELECT * FROM tbllogin WHERE email=? AND sifre=? AND ban=?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("ssi", $mail, $psw,$ban);//veriler bağlanıyor
                    $stmt->execute();//işlem icra et 
                    $result = $stmt->get_result();//varsa sonuç getir

                    if ($result->num_rows > 0) {//sonuç dönderse yapılacak işlem
                        $err = false;
                        session_start();
                        $_SESSION['email']=$mail;
                        header("Location:urunekle.php");
                    } else {//sonuç hatalıysa
                        $err = true;
                        $msg = "Hatalı e-posta veya şifre!";
                    }

                    $stmt->close();
                } else {
                    $err = true;
                    $msg = "HATA, Code:1003 SQL Error!";
                }
            }

            $conn->close();
        }
    } else {
        $err = true;
        $errCode = "1001";
        $msg = "E-posta ve şifre gönderilmedi!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style type="text/css">
  	body{
  		background: url("bg.jpg") #bbb;
  	}
  </style>
</head>
<body>
	<form method="post">
		<div class="container-fluid ">
		<div class="row d-flex justify-content-center">
		<div class="col col-md-6 p-3">
		<div class="form-group">
			<label>E-Mail</label>
		<input type="email" name="email" id="email" class="form-control" >
	   </div>
		<div class="form-group">
			<label>Şifre</label>
		<input type="password" name="psw" id="psw" class="form-control" >
	   </div>
	   <div class="form-group">
		<label>Şifrenizi mi unuttunuz?</label><a href="">Şifremi unuttum</a>
		<button id="btnm" class="btn btn-primary mt-3 d-flex justify-content-end">Giriş</button>
	   </div>
	   <?php 
	   if($msg==""){

	   }
	   else{
	   		if($err==false){
	   		echo'<p class="alert alert-success mt-3" id="uyari">'. $msg.'</p>';
	   		}
	   		else{
	   		echo '<p class="alert alert-danger mt-3" id="uyari">'. $msg.'</p>';
	   		}
		}
	   ?>
	   
		</div>
		</div>
		</div>
	</form>
</body>
</html>
<script type="text/javascript">
	document.getElementById('uyari').style.display = 'block';
</script>