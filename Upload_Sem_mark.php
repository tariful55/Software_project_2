<?php
include 'db_connect.php';

$result = mysqli_query($conn, "SELECT roll, name FROM ece2217 WHERE roll NOT IN (0, 1, 2)");
$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Semester Assessment Upload</title>
  <style>
    body {
      font-family: Arial;
      background: #121212;
      color: #fff;
      padding: 20px;
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: #1e1e1e;
    }
    th, td {
      border: 1px solid #4caf50;
      padding: 8px;
      text-align: center;
    }
    th {
      background: #2e2e2e;
      color: #4caf50;
    }
    input, select {
      background: #333;
      color: white;
      border: none;
      padding: 5px;
      width: 60px;
      text-align: center;
    }
    button {
      padding: 10px 20px;
      background-color: #4caf50;
      border: none;
      font-size: 16px;
      margin-top: 20px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<h2>Semester Assessment Form</h2>

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
        <td></td>
        <td></td>
        <?php for ($i = 1; $i <= 32; $i++): ?>
          <td>Q<?= $i ?></td>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>

      <!-- CLO row -->
      <tr>
        <td>0</td>
        <td><input type="text" name="row_0_name" value="CLO"></td>
        <?php for ($i = 17; $i <= 48; $i++): ?>
          <td>
            <select name="c<?= $i ?>_clo">
              <?php for ($j = 1; $j <= 5; $j++): ?>
                <option value="<?= $j ?>">CO-<?= $j ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php endfor; ?>
      </tr>

      <!-- PLO row -->
      <tr>
        <td>1</td>
        <td><input type="text" name="row_1_name" value="PLO"></td>
        <?php for ($i = 17; $i <= 48; $i++): ?>
          <td>
            <select name="c<?= $i ?>_plo">
              <?php for ($j = 1; $j <= 12; $j++): ?>
                <option value="<?= $j ?>">PO-<?= $j ?></option>
              <?php endfor; ?>
            </select>
          </td>
        <?php endfor; ?>
      </tr>

      <!-- Full Marks row -->
      <tr>
        <td>2</td>
        <td><input type="text" name="row_2_name" value="Full Marks"></td>
        <?php for ($i = 17; $i <= 48; $i++): ?>
          <td><input type="number" name="c<?= $i ?>_full" min="0"></td>
        <?php endfor; ?>
      </tr>

      <!-- Student rows -->
      <?php
        $field_counter = 102; // skip s0 to s101 for 3 metadata rows
        foreach ($students as $index => $stu):
      ?>
        <tr>
          <td><input type="text" name="s<?= $field_counter++ ?>" value="<?= $stu['roll'] ?>"></td>
          <td><input type="text" name="s<?= $field_counter++ ?>" value="<?= $stu['name'] ?>"></td>
          <?php for ($i = 17; $i <= 48; $i++): ?>
            <td><input type="number" name="s<?= $field_counter++ ?>" step="0.01" min="0"></td>
          <?php endfor; ?>
        </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

  <button type="submit">Submit Semester Marks</button>
</form>

</body>
</html>