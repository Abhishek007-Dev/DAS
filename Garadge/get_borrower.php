<?php
require_once "db_connect.php";
header('Content-Type: application/json');
$id = intval($_GET['customer_id'] ?? 0);
if (!$id) { echo json_encode([]); exit; }

// Fetch borrower
$sql = "SELECT * FROM borrower_details WHERE customer_id= ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);

// Fetch addresses
$addr_sql = "SELECT * FROM addresses WHERE profile_id = ? AND profile_type = 'borrower'";
$stmt = mysqli_prepare($conn, $addr_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$addr_res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($addr_res)) {
    foreach ($row as $k => $v) {
        $data[$row['address_type'].'_'.$k] = $v;
    }
}

// Fetch family details
$fam_sql = "SELECT * FROM family_details WHERE profile_id = ? AND profile_type = 'borrower'";
$stmt = mysqli_prepare($conn, $fam_sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$fam_res = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($fam_res)) {
    $data[strtolower(str_replace(' ', '_', $row['relation']))] = $row['name'];
}

echo json_encode($data);