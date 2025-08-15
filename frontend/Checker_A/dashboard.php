<?php
session_start();

// Only allow if user is checker
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'checker') {
    header("Location: ../login.php");
    exit();
}

// Include database connection and role check
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";

// Check user role is 'checker'
checkRole('checker');

// Set session timeout duration (15 minutes)
$timeout_duration = 900;

if (isset($_SESSION['LAST_ACTIVITY'])) {
    $time_inactive = time() - $_SESSION['LAST_ACTIVITY'];

    if ($time_inactive > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: ../login.php?message=Session expired. Please log in again.");
        exit();
    }
}

// Initialize count variables
$total = 0;
$approved = 0;
$pending = 0;
$returned = 0;

// Fetch profile status counts assigned to this checker
$count_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
    FROM individual_profile
    WHERE checked_by = ?
";

$stmt = $conn->prepare($count_query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $counts = $result->fetch_assoc();
    $total = $counts['total'] ?? 0;
    $approved = $counts['approved'] ?? 0;
    $pending = $counts['pending'] ?? 0;
    $returned = $counts['returned'] ?? 0;
} else {
    // Handle query error
    error_log("Error fetching counts: " . $conn->error);
}

// Update session activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checker Dashboard | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/dash.css">
  <style>
    .role-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 0.7rem;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top px-3">
    <a class="navbar-brand d-flex align-items-center" href="#">
      <img src="/DAS/assets/images/sbilogo.png" alt="Logo" style="width: 200px; height: 42px;">
      <span class="d-none d-md-inline">Document Automation System</span>
    </a>
    <div class="ms-auto d-flex align-items-center">
       <img src="/DAS/assets/images/avatar2.png" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
      <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
          <li><a href="../login.php" class="logout-button dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
      <!-- Notification Icon -->
        <div class="ms-3">
            <a href="#" class="text-white" data-bs-toggle="tooltip" data-bs-placement="bottom" title="You have 3 new notifications">
                <i class="bi bi-bell" style="font-size: 1.5rem;"></i>
            </a>
        </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
    <ul class="nav flex-column px-2">
      <li class="nav-item">
        <a class="nav-link active" href="#"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-clipboard-check"></i><span>Pending Approvals</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-check-circle"></i><span>Approved Records</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-arrow-return-left"></i><span>Returned Records</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-folder2-open"></i><span>All Records</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-gear"></i><span>Settings</span></a>
      </li>
    </ul>
  </div>

  <!-- Layout -->
  <div class="layout collapsed" id="layout">
    <div class="main-content">
      <div class="container-fluid">
        <h4 class="mb-4">Checker Dashboard</h4>

        <!-- Dashboard Cards -->
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-primary text-white d-flex align-items-center position-relative">
              <i class="bi bi-clipboard-check fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Pending Review</h5>
                <p class="mb-0 fs-5">15</p>
              </div>
              <span class="role-badge badge bg-light text-dark">Checker</span>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-success text-white d-flex align-items-center">
              <i class="bi bi-check-circle-fill fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Approved Today</h5>
                <p class="mb-0 fs-5">8</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-warning text-dark d-flex align-items-center">
              <i class="bi bi-exclamation-triangle-fill fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Requires Attention</h5>
                <p class="mb-0 fs-5">3</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-danger text-white d-flex align-items-center">
              <i class="bi bi-arrow-return-left fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Returned Today</h5>
                <p class="mb-0 fs-5">2</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Approval Activities Table -->
        <div class="card mt-4">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #c0264c, #7b145d 50%, #29006e); color: #fff;">
            Recent Approval Activities
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Reference ID</th>
                    <th>Customer Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>DAS-2025-0015</td>
                    <td>Global Tech</td>
                    <td>Corporate</td>
                    <td>2025-07-15 09:30</td>
                    <td><span class="badge bg-success">Approved</span></td>
                    <td>
                      <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-info text-white" title="Details"><i class="bi bi-list-ul"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>DAS-2025-0014</td>
                    <td>Sarah Johnson</td>
                    <td>Individual</td>
                    <td>2025-07-15 11:45</td>
                    <td><span class="badge bg-warning text-dark">Pending Review</span></td>
                    <td>
                      <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-success text-white" title="Approve"><i class="bi bi-check-lg"></i></button>
                      <button class="btn btn-sm btn-danger" title="Return"><i class="bi bi-arrow-return-left"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>DAS-2025-0013</td>
                    <td>Mike & Partners</td>
                    <td>Co-Borrower</td>
                    <td>2025-07-14 16:20</td>
                    <td><span class="badge bg-danger">Returned</span></td>
                    <td>
                      <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-info text-white" title="Details"><i class="bi bi-list-ul"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Optional long content -->
        <div style="height: 100px;"></div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-custom">
    <div>© Abhii</div>
    <div id="datetime"></div>
  </footer>

  <!-- JS -->
  <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/dashboard.js"></script>
</body>
</html>