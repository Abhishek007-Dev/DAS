<?php
session_start();
require_once "db_connect.php";

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get and sanitize POST data
$customer_id = trim($_POST['customer_id'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$dob = $_POST['dob'] ?? '';
$gender = $_POST['gender'] ?? '';
$relationship_status = $_POST['relationship_status'] ?? '';
$citizenship_number = trim($_POST['citizenship_number'] ?? '');
$citizenship_issue_date = $_POST['citizenship_issue_date'] ?? '';
$citizenship_issue_authority = trim($_POST['citizenship_issue_authority'] ?? '');
$citizenship_issue_district = trim($_POST['citizenship_issue_district'] ?? '');

// Basic validation
if (!$customer_id || !$full_name || !$dob || !$citizenship_number) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Insert into guarantors-d table
$sql = "INSERT INTO `guarantors` 
    (customer_id, full_name, dob, gender, relationship_status, citizenship_number, citizenship_issue_date, citizenship_issue_authority, citizenship_issue_district, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssss", $customer_id, $full_name, $dob, $gender, $relationship_status, $citizenship_number, $citizenship_issue_date, $citizenship_issue_authority, $citizenship_issue_district);

if (mysqli_stmt_execute($stmt)) {
    $guarantor_id = mysqli_insert_id($conn);

    // Save family details if provided
    if (!empty($_POST['family_details'])) {
        $family_details = json_decode($_POST['family_details'], true);
        foreach ($family_details as $fd) {
            $relation = $fd['relation'];
            $name = $fd['name'];
            if ($name) {
                $sql_fd = "INSERT INTO family_details (profile_id, profile_type, relation, name) VALUES (?, 'guarantor', ?, ?)";
                $stmt_fd = mysqli_prepare($conn, $sql_fd);
                mysqli_stmt_bind_param($stmt_fd, "iss", $guarantor_id, $relation, $name);
                mysqli_stmt_execute($stmt_fd);
            }
        }
    }

    // Save addresses if provided
    if (!empty($_POST['addresses'])) {
        $addresses = json_decode($_POST['addresses'], true);
        foreach ($addresses as $ad) {
            $type = $ad['type'];
            $country = $ad['country'];
            $province = $ad['province'];
            $district = $ad['district'];
            $municipality_vdc = $ad['municipality_vdc'];
            $ward_number = $ad['ward_number'];
            $town_village = $ad['town_village'];
            $tole = $ad['tole'];
            $street_number = $ad['street_number'];
            $sql_addr = "INSERT INTO addresses (profile_id, profile_type, address_type, country, province, district, municipality_vdc, ward_number, town_village, tole, street_number) VALUES (?, 'guarantor', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_addr = mysqli_prepare($conn, $sql_addr);
            mysqli_stmt_bind_param($stmt_addr, "issssssssss", $guarantor_id, $type, $country, $province, $district, $municipality_vdc, $ward_number, $town_village, $tole, $street_number);
            mysqli_stmt_execute($stmt_addr);
        }
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
}