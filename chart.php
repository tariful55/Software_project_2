<?php
include 'db_connect.php';

$clo_totals = array_fill(1, 5, 0);
$plo_totals = array_fill(1, 12, 0);

$sql = "SELECT * FROM CLO_PLO_Table";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        for ($i = 1; $i <= 5; $i++) {
            $clo_totals[$i] += floatval($row["CLO$i"]);
        }
        for ($i = 1; $i <= 12; $i++) {
            $plo_totals[$i] += floatval($row["PLO$i"]);
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLO & PLO Charts</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: white;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            color: #4dbf00;
            margin-bottom: 20px;
        }

        .chart-row {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
        }

        .chart-container {
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            width: 320px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            text-align: center;
        }

        canvas {
            max-width: 280px;
            max-height: 280px;
        }

        .back-button {
            margin-bottom: 20px;
        }

        .back-button a {
            background-color: #4dbf00;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            transition: 0.3s ease;
        }

        .back-button a:hover {
            background-color: #45a100;
        }
    </style>
</head>
<body>

    <div class="back-button">
        <a href="index.html">← Back to Dashboard</a>
    </div>

    <h1>CLO & PLO Charts</h1>

    <div class="chart-row">
        <div class="chart-container">
            <h2>CLO Chart</h2>
            <canvas id="cloChart"></canvas>
        </div>
        <div class="chart-container">
            <h2>PLO Chart</h2>
            <canvas id="ploChart"></canvas>
        </div>
    </div>

    <script>
        const cloLabels = <?php echo json_encode(array_map(fn($i) => "CLO$i", array_keys($clo_totals))); ?>;
        const cloData = <?php echo json_encode(array_values($clo_totals)); ?>;

        const ploLabels = <?php echo json_encode(array_map(fn($i) => "PLO$i", array_keys($plo_totals))); ?>;
        const ploData = <?php echo json_encode(array_values($plo_totals)); ?>;

        const colors = [
            '#4dbf00', '#36A2EB', '#FF6384', '#FFCE56', '#8E44AD',
            '#1ABC9C', '#F39C12', '#D35400', '#2ECC71', '#E74C3C',
            '#5D6D7E', '#7D3C98', '#16A085', '#2980B9', '#C0392B'
        ];

        new Chart(document.getElementById('cloChart'), {
            type: 'pie',
            data: {
                labels: cloLabels,
                datasets: [{
                    data: cloData,
                    backgroundColor: colors.slice(0, cloData.length),
                    borderColor: '#121212',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white',
                            boxWidth: 12,
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('ploChart'), {
            type: 'pie',
            data: {
                labels: ploLabels,
                datasets: [{
                    data: ploData,
                    backgroundColor: colors.slice(0, ploData.length),
                    borderColor: '#121212',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white',
                            boxWidth: 12,
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
