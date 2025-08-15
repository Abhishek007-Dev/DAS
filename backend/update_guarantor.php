<?php
session_start();
require_once "db_connect.php";
header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$guarantor_id = trim($_POST['guarantor_id'] ?? '');
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
if (!$guarantor_id || !$customer_id || !$full_name || !$dob || !$citizenship_number) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Update main table
$sql = "UPDATE guarantors SET 
    customer_id = ?, full_name = ?, dob = ?, gender = ?, relationship_status = ?, citizenship_number = ?, citizenship_issue_date = ?, citizenship_issue_authority = ?, citizenship_issue_district = ?
    WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sssssssssi", $customer_id, $full_name, $dob, $gender, $relationship_status, $citizenship_number, $citizenship_issue_date, $citizenship_issue_authority, $citizenship_issue_district, $guarantor_id);

if (mysqli_stmt_execute($stmt)) {
    // Update family details: delete old, insert new
    if (isset($_POST['family_details'])) {
        $sql_del_fd = "DELETE FROM family_details WHERE profile_id = ? AND profile_type = 'guarantor'";
        $stmt_del_fd = mysqli_prepare($conn, $sql_del_fd);
        mysqli_stmt_bind_param($stmt_del_fd, "i", $guarantor_id);
        mysqli_stmt_execute($stmt_del_fd);

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

    // Update addresses: delete old, insert new
    if (isset($_POST['addresses'])) {
        $sql_del_addr = "DELETE FROM addresses WHERE profile_id = ? AND profile_type = 'guarantor'";
        $stmt_del_addr = mysqli_prepare($conn, $sql_del_addr);
        mysqli_stmt_bind_param($stmt_del_addr, "i", $guarantor_id);
        mysqli_stmt_execute($stmt_del_addr);

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