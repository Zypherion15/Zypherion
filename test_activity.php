<?php
include 'config.php';
$uid = 1; // palitan mo ng ID mo

$sql = "INSERT INTO activities (user_id, type, title, details, link) VALUES ($uid, 'test', 'Manual Test Activity', 'Testing kung nag iinsert', 'dashboard.php?uid=$uid')";

if(mysqli_query($conn, $sql)){
    echo "SUCCESS! Nag insert na. Check mo sa phpMyAdmin > activities table";
} else {
    echo "FAILED! Error: ".mysqli_error($conn);
}
?>  