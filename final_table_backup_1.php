
<?php
include "db_connect.php";
include 'nab_bar.php';
// CLOs and PLOs
$clo_columns = ['CLO1', 'CLO2', 'CLO3', 'CLO4', 'CLO5'];
$plo_columns = ['PLO1', 'PLO2', 'PLO3', 'PLO4', 'PLO5', 'PLO6', 'PLO7', 'PLO8', 'PLO9', 'PLO10', 'PLO11', 'PLO12'];

$performance_levels = [
    'Exemplary' => 0,
    'Satisfactory' => 0,
    'Developing' => 0,
    'Unsatisfactory' => 0
];

// Classification function
function classify_percentage($percent) {
    if ($percent >= 80) return 'Exemplary';
    elseif ($percent >= 60) return 'Satisfactory';
    elseif ($percent >= 40) return 'Developing';
    else return 'Unsatisfactory';
}

// Fetch totals
$total_query = "SELECT * FROM clo_plo_summary WHERE Roll='Total'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);

// Fetch students
$students_query = "SELECT * FROM clo_plo_summary WHERE Roll != 'Total'";
$students_result = mysqli_query($conn, $students_query);
$total_students = mysqli_num_rows($students_result);

// Helper to process CLO/PLOs
function get_performance_percent($columns, $students_result, $total_row, $total_students) {
    global $performance_levels;

    $performance_counts = [];
    foreach ($columns as $col) {
        $performance_counts[$col] = $performance_levels;
    }

    mysqli_data_seek($students_result, 0); // Reset pointer

    while ($student = mysqli_fetch_assoc($students_result)) {
        foreach ($columns as $col) {
            $score = floatval($student[$col]);
            $max = floatval($total_row[$col]);
            if ($max == 0) continue;
            $percent = ($score / $max) * 100;
            $cat = classify_percentage($percent);
            $performance_counts[$col][$cat]++;
        }
    }

    $performance_percent = [];
    foreach ($columns as $col) {
        $performance_percent[$col] = [];
        foreach ($performance_levels as $level => $_) {
            $count = $performance_counts[$col][$level];
            $performance_percent[$col][$level] = $total_students > 0 ? round(($count / $total_students) * 100, 1) : 0;
        }
    }

    return $performance_percent;
}

// Remove columns that have all 0% in performance
function filter_nonzero_columns($performance_percent, $columns) {
    $filtered = [];
    foreach ($columns as $col) {
        $all_zero = true;
        foreach ($performance_percent[$col] as $val) {
            if ($val > 0) {
                $all_zero = false;
                break;
            }
        }
        if (!$all_zero) {
            $filtered[] = $col;
        }
    }
    return $filtered;
}

// Prepare chart datasets
function prepare_chart_data($columns, $performance_percent) {
    $levels = ['Exemplary', 'Satisfactory', 'Developing', 'Unsatisfactory'];
    $colors = [
        'Exemplary' => 'rgba(75, 192, 192, 0.7)',
        'Satisfactory' => 'rgba(54, 162, 235, 0.7)',
        'Developing' => 'rgba(255, 206, 86, 0.7)',
        'Unsatisfactory' => 'rgba(255, 99, 132, 0.7)'
    ];

    $chart_data = [];
    foreach ($levels as $level) {
        $data = [];
        foreach ($columns as $col) {
            $data[] = $performance_percent[$col][$level];
        }
        $chart_data[] = [
            'label' => $level,
            'data' => $data,
            'backgroundColor' => $colors[$level]
        ];
    }
    return $chart_data;
}

// Helper for table generation
function render_table($columns, $performance_percent) {
    global $performance_levels;

    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; width:60%; margin-bottom:30px'>";
    echo "<tr><th>Level</th>";
    foreach ($columns as $col) {
        echo "<th>$col</th>";
    }
    echo "</tr>";

    foreach (array_keys($performance_levels) as $level) {
        echo "<tr><td>$level</td>";
        foreach ($columns as $col) {
            echo "<td>" . $performance_percent[$col][$level] . "</td>";
        }
        echo "</tr>";
    }

    echo "<tr><td><strong>Total</strong></td>";
    foreach ($columns as $_) {
        echo "<td>100.0</td>";
    }
    echo "</tr>";
    echo "</table>";
}

// === Process CLO and PLO ===
$clo_performance_percent = get_performance_percent($clo_columns, $students_result, $total_row, $total_students);
$plo_performance_percent = get_performance_percent($plo_columns, $students_result, $total_row, $total_students);

// Filter columns with no performance
$clo_columns = filter_nonzero_columns($clo_performance_percent, $clo_columns);
$plo_columns = filter_nonzero_columns($plo_performance_percent, $plo_columns);

// Rebuild chart data with filtered columns
$clo_chart_data = prepare_chart_data($clo_columns, $clo_performance_percent);
$plo_chart_data = prepare_chart_data($plo_columns, $plo_performance_percent);
?>

<!DOCTYPE html>
<html>
<head>
    <title>CLO & PLO Performance Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f9f9f9;
        }
        h2 {
            text-align: center;
            margin-top: 50px;
        }
        .section {
            margin-bottom: 60px;
            text-align: center;
        }
        .chart-container {
            width: 80%;
            max-width: 900px;
            margin: auto;
            overflow-x: auto;
        }
        canvas {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            margin-top: 20px;
        }
        table {
            margin: auto;
            background: #fff;
        }

        @media screen and (max-width: 768px) {
            .chart-container, table {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

<div class="section">
    <h2>CLO Performance Table</h2>
    <?php render_table($clo_columns, $clo_performance_percent); ?>
</div>

<div class="section">
    <h2>CLO Performance Chart</h2>
    <div class="chart-container">
        <canvas id="cloChart"></canvas>
    </div>
</div>

<div class="section">
    <h2>PLO Performance Table</h2>
    <?php render_table($plo_columns, $plo_performance_percent); ?>
</div>

<div class="section">
    <h2>PLO Performance Chart</h2>
    <div class="chart-container">
        <canvas id="ploChart"></canvas>
    </div>
</div>

<script>
    const cloCtx = document.getElementById('cloChart').getContext('2d');
    const ploCtx = document.getElementById('ploChart').getContext('2d');

    const cloChart = new Chart(cloCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($clo_columns); ?>,
            datasets: <?php echo json_encode($clo_chart_data); ?>
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' }},
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Percentage (%)' }},
                x: { title: { display: true, text: 'CLOs' }}
            }
        }
    });

    const ploChart = new Chart(ploCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($plo_columns); ?>,
            datasets: <?php echo json_encode($plo_chart_data); ?>
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' }},
            scales: {
                y: { beginAtZero: true, max: 100, title: { display: true, text: 'Percentage (%)' }},
                x: { title: { display: true, text: 'PLOs' }}
            }
        }
    });
</script>

</body>
</html>
