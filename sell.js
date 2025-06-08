import { getFirestore, collection, addDoc, getDocs, updateDoc, deleteDoc, doc } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

const db = getFirestore();
const inventoryCol = collection(db, "inventory");
const salesCol = collection(db, "sales");

let cart = [];
let total = 0;

const sellInventoryList = document.getElementById('sell-inventory-list');
const sellCart = document.getElementById('sell-cart');
const sellTotal = document.getElementById('sell-total');
const sellCheckout = document.getElementById('sell-checkout');

let inventory = [];

async function fetchInventory() {
  const snapshot = await getDocs(inventoryCol);
  inventory = snapshot.docs.map(doc => ({ ...doc.data(), id: doc.id }));
  renderSellInventory();
}

function renderSellInventory() {
  sellInventoryList.innerHTML = inventory.map(item =>
    item.sizes.map(sz =>
      `<li>
        <b>${item.name}</b> (${item.brand}) - ${sz.size} | Qty: ${sz.qty} | $${sz.price}
        <button onclick="addToCart('${item.id}','${sz.size}')">Add</button>
      </li>`
    ).join('')
  ).join('');
}

window.addToCart = function(itemId, size) {
  const item = inventory.find(i => i.id === itemId);
  const sz = item.sizes.find(s => s.size === size);
  if (sz.qty <= 0) return alert('Out of stock!');
  cart.push({ itemId, name: item.name, brand: item.brand, size, price: sz.price });
  total += sz.price;
  renderCart();
};

function renderCart() {
  sellCart.innerHTML = cart.map((c, i) =>
    `<li>${c.name} (${c.brand}) - ${c.size} | $${c.price} <button onclick="removeFromCart(${i})">Remove</button></li>`
  ).join('');
  sellTotal.textContent = total.toFixed(2);
}

window.removeFromCart = function(idx) {
  total -= cart[idx].price;
  cart.splice(idx, 1);
  renderCart();
};

sellCheckout.onclick = async function() {
  if (!cart.length) return alert('Cart empty!');
  await addDoc(salesCol, {
    cart,
    total,
    timestamp: new Date().toISOString()
  });
  // Update inventory quantities
  for (const c of cart) {
    const item = inventory.find(i => i.id === c.itemId);
    const sz = item.sizes.find(s => s.size === c.size);
    sz.qty -= 1;
    await updateDoc(doc(db, "inventory", c.itemId), { sizes: item.sizes });
  }
  cart = [];
  total = 0;
  renderCart();
  await fetchInventory();
  alert('Sale completed!');
};

fetchInventory();
