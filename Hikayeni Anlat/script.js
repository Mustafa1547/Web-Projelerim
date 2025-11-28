document.addEventListener("DOMContentLoaded", () => {
    hikayeleriYukle(); // Sayfa açıldığında hikayeleri getir

    document.getElementById("storyForm").addEventListener("submit", async function (e) {
        e.preventDefault();

        const storyData = {
            authorName: document.getElementById("authorName").value.trim(),
            storyTitle: document.getElementById("storyTitle").value.trim(),
            storyCategory: document.getElementById("storyCategory").value,
            storyContent: document.getElementById("storyContent").value.trim()
        };

        if (Object.values(storyData).some(value => !value)) {
            alert("Lütfen tüm alanları doldurun.");
            return;
        }

        try {
            const response = await fetch("story_add.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(storyData)
            });

            const result = await response.json();

            if (result.success) {
                alert("Hikaye başarıyla eklendi.");
                this.reset();
                hikayeleriYukle();
                showSection('stories');
            } else {
                alert("Hata: " + result.message);
            }
        } catch (error) {
            console.error("Sunucu hatası:", error);
            alert("Bir hata oluştu.");
        }
    });
});

async function hikayeleriYukle() {
    try {
        const response = await fetch("story_list.php");

        if (!response.ok) throw new Error("Hikaye listesi alınamadı.");

        const data = await response.json();

        if (!Array.isArray(data)) {
            document.getElementById("storiesContainer").innerHTML = "<p class='text-muted'>Henüz hiç hikaye yok.</p>";
            return;
        }

        hikayeleriGoster(data.reverse());
    } catch (error) {
        console.error("Hikaye verisi alınırken hata oluştu:", error);
        document.getElementById("storiesContainer").innerHTML = "<p class='text-danger'>Hikayeler yüklenemedi.</p>";
    }
}

function hikayeleriGoster(hikayeler) {
    const container = document.getElementById("storiesContainer");
    container.innerHTML = "";

    hikayeler.forEach(hikaye => {
        const card = document.createElement("div");
        card.className = "card my-3 shadow-sm";

        card.innerHTML = `
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="story-title mb-0 fw-bold text-dark">${escapeHtml(hikaye.story_title)}</h5>
                <span class="story-category">${escapeHtml(hikaye.category)}</span>
            </div>
            <div class="card-body">
                <p class="author-name mb-1" style="font-size: 0.9rem;">
                    👤 ${escapeHtml(hikaye.author_name)} |
                    <small class="story-date" style="font-size: 1rem;">
                    📅 ${formatDate(hikaye.story_date)}
                    </small>
                </p>
                <p class="story-content mt-3">${nl2br(escapeHtml(hikaye.story_content))}</p>
            </div>
            <div class="story-actions d-flex flex-column gap-2">
                <div>
                    <button class="story_like btn btn-outline-danger" data-id="${hikaye.id}" aria-pressed="false">
                        <span class="heart-icon">${hikaye.likes > 0 ? "❤️" : "🤍"}</span> 
                        <span class="like-count">${hikaye.likes}</span>
                    </button>
                  <button class="story_comment btn btn-outline-secondary">
                   💬 ${hikaye.comments || 0} Yorum
                  </button>
           </div>

          <!-- Yorum kutusu -->
          <div class="comment-box" style="display: none;">
             <div style="display: flex; gap: 10px; margin-top: 10px;">
                <input type="text" class="form-control comment-input" placeholder="Yorumunuzu yazın...">
          <button class="btn-submit btn-sm">Gönder</button>
          </div>
          </div>
        `;

         //Like Bölümü
         
        const likeBtn = card.querySelector(".story_like");

        likeBtn.addEventListener("click", async (e) => {
            const button = e.currentTarget;
            const storyId = button.dataset.id;
            const isLiked = button.classList.contains("liked");

            try {
                const response = await fetch("like_story.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        story_id: storyId,
                        action: isLiked ? "unlike" : "like"
                    })
                });

                const result = await response.json();

                if (result.success) {
                    const countSpan = button.querySelector(".like-count");
                    const heartIcon = button.querySelector(".heart-icon");

                    countSpan.textContent = result.newLikes;

                    // Toggle
                    if (isLiked) {
                        button.classList.remove("liked");
                        heartIcon.textContent = "🤍";
                        button.setAttribute("aria-pressed", "false");
                    } else {
                        button.classList.add("liked");
                        heartIcon.textContent = "❤️";
                        button.setAttribute("aria-pressed", "true");
                    }
                }
            } catch (err) {
                console.error("Beğeni hatası:", err);
                alert("Bir hata oluştu.");
            }
        });

        // Yorum kutusunu açma/gizleme
        const commentBtn = card.querySelector(".story_comment");
        const commentBox = card.querySelector(".comment-box");

        commentBtn.addEventListener("click", () => {
            commentBox.style.display = commentBox.style.display === "none" ? "block" : "none";
        });

        container.appendChild(card);
    });
}

// Yardımcı fonksiyonlar
function formatDate(dateStr) {
    const tarih = new Date(dateStr);
    return tarih.toLocaleString("tr-TR");
}

function nl2br(str) {
    return str.replace(/\n/g, "<br>");
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function showSection(sectionName) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));

    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => tab.classList.remove('active'));

    document.getElementById(sectionName).classList.add('active');
    if(event && event.target) event.target.classList.add('active');
}

function showLoginModal() {

    const login=document.querySelectorAll(".btn-login");

    

}