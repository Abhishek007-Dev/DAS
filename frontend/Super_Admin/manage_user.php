<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') 
{
    header("Location: ../login.php");
    exit();
}
// Include database connection
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";
// Check if the user has the 'superadmin' role
// This will redirect to login if the role is not allowed
checkRole('superadmin'); // Only allow users with the 'superadmin' role
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
  <title>Manage Users | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/dash.css">
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
        Abhishek
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
        <li><a href="../login.php" class="logout-button dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Sidebar -->
 <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
    <ul class="nav flex-column px-2">
      <li class="nav-item">
        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" id="createToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-file-earmark-plus"></i><span>Create</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="createSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="#">Individual Borrower</a></li>
          <li><a class="nav-link" href="#">Corporate Borrower</a></li>
          <li><a class="nav-link" href="#">Co-Borrower</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-folder2-open"></i><span>Records</span></a>
      </li>
      <li class="nav-item">
       <a class="nav-link" href="manage_user.php"><i class="bi bi-person-lines-fill"></i><span>Manage Users</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-gear"></i><span>Settings</span></a>
      </li>
    </ul>
  </div>

<!-- Main Content -->
<div class="layout collapsed" id="layout">
  <div class="main-content">
    <div class="container-fluid">

      <h4 class="mt-5 mb-3">Manage Users</h4>

      <!-- Action Buttons -->
      <div class="mb-4">
        <button class="btn btn-primary me-2"><i class="bi bi-plus-circle me-1"></i>Add User</button>
        <button class="btn btn-warning me-2 text-dark"><i class="bi bi-pencil-square me-1"></i>Modify User</button>
        <button class="btn btn-info me-2 text-white"><i class="bi bi-arrow-left-right me-1"></i>Transfer User</button>
        <button class="btn btn-danger"><i class="bi bi-trash me-1"></i>Delete User</button>
      </div>

      <!-- Stats Card -->
      <div class="row mb-4">
        <div class="col-md-3">
          <div class="dashboard-card bg-primary text-white d-flex align-items-center">
            <i class="bi bi-people-fill fs-1 me-3"></i>
            <div>
              <h5 class="mb-1">Total Users</h5>
              <p class="mb-0 fs-5"><?php echo $total_users; ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="card-header bg-gradient text-white" style="background: linear-gradient(90deg, #0061a8, #00759c);">
          User List
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead class="table-light">
                <tr>
                  <th>Staff Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Branch</th>
                  <th>SOL ID</th>
                  <th>Role</th>
                  <th>Created At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>
                    <td>{$row['staff_name']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['branch']}</td>
                    <td>{$row['sol_id']}</td>
                    <td><span class='badge bg-success text-uppercase'>{$row['role']}</span></td>
                    <td>{$row['created_at']}</td>
                    <td>
                      <button class='btn btn-sm btn-primary me-1' title='View'><i class='bi bi-eye'></i></button>
                      <button class='btn btn-sm btn-warning text-dark' title='Edit'><i class='bi bi-pencil'></i></button>
                    </td>
                  </tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Footer -->
<footer class="footer-custom">
  <div>© Abhii</div>
  <div id="datetime"></div>
</footer>

<!-- Scripts -->
<script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/dashboard.js"></script>
</body>
</html>
