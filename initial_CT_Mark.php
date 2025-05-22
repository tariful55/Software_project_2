<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Assessment Data</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Assessment Upload Form</h2>

<form action="Upload_CT_Mark.php" method="POST">
  <table border="1" cellspacing="0" cellpadding="5">
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
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
      </tr>
    </thead>
    <tbody>
      <!-- CLO row -->
      <tr>
        <td>0</td>
        <td><input type="text" name="row_0_name" value="CLO"></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
      </tr>

      <!-- PLO row -->
      <tr>
        <td>1</td>
        <td><input type="text" name="row_1_name" value="PLO"></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td>
      </tr>

      <!-- Full Marks row -->
      <tr>
        <td>2</td>
        <td><input type="text" name="row_2_name" value="Full Marks"></td>
        <td><input type="number" name="ct1_q1_full"></td>
        <td><input type="number" name="ct1_q2_full"></td>
        <td><input type="number" name="ct1_q3_full"></td>
        <td><input type="number" name="ct1_q4_full"></td>
        <td><input type="number" name="ct2_q1_full"></td>
        <td><input type="number" name="ct2_q2_full"></td>
        <td><input type="number" name="ct2_q3_full"></td>
        <td><input type="number" name="ct2_q4_full"></td>
        <td><input type="number" name="ct3_q1_full"></td>
        <td><input type="number" name="ct3_q2_full"></td>
        <td><input type="number" name="ct3_q3_full"></td>
        <td><input type="number" name="ct3_q4_full"></td>
        <td><input type="number" name="ct4_q1_full"></td>
        <td><input type="number" name="ct4_q2_full"></td>
        <td><input type="number" name="ct4_q3_full"></td>
        <td><input type="number" name="ct4_q4_full"></td>
      </tr>
    </tbody>
  </table>

  <button type="submit">Upload Data</but ton>
</form>

<script>
window.onload = function () {
    const table = document.querySelector("table");
    const tableBody = document.querySelector("table tbody");

    const startRoll = 2110001;
    const numberOfStudents = 6;
    const fields = ['ct1_q1','ct1_q2','ct1_q3','ct1_q4',
                    'ct2_q1','ct2_q2','ct2_q3','ct2_q4',
                    'ct3_q1','ct3_q2','ct3_q3','ct3_q4',
                    'ct4_q1','ct4_q2','ct4_q3','ct4_q4']; // 16 fields

    function generateStudentRow(studentIndex, nameStartIndex) {
        const roll = startRoll + studentIndex - 1;
        let rowHtml = `<tr>`;

        // sX = Roll
        rowHtml += `<td><input type="text" name="s${nameStartIndex}" value="${roll}"></td>`;

        // sX+1 = Name
        rowHtml += `<td><input type="text" name="s${nameStartIndex + 1}" value="Stud-${studentIndex}"></td>`;

        // sX+2 to sX+17 = CT marks
        for (let i = 0; i < fields.length; i++) {
            const nameAttr = `s${nameStartIndex + 2 + i}`;
            rowHtml += `<td><input type="number" name="${nameAttr}"></td>`;
        }

        rowHtml += `</tr>`;
        return rowHtml;
    }

    let allStudentRowsHtml = '';
    let nameCounter = 1;

    for (let i = 0; i < numberOfStudents; i++) {
        allStudentRowsHtml += generateStudentRow(i + 1, nameCounter);
        nameCounter += 18; // Move to next set of 18
    }

    tableBody.innerHTML += allStudentRowsHtml;

    // CLO Select Row (row 2)
    const cloRow = table.rows[2];
    for (let i = 2; i < cloRow.cells.length; i++) {
        const cell = cloRow.cells[i];
        const select = document.createElement("select");
        select.name = `ct${Math.floor((i - 2) / 4) + 1}_q${(i - 2) % 4 + 1}_clo`;

        for (let j = 1; j <= 5; j++) {
            const option = document.createElement("option");
            option.value = `${j}`;
            option.text = `CO-${j}`;
            select.appendChild(option);
        }

        cell.innerHTML = '';
        cell.appendChild(select);
    }

    // PLO Select Row (row 3)
    const ploRow = table.rows[3];
    for (let i = 2; i < ploRow.cells.length; i++) {
        const cell = ploRow.cells[i];
        const select = document.createElement("select");
        select.name = `ct${Math.floor((i - 2) / 4) + 1}_q${(i - 2) % 4 + 1}_plo`;

        for (let j = 1; j <= 12; j++) {
            const option = document.createElement("option");
            option.value = `${j}`;
            option.text = `PO-${j}`;
            select.appendChild(option);
        }

        cell.innerHTML = '';
        cell.appendChild(select);
    }
};
</script>


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

</body>
</html>
