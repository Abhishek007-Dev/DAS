<?php
require_once "db_connect.php";
header('Content-Type: application/json');

$borrowerId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$borrowerId) {
    echo json_encode(['error' => 'Invalid borrower ID']);
    exit;
}

$query = "SELECT b.*, 
          pa.country AS perm_country, pa.province AS perm_province, pa.district AS perm_district,
          pa.municipality_vdc AS perm_municipality, pa.ward_number AS perm_ward, pa.town_village AS perm_town_village,
          pa.tole AS perm_tole, pa.street_number AS perm_street_number,
          ta.country AS temp_country, ta.province AS temp_province, ta.district AS temp_district,
          ta.municipality_vdc AS temp_municipality, ta.ward_number AS temp_ward, ta.town_village AS temp_town_village,
          ta.tole AS temp_tole, ta.street_number AS temp_street_number
          FROM borrower_details b
          LEFT JOIN addresses pa ON b.id = pa.profile_id AND pa.profile_type = 'borrower' AND pa.address_type = 'permanent'
          LEFT JOIN addresses ta ON b.id = ta.profile_id AND ta.profile_type = 'borrower' AND ta.address_type = 'temporary'
          WHERE b.id = ?";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $borrowerId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo json_encode(['error' => 'Borrower not found']);
    exit;
}

// Fetch family details
$fam_sql = "SELECT relation, name FROM family_details WHERE profile_id = ? AND profile_type = 'borrower'";
$stmt_fam = mysqli_prepare($conn, $fam_sql);
mysqli_stmt_bind_param($stmt_fam, "i", $borrowerId);
mysqli_stmt_execute($stmt_fam);
$fam_res = mysqli_stmt_get_result($stmt_fam);
while ($row = mysqli_fetch_assoc($fam_res)) {
    $key = strtolower(str_replace(' ', '_', $row['relation']));
    $data[$key] = $row['name'];
}

echo json_encode($data);