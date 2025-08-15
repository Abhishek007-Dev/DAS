<?php
require_once "db_connect.php";

$municipality = $_GET['municipality'] ?? '';
$query = "SELECT DISTINCT wada_count FROM demoenglish WHERE municipality_vdc = ? ORDER BY wada_count";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $municipality);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$options = "";
if ($row = mysqli_fetch_assoc($result)) {
    $ward_count = (int)$row['wada_count'];
    for ($i = 1; $i <= $ward_count; $i++) {
        $options .= "<option value='{$i}'>Ward {$i}</option>";
    }
}

echo $options;
?>