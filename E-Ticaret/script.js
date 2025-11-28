let cart = [];  // Sepet verisi

// Sepete ürün ekleme fonksiyonu
function addProduct(productName, productPrice, sizeId) {
  const size = document.getElementById(sizeId).value;
  const product = {
    name: productName,
    price: productPrice,
    size: size
  };

  cart.push(product);
  updateCart();
}

// Sepeti güncelleme fonksiyonu
function updateCart() {
  // Sepet öğelerini navbar'da güncelleme
  let cartItems = document.getElementById("cart-items");
  cartItems.innerHTML = '';  // Önceki içerikleri temizle

  // Sepet öğelerini listeleme
  cart.forEach((item, index) => {
    cartItems.innerHTML += `<p>${item.name} - ${item.size} - ${item.price} TL</p>`;
  });

  // Sepet sayısını navbar'da güncelleme
  document.getElementById("count").innerText = cart.length;

  // Sepet detaylarını gösterme
  document.getElementById("cart-details").classList.remove("d-none");
}
