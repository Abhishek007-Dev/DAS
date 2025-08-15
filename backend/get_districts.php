<?php
require_once "db_connect.php";

$province = $_GET['province'] ?? '';
$query = "SELECT DISTINCT district FROM demoenglish WHERE province = ? ORDER BY district";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $province);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$options = "";
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='{$row['district']}'>{$row['district']}</option>";
}

echo $options;
?>