import { getFirestore, collection, addDoc, getDocs, updateDoc, deleteDoc, doc } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

const db = getFirestore();
const inventoryCol = collection(db, "inventory");
const salesCol = collection(db, "sales");

let inventory = [];
let editingId = null;
let sizes = [];

const form = document.getElementById('inventory-form');
const sizesList = document.getElementById('sizes-list');
const inventoryList = document.getElementById('inventory-list');
const addSizeBtn = document.getElementById('add-size');

function renderSizes() {
  sizesList.innerHTML = sizes.map((s, i) =>
    `<span style="display:inline-block;background:#e3e6ff;padding:4px 10px;border-radius:8px;margin:2px 6px 2px 0;">
      ${s.size} | Qty: ${s.qty} | $${s.price}
      <button onclick="removeSize(${i})" style="margin-left:6px;background:#ff5e62;color:#fff;border:none;border-radius:4px;padding:0 6px;">x</button>
    </span>`
  ).join('');
}
window.removeSize = function(idx) {
  sizes.splice(idx, 1);
  renderSizes();
};

addSizeBtn.onclick = function() {
  const size = document.getElementById('item-size').value.trim();
  const qty = parseInt(document.getElementById('item-qty').value);
  const price = parseFloat(document.getElementById('item-price').value);
  if (!size || isNaN(qty) || isNaN(price)) return;
  sizes.push({ size, qty, price });
  renderSizes();
  document.getElementById('item-size').value = '';
  document.getElementById('item-qty').value = '';
  document.getElementById('item-price').value = '';
};

form.onsubmit = async function(e) {
  e.preventDefault();
  const name = document.getElementById('item-name').value.trim();
  const brand = document.getElementById('item-brand').value.trim();
  const category = document.getElementById('item-category').value.trim();
  const imageInput = document.getElementById('item-image');
  let image = '';
  if (imageInput && imageInput.files && imageInput.files[0]) {
    const reader = new FileReader();
    reader.onload = async function(evt) {
      image = evt.target.result;
      await saveItem(name, brand, category, sizes.slice(), image);
    };
    reader.readAsDataURL(imageInput.files[0]);
    return;
  }
  await saveItem(name, brand, category, sizes.slice(), image);
};

async function saveItem(name, brand, category, sizesArr, image) {
  if (editingId) {
    const itemRef = doc(db, "inventory", editingId);
    await updateDoc(itemRef, { name, brand, category, sizes: sizesArr, image });
    editingId = null;
  } else {
    await addDoc(inventoryCol, {
      name, brand, category, sizes: sizesArr, image, added: new Date().toLocaleString()
    });
  }
  sizes = [];
  renderSizes();
  form.reset();
  await fetchInventory();
}

async function fetchInventory() {
  const snapshot = await getDocs(inventoryCol);
  inventory = snapshot.docs.map(doc => ({ ...doc.data(), id: doc.id }));
  renderInventory();
}

function renderInventory() {
  if (!inventory.length) {
    inventoryList.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#888;padding:24px;">No items in inventory.</td></tr>';
    return;
  }
  inventoryList.innerHTML = inventory.map((item, idx) =>
    item.sizes && item.sizes.length ?
      item.sizes.map((sz, sidx) =>
        `<tr>
          ${sidx === 0 ? `<td rowspan="${item.sizes.length}">${item.name}</td><td rowspan="${item.sizes.length}">${item.brand}</td><td rowspan="${item.sizes.length}">${item.category}</td>` : ''}
          <td>${sz.size}</td>
          <td>${sz.qty}</td>
          <td>$${sz.price}</td>
          ${sidx === 0 ? `<td rowspan="${item.sizes.length}">${item.image ? `<img src="${item.image}" style="height:40px;">` : ''}</td><td rowspan="${item.sizes.length}">${item.added || ''}</td><td rowspan="${item.sizes.length}">
            <button onclick="editItem('${item.id}')">Edit</button>
            <button onclick="deleteItem('${item.id}')">Delete</button>
          </td>` : ''}
        </tr>`
      ).join('')
    : `<tr><td>${item.name}</td><td>${item.brand}</td><td>${item.category}</td><td colspan="6" style="text-align:center;color:#888;">No sizes</td></tr>`
  ).join('');
}

window.editItem = function(id) {
  const item = inventory.find(i => i.id === id);
  document.getElementById('item-name').value = item.name;
  document.getElementById('item-brand').value = item.brand;
  document.getElementById('item-category').value = item.category;
  sizes = item.sizes.slice();
  renderSizes();
  editingId = id;
};

window.deleteItem = async function(id) {
  if (confirm('Delete this item?')) {
    await deleteDoc(doc(db, "inventory", id));
    await fetchInventory();
  }
};

// Initial fetch
fetchInventory();
