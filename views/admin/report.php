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
<html>
<head>
<meta charset="UTF-8">
<title>Report</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<link rel="stylesheet" href="../../assets/css/admin/panel.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
                    <p id="totalOrder">1.240</p>
                </article>
                <article class="card">
                    <h3>Rata-Rata</h3>
                    <p id="avgOrder"></p>
                </article>
    </section>
</div>

<div class="charts">
    <div class="panel">
        <h3>Monthly Sales</h3>
        <canvas id="chartMonthly"></canvas>
    </div>

    <div class="panel">
        <h3>Category Sales</h3>
        <canvas id="chartCategory"></canvas>
    </div>
    <div class="panel">
      <h3 id="detailTitle">Detail Penjualan Category</h3>
      <canvas id="chartCategoryDetail"></canvas>
    </div>
</div>
<div class="panel">
    <h3>Top Category</h3>
    <button onclick="downloadCSV()">Download CSV</button>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody id="tableCategory"></tbody>
    </table>
</div>

</main>
</div>

<script>
let chartMonthly;
let chartCategory;
let chartCategoryDetail;

function formatRupiah(number) {
    return "Rp. " + new Intl.NumberFormat("id-ID").format(number);
}

document.addEventListener("DOMContentLoaded", function () {

    fetch("report.php?type=summary")
    .then(r => r.json())
    .then(d => {

        document.getElementById("totalSales").innerText =
            formatRupiah(d.total_sales);

        document.getElementById("totalOrder").innerText =
            new Intl.NumberFormat("id-ID").format(d.total_order);

        document.getElementById("avgOrder").innerText =
            formatRupiah(d.avg_order);
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

                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatRupiah(context.raw);
                                }
                            }
                        }
                    },

                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return formatRupiah(value);
                                }
                            }
                        }
                    }
                }
            }
        );
    });

    fetch("report.php?type=category")
    .then(r => r.json())
    .then(d => {

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

                    onClick: (e, elements) => {

                        if (elements.length > 0) {

                            let index = elements[0].index;
                            let category = d.labels[index];

                            loadCategoryDetail(category);
                        }
                    },

                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatRupiah(context.raw);
                                }
                            }
                        }
                    },

                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return formatRupiah(value);
                                }
                            }
                        }
                    }
                }
            }
        );

        let html = "";

        d.labels.forEach((l, i) => {

            html += `
            <tr onclick="loadCategoryDetail('${l}')"
                style="cursor:pointer">

                <td>${l}</td>

                <td>${formatRupiah(d.data[i])}</td>

            </tr>
            `;
        });

        document.getElementById("tableCategory").innerHTML = html;

        if (d.labels.length > 0) {
            loadCategoryDetail(d.labels[0]);
        }
    });

});

function loadCategoryDetail(category) {

    fetch(`report.php?type=category_detail&category=${encodeURIComponent(category)}`)

    .then(r => r.json())

    .then(d => {

        document.getElementById("detailTitle").innerText =
            "Detail Penjualan : " + category;

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

                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return formatRupiah(context.raw);
                                }
                            }
                        }
                    },

                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return formatRupiah(value);
                                }
                            }
                        }
                    }
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

        let blob = new Blob(
            [csv],
            { type: "text/csv" }
        );

        let url = URL.createObjectURL(blob);

        let a = document.createElement("a");

        a.href = url;
        a.download = "report.csv";

        a.click();
    });
}
</script>
<script src="../../assets/script/admin/shared-layout.js"></script>
</body>
</html>