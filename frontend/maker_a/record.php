<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'maker') {
    header("Location: ../login.php");
    exit();
}

require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";

// Set session timeout
$timeout_duration = 900;
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Get filter from query string
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$query = "SELECT * FROM individual_profile";
$params = [];
$types = "";

if ($filter !== 'all') {
    $query .= " WHERE customer_type = ?";
    $params[] = $filter;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get counts for dashboard cards
$total_query = "SELECT COUNT(*) as total FROM individual_profile";
$approved_query = "SELECT COUNT(*) as approved FROM individual_profile WHERE status = 'approved'";
$pending_query = "SELECT COUNT(*) as pending FROM individual_profile WHERE status = 'pending'";
$returned_query = "SELECT COUNT(*) as returned FROM individual_profile WHERE status = 'rejected'";

$total = mysqli_fetch_assoc(mysqli_query($conn, $total_query))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, $approved_query))['approved'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, $pending_query))['pending'];
$returned = mysqli_fetch_assoc(mysqli_query($conn, $returned_query))['returned'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Records | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
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
    .filter-active {
      background-color: #7b145d;
      color: white;
    }
    .table-actions {
      white-space: nowrap;
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
        <a href="#" class="text-white" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notifications">
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
        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
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
        <a class="nav-link active" href="records.php"><i class="bi bi-folder2-open"></i><span>Records</span></a>
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
        <h4 class="mb-4">Customer Records</h4>

        <!-- Filter Buttons -->
        <div class="d-flex mb-4 gap-2">
          <a href="records.php?filter=all" class="btn btn-sm btn-outline-secondary <?php echo $filter === 'all' ? 'filter-active' : ''; ?>">All Customers</a>
          <a href="records.php?filter=individual" class="btn btn-sm btn-outline-secondary <?php echo $filter === 'individual' ? 'filter-active' : ''; ?>">Individual</a>
          <a href="records.php?filter=corporate" class="btn btn-sm btn-outline-secondary <?php echo $filter === 'corporate' ? 'filter-active' : ''; ?>">Corporate</a>
          <a href="records.php?filter=joint" class="btn btn-sm btn-outline-secondary <?php echo $filter === 'joint' ? 'filter-active' : ''; ?>">Joint</a>
        </div>

        <!-- Customer Records Table -->
        <div class="card">
          <div class="card-header bg-gradient" style="background: linear-gradient(90deg, #c0264c, #7b145d 50%, #29006e); color: #fff;">
            Customer Records
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Customer ID</th>
                    <th>Full Name</th>
                    <th>Type</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created On</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $customer): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($customer['customer_id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                        <td>
                          <?php 
                          $type_badge = [
                            'individual' => 'primary',
                            'corporate' => 'success',
                            'joint' => 'warning'
                          ];
                          ?>
                          <span class="badge bg-<?php echo $type_badge[$customer['customer_type']] ?? 'secondary'; ?>">
                            <?php echo ucfirst($customer['customer_type']); ?>
                          </span>
                        </td>
                        <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
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
                        <td><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></td>
                        <td class="table-actions text-center">
                          <a href="customer_profile.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-primary me-1" title="View">
                            <i class="bi bi-eye"></i>
                          </a>
                          <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                            <i class="bi bi-pencil"></i>
                          </a>
                          <button class="btn btn-sm btn-danger" title="Delete" onclick="confirmDelete('<?php echo $customer['customer_id']; ?>')">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center">No customers found</td>
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
  <script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Set current date and time in footer
    function updateDateTime() {
      const now = new Date();
      document.getElementById('datetime').textContent = now.toLocaleString();
    }
    setInterval(updateDateTime, 1000);
    updateDateTime();

    // Confirm delete function
    function confirmDelete(customerId) {
      if (confirm('Are you sure you want to delete this customer?')) {
        window.location.href = 'delete_customer.php?id=' + customerId;
      }
    }
  </script>
</body>
</html>