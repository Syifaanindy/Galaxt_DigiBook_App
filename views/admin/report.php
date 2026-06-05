<?php
if (isset($_GET['type'])) {

    header('Content-Type: application/json');

    $conn = new mysqli("localhost","root","","galaxy_digibook");

    $type = $_GET['type'];

   if ($type === 'summary') {
      $result = $conn->query("
          SELECT 
              COALESCE(SUM(total_price),0) AS total_sales,
              COUNT(id) AS total_order,
              COALESCE(ROUND(AVG(total_price),0),0) AS avg_order
          FROM `transaction`
      ");

      echo json_encode($result->fetch_assoc());
      exit;
    }

    if ($type === 'monthly') {
        $result = $conn->query("
            SELECT 
                DATE_FORMAT(transaction_date,'%b') AS label,
                SUM(total_price) AS total
            FROM `transaction`
            GROUP BY MONTH(transaction_date)
            ORDER BY MONTH(transaction_date)
        ");

        $labels = [];
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['label'];
            $data[] = (int)$row['total'];
        }

        echo json_encode(["labels"=>$labels,"data"=>$data]);
        exit;
    }

    if ($type === 'category') {
        $result = $conn->query("
            SELECT 
                c.category_name AS label,
                SUM(t.total_price) AS total
            FROM `transaction` t
            JOIN books b ON t.book_id = b.id
            JOIN category c ON b.category_id = c.id
            GROUP BY c.id
            ORDER BY total DESC
        ");

        $labels = [];
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['label'];
            $data[] = (int)$row['total'];
        }

        echo json_encode(["labels"=>$labels,"data"=>$data]);
        exit;
    }

    if ($type === 'category_detail') {
        $category = $_GET['category'];

        $stmt = $conn->prepare("
            SELECT 
                DATE_FORMAT(t.transaction_date,'%b') AS label,
                SUM(t.total_price) AS total
            FROM `transaction` t
            JOIN books b ON t.book_id = b.id
            JOIN category c ON b.category_id = c.id
            WHERE c.category_name = ?
            GROUP BY MONTH(t.transaction_date)
            ORDER BY MONTH(t.transaction_date)
        ");

        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();

        $labels = [];
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $labels[] = $row['label'];
            $data[] = (int)$row['total'];
        }

        echo json_encode([
            "labels" => $labels,
            "data" => $data
        ]);
        exit;
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/admin/panel.css">
  <link rel="stylesheet" href="../../assets/css/admin/sidebar.css">
  <link rel="stylesheet" href="../../assets/css/admin/pagination.css"> 
  <style>
      
      .chart-container-monthly {
          position: relative; 
          height: 260px; 
          width: 100%;
      }
      
      
      .chart-container-category, .chart-container-detail {
          position: relative; 
          height: 220px; 
          width: 100%;
      }

      
      .modal-content-clean {
          background: #ffffff;
          padding: 20px;
          border-radius: 8px;
          margin-bottom: 20px;
          border: 1px solid rgba(0, 0, 0, 0.08);
          box-shadow: 0 4px 12px rgba(0,0,0,0.03);
      }

      .modal-dialog-scrollable .modal-body {
          overflow-y: auto;
          padding: 25px !important;
      }
  </style>
</head>

<body>

<div class="admin-layout">
<?php include 'partials/sidebar.php'; ?>

<main class="main-content">

<header class="topbar">
<h2>Report Dashboard</h2>
<p>Laporan Penjualan</p>
</header>
        <div class="dashboard">
        <section class="cards">
                        <article class="card">
                            <h3>Total Penjualan</h3>
                            <p id="totalSales"></p>
                        </article>
                        <article class="card">
                            <h3>Total Order</h3>
                            <p id="totalOrder"></p>
                        </article>
                        <article class="card">
                            <h3>Rata-Rata</h3>
                            <p id="avgOrder"></p>
                        </article>
            </section>
        </div>

<div class="charts">
    <div class="panel w-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Monthly Sales</h3>
            <button class="btn btn-primary btn-sm" onclick="openMonthlyDetailModal()">
                Lihat Detail Kategori <i class="fa fa-expand-alt ms-1"></i>
            </button>
        </div>
        <div class="chart-container-monthly">
            <canvas id="chartMonthly"></canvas>
        </div>
    </div>
</div>

<div class="modal fade" id="monthlyDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 90%;">
    <div class="modal-content" style="box-shadow: 0 10px 30px rgba(0,0,0,0.15); border:none;">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight: 700;">Detail Analisis & Tren Penjualan Kategori</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light">
        
        <div class="row">
            <div class="col-lg-6">
                <div class="modal-content-clean">
                    <h3 class="fs-5 mb-3" style="color: #333; font-weight: 700;">Category Sales</h3>
                    <div class="chart-container-category">
                        <canvas id="chartCategory"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="modal-content-clean">
                    <h3 class="fs-5 mb-3" id="detailTitle" style="color: #333; font-weight: 700;">Detail Tren Bulanan (Pilih Kategori)</h3>
                    <div class="chart-container-detail">
                        <canvas id="chartCategoryDetail"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-content-clean mb-0">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="fs-5 m-0" style="color: #333; font-weight: 700;">Top Category</h3>
                <button class="btn btn-outline-primary btn-sm" onclick="downloadCSV()">
                    <i class="fa fa-download me-1"></i> Download CSV
                </button>
            </div>

            <div class="table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Revenue</th>
                            <th class="text-center" style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableCategory"></tbody>
                </table>
            </div>
            <nav id="paginationContainer" class="d-flex justify-content-between align-items-center mt-3"></nav>
        </div>

      </div>
    </div>
  </div>
</div>

</main>
</div>

<script>
let chartMonthly;
let chartCategory;
let chartCategoryDetail;
let globalCategoryData = { labels: [], data: [] };
let currentPage = 1;
const rowsPerPage = 5; 

let modalMonthlyDetail;

function formatRupiah(number) {
    return "Rp. " + new Intl.NumberFormat("id-ID").format(number);
}

document.addEventListener("DOMContentLoaded", function () {
    modalMonthlyDetail = new bootstrap.Modal(document.getElementById('monthlyDetailModal'));

    fetch("report.php?type=summary")
    .then(r => r.json())
    .then(d => {
        document.getElementById("totalSales").innerText = formatRupiah(d.total_sales);
        document.getElementById("totalOrder").innerText = new Intl.NumberFormat("id-ID").format(d.total_order);
        document.getElementById("avgOrder").innerText = formatRupiah(d.avg_order);
    });

    fetch("report.php?type=monthly")
    .then(r => r.json())
    .then(d => {
        chartMonthly = new Chart(
            document.getElementById("chartMonthly"),
            {
                type: "line",
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: "Monthly Sales",
                        data: d.data,
                        borderColor: "#4a0d68",
                        backgroundColor: "rgba(74,13,104,0.1)",
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: { callbacks: { label: function(context) { return formatRupiah(context.raw); } } }
                    },
                    scales: { y: { ticks: { callback: function(value) { return formatRupiah(value); } } } }
                }
            }
        );
    });
});

function openMonthlyDetailModal() {
    modalMonthlyDetail.show();
    if (!chartCategory) {
        loadCategoryData();
    }
}

function loadCategoryData() {
    fetch("report.php?type=category")
    .then(r => r.json())
    .then(d => {
        globalCategoryData = d;

        chartCategory = new Chart(
            document.getElementById("chartCategory"),
            {
                type: "bar",
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: "Category Sales",
                        data: d.data,
                        backgroundColor: "#4a0d68"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    onClick: (e, elements) => {
                        if (elements.length > 0) {
                            let index = elements[0].index;
                            let category = d.labels[index];
                            loadCategoryDetail(category);
                        }
                    },
                    plugins: {
                        tooltip: { callbacks: { label: function(context) { return formatRupiah(context.raw); } } }
                    },
                    scales: { y: { ticks: { callback: function(value) { return formatRupiah(value); } } } }
                }
            }
        );

        displayTablePage(currentPage);
        setupPagination();

        if(d.labels.length > 0) {
            loadCategoryDetail(d.labels[0]);
        }
    });
}

function displayTablePage(page) {
    let tbody = document.getElementById("tableCategory");
    tbody.innerHTML = "";
    
    let start = (page - 1) * rowsPerPage;
    let end = start + rowsPerPage;
    
    let paginatedLabels = globalCategoryData.labels.slice(start, end);
    
    if(paginatedLabels.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center">Belum ada data category.</td></tr>`;
        return;
    }

    paginatedLabels.forEach((l, i) => {
        let actualIndex = start + i; 
        let row = document.createElement("tr");
        row.innerHTML = `
            <td>${l}</td>
            <td>${formatRupiah(globalCategoryData.data[actualIndex])}</td>
            <td class="text-center">
                <button class="btn btn-warning btn-sm text-white" onclick="loadCategoryDetail('${l}')">
                    <i class="fa fa-chart-line me-1"></i> Detail
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function setupPagination() {
    let container = document.getElementById("paginationContainer");
    container.innerHTML = "";

    let totalData = globalCategoryData.labels.length;
    let totalPages = Math.ceil(totalData / rowsPerPage);

    if (totalPages <= 1) return;

    let startEntry = ((currentPage - 1) * rowsPerPage) + 1;
    let endEntry = Math.min(currentPage * rowsPerPage, totalData);

    let infoText = document.createElement("div");
    infoText.className = "pagination-info text-muted small";
    infoText.innerText = `Showing ${startEntry} to ${endEntry} of ${totalData} entries`;
    container.appendChild(infoText);

    let nav = document.createElement("ul");
    nav.className = "pagination pagination-sm m-0";

    let prevLi = document.createElement("li");
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<button class="page-link"><i class="fa fa-angle-left"></i></button>`;
    if (currentPage > 1) {
        prevLi.onclick = () => { currentPage--; displayTablePage(currentPage); setupPagination(); };
    }
    nav.appendChild(prevLi);

    for (let i = 1; i <= totalPages; i++) {
        let li = document.createElement("li");
        li.className = `page-item ${currentPage === i ? 'active' : ''}`;
        li.innerHTML = `<button class="page-link">${i}</button>`;
        li.onclick = () => { currentPage = i; displayTablePage(currentPage); setupPagination(); };
        nav.appendChild(li);
    }

    let nextLi = document.createElement("li");
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<button class="page-link"><i class="fa fa-angle-right"></i></button>`;
    if (currentPage < totalPages) {
        nextLi.onclick = () => { currentPage++; displayTablePage(currentPage); setupPagination(); };
    }
    nav.appendChild(nextLi);

    container.appendChild(nav);
}

function loadCategoryDetail(category) {
    fetch(`report.php?type=category_detail&category=${encodeURIComponent(category)}`)
    .then(r => r.json())
    .then(d => {
        document.getElementById("detailTitle").innerText = "Tren Bulanan : " + category;

        if (chartCategoryDetail) {
            chartCategoryDetail.destroy();
        }

        chartCategoryDetail = new Chart(
            document.getElementById("chartCategoryDetail"),
            {
                type: "line",
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: category,
                        data: d.data,
                        borderColor: "#ff9800",
                        backgroundColor: "rgba(255,152,0,0.2)",
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: { callbacks: { label: function(context) { return formatRupiah(context.raw); } } }
                    },
                    scales: { y: { ticks: { callback: function(value) { return formatRupiah(value); } } } }
                }
            }
        );
    });
}

function downloadCSV() {
    fetch("report.php?type=category")
    .then(r => r.json())
    .then(d => {
        let csv = "Category,Revenue\n";
        d.labels.forEach((l, i) => {
            csv += `${l},"${formatRupiah(d.data[i])}"\n`;
        });

        let blob = new Blob([csv], { type: "text/csv" });
        let url = URL.createObjectURL(blob);
        let a = document.createElement("a");
        a.href = url;
        a.download = "report.csv";
        a.click();
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>