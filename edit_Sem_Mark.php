<?php
// Update_Sem_mark.php
include 'db_connect.php';
include 'nab_bar.php';
// Fetch data from table
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
<title>Update Semester Marks</title>
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
  width: 60px;
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

<h2>Edit Semester Marks</h2>

<form action="upload_semester_process.php" method="POST">
  <table>
    <thead>
      <tr>
        <th>Roll</th>
        <th>Name</th>
        <?php for ($i = 1; $i <= 8; $i++): ?>
          <th colspan="4">Q<?= $i ?></th>
        <?php endfor; ?>
      </tr>      
      <tr>
        <th></th>
        <th></th>
        <?php for ($q = 1; $q <= 32; $q++): ?>
          <th>Q<?= $q ?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>

    <!-- CLO Row (Roll 0) -->
    <tr>
      <td>0<input type="hidden" name="roll_0" value="0" /></td>
      <td><input type="text" name="row_0_name" value="<?= get_val($data, 0, 'Name') ?: 'CLO' ?>" readonly></td>
      <?php for ($i = 17; $i <= 48; $i++): ?>
        <?php $val = get_val($data, 0, "c$i"); ?>
        <td>
          <select name="c<?= $i ?>_clo">
            <?php for ($j = 1; $j <= 5; $j++): ?>
              <option value="<?= $j ?>" <?= $val == $j ? 'selected' : '' ?>>CO-<?= $j ?></option>
            <?php endfor; ?>
          </select>
        </td>
      <?php endfor; ?>
    </tr>

    <!-- PLO Row (Roll 1) -->
    <tr>
      <td>1<input type="hidden" name="roll_1" value="1" /></td>
      <td><input type="text" name="row_1_name" value="<?= get_val($data, 1, 'Name') ?: 'PLO' ?>" readonly></td>
      <?php for ($i = 17; $i <= 48; $i++): ?>
        <?php $val = get_val($data, 1, "c$i"); ?>
        <td>
          <select name="c<?= $i ?>_plo">
            <?php for ($j = 1; $j <= 12; $j++): ?>
              <option value="<?= $j ?>" <?= $val == $j ? 'selected' : '' ?>>PO-<?= $j ?></option>
            <?php endfor; ?>
          </select>
        </td>
      <?php endfor; ?>
    </tr>

    <!-- Full Marks Row (Roll 2) -->
    <tr>
      <td>2<input type="hidden" name="roll_2" value="2" /></td>
      <td><input type="text" name="row_2_name" value="<?= get_val($data, 2, 'Name') ?: 'Full Marks' ?>" readonly></td>
      <?php for ($i = 17; $i <= 48; $i++): ?>
        <?php $val = get_val($data, 2, "c$i"); ?>
        <td><input type="number" name="c<?= $i ?>_full" value="<?= $val ?>" min="0" max="255"></td>
      <?php endfor; ?>
    </tr>

    <!-- Student Rows -->
    <?php
      $studentRolls = array_filter(array_keys($data), fn($r) => $r >= 100);
      sort($studentRolls);
      $sIndex = 102; // skip s0-s101 for meta rows
      foreach ($studentRolls as $roll):
    ?>
      <tr>
        <td><input type="text" name="s<?= $sIndex++ ?>" value="<?= $roll ?>"></td>
        <td><input type="text" name="s<?= $sIndex++ ?>" value="<?= get_val($data, $roll, 'Name') ?>"></td>
        <?php for ($i = 17; $i <= 48; $i++): ?>
          <td><input type="number" name="s<?= $sIndex++ ?>" value="<?= get_val($data, $roll, "c$i") ?>" min="0" max="255"></td>
        <?php endfor; ?>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>

  <button type="submit">Update Semester Marks</button>
</form>

</body>
</html>

