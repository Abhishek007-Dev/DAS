<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'maker') {
    header("Location: ../login.php");
    exit();
}
include "../../backend/db_connect.php"; // change path as per your project

$type = $_GET['type'] ?? '';
$province = $_GET['province'] ?? '';
$district = $_GET['district'] ?? '';
$municipality = $_GET['municipality'] ?? '';

$response = [];

if ($type === 'province') {
    $sql = "SELECT DISTINCT province FROM demoenglish ORDER BY province ASC";
    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $response[] = $row['province'];
    }
} elseif ($type === 'district' && $province !== '') {
    $stmt = $conn->prepare("SELECT DISTINCT district FROM demoenglish WHERE province = ?");
    $stmt->bind_param("s", $province);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = $row['district'];
    }
} elseif ($type === 'municipality' && $district !== '') {
    $stmt = $conn->prepare("SELECT DISTINCT municipality_vdc FROM demoenglish WHERE district = ?");
    $stmt->bind_param("s", $district);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = $row['municipality_vdc'];
    }
} elseif ($type === 'ward' && $municipality !== '') {
    $stmt = $conn->prepare("SELECT wada_count FROM demoenglish WHERE municipality_vdc = ? LIMIT 1");
    $stmt->bind_param("s", $municipality);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    for ($i = 1; $i <= $count; $i++) {
        $response[] = $i;
    }
}

echo json_encode($response);
?>
