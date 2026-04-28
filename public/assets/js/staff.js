function loadTables() {
  fetch("/buffet-chay/staff/get-tables-list")
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      var html = "";

      data.forEach(function (t) {
        html += `
          <div class="table ${t.status}" onclick="loadOrders(${t.id})">
            ${t.table_number}
          </div>
        `;
      });

      document.getElementById("tables").innerHTML = html;
    });
}

function loadOrders(tableId) {
  fetch("/buffet-chay/staff/get-orders-by-table?table_id=" + tableId)
    .then(function (r) {
      return r.json();
    })
    .then(function (data) {
      var html = "";

      if (data.length === 0) {
        html = "<p>Không có món nào 🍃</p>";
      } else {
        data.forEach(function (item, index) {
          html += `
            <div class="order-card">
              #${index + 1} - ${item.name} x${item.quantity}
            </div>
          `;
        });
      }

      document.getElementById("orders").innerHTML = html;
    });
}

// load lần đầu
loadTables();

// auto refresh bàn mỗi 3s
setInterval(loadTables, 3000);
let currentTable = null;

function loadOrders(tableId) {
  currentTable = tableId;

  fetch("/buffet-chay/staff/get-orders-by-table?table_id=" + tableId)
    .then((r) => r.json())
    .then((data) => {
      var html = "";

      data.forEach(function (item, index) {
        html += `
          <div class="order-card">
            #${index + 1} - ${item.name} x${item.quantity}
          </div>
        `;
      });

      document.getElementById("orders").innerHTML = html;
    });

  let currentTable = null;

  function loadTables() {
    fetch("/buffet-chay/staff/get-tables-list")
      .then((r) => r.json())
      .then((data) => {
        let html = "";

        data.forEach((t) => {
          let selected = currentTable == t.id ? "selected" : "";

          html += `
          <div class="table ${t.status} ${selected}" onclick="loadOrders(${t.id})">
            ${t.table_number}
          </div>
        `;
        });

        document.getElementById("tables").innerHTML = html;
      });
  }

  function loadOrders(tableId) {
    currentTable = tableId;

    fetch("/buffet-chay/staff/get-orders-by-table?table_id=" + tableId)
      .then((r) => r.json())
      .then((data) => {
        let html = "";

        if (data.length === 0) {
          html = "<p>Không có món 🍃</p>";
        } else {
          data.forEach((item, i) => {
            html += `
            <div class="order-card">
              #${i + 1} - ${item.name} x${item.quantity}
            </div>
          `;
          });
        }

        document.getElementById("orders").innerHTML = html;
      });
  }

  loadTables();
  setInterval(loadTables, 3000);
}
