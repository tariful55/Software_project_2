<?php
// fetch_data_CT_Mark.php
include 'db_connect.php';
include 'nab_bar.php';
// Fetch existing data from DB table ece2217
$sql = "SELECT * FROM ece2217 WHERE Roll IN (0,1,2) OR Roll >= 100 ORDER BY Roll ASC";
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
<meta name="viewport" content="width=device-width, initial-scale=1" />
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
button {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #4dbf00;
  color: black;
  border: none;
  font-size: 16px;
  cursor: pointer;
}
button:hover {
  background-color: #6fe000;
}
</style>
</head>
<body>

<h2>Edit CT Marks</h2>

<form action="Upload_CT_Mark.php" method="POST">
  <table>
    <thead>
      <tr>
        <th>Roll</th>
        <th>Name</th>
        <th colspan="4">CT1</th>
        <th colspan="4">CT2</th>
        <th colspan="4">CT3</th>
        <th colspan="4">CT4</th>
      </tr>
      <tr>
        <th></th>
        <th></th>
        <?php for ($ct = 1; $ct <= 4; $ct++): ?>
          <?php for ($q = 1; $q <= 4; $q++): ?>
            <th>Q<?= $q ?></th>
          <?php endfor; ?>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>

      <!-- CLO row (Roll 0) -->
      <tr>
        <td>0<input type="hidden" name="roll_0" value="0" /></td>
        <td><input type="text" name="row_0_name" value="<?= get_val($data, 0, 'Name') ?: 'CLO' ?>" readonly></td>
        <?php
          for ($ct = 1; $ct <= 4; $ct++):
            for ($q = 1; $q <= 4; $q++):
              $col = "c" . (($ct - 1) * 4 + $q);
              $val = get_val($data, 0, $col);
        ?>
          <td>
            <select name="ct<?= $ct ?>_q<?= $q ?>_clo">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= $val == $i ? 'selected' : '' ?>>CO-<?= $i ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php
            endfor;
          endfor;
        ?>
      </tr>

      <!-- PLO row (Roll 1) -->
      <tr>
        <td>1<input type="hidden" name="roll_1" value="1" /></td>
        <td><input type="text" name="row_1_name" value="<?= get_val($data, 1, 'Name') ?: 'PLO' ?>" readonly></td>
        <?php
          for ($ct = 1; $ct <= 4; $ct++):
            for ($q = 1; $q <= 4; $q++):
              $col = "c" . (($ct - 1) * 4 + $q);
              $val = get_val($data, 1, $col);
        ?>
          <td>
            <select name="ct<?= $ct ?>_q<?= $q ?>_plo">
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= $val == $i ? 'selected' : '' ?>>PO-<?= $i ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php
            endfor;
          endfor;
        ?>
      </tr>

      <!-- Full Marks row (Roll 2) -->
      <tr>
        <td>2<input type="hidden" name="roll_2" value="2" /></td>
        <td><input type="text" name="row_2_name" value="<?= get_val($data, 2, 'Name') ?: 'Full Marks' ?>" readonly></td>
        <?php
          for ($ct = 1; $ct <= 4; $ct++):
            for ($q = 1; $q <= 4; $q++):
              $col = "c" . (($ct - 1) * 4 + $q);
              $val = get_val($data, 2, $col);
        ?>
          <td><input type="number" min="0" max="255" name="ct<?= $ct ?>_q<?= $q ?>_full" value="<?= $val ?>"></td>
        <?php
            endfor;
          endfor;
        ?>
      </tr>

      <!-- Student rows (Roll >= 100) -->
      <?php
        $studentRolls = array_filter(array_keys($data), fn($r) => $r >= 100);
        sort($studentRolls);
        $sIndex = 1;
        foreach ($studentRolls as $roll):
      ?>
      <tr>
        <td><input type="number" name="s<?= $sIndex ?>" value="<?= $roll ?>" required></td>
        <td><input type="text" name="s<?= $sIndex + 1 ?>" value="<?= get_val($data, $roll, 'Name') ?>" required></td>
        <?php for ($i = 1; $i <= 16; $i++):
          $col = "c$i";
          $val = get_val($data, $roll, $col);
        ?>
          <td><input type="number" min="0" max="255" name="s<?= $sIndex + 1 + $i ?>" value="<?= $val ?>"></td>
        <?php endfor; ?>
      </tr>
      <?php $sIndex += 18; endforeach; ?>

    </tbody>
  </table>

  <button type="submit">Update Data</button>
</form>

</body>
</html>
