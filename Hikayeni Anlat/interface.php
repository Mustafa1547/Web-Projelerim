<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HikayeVer - Hikayeni Paylaş</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-title">
            Hikayeni Yaz
        </div>
        <div class="user-buttons">
             <a href="login.php" class="btn-login btn-light btn-sm">Giriş Yap</a>
             <a class="btn-register btn-light btn-sm ">Kayıt Ol</a>
        </div>
    </nav>

    <div class="container">
        <header class="d-flex justify-content-center flex-column align-items-center text-center my-4">
            <h1 class="text-white">📚 Hikayeni Anlat</h1>
            <p class="subtitle text-white">Hayallerini kelimelerle buluştur</p>
        </header>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number" id="totalStories">42</div>
                <div class="stat-label">Toplam Hikaye</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalAuthors">15</div>
                <div class="stat-label">Yazar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="totalComments">128</div>
                <div class="stat-label">Yorum</div>
            </div>
        </div>

        <div class="tabs d-flex justify-content-center p-3 mb-4">
            <button class="tab active" onclick="showSection('stories')">📖 Hikayeleri Oku</button>
            <button class="tab" onclick="showSection('write')">✍️ Hikayeni Yaz</button>
            <button class="tab" onclick="showSection('categories')">🏷️ Kategoriler</button>
        </div>

        <!-- Hikaye Listesi -->
        <div id="stories" class="content-section active">
            <div id="storiesContainer">
               
            </div>
        </div>

        <!-- Hikaye Yazma Formu -->
        <div id="write" class="content-section">
            <h2 style="margin-bottom: 25px; color: #333;">✨ Yeni Hikaye Yaz</h2>
            <form id="storyForm">
                <div class="form-group">
                    <label for="authorName" class="form-label">👤 Yazar Adı:</label>
                    <input type="text" id="authorName" name="authorName" class="form-control" required placeholder="Adınızı girin...">
                </div>

                <div class="form-group">
                    <label for="storyTitle" class="form-label">📝 Hikaye Başlığı:</label>
                    <input type="text" class="form-control" id="storyTitle" name="storyTitle" required placeholder="Hikanenizin başlığını girin...">
                </div>

                <div class="form-group">
                    <label for="storyCategory" class="form-label">🏷️ Kategori:</label>
                    <select class="form-select" id="storyCategory" name="storyCategory" required>
                        <option value="">Kategori seçin...</option>
                        <option value="Romantik">💕 Romantik</option>
                        <option value="Macera">🗺️ Macera</option>
                        <option value="Bilim Kurgu">🚀 Bilim Kurgu</option>
                        <option value="Korku">👻 Korku</option>
                        <option value="Komedi">😄 Komedi</option>
                        <option value="Drama">🎭 Drama</option>
                        <option value="Fantastik">🧙‍♂️ Fantastik</option>
                        <option value="Gerçek Hayat">🌍 Gerçek Hayat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="storyContent" class="form-label">📖 Hikaye İçeriği:</label>
                    <textarea id="storyContent" class="form-control" name="storyContent" required placeholder="Hikanenizi buraya yazın... Hayal gücünüzü serbest bırakın!"></textarea>
                </div>

                <button type="submit" class="btn-share mt-3" id="storyShare" name="storyShare">🚀 Hikayemi Paylaş</button>
            </form>
        </div>

        <!-- Kategoriler -->
        <div id="categories" class="content-section">
            <h2 style="margin-bottom: 25px; color: #333;">🏷️ Kategoriler</h2>
            <div id="categoryStats">
                <!-- Kategori istatistikleri buraya gelecek -->
            </div>
        </div>
    </div>

    <!-- JS Bağlantıları -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>


</body>
</html>
