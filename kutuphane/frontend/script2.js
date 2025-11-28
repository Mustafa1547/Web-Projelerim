
// Sayfa tamamen yüklendiğinde aşağıdaki fonksiyon çalışacak
document.addEventListener("DOMContentLoaded", () => {
    kitaplariYukle();    // Kitapları sunucudan çekip listele
    sepetiYukle();       // Sepeti başlat, boş sepet ve sıfırlı sayac

    // Kategori seçimi değişince kitapları tekrar yükle
    document.getElementById("categoryFilter").addEventListener("change", kitaplariYukle);
    // Sıralama seçimi değişince kitapları tekrar yükle
    document.getElementById("sortFilter").addEventListener("change", kitaplariYukle);
    // Arama kutusuna yazı girince kitapları filtrele ve göster
    document.getElementById("searchInput").addEventListener("input", kitaplariYukle);
});

// Kitapları backend'den çeken, filtreleyip sıralayan ve gösteren async fonksiyon
async function kitaplariYukle() {
    const kategori = document.getElementById("categoryFilter").value;  // Kategori seçimi
    const siralama = document.getElementById("sortFilter").value;      // Sıralama seçimi
    const arama = document.getElementById("searchInput").value.toLocaleLowerCase('tr-TR') || "";  // Arama kelimesi

    let url = "../backend/kitaplar.php";  // Backend dosya yolu
    if (kategori) {
        // Eğer kategori seçiliyse, URL parametresi olarak ekle
        url += `?tur=${encodeURIComponent(kategori)}`;
    }

    try {
        const res = await fetch(url); // Backend'den kitap verisini al
        
        if (!res.ok) throw new Error("Ağ hatası: " + res.status);  // Hata varsa yaz
        
        const data = await res.json();  // JSON formatına çevir

        if (!Array.isArray(data)) {
            // Eğer veri dizi değilse boş liste göster
            kitaplariGoster([]);
            return;
        }

        // Arama terimine göre kitapları filtrele (kitap adı veya yazar adı içeriyorsa)
        const kitaplar = data.filter(kitap => {
            const kitapAdi = kitap.kitap_adi.toLocaleLowerCase('tr-TR');
            const yazarAdi = kitap.yazar.toLocaleLowerCase('tr-TR');
            return kitapAdi.includes(arama) || yazarAdi.includes(arama);
        });

        // Sıralama işlemi
        if (siralama === "title") {
            kitaplar.sort((a, b) => a.kitap_adi.localeCompare(b.kitap_adi, 'tr-TR'));
        } else if (siralama === "author") {
            kitaplar.sort((a, b) => a.yazar.localeCompare(b.yazar, 'tr-TR'));
        }

        // Filtrelenmiş ve sıralanmış kitapları 
        kitaplariGoster(kitaplar);
    } catch (err) {
        // Hata varsa konsola yazdır ve kullanıcıya hata mesajı göster
        console.error("Kitaplar yüklenemedi:", err);
        document.getElementById("bookList").innerHTML = `<p class="text-danger">Kitaplar yüklenirken hata oluştu.</p>`;
    }
}

// Kitapları ekranda Bootstrap kartlarıyla gösteren fonksiyon
function kitaplariGoster(kitaplar) {
    const liste = document.getElementById("bookList");  // Kitapların listeleneceği container
    liste.innerHTML = "";  // Önce içeriği temizle

    // Eğer kitap listesi boşsa kullanıcıya mesaj göster
    if (kitaplar.length === 0) {
        liste.innerHTML = `<p class="text-muted">Hiç kitap bulunamadı.</p>`;
        return;
    }

    // Her kitap için Bootstrap kartı oluştur
    kitaplar.forEach(kitap => {
        const kitapHTML = `
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="img/${kitap.resim || 'default.jpg'}" 
                         class="card-img-top" 
                         alt="${kitap.kitap_adi}">
                    <div class="card-body">
                        <p><strong>Kitap Adı:</strong> ${kitap.kitap_adi}</p>
                        <p><strong>Yazar:</strong> ${kitap.yazar}</p>
                        <p><strong>Tür:</strong> ${kitap.tur}</p>
                        <p><strong>Sayfa Sayısı:</strong> ${kitap.sayfa_sayisi}</p>
                    </div>
                    <div class="card-footer text-end bg-transparent border-top-0">
                        <button class="btn btn-primary btn-sm" 
                                onclick="sepeteEkle(${kitap.id}, '${kitap.kitap_adi}', '${kitap.resim || 'default.jpg'}')">
                            Ödünç Al
                        </button>
                    </div>
                </div>
            </div>
        `;
        liste.insertAdjacentHTML("beforeend", kitapHTML);
    });
}






// Kitapları Sepette Gösterme

// Sepet içeriğini tutan global nesne
let sepet = {};

// Sepeti sıfırlayan fonksiyon
function sepetiYukle() {
    sepet = {};  // Sepet objesini boşalt
    // Sepetteki toplam kitap sayısını gösteren elementi sıfırla
    document.getElementById("cart-count").textContent = 0;
    sepetiGoster(); // Sepetin boş olduğunu arayüzde göster
}


function sepeteEkle(kitapId, kitapAdi, kitapResmi) {
    kitapId = String(kitapId);  // Kitap ID'sini stringe çevir (objede anahtar olarak kullanılacak)
    
    // Eğer sepet içinde bu kitap yoksa yeni kayıt oluştur
    if (!sepet[kitapId]) {
        sepet[kitapId] = { adet: 0, kitap_adi: kitapAdi, resim: kitapResmi };
    }

    // Kitap adedini 1 artır
    sepet[kitapId].adet += 1;

    // Sepette toplam kaç kitap var hesapla (tüm adetlerin toplamı)
    const toplamKitap = Object.values(sepet).reduce((toplam, item) => toplam + item.adet, 0);
    
    // Sepet sayısını arayüzde güncelle
    document.getElementById("cart-count").textContent = toplamKitap;

    // Sepetin güncel içeriğini göster
    sepetiGoster();
}

// Sepet dropdown menüsünü güncelleyen fonksiyon
function sepetiGoster() {
    const dropdownMenu = document.getElementById("cart-dropdown-menu"); // Sepet dropdown elementi
    dropdownMenu.innerHTML = ""; // Önce içeriği temizle

    // Eğer sepet boşsa kullanıcıya bilgi göster ve ödünç alma butonunu devre dışı bırak
    if (Object.keys(sepet).length === 0) {
        dropdownMenu.innerHTML = `
            <li><p class="dropdown-item text-muted">Sepetiniz boş</p></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item btn btn-primary disabled">Ödünç Al</button></li>
        `;
        return;  // İşlem burada biter
    }

    // Sepetteki her kitap için liste öğesi oluştur
    for (const kitapId in sepet) {
        const { kitap_adi, resim, adet } = sepet[kitapId];  // Kitap detaylarını al
        
        dropdownMenu.insertAdjacentHTML("beforeend", `
            <li class="dropdown-item d-flex align-items-center">
                <img src="img/${resim || 'default.jpg'}" 
                     style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;" 
                     alt="${kitap_adi}">
                <div>
                    <span>${kitap_adi}</span>
                    <span class="badge bg-primary ms-2">${adet}</span>  <!-- Kitap adedi -->
                </div>
            </li>
        `);
    }

    // En sona ödünç alma butonunu aktif şekilde ekle
    dropdownMenu.insertAdjacentHTML("beforeend", `
        <li><hr class="dropdown-divider"></li>
        <li><button class="dropdown-item btn btn-primary" onclick="oduncAl()">Ödünç Al</button></li>
    `);
}








//Ödünç Alma Kısmı
// Ödünç alma işlemi için backend'e veri gönderen async fonksiyon
// Kitapları sepete ekledikten sonra ödünç alma işlemini başlatan asenkron fonksiyon
async function oduncAl() {
    // Eğer sepet tanımlı değilse ya da içinde hiç kitap yoksa kullanıcıyı uyar ve işlemi durdur
    if (!sepet || Object.keys(sepet).length === 0) {
        alert("Sepetiniz boş, lütfen kitap ekleyin!");
        return;
    }

    // Sepetteki kitapları, kitap_id ve adet bilgileriyle birlikte bir dizi haline getir
    const kitaplar = Object.keys(sepet).map(kitapId => ({
        kitap_id: kitapId,                     // Kitabın benzersiz ID'si
        adet: sepet[kitapId].adet              // Sepetteki adedi
    }));
 

    try {

        // Fetch API ile backend'e POST isteği gönder
        const response = await fetch(`../backend/oduncAl.php`, {
            method: "POST",                         // HTTP metodu: POST
            headers: {
                "Content-Type": "application/json"  // Gönderilen verinin türü JSON
            },
            body: JSON.stringify(kitaplar)          // Sepet verilerini JSON formatında gönder
        });


        // Eğer yanıt başarısızsa (örn. 404, 500 vb.) hata fırlat
        if (!response.ok) {
            if (response.status === 404) {
                // 404 hatası: dosya bulunamadı
                throw new Error("Sunucu hatası: 404 - Backend dosyası bulunamadı.");
            }
            // Diğer HTTP hataları
            throw new Error(`Sunucu hatası: ${response.status} ${response.statusText}`);
        }

        // Yanıttaki JSON verisini çözümle
        const data = await response.json();

        // Sunucudan success:true geldiyse kullanıcıya başarı mesajı göster
        if (data.success) {
            alert("Ödünç alma işlemi başarılı!");
            sepetiYukle(); // Sepeti sıfırla veya yeniden yükle
        } else {
            // Hata mesajı göster (gelen JSON içindeki hata varsa onu göster)
            alert("Hata: " + (data.hata || "Bilinmeyen hata"));
        }
    } catch (error) {
        // Sunucu bağlantı hataları veya yukarıdaki throw edilen hatalar burada yakalanır
        console.error("Hata detayları:", error);
        alert("Sunucu hatası: " + error.message);
    }
}








