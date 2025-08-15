<?php
require_once "db_connect.php";

$district = $_GET['district'] ?? '';
$query = "SELECT DISTINCT municipality_vdc FROM demoenglish WHERE district = ? ORDER BY municipality_vdc";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $district);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$options = "";
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='{$row['municipality_vdc']}'>{$row['municipality_vdc']}</option>";
}

echo $options;
?>