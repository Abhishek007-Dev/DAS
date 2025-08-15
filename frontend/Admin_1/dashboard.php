<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') 
{
    header("Location: ../login.php");
    exit();
}
// Include database connection
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";
// Check if the user has the 'admin' role
// This will redirect to login if the role is not allowed
checkRole('admin'); // Only allow users with the 'admin' role
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


// Update the last activity time
$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_AGENT';

        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, role, ip_address, user_agent, success)
                                VALUES (?, ?, ?, ?, ?, TRUE)");
        $stmt->bind_param("issss", $user['id'], $username, $user['role'], $ip, $agent);
        $stmt->execute();

        echo "Login successful.";
    } else {
        // Failed login - wrong password
        $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, ip_address, user_agent, success)
                                VALUES (NULL, ?, ?, ?, FALSE)");
        $stmt->bind_param("sss", $username, $ip, $agent);
        $stmt->execute();

        echo "Incorrect password.";
    }
} else {
    // Failed login - user not found
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, username, ip_address, user_agent, success)
                            VALUES (NULL, ?, ?, ?, FALSE)");
    $stmt->bind_param("sss", $username, $ip, $agent);
    $stmt->execute();

    echo "User not found.";
}

// Update last activity time stamp for active session
$_SESSION['LAST_ACTIVITY'] = time();

?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard | Document Automation System</title>
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
    .admin-card {
      border-left: 4px solid #6f42c1;
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
        <span class="badge superadmin-badge">
          <i class="bi bi-shield-lock me-1"></i>ADMIN
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
        <a class="nav-link" href="#"><i class="bi bi-people-fill"></i><span>User Management</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" id="reportsToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-file-earmark-bar-graph"></i><span>Reports</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="reportsSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="#">Daily Activity</a></li>
          <li><a class="nav-link" href="#">User Performance</a></li>
          <li><a class="nav-link" href="#">Approval Metrics</a></li>
          <li><a class="nav-link" href="#">Audit Logs</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-shield-lock"></i><span>Security</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-sliders"></i><span>System Settings</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-folder2-open"></i><span>All Records</span></a>
      </li>
    </ul>
  </div>

  <!-- Layout -->
  <div class="layout collapsed" id="layout">
    <div class="main-content">
      <div class="container-fluid">
        <h4 class="mb-4">Admin Dashboard</h4>

        <!-- Dashboard Cards -->
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="dashboard-card admin-card bg-white text-dark d-flex align-items-center position-relative">
              <i class="bi bi-people-fill fs-1 me-3 text-purple"></i>
              <div>
                <h5 class="mb-1">Total Users</h5>
                <p class="mb-0 fs-5">24</p>
                <small class="text-muted">5 Makers | 15 Checkers | 4 Admins</small>
              </div>
              <span class="role-badge badge bg-purple text-white">Admin</span>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-primary text-white d-flex align-items-center">
              <i class="bi bi-file-earmark-text fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Total Documents</h5>
                <p class="mb-0 fs-5">187</p>
                <small class="text-white-50">42 Today</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-success text-white d-flex align-items-center">
              <i class="bi bi-check-circle fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Approved Today</h5>
                <p class="mb-0 fs-5">28</p>
                <small class="text-white-50">85% Success Rate</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-warning text-dark d-flex align-items-center">
              <i class="bi bi-exclamation-triangle fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Pending Actions</h5>
                <p class="mb-0 fs-5">7</p>
                <small class="text-muted">3 High Priority</small>
              </div>
            </div>
          </div>
        </div>

        <!-- System Overview Section -->
        <div class="row mt-4">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #6f42c1, #5a3ac7 50%, #4a36cf); color: #fff;">
                <i class="bi bi-activity me-2"></i>System Activity
              </div>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0">Recent Activities</h6>
                  <small class="text-muted">Last 24 hours</small>
                </div>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-person-plus text-success me-2"></i>
                      New user registered
                      <small class="d-block text-muted">Checker - 10 mins ago</small>
                    </div>
                    <span class="badge bg-light text-dark">User Mgmt</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-file-earmark-check text-primary me-2"></i>
                      Bulk document approval
                      <small class="d-block text-muted">15 docs - 1 hour ago</small>
                    </div>
                    <span class="badge bg-light text-dark">Approval</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <i class="bi bi-exclamation-octagon text-danger me-2"></i>
                      System warning
                      <small class="d-block text-muted">Storage threshold - 2 hours ago</small>
                    </div>
                    <span class="badge bg-light text-dark">System</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #6f42c1, #5a3ac7 50%, #4a36cf); color: #fff;">
                <i class="bi bi-bar-chart me-2"></i>Performance Metrics
              </div>
              <div class="card-body">
                <div class="row text-center">
                  <div class="col-4 border-end">
                    <h3 class="text-purple">85%</h3>
                    <small class="text-muted">Approval Rate</small>
                  </div>
                  <div class="col-4 border-end">
                    <h3 class="text-success">4.7</h3>
                    <small class="text-muted">Avg. Rating</small>
                  </div>
                  <div class="col-4">
                    <h3 class="text-primary">12.3s</h3>
                    <small class="text-muted">Avg. Response</small>
                  </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0">Top Performers</h6>
                  <small class="text-muted">This Week</small>
                </div>
                <div class="d-flex mb-2">
                  <img src="/DAS/assets/images/avatar1.png" class="rounded-circle me-2" width="40" height="40">
                  <div class="flex-grow-1">
                    <h6 class="mb-0">Rahul Sharma</h6>
                    <small class="text-muted">42 approvals | 98% success</small>
                  </div>
                  <span class="badge bg-success">Checker</span>
                </div>
                <div class="d-flex mb-2">
                  <img src="/DAS/assets/images/avatar2.png" class="rounded-circle me-2" width="40" height="40">
                  <div class="flex-grow-1">
                    <h6 class="mb-0">Priya Patel</h6>
                    <small class="text-muted">38 submissions | 2 returns</small>
                  </div>
                  <span class="badge bg-info">Maker</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent System Activities Table -->
        <div class="card mt-4">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #6f42c1, #5a3ac7 50%, #4a36cf); color: #fff;">
            <i class="bi bi-list-check me-2"></i>Recent System Activities
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Timestamp</th>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Details</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-07-15 14:30</td>
                    <td>User created</td>
                    <td>Sanjay Gupta</td>
                    <td><span class="badge bg-info">Maker</span></td>
                    <td>New maker account</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>2025-07-15 13:45</td>
                    <td>Document approved</td>
                    <td>Neha Singh</td>
                    <td><span class="badge bg-success">Checker</span></td>
                    <td>DAS-2025-0015</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>2025-07-15 12:20</td>
                    <td>System update</td>
                    <td>Admin User</td>
                    <td><span class="badge bg-purple">Admin</span></td>
                    <td>Security patches applied</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary" title="View"><i class="bi bi-eye"></i></button>
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
  <script>
    // Additional admin-specific JS can go here
    function toggleSidebarDropdown(event) {
      event.preventDefault();
      const submenu = event.currentTarget.nextElementSibling;
      submenu.style.display = submenu.style.display === 'none' ? 'block' : 'none';
      event.currentTarget.querySelector('.bi-chevron-down').classList.toggle('bi-chevron-up');
    }
  </script>
</body>
</html>