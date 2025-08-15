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
  <title>Create Customer Profile | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <!-- Bootstrap & Icons -->
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
          <?php echo htmlspecialchars($_SESSION['username']); ?>
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
        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link active" href="#" id="createToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-file-earmark-plus"></i><span>Create</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="createSubmenu" style="display: block; padding-left: 40px;">
          <li><a class="nav-link active" href="create_customer.php">Individual Borrower</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4><i class="bi bi-person-plus me-2"></i>Create Individual Borrower Profile</h4>
          <a href="#" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to List</a>
        </div>

        <!-- Customer Profile Form -->
        <div class="card">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #c0264c, #7b145d 50%, #29006e); color: #fff;">
            <i class="bi bi-card-text me-2"></i>Customer Details
          </div>
          <div class="card-body">
            <form id="customerForm">
              <!-- Row 1 -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Citizenship Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" placeholder="12345/06789" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Gender <span class="text-danger">*</span></label>
                  <select class="form-select" required>
                    <option value="">Select Gender</option>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                    <option>Prefer not to say</option>
                  </select>
                </div>
              </div>

              <!-- Row 2 -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Marital Status</label>
                  <select class="form-select">
                    <option value="">Select Status</option>
                    <option>Single</option>
                    <option>Married</option>
                    <option>Divorced</option>
                    <option>Widowed</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">+977</span>
                    <input type="tel" class="form-control" placeholder="98XXXXXXXX" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Email Address</label>
                  <input type="email" class="form-control" placeholder="example@domain.com">
                </div>
              </div>

              <!-- Row 3 -->
              <div class="row mb-3">
                
               
              </div>

              <!-- Row 4 -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <label class="form-label">Province <span class="text-danger">*</span></label>
                  <select class="form-select" required>
                    <option value="">Select Province</option>
                    <option>Province 1</option>
                    <option>Madhesh</option>
                    <option>Bagmati</option>
                    <option>Gandaki</option>
                    <option>Lumbini</option>
                    <option>Karnali</option>
                    <option>Sudurpaschim</option>
                  </select>
                </div>
                
                <div class="col-md-4">
                  <label class="form-label">Branch <span class="text-danger">*</span></label>
                  <select class="form-select" required>
                    <option value="">Select Branch</option>
                    <option>Kathmandu Main Branch</option>
                    <option>Pokhara Branch</option>
                    <option>Birgunj Branch</option>
                    <option>Biratnagar Branch</option>
                  </select>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="d-flex justify-content-end">
                    <button type="reset" class="btn btn-outline-secondary me-2">
                      <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-save me-1"></i> Save Customer
                    </button>
                  </div>
                </div>
              </div>
            </form>
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

  <!-- JS -->
  <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../../assets/js/dashboard.js"></script>
  <script>
    // Form submission handling
    document.getElementById('customerForm').addEventListener('submit', function(e) {
      e.preventDefault();
      // Add form validation here
      alert('Customer profile created successfully!');
      // this.submit(); // Uncomment for actual form submission
    });
  </script>
</body>
</html>