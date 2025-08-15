<?php
require_once "db_connect.php";

$id = $_POST['id'] ?? 0;

// Update borrower_details
$sql = "UPDATE borrower_details SET 
    full_name=?, dob=?, gender=?, relationship_status=?, citizenship_number=?, citizenship_issue_date=?, citizenship_issue_authority=?, citizenship_issue_district=?
    WHERE id=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssssssssi",
    $_POST['full_name'], $_POST['dob'], $_POST['gender'], $_POST['relationship_status'],
    $_POST['citizenship_number'], $_POST['citizenship_issue_date'], $_POST['citizenship_issue_authority'], $_POST['citizenship_issue_district'], $id);
mysqli_stmt_execute($stmt);

// Update addresses (permanent & temporary)
$address_types = [
    'perm' => 'permanent',
    'temp' => 'temporary'
];
foreach ($address_types as $type => $db_type) {
    $sql_addr = "UPDATE addresses SET country=?, province=?, district=?, municipality_vdc=?, ward_number=?, town_village=?, tole=?, street_number=? WHERE profile_id=? AND profile_type='borrower' AND address_type=?";
    $stmt_addr = mysqli_prepare($conn, $sql_addr);
    mysqli_stmt_bind_param($stmt_addr, "ssssssssis",
        $_POST[$type.'_country'], $_POST[$type.'_province'], $_POST[$type.'_district'], $_POST[$type.'_municipality_vdc'],
        $_POST[$type.'_ward_number'], $_POST[$type.'_town_village'], $_POST[$type.'_tole'], $_POST[$type.'_street_number'],
        $id, $db_type
    );
    mysqli_stmt_execute($stmt_addr);
}

// Update family details
$relations = [
    'Grandfather Name'=>'grandfather_name',
    'Grandmother Name'=>'grandmother_name',
    'Father Name'=>'father_name',
    'Mother Name'=>'mother_name',
    'Spouse Name'=>'spouse_name',
    'Son Name'=>'son_name',
    'Daughter Name'=>'daughter_name',
    'Father In Law'=>'father_in_law',
    'Mother In Law'=>'mother_in_law'
];
foreach ($relations as $relation=>$field) {
    $sql_fam = "UPDATE family_details SET name=? WHERE profile_id=? AND profile_type='borrower' AND relation=?";
    $stmt_fam = mysqli_prepare($conn, $sql_fam);
    mysqli_stmt_bind_param($stmt_fam, "sis", $_POST[$field], $id, $relation);
    mysqli_stmt_execute($stmt_fam);
}

echo json_encode(['success'=>true]);