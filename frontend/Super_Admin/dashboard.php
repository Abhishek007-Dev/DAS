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
  <title>Super Admin Dashboard | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">

  <!-- Bootstrap & Icons -->
  <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/dash.css">
  <style>
    .superadmin-badge {
      background: linear-gradient(135deg, #8e2de2, #4a00e0);
      color: white;
    }
    .system-health-card {
      border-left: 4px solid #28a745;
    }
    .critical-alert {
      border-left: 4px solid #dc3545;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
      70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
      100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    .user-activity-timeline {
      position: relative;
      padding-left: 30px;
    }
    .user-activity-timeline::before {
      content: '';
      position: absolute;
      left: 10px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: #dee2e6;
    }
    .timeline-item {
      position: relative;
      margin-bottom: 20px;
    }
    .timeline-item::before {
      content: '';
      position: absolute;
      left: -30px;
      top: 5px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #6f42c1;
      border: 2px solid white;
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
          <i class="bi bi-shield-lock me-1"></i>SUPER ADMIN
        </span>
      </div>
      <img src="/DAS/assets/images/avatar2.png" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
      <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle text-white" type="button" data-bs-toggle="dropdown">
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
          <li><a class="dropdown-item" href="#"><i class="bi bi-key me-2"></i>Security</a></li>
          <li><hr class="dropdown-divider"></li>
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
      <li class="nav-item">
        <a class="nav-link" href="#" id="systemToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-server"></i><span>System Control</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="systemSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="#"><i class="bi bi-database me-2"></i>Database Admin</a></li>
          <li><a class="nav-link" href="#"><i class="bi bi-shield-check me-2"></i>Security Center</a></li>
          <li><a class="nav-link" href="#"><i class="bi bi-arrow-repeat me-2"></i>Backup/Restore</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" id="userToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-people-fill"></i><span>User Management</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="userSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="#"><i class="bi bi-person-plus me-2"></i>Create Users</a></li>
          <li><a class="nav-link" href="#"><i class="bi bi-person-lines-fill me-2"></i>Manage Users</a></li>
          <li><a class="nav-link" href="#"><i class="bi bi-person-x me-2"></i>Deactivate Users</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-bar-chart-line"></i><span>Analytics</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="profile_cust.php"><i class="bi bi-folder2-open"></i><span>Records</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-file-earmark-lock"></i><span>Audit Logs</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-sliders"></i><span>Global Settings</span></a>
      </li>
    </ul>
  </div>

  <!-- Layout -->
  <div class="layout collapsed" id="layout">
    <div class="main-content">
      <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4>Super Admin Dashboard</h4>
          <div class="d-flex">
            <button class="btn btn-sm btn-outline-primary me-2">
              <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <button class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-gear"></i> Quick Settings
            </button>
          </div>
        </div>

        <!-- System Status Cards -->
        <div class="row">
          <div class="col-md-3 mb-4">
            <div class="dashboard-card system-health-card bg-white text-dark d-flex align-items-center position-relative">
              <i class="bi bi-check-circle-fill fs-1 me-3 text-success"></i>
              <div>
                <h5 class="mb-1">System Health</h5>
                <p class="mb-0 fs-5">Optimal</p>
                <small class="text-muted">All systems normal</small>
              </div>
              <span class="badge bg-success position-absolute top-0 end-0 m-2">LIVE</span>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-primary text-white d-flex align-items-center">
              <i class="bi bi-people-fill fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Active Users</h5>
                <p class="mb-0 fs-5">42</p>
                <small class="text-white-50">5 currently online</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card bg-info text-white d-flex align-items-center">
              <i class="bi bi-database fs-1 me-3"></i>
              <div>
                <h5 class="mb-1">Database</h5>
                <p class="mb-0 fs-5">1.2TB</p>
                <small class="text-white-50">65% capacity</small>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-4">
            <div class="dashboard-card critical-alert bg-white text-dark d-flex align-items-center">
              <i class="bi bi-exclamation-triangle-fill fs-1 me-3 text-danger"></i>
              <div>
                <h5 class="mb-1">Alerts</h5>
                <p class="mb-0 fs-5">3 Critical</p>
                <small class="text-danger">Action required</small>
              </div>
            </div>
          </div>
        </div>

        <!-- System Overview Section -->
        <div class="row mt-4">
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: #fff;">
                <i class="bi bi-activity me-2"></i>Real-time System Activity
              </div>
              <div class="card-body">
                <div class="user-activity-timeline">
                  <div class="timeline-item">
                    <h6 class="mb-1">Database backup completed</h6>
                    <small class="text-muted">2 minutes ago</small>
                    <p class="mb-0 text-success">Success: 1.2GB backed up</p>
                  </div>
                  <div class="timeline-item">
                    <h6 class="mb-1">User login detected</h6>
                    <small class="text-muted">5 minutes ago</small>
                    <p class="mb-0">Rahul (Maker) from 192.168.1.45</p>
                  </div>
                  <div class="timeline-item">
                    <h6 class="mb-1">Security scan completed</h6>
                    <small class="text-muted">15 minutes ago</small>
                    <p class="mb-0 text-warning">2 medium vulnerabilities found</p>
                  </div>
                  <div class="timeline-item">
                    <h6 class="mb-1">System update available</h6>
                    <small class="text-muted">25 minutes ago</small>
                    <p class="mb-0">Version 2.5.1 ready to install</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card h-100">
              <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: #fff;">
                <i class="bi bi-shield-lock me-2"></i>Security Overview
              </div>
              <div class="card-body">
                <div class="mb-4">
                  <h6>Threat Detection <span class="badge bg-danger float-end">3 Active</span></h6>
                  <div class="progress mt-2" style="height: 10px;">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 15%"></div>
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 25%"></div>
                    <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                  </div>
                  <small class="text-muted">Critical: 3 | Warning: 5 | Normal: 12</small>
                </div>
                
                <div class="mb-3">
                  <h6>Recent Security Events</h6>
                  <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                      <div>
                        <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>
                        Failed login attempt
                        <small class="d-block text-muted">IP: 103.45.67.89</small>
                      </div>
                      <span class="badge bg-light text-dark">5 min ago</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                      <div>
                        <i class="bi bi-shield-check text-success me-2"></i>
                        Firewall updated
                        <small class="d-block text-muted">Ruleset v5.2 applied</small>
                      </div>
                      <span class="badge bg-light text-dark">1 hour ago</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Critical Alerts Section -->
        <div class="card mt-4 border-danger">
          <div class="card-header bg-danger text-white">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Critical Alerts Requiring Attention
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Alert ID</th>
                    <th>Description</th>
                    <th>Severity</th>
                    <th>Detected</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>ALERT-2025-0042</td>
                    <td>Database connection spikes detected</td>
                    <td><span class="badge bg-danger">Critical</span></td>
                    <td>15 minutes ago</td>
                    <td>
                      <button class="btn btn-sm btn-outline-danger me-1">Details</button>
                      <button class="btn btn-sm btn-danger">Resolve</button>
                    </td>
                  </tr>
                  <tr>
                    <td>ALERT-2025-0041</td>
                    <td>Unauthorized access attempt from blocked IP</td>
                    <td><span class="badge bg-danger">Critical</span></td>
                    <td>32 minutes ago</td>
                    <td>
                      <button class="btn btn-sm btn-outline-danger me-1">Details</button>
                      <button class="btn btn-sm btn-danger">Resolve</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- User Management Section -->
        <div class="card mt-4">
          <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #8e2de2, #4a00e0); color: #fff;">
            <i class="bi bi-people-fill me-2"></i>Recent User Activities
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Activity</th>
                    <th>Time</th>
                    <th>IP Address</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Rahul Sharma</td>
                    <td><span class="badge bg-info">Maker</span></td>
                    <td>Created new customer record</td>
                    <td>2025-07-15 14:30</td>
                    <td>192.168.1.45</td>
                    <td><span class="badge bg-success">Completed</span></td>
                  </tr>
                  <tr>
                    <td>Priya Patel</td>
                    <td><span class="badge bg-success">Checker</span></td>
                    <td>Approved 5 documents</td>
                    <td>2025-07-15 14:15</td>
                    <td>192.168.1.67</td>
                    <td><span class="badge bg-success">Completed</span></td>
                  </tr>
                  <tr>
                    <td>Admin User</td>
                    <td><span class="badge superadmin-badge">Super Admin</span></td>
                    <td>System configuration change</td>
                    <td>2025-07-15 13:45</td>
                    <td>192.168.1.1</td>
                    <td><span class="badge bg-success">Completed</span></td>
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
    <div>System Version: 0.0.1</div>
  </footer>

  <!-- JS -->
  <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/dashboard.js"></script>
  <script>
    // Super Admin specific scripts
    function toggleSidebarDropdown(event) {
      event.preventDefault();
      const submenu = event.currentTarget.nextElementSibling;
      submenu.style.display = submenu.style.display === 'none' ? 'block' : 'none';
      event.currentTarget.querySelector('.bi-chevron-down').classList.toggle('bi-chevron-up');
    }
    
    // Real-time updates simulation
    setInterval(() => {
      const onlineCount = Math.floor(Math.random() * 3) + 3;
      document.querySelector('.dashboard-card.bg-primary .text-white-50').textContent = 
        `${onlineCount} currently online`;
    }, 5000);
  </script>
</body>
</html>