<?php
session_start();
unset($_SESSION['selected_course']);
header("Location: index.php");
exit();

