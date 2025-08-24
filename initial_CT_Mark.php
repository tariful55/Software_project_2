<?php
session_start();
include 'db_connect.php';
include 'nab_bar.php';

$table = $_SESSION['selected_course'];

$sql = "SELECT * FROM `$table` WHERE Roll IN (0,1,2) OR Roll >= 100 ORDER BY Roll ASC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row['Roll']] = $row;
}
$conn->close();

function get_val($data, $roll, $col) {
    return isset($data[$roll][$col]) ? htmlspecialchars($data[$roll][$col]) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Assessment Data</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #1e1e1e;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    table {
      width: 98%;
      margin: 20px auto;
      border-collapse: collapse;
      background-color: #2e2e2e;
      box-shadow: 0 0 15px rgba(77, 191, 0, 0.7);
    }
    th, td {
      padding: 10px;
      border: 1px solid #4dbf00;
      text-align: center;
    }
    th {
      background-color: #444;
      color: #4dbf00;
    }
    select, input[type="number"], input[type="text"] {
      background-color: #333;
      color: white;
      border: none;
      padding: 5px;
      width: 100px;
      font-size: 14px;
      text-align: center;
    }
    button, .add-row-btn {
      margin-top: 15px;
      padding: 8px 16px;
      background-color: #4dbf00;
      color: black;
      border: none;
      font-size: 14px;
      cursor: pointer;
    }
    button:hover, .add-row-btn:hover {
      background-color: #6fe000;
    }
    .control-box {
      margin-bottom: 20px;
    }
    .control-box input {
      width: 120px;
      padding: 5px;
      margin: 0 10px;
    }
  </style>
</head>
<body>

<h2>Upload Initial CT Marks</h2>

<div class="control-box">
  <label for="startRollInput">Starting Roll:</label>
  <input type="number" id="startRollInput" value="2110001" min="1">
  <label for="studentCountInput">Number of Students:</label>
  <input type="number" id="studentCountInput" value="6" min="1">
  <button type="button" onclick="generateRows()">Generate Rows</button>
</div>

<form action="Upload_CT_Mark.php" method="POST">
  <table id="marksTable">
    <thead>
      <tr>
        <th>Roll</th>
        <th>Name</th>
        <?php for ($ct = 1; $ct <= 4; $ct++): ?>
          <th colspan="4">CT<?= $ct ?></th>
        <?php endfor; ?>
      </tr>
      <tr>
        <th></th>
        <th></th>
        <?php for ($i = 1; $i <= 4 * 4; $i++): ?>
          <th>Q<?= (($i - 1) % 4) + 1 ?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody id="marksBody">
      <!-- CLO -->
      <tr>
        <td>0<input type="hidden" name="roll_0" value="0"></td>
        <td><input type="text" name="row_0_name" value="<?= get_val($data, 0, 'Name') ?: 'CLO' ?>" readonly></td>
        <?php for ($i = 1; $i <= 16; $i++): 
          $val = get_val($data, 0, "c$i"); ?>
          <td>
            <select name="ct<?= ceil($i / 4) ?>_q<?= ($i - 1) % 4 + 1 ?>_clo">
              <?php for ($j = 1; $j <= 5; $j++): ?>
                <option value="<?= $j ?>" <?= $val == $j ? 'selected' : '' ?>>CLO-<?= $j ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php endfor; ?>
      </tr>

      <!-- PLO -->
      <tr>
        <td>1<input type="hidden" name="roll_1" value="1"></td>
        <td><input type="text" name="row_1_name" value="<?= get_val($data, 1, 'Name') ?: 'PLO' ?>" readonly></td>
        <?php for ($i = 1; $i <= 16; $i++): 
          $val = get_val($data, 1, "c$i"); ?>
          <td>
            <select name="ct<?= ceil($i / 4) ?>_q<?= ($i - 1) % 4 + 1 ?>_plo">
              <?php for ($j = 1; $j <= 12; $j++): ?>
                <option value="<?= $j ?>" <?= $val == $j ? 'selected' : '' ?>>PLO-<?= $j ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php endfor; ?>
      </tr>

      <!-- Full Marks -->
      <tr>
        <td>2<input type="hidden" name="roll_2" value="2"></td>
        <td><input type="text" name="row_2_name" value="<?= get_val($data, 2, 'Name') ?: 'Full Marks' ?>" readonly></td>
        <?php for ($i = 1; $i <= 16; $i++): 
          $val = get_val($data, 2, "c$i"); ?>
          <td><input type="number" name="q<?= $i ?>_full" value="<?= $val ?>" min="0" max="255"></td>
        <?php endfor; ?>
      </tr>

      <!-- Pre-existing students from DB -->
      <?php
      $studentRolls = array_filter(array_keys($data), fn($r) => $r >= 100);
      sort($studentRolls);
      $studentIndex = 1;

      foreach ($studentRolls as $roll):
      ?>
      <tr>
        <td><input type="text" name="s<?= $studentIndex ?>" value="<?= $roll ?>"></td>
        <td><input type="text" name="s<?= $studentIndex + 1 ?>" value="<?= get_val($data, $roll, 'Name') ?>"></td>
        <?php for ($i = 1; $i <= 16; $i++): 
          $val = get_val($data, $roll, "c$i"); ?>
          <td><input type="number" name="s<?= $studentIndex + 1 + $i ?>" value="<?= $val ?>" min="0" max="255"></td>
        <?php endfor; ?>
      </tr>
      <?php $studentIndex += 18; endforeach; ?>
    </tbody>
  </table>

  
  <button type="button" class="add-row-btn" style="float:left;" onclick="addExtraRow()">+ Add Row</button>

  <button type="submit">Save Students</button>
</form>

<script>
let studentCounter = <?= $studentIndex ?>;

function generateRows() {
  const tbody = document.getElementById("marksBody");
  const startRoll = parseInt(document.getElementById("startRollInput").value);
  const count = parseInt(document.getElementById("studentCountInput").value);

  for (let i = 0; i < count; i++) {
    addStudentRow(startRoll + i, `Student-${i + 1}`);
  }
}

function addExtraRow() {
  addStudentRow('', '');
}

function addStudentRow(roll, name) {
  const tbody = document.getElementById("marksBody");
  const row = document.createElement("tr");

  row.innerHTML += `<td><input type="text" name="s${studentCounter}" value="${roll}"></td>`;
  row.innerHTML += `<td><input type="text" name="s${studentCounter + 1}" value="${name}"></td>`;

  for (let i = 0; i < 16; i++) {
    row.innerHTML += `<td><input type="number" name="s${studentCounter + 2 + i}" value="" min="0" max="255"></td>`;
  }

  studentCounter += 18;
  tbody.appendChild(row);
}
</script>

</body>
</html>
