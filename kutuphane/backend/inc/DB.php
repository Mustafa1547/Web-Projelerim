<?php
class Database {
    private $baglanti; // MySQL bağlantı nesnesini tutar

    // Yapıcı metod: mysqli nesnesi dışarıdan alınır ve sınıfa atanır
    public function __construct(mysqli $baglanti) {
        $this->baglanti = $baglanti;   // Bağlantı sınıfın değişkenine atanır
    }

    // Yıkıcı metod: nesne yok edilirken bağlantı kapatılır
    public function __destruct(){
        if ($this->baglanti) { // Eğer bağlantı varsa
            $this->baglanti->close(); // Bağlantıyı kapat
        }
    }

    // Veri ekleme fonksiyonu: verilen tabloya $formData dizisi ile kayıt ekler
    public function veriEkle($formData, $table) {
        try {
            $columns = array_keys($formData); // Dizideki anahtarlar = kolon isimleri
            $values = array_values($formData); // Dizideki değerler = kolonlara girilecek değerler

            // Eğer şifre alanı varsa, güvenlik için hash'le
            if (isset($formData['sifre'])) {
                $formData['sifre'] = password_hash($formData['sifre'], PASSWORD_DEFAULT);
                $values = array_values($formData); // Güncellenen değerleri tekrar al
            }

            $columnsStr = implode(",", $columns); // Kolon isimlerini virgülle birleştir
            $placeholders = implode(",", array_fill(0, count($values), "?")); // Her değer için ? oluştur

            $sql = "INSERT INTO $table ($columnsStr) VALUES ($placeholders)"; // SQL sorgusu hazırla
            $stmt = $this->baglanti->prepare($sql); // Sorguyu hazırla

            $types = str_repeat("s", count($values)); // Parametre tipleri, tümü string olarak ayarlanıyor
            $stmt->bind_param($types, ...$values); // Parametreleri bağla

            if ($stmt->execute()) { // Sorgu çalıştırılırsa
                $stmt->close(); // Statement kapatılır
                return true; // Başarılı dönüş
            } else {
                $stmt->close(); // Statement kapatılır
                return false; // Başarısız dönüş
            }
        } catch (Exception $e) {
            return false; // Hata durumunda false döner
        }
    }

    // Tüm kitap verilerini çekme fonksiyonu, tablo sabit 'tblkitap'
    public function tumVeriCek() {
        try {
            $sql = "SELECT * FROM tblkitap"; // Kitapların tümü seçilir
            $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır

            if (!$stmt) { // Hazırlama başarısızsa hata fırlatılır
                throw new Exception("Sorgu hazırlanamadı: " . $this->baglanti->error);
            }

            $stmt->execute(); // Sorgu çalıştırılır
            $result = $stmt->get_result(); // Sonuç alınır
            return $result; // Sonuç döner

        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage(); // Hata ekrana yazılır
            return false; // Hata durumunda false döner
        }
    }

    // Çoklu arama fonksiyonu, kitap adında arama yapar
    public function cokluArama($table, $aranan) {
        try {
            $sql = "SELECT * FROM $table WHERE kitap_adi LIKE ?"; // Arama sorgusu, LIKE ile
            $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır

            if (!$stmt) { // Hazırlama başarısızsa hata fırlatılır
                throw new Exception("Sorgu hazırlanamadı: " . $this->baglanti->error);
            }

            $like = "%$aranan%"; // Arama değeri %aranan% şeklinde yapılır
            $stmt->bind_param("s", $like); // Parametre bağlanır
            $stmt->execute(); // Sorgu çalıştırılır

            return $stmt->get_result(); // Sonuç döner

        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage(); // Hata ekrana yazılır
            return false; // Hata durumunda false döner
        }
    }

    // Belirli kolon verilerini çekme fonksiyonu, id şartlı veya tümü
    public function kolonVeriCek($table, $kolon, $sart){
        try {
            if ($sart !== null) { // Eğer şart verilmişse
                $sql = "SELECT $kolon FROM $table WHERE id = ?"; // Şartlı sorgu
                $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır
                $stmt->bind_param("i", $sart); // Şart parametresi bağlanır (integer)
            } else { // Şart yoksa tüm veriler alınır
                $sql = "SELECT $kolon FROM $table"; // Şartsız sorgu
                $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır
            }
            $stmt->execute(); // Sorgu çalıştırılır
            return $stmt->get_result(); // Sonuç döner
        } catch (Exception $e) {
            return false; // Hata durumunda false döner
        }
    }

    // Veri güncelleme fonksiyonu: id şartına göre güncelleme yapar
    public function veriGuncelle($formData, $table, $sart) {
        try {
            $setStr = []; // Güncellenecek kolonlar ve parametre yer tutucular
            $values = []; // Parametre değerleri

            foreach ($formData as $column => $value) {
                $setStr[] = "$column = ?"; // Her kolon için 'kolon = ?' oluşturulur
                $values[] = $value; // Değer parametre listesine eklenir
            }

            $setClause = implode(", ", $setStr); // Tüm kolonlar virgülle ayrılarak birleştirilir

            $sql = "UPDATE $table SET $setClause WHERE id = ?"; // Güncelleme sorgusu id şartlı
            $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır

            if (!$stmt) { // Hazırlama başarısızsa hata fırlatılır
                throw new Exception("Hazırlama hatası: " . $this->baglanti->error);
            }

            $values[] = $sart; // id şartı parametre listesine eklenir
            $types = str_repeat("s", count($values)); // Tüm parametreler string olarak ayarlanıyor 

            $stmt->bind_param($types, ...$values); // Parametreler bağlanır

            if ($stmt->execute()) { // Sorgu başarılı ise
                //echo "Veri başarıyla güncellendi."; // Başarı mesajı
            } else {
                echo "Sorgu hatası: " . $stmt->error; // Hata mesajı
            }

            $stmt->close(); // Statement kapatılır
        } catch (Exception $e) {
            echo "Hata: " . $e->getMessage(); // Hata mesajı ekrana yazılır
        }
    }

    // Veri silme fonksiyonu: id'ye göre kayıt siler
    public function veriSil($table, $sart) {
        try {
            if (empty($sart) || !is_numeric($sart)) { // Eğer şart boş veya numeric değilse
                return -2; // Hatalı parametre kodu döner
            }

            $sql = "DELETE FROM $table WHERE id = ?"; // Silme sorgusu
            $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır

            if ($stmt === false) { // Hazırlama başarısızsa hata döner
                return "Error preparing statement: " . $this->baglanti->error;
            }

            $stmt->bind_param("i", $sart); // id parametresi bağlanır (integer)

            if ($stmt->execute()) { // Sorgu çalıştırılır
                if ($stmt->affected_rows > 0) { // Eğer kayıt silindiyse
                    return 1; // Başarı kodu
                } else {
                    return 0; // Silinecek kayıt yok
                }
            } else {
                return "Execution failed: " . $stmt->error; // Çalıştırma hatası
            }
        } catch (Exception $e) {
            return "Exception: " . $e->getMessage(); // Genel hata mesajı
        }
    }

    // Kullanıcı girişi fonksiyonu: kullanıcı adı ve şifreyi kontrol eder
    public function kullaniciGirisi($table, $kullaniciAdiKolon, $sifreKolon, $gelenKullaniciAdi, $gelenSifre) {
        try {
            $sql = "SELECT * FROM $table WHERE $kullaniciAdiKolon = ?"; // Kullanıcı adı ile sorgu
            $stmt = $this->baglanti->prepare($sql); // Sorgu hazırlanır

            if (!$stmt) { // Hazırlama başarısızsa hata fırlatılır
                throw new Exception("Sorgu hazırlanamadı: " . $this->baglanti->error);
            }

            $stmt->bind_param("s", $gelenKullaniciAdi); // Kullanıcı adı parametresi bağlanır
            $stmt->execute(); // Sorgu çalıştırılır
            $result = $stmt->get_result(); // Sonuç alınır

            if ($result->num_rows === 1) { // Eğer kullanıcı bulunduysa
                $kullanici = $result->fetch_assoc(); // Kullanıcı bilgileri alınır

                if ($gelenSifre === $kullanici[$sifreKolon]) { // Şifre karşılaştırması (hash değil)
                    return  $kullanici["rol"]; // Giriş başarılı, rol döner
                } else {
                    return -1; // Şifre yanlış
                }
            } else {
                return 0; // Kullanıcı bulunamadı
            }
        } catch (Exception $e) {
            return "Hata: " . $e->getMessage(); // Hata mesajı döner
        }
    }

    // Belirli kolona göre veri çekme fonksiyonu: filtreye göre
    public function veriCek($table, $kolonlar = "*", $tur, $filtreKolonu){
        try {
            $sql = "SELECT $kolonlar FROM $table WHERE $filtreKolonu = ?"; // Sorgu hazırlanır

            $stmt = $this->baglanti->prepare($sql); // Hazırla
            $stmt->bind_param("s", $tur); // Parametre bağla
            $stmt->execute(); // Çalıştır
            $result = $stmt->get_result(); // Sonucu al
            return $result; // Döndür
        } 
        catch (Exception $e) {
            return false; // Hata durumunda false döner
        }
    }
}
?>
