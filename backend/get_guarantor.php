<?php
require_once "db_connect.php";
header('Content-Type: application/json');

// Get guarantor by ID
$guarantor_id = $_GET['guarantor_id'] ?? '';
if (!$guarantor_id) {
    echo json_encode(['success' => false, 'message' => 'Missing guarantor_id']);
    exit;
}

// Get main guarantor info
$sql = "SELECT * FROM guarantors WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $guarantor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$guarantor = mysqli_fetch_assoc($result);

if (!$guarantor) {
    echo json_encode(['success' => false, 'message' => 'Guarantor not found']);
    exit;
}

// Get family details
$sql_fd = "SELECT relation, name FROM family_details WHERE profile_id = ? AND profile_type = 'guarantor'";
$stmt_fd = mysqli_prepare($conn, $sql_fd);
mysqli_stmt_bind_param($stmt_fd, "i", $guarantor_id);
mysqli_stmt_execute($stmt_fd);
$result_fd = mysqli_stmt_get_result($stmt_fd);
$family_details = [];
while ($row = mysqli_fetch_assoc($result_fd)) {
    $family_details[] = $row;
}

// Get addresses
$sql_addr = "SELECT address_type, country, province, district, municipality_vdc, ward_number, town_village, tole, street_number FROM addresses WHERE profile_id = ? AND profile_type = 'guarantor'";
$stmt_addr = mysqli_prepare($conn, $sql_addr);
mysqli_stmt_bind_param($stmt_addr, "i", $guarantor_id);
mysqli_stmt_execute($stmt_addr);
$result_addr = mysqli_stmt_get_result($stmt_addr);
$addresses = [];
while ($row = mysqli_fetch_assoc($result_addr)) {
    $addresses[] = $row;
}

echo json_encode([
    'success' => true,
    'guarantor' => $guarantor,
    'family_details' => $family_details,
    'addresses' => $addresses
]);