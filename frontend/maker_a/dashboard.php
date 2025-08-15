<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'maker') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";

// Check if the user has the 'maker' role
checkRole('maker'); // Only allow users with the 'maker' role

// Set the session timeout duration (in seconds)
$timeout_duration = 900; // 15 minutes

// Check if "LAST_ACTIVITY" is set in the session
if (isset($_SESSION['LAST_ACTIVITY'])) {
    // Calculate time since last activity
    $time_inactive = time() - $_SESSION['LAST_ACTIVITY'];
    
    // If the time inactive is greater than timeout duration, log out
    if ($time_inactive > $timeout_duration) {
        session_unset(); // remove session variables
        session_destroy(); // destroy the session
        header("Location: login.php?message=Session expired. Please log in again.");
        exit();
    }
}

// Initialize all count variables
$total = 0;
$approved = 0;
$pending = 0;
$returned = 0;

// Get counts from database using a single optimized query
$count_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned
    FROM individual_profile WHERE created_by = ?";

$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $counts = $result->fetch_assoc();
    $total = $counts['total'] ?? 0;
    $approved = $counts['approved'] ?? 0;
    $pending = $counts['pending'] ?? 0;
    $returned = $counts['returned'] ?? 0;
}

// Update last activity time stamp for active session
$_SESSION['LAST_ACTIVITY'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Maker Dashboard | Document Automation System</title>
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

    .maker-badge {
      background: linear-gradient(135deg, #8e2de2, #4a00e0);
      color: white;
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
       <div class="me-3">
        <span class="badge maker-badge">
          <i class="bi bi-shield-lock me-1"></i>MAKER
        </span>
      </div>
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
        <a class="nav-link" href="#" id="createToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-file-earmark-plus"></i><span>Create</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="createSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="create_individual.php">Individual Borrower</a></li>
          <li><a class="nav-link" href="create_corporate_borrower.php">Corporate Borrower</a></li>
          <li><a class="nav-link" href="create_co_borrower.php">Co-Borrower</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-pencil-square"></i><span>My Submissions</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="record.php"><i class="bi bi-folder2-open"></i><span>Records</span></a>
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
        <h4 class="mb-4">Maker Dashboard</h4>

        <!-- Dashboard Cards -->
        <div class="row">
        <div class="col-md-3 mb-4">
          <div class="dashboard-card bg-primary text-white d-flex align-items-center position-relative">
            <i class="bi bi-people-fill fs-1 me-3"></i>
            <div>
              <h5 class="mb-1">Total Customers</h5>
              <p class="mb-0 fs-5"><?php echo $total; ?></p>
            </div>
            <span class="role-badge badge bg-light text-dark">Maker</span>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="dashboard-card bg-success text-white d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-1 me-3"></i>
            <div>
              <h5 class="mb-1">Approved</h5>
              <p class="mb-0 fs-5"><?php echo $approved; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="dashboard-card bg-warning text-dark d-flex align-items-center">
            <i class="bi bi-hourglass-split fs-1 me-3"></i>
            <div>
              <h5 class="mb-1">Pending</h5>
              <p class="mb-0 fs-5"><?php echo $pending; ?></p>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-4">
          <div class="dashboard-card bg-danger text-white d-flex align-items-center">
            <i class="bi bi-arrow-return-left fs-1 me-3"></i>
            <div>
              <h5 class="mb-1">Returned</h5>
              <p class="mb-0 fs-5"><?php echo $returned; ?></p>
            </div>
          </div>
        </div>
      </div>

        <!-- Recent Submissions Table -->
        <div class="card mt-4">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #c0264c, #7b145d 50%, #29006e); color: #fff;">
            My Recent Submissions
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                  $recent_query = "SELECT * FROM individual_profile 
                                  WHERE created_by = ? 
                                  ORDER BY created_at DESC LIMIT 5";
                  $stmt = mysqli_prepare($conn, $recent_query);
                  mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
                  mysqli_stmt_execute($stmt);
                  $recent_result = mysqli_stmt_get_result($stmt);
                  $recent_customers = mysqli_fetch_all($recent_result, MYSQLI_ASSOC);
                  
                  if (count($recent_customers) > 0): ?>
                    <?php foreach ($recent_customers as $customer): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                        <td><?php echo ucfirst($customer['customer_type']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></td>
                        <td>
                          <?php 
                          $status_badge = [
                            'active' => 'primary',
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger'
                          ];
                          ?>
                          <span class="badge bg-<?php echo $status_badge[$customer['status']] ?? 'secondary'; ?>">
                            <?php echo ucfirst($customer['status']); ?>
                          </span>
                        </td>
                        <td>
                          <a href="customer_profile.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-primary me-1" title="View">
                            <i class="bi bi-eye"></i>
                          </a>
                          <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-warning text-dark" title="Edit">
                            <i class="bi bi-pencil"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center">No recent submissions</td>
                    </tr>
                  <?php endif; ?>
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