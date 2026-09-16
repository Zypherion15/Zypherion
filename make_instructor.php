<?php
include 'config.php';
$uid = $_GET['uid'];
mysqli_query($conn, "UPDATE users SET role='INSTRUCTOR' WHERE id=$uid");
header("Location: dashboard.php?uid=$uid");
?>