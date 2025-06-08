// Inventory CRUD logic for items with multiple sizes
// Uses localStorage for demo; switch to Firebase for production

let inventory = JSON.parse(localStorage.getItem('inventory') || '[]');
let editingIndex = null;
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

form.onsubmit = function(e) {
  e.preventDefault();
  const name = document.getElementById('item-name').value.trim();
  const brand = document.getElementById('item-brand').value.trim();
  const category = document.getElementById('item-category').value.trim();
  const imageInput = document.getElementById('item-image');
  let image = '';
  if (imageInput.files[0]) {
    const reader = new FileReader();
    reader.onload = function(evt) {
      image = evt.target.result;
      saveItem(name, brand, category, sizes.slice(), image);
    };
    reader.readAsDataURL(imageInput.files[0]);
    return;
  }
  saveItem(name, brand, category, sizes.slice(), image);
};

function saveItem(name, brand, category, sizesArr, image) {
  if (editingIndex !== null) {
    inventory[editingIndex] = { name, brand, category, sizes: sizesArr, image, added: new Date().toLocaleString() };
    editingIndex = null;
  } else {
    inventory.push({ name, brand, category, sizes: sizesArr, image, added: new Date().toLocaleString() });
  }
  localStorage.setItem('inventory', JSON.stringify(inventory));
  sizes = [];
  renderSizes();
  form.reset();
  renderInventory();
}

function renderInventory() {
  inventoryList.innerHTML = inventory.map((item, idx) =>
    item.sizes.map((sz, sidx) =>
      `<tr>
        ${sidx === 0 ? `<td rowspan="${item.sizes.length}">${item.name}</td><td rowspan="${item.sizes.length}">${item.brand}</td><td rowspan="${item.sizes.length}">${item.category}</td>` : ''}
        <td>${sz.size}</td>
        <td>${sz.qty}</td>
        <td>$${sz.price}</td>
        ${sidx === 0 ? `<td rowspan="${item.sizes.length}">${item.image ? `<img src="${item.image}" style="height:40px;">` : ''}</td><td rowspan="${item.sizes.length}">${item.added || ''}</td><td rowspan="${item.sizes.length}">
          <button onclick="editItem(${idx})">Edit</button>
          <button onclick="deleteItem(${idx})">Delete</button>
        </td>` : ''}
      </tr>`
    ).join('')
  ).join('');
}

window.editItem = function(idx) {
  const item = inventory[idx];
  document.getElementById('item-name').value = item.name;
  document.getElementById('item-brand').value = item.brand;
  document.getElementById('item-category').value = item.category;
  sizes = item.sizes.slice();
  renderSizes();
  editingIndex = idx;
};

window.deleteItem = function(idx) {
  if (confirm('Delete this item?')) {
    inventory.splice(idx, 1);
    localStorage.setItem('inventory', JSON.stringify(inventory));
    renderInventory();
  }
};

// Initial render
renderInventory();
