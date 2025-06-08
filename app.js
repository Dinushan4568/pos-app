let cart = [];
let total = 0;

function addToCart(product, price) {
  cart.push({ product, price });
  total += price;
  updateCart();
}

function updateCart() {
  const cartList = document.getElementById('cart');
  cartList.innerHTML = '';
  cart.forEach(item => {
    const li = document.createElement('li');
    li.textContent = `${item.product} - $${item.price}`;
    cartList.appendChild(li);
  });
  document.getElementById('total').textContent = total;
}

function checkout() {
  if (cart.length === 0) {
    alert("Cart is empty!");
    return;
  }

  db.collection("sales").add({
    cart: cart,
    total: total,
    timestamp: new Date()
  })
  .then(() => {
    alert("Sale saved!");
    cart = [];
    total = 0;
    updateCart();
  })
  .catch(error => {
    console.error("Error saving sale: ", error);
  });
}
