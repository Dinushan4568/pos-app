import { getFirestore, collection, getDocs } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

const db = getFirestore();
const inventoryCol = collection(db, "inventory");
const salesCol = collection(db, "sales");

async function renderDashboard() {
  // Fetch inventory
  const inventorySnap = await getDocs(inventoryCol);
  const inventory = inventorySnap.docs.map(doc => doc.data());
  // Fetch sales
  const salesSnap = await getDocs(salesCol);
  const sales = salesSnap.docs.map(doc => doc.data());

  // Calculate stats
  const totalItems = inventory.length;
  const totalStock = inventory.reduce((sum, item) => sum + item.sizes.reduce((s, sz) => s + sz.qty, 0), 0);
  const totalValue = inventory.reduce((sum, item) => sum + item.sizes.reduce((s, sz) => s + sz.qty * sz.price, 0), 0);
  const lowStock = inventory.filter(item => item.sizes.some(sz => sz.qty <= 2)).length;

  // Today's sales
  const today = new Date().toISOString().slice(0, 10);
  const todaysSales = sales.filter(s => (s.timestamp || '').slice(0, 10) === today);
  const todaysTotal = todaysSales.reduce((sum, s) => sum + (s.total || 0), 0);
  const todaysCount = todaysSales.reduce((sum, s) => sum + (s.cart ? s.cart.length : 0), 0);

  document.getElementById('dashboard-details').innerHTML = `
    <div style="display:flex;gap:24px;justify-content:center;margin-bottom:32px;flex-wrap:wrap;">
      <div style="background:#f7f8ff;border-radius:16px;padding:18px 32px;box-shadow:0 2px 8px #7b7dfa22;min-width:120px;text-align:center;">
        <div style="color:#3d32c9;font-weight:600;">Total Items</div>
        <div style="font-size:2rem;font-weight:bold;">${totalItems}</div>
      </div>
      <div style="background:#f7f8ff;border-radius:16px;padding:18px 32px;box-shadow:0 2px 8px #7b7dfa22;min-width:120px;text-align:center;">
        <div style="color:#3d32c9;font-weight:600;">Total Stock</div>
        <div style="font-size:2rem;font-weight:bold;">${totalStock}</div>
      </div>
      <div style="background:#f7f8ff;border-radius:16px;padding:18px 32px;box-shadow:0 2px 8px #7b7dfa22;min-width:120px;text-align:center;">
        <div style="color:#3d32c9;font-weight:600;">Total Value</div>
        <div style="font-size:2rem;font-weight:bold;">LKR ${totalValue.toLocaleString(undefined, {minimumFractionDigits:2})}</div>
      </div>
      <div style="background:#f7f8ff;border-radius:16px;padding:18px 32px;box-shadow:0 2px 8px #7b7dfa22;min-width:120px;text-align:center;">
        <div style="color:#3d32c9;font-weight:600;">Low Stock</div>
        <div style="font-size:2rem;font-weight:bold;">${lowStock}</div>
      </div>
    </div>
    <div style="background:#f7f8ff;border-radius:16px;padding:24px 0;text-align:center;box-shadow:0 2px 8px #7b7dfa22;margin-bottom:32px;">
      <div style="color:#3d32c9;font-weight:600;font-size:1.2rem;">Today's Sales</div>
      <div style="font-size:2rem;font-weight:bold;">LKR ${todaysTotal.toLocaleString(undefined, {minimumFractionDigits:2})}</div>
      <div style="color:#3d32c9;font-size:1.1rem;">(${todaysCount} items)</div>
    </div>
    <canvas id="stockChart" style="max-width:600px;margin:0 auto 32px auto;display:block;"></canvas>
    <canvas id="salesChart" style="max-width:600px;margin:0 auto 32px auto;display:block;"></canvas>
  `;

  // Draw stock chart (bar: item name vs total qty)
  const ctx1 = document.getElementById('stockChart').getContext('2d');
  const stockLabels = inventory.map(i => i.name);
  const stockData = inventory.map(i => i.sizes.reduce((s, sz) => s + sz.qty, 0));
  new window.Chart(ctx1, {
    type: 'bar',
    data: {
      labels: stockLabels,
      datasets: [{
        label: 'Stock Qty',
        data: stockData,
        backgroundColor: '#7b7dfa99',
        borderColor: '#3d32c9',
        borderWidth: 1
      }]
    },
    options: {responsive:true,plugins:{legend:{display:false}}}
  });

  // Draw sales chart (line: last 7 days sales)
  const ctx2 = document.getElementById('salesChart').getContext('2d');
  const days = Array.from({length:7},(_,i)=>{
    const d = new Date(); d.setDate(d.getDate()-6+i);
    return d.toISOString().slice(0,10);
  });
  const salesPerDay = days.map(day =>
    sales.filter(s => (s.timestamp||'').slice(0,10) === day).reduce((sum,s)=>sum+(s.total||0),0)
  );
  new window.Chart(ctx2, {
    type: 'line',
    data: {
      labels: days,
      datasets: [{
        label: 'Sales (LKR)',
        data: salesPerDay,
        fill: false,
        borderColor: '#ff5e62',
        backgroundColor: '#ff5e62',
        tension: 0.2
      }]
    },
    options: {responsive:true,plugins:{legend:{display:false}}}
  });
}

// Load Chart.js if not loaded
if (!window.Chart) {
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
  script.onload = renderDashboard;
  document.head.appendChild(script);
} else {
  renderDashboard();
}

// Dashboard logic will be added here
