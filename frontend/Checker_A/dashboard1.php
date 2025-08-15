<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'checker') 
{
    header("Location: ../login.php");
    exit();
}
// Include database connection
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";
// Check if the user has the 'superadmin' role
// This will redirect to login if the role is not allowed
checkRole('checker'); // Only allow users with the 'checker' role
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

$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard | Document Automation System</title>
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
          <span class="badge bg-info ms-1"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
          <li><a href="../login.php" class="logout-button dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
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
      
      <?php if ($_SESSION['role'] === 'maker') { ?>
        <!-- Maker Menu -->
        <li class="nav-item">
          <a class="nav-link" href="#" id="createToggle" onclick="toggleSidebarDropdown(event)">
            <i class="bi bi-file-earmark-plus"></i><span>Create</span>
            <i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul class="sidebar-submenu list-unstyled" id="createSubmenu" style="display: none; padding-left: 40px;">
            <li><a class="nav-link" href="create_customer.php">Individual Borrower</a></li>
            <li><a class="nav-link" href="create_corporate_borrower.php">Corporate Borrower</a></li>
            <li><a class="nav-link" href="create_co_borrower.php">Co-Borrower</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-pencil-square"></i><span>My Submissions</span></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-folder2-open"></i><span>Records</span></a>
        </li>
      <?php } else if ($_SESSION['role'] === 'checker') { ?>
        <!-- Checker Menu -->
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
      <?php } ?>
      
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-gear"></i><span>Settings</span></a>
      </li>
    </ul>
  </div>

  <!-- Layout -->
  <div class="layout collapsed" id="layout">
    <div class="main-content">
      <div class="container-fluid">
        <h4 class="mb-4">Welcome to the Dashboard</h4>

        <!-- Dashboard Cards -->
        <div class="row">
          <?php if ($_SESSION['role'] === 'maker') { ?>
            <!-- Maker Dashboard Cards -->
            <div class="col-md-3 mb-4">
              <div class="dashboard-card bg-primary text-white d-flex align-items-center position-relative">
                <i class="bi bi-people-fill fs-1 me-3"></i>
                <div>
                  <h5 class="mb-1">Total Customers</h5>
                  <p class="mb-0 fs-5">42</p>
                </div>
                <span class="role-badge badge bg-light text-dark">Maker</span>
              </div>
            </div>
            <div class="col-md-3 mb-4">
              <div class="dashboard-card bg-success text-white d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-1 me-3"></i>
                <div>
                  <h5 class="mb-1">Approved</h5>
                  <p class="mb-0 fs-5">18</p>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-4">
              <div class="dashboard-card bg-warning text-dark d-flex align-items-center">
                <i class="bi bi-hourglass-split fs-1 me-3"></i>
                <div>
                  <h5 class="mb-1">Pending</h5>
                  <p class="mb-0 fs-5">5</p>
                </div>
              </div>
            </div>
            <div class="col-md-3 mb-4">
              <div class="dashboard-card bg-danger text-white d-flex align-items-center">
                <i class="bi bi-arrow-return-left fs-1 me-3"></i>
                <div>
                  <h5 class="mb-1">Returned</h5>
                  <p class="mb-0 fs-5">2</p>
                </div>
              </div>
            </div>
          <?php } else if ($_SESSION['role'] === 'checker') { ?>
            <!-- Checker Dashboard Cards -->
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
          <?php } ?>
        </div>

        <!-- Recent Activity Table -->
        <div class="card mt-4">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #c0264c, #7b145d 50%, #29006e); color: #fff;">
            <?php echo $_SESSION['role'] === 'maker' ? 'My Recent Submissions' : 'Recent Approval Activities'; ?>
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
                  <?php if ($_SESSION['role'] === 'maker') { ?>
                    <tr>
                      <td>DAS-2025-0012</td>
                      <td>John Doe</td>
                      <td>Individual</td>
                      <td>2025-07-15</td>
                      <td><span class="badge bg-success">Approved</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-warning text-dark" title="Edit"><i class="bi bi-pencil"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td>DAS-2025-0011</td>
                      <td>Acme Corp</td>
                      <td>Corporate</td>
                      <td>2025-07-14</td>
                      <td><span class="badge bg-warning text-dark">Pending</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-warning text-dark" title="Edit"><i class="bi bi-pencil"></i></button>
                      </td>
                    </tr>
                    <tr>
                      <td>DAS-2025-0010</td>
                      <td>Jane Smith</td>
                      <td>Co-Borrower</td>
                      <td>2025-07-13</td>
                      <td><span class="badge bg-danger">Returned</span></td>
                      <td>
                        <button class="btn btn-sm btn-primary me-1" title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-warning text-dark" title="Edit"><i class="bi bi-pencil"></i></button>
                      </td>
                    </tr>
                  <?php } else { ?>
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
                  <?php } ?>
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

  <!-- Footer (now outside layout, full width, fixed) -->
  <footer class="footer-custom">
    <div>© Abhii</div>
    <div id="datetime"></div>
  </footer>

  <!-- JS -->
  <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/dashboard.js"></script>
</body>
</html>