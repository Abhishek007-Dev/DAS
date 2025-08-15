<?php
require_once "db_connect.php";

$query = "SELECT DISTINCT province FROM demoenglish ORDER BY province";
$result = mysqli_query($conn, $query);

$options = "";
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='{$row['province']}'>{$row['province']}</option>";
}

echo $options;
?>