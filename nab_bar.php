<?php
session_start();

// ✅ Define the variable first
$selected_course = isset($_SESSION['selected_course']) ? $_SESSION['selected_course'] : null;

if (!$selected_course) {
    header("Location: index.php");
    exit(); // Important: stop further execution
}


// Extract course name and series from session
$course_display = '';
if ($selected_course) {
    [$course, $series] = explode('_', $selected_course);
    $course_display = strtoupper($course) . " - Series " . $series;
}
?>

<!-- nav_bar.php -->
<style>
.navbar {
    background-color: #111;
    padding: 10px 20px;
    position: fixed;
    top: 0;
    width: 100%;
    color: #4dbf00;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
    box-shadow: 0 0 10px #4dbf00;
}
.navbar .left {
    font-weight: bold;
    font-size: 18px;
}
.navbar .right ul {
    list-style: none;
    display: flex;
    gap: 20px;
    margin: 0;
    padding: 0;
}
.navbar .right a {
    color: #4dbf00;
    text-decoration: none;
    font-weight: bold;
}
.navbar .right a:hover {
    color: white;
}
.navbar button {
    background-color: transparent;
    border: 1px solid #4dbf00;
    color: #4dbf00;
    padding: 5px 10px;
    cursor: pointer;
}
.navbar button:hover {
    background-color: #4dbf00;
    color: black;
}
body {
    padding-top: 70px; /* prevent content from hiding behind fixed nav */
}
</style>

<div class="navbar">
    <div class="left">
        <?= $course_display ? "📘 Course: <span style='color:white;'>$course_display</span>" : "🔴 No Course Selected" ?>
    </div>

    <div class="right">
        <ul>
            <?php if ($selected_course): ?>
                <li><a href="initial_CT_Mark.php">Add Students</a></li>
                <li><a href="edit_CT_Mark.php">Update CT Marks</a></li>
                <li><a href="edit_Sem_Mark.php">Update Semester Marks</a></li>
                <li><a href="view_full_table.php">Show Full Table</a></li>
                <li><a href="clo_plo_graph.php">Show CLO & PLO Graph</a></li>
                <li>
                    <form method="post" action="clear_session.php" style="display:inline;">
                        <button type="submit">🔙 Back / Change Course</button>
                    </form>
                </li>
            <?php else: ?>
                <li><a href="index.php">➡️ Go to Course Page</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

