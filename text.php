<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'maker') {
    header("Location: ../login.php");
    exit();
}

require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";

// Get customer ID from URL and validate
$customer_id = isset($_GET['id']) ? trim($_GET['id']) : null;

if (!$customer_id) {
    die("No customer ID provided");
}

// Fetch customer details with prepared statement
$customer_query = "SELECT * FROM individual_profile WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $customer_query);
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$customer_result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($customer_result);

if (!$customer) {
    die("Customer not found in database");
}

// Fetch address details - changed table name from customer_addresses to individual_addresses


// Record view activity
$user_id = $_SESSION['user_id'];
$activity_query = "INSERT INTO activity_log (user_id, customer_id, action, description) 
                   VALUES (?, ?, 'view', 'Viewed customer profile')";
$stmt = mysqli_prepare($conn, $activity_query);
mysqli_stmt_bind_param($stmt, "is", $user_id, $customer_id);
mysqli_stmt_execute($stmt);

// Fetch recent activities
$activities_query = "SELECT al.*, u.username 
                     FROM activity_log al
                     JOIN users u ON al.user_id = u.id
                     WHERE al.customer_id = ?
                     ORDER BY al.created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $activities_query);
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$activities_result = mysqli_stmt_get_result($stmt);
$activities = [];
while ($row = mysqli_fetch_assoc($activities_result)) {
    $activities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($customer['full_name'] ?? 'Customer Profile'); ?> | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/dash.css">
  <style>
    .profile-header {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      border-radius: 10px;
    }
    .status-badge {
      font-size: 0.8rem;
      padding: 5px 10px;
      border-radius: 20px;
    }
    .section-card {
      border-left: 4px solid #7b145d;
      transition: all 0.3s ease;
    }
    .section-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .flow-step {
      position: relative;
      padding-left: 30px;
    }
    .flow-step:before {
      content: "";
      position: absolute;
      left: 10px;
      top: 0;
      height: 100%;
      width: 2px;
      background: #dee2e6;
    }
    .flow-step.active:before {
      background: #7b145d;
    }
    .flow-step:last-child:before {
      height: 50%;
    }
    .flow-step .step-icon {
      position: absolute;
      left: 0;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #dee2e6;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
    }
    .flow-step.active .step-icon {
      background: #7b145d;
      color: white;
    }
    .empty-data {
      color: #6c757d;
      font-style: italic;
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
        <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" id="createToggle" onclick="toggleSidebarDropdown(event)">
          <i class="bi bi-file-earmark-plus"></i><span>Create</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul class="sidebar-submenu list-unstyled" id="createSubmenu" style="display: none; padding-left: 40px;">
          <li><a class="nav-link" href="create_individual.php">Individual Borrower</a></li>
          <li><a class="nav-link" href="create_corporate.php">Corporate Borrower</a></li>
          <li><a class="nav-link" href="create_co_borrower.php">Joint-Borrower</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="bi bi-folder2-open"></i><span>Records</span></a>
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php"><i class="bi bi-house-door"></i></a></li>
            <li class="breadcrumb-item"><a href="#">Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
          </ol>
        </nav>

        <!-- Profile Header Section -->
        <div class="card profile-header mb-4">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2 text-center">
               <img src="/DAS/assets/images/avatar2.png" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px;">
                <div>
                  <span class="status-badge bg-success text-white">Active</span>
                </div>
              </div>
              <div class="col-md-6">
                <h3 class="mb-1"><?php echo htmlspecialchars($customer['full_name'] ?? 'N/A'); ?></h3>
                <p class="text-muted mb-2"><i class="bi bi-person-badge me-2"></i>Customer ID: <?php echo htmlspecialchars($customer['customer_id'] ?? 'N/A'); ?></p>
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge bg-light text-dark"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></span>
                  <?php if (!empty($customer['email'])): ?>
                    <span class="badge bg-light text-dark"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($customer['email']); ?></span>
                  <?php endif; ?>
                  <span class="badge bg-light text-dark"><i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($customer['province'] ?? 'N/A'); ?></span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-end gap-2">
                  <button class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i> Print</button>
                  <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#"><i class="bi bi-eye me-2"></i>View History</a></li>
                      <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text me-2"></i>Generate Report</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i>Delete</a></li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Profile Sections -->
        <div class="row">
          <div class="col-md-8">
            <!-- Customer Details -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Customer Details</h5>
                <button class="btn btn-sm btn-outline-primary">Edit</button>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                      <tr>
                        <th width="40%">Full Name:</th>
                        <td><?php echo htmlspecialchars($customer['full_name'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Citizenship No:</th>
                        <td><?php echo htmlspecialchars($customer['citizenship_number'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Phone Number:</th>
                        <td><?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Account Number:</th>
                        <td><?php echo !empty($customer['account_number']) ? htmlspecialchars($customer['account_number']) : '<span class="empty-data">-</span>'; ?></td>
                      </tr>
                    </table>
                  </div>
                  <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                      <tr>
                        <th width="40%">Email:</th>
                        <td><?php echo !empty($customer['email']) ? htmlspecialchars($customer['email']) : '<span class="empty-data">-</span>'; ?></td>
                      </tr>
                      <tr>
                        <th>Province:</th>
                        <td><?php echo htmlspecialchars($customer['province'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Branch:</th>
                        <td><?php echo htmlspecialchars($customer['branch'] ?? 'N/A'); ?></td>
                      </tr>
                      <tr>
                        <th>Created On:</th>
                        <td><?php echo !empty($customer['created_at']) ? date('Y-m-d', strtotime($customer['created_at'])) : '<span class="empty-data">-</span>'; ?></td>
                      </tr>
                    </table>
                  </div>
                </div>
                
                <h6 class="mt-4 mb-3"><i class="bi bi-geo-alt me-2"></i>Address Details</h6>
                <div class="row">
                  <div class="col-md-6">
                    <div class="card bg-light mb-3">
                      <div class="card-header bg-light">Permanent Address</div>
                      <div class="card-body">
                        <?php if (isset($addresses['permanent'])): ?>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['permanent']['municipality_vdc'] ?? 'N/A'); ?>, Ward No. <?php echo htmlspecialchars($addresses['permanent']['ward_number'] ?? 'N/A'); ?></p>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['permanent']['district'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($addresses['permanent']['province'] ?? 'N/A'); ?></p>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['permanent']['country'] ?? 'N/A'); ?></p>
                        <?php else: ?>
                          <p class="mb-1 empty-data">No permanent address recorded</p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card bg-light">
                      <div class="card-header bg-light">Temporary Address</div>
                      <div class="card-body">
                        <?php if (isset($addresses['temporary'])): ?>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['temporary']['municipality_vdc'] ?? 'N/A'); ?>, Ward No. <?php echo htmlspecialchars($addresses['temporary']['ward_number'] ?? 'N/A'); ?></p>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['temporary']['district'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($addresses['temporary']['province'] ?? 'N/A'); ?></p>
                          <p class="mb-1"><?php echo htmlspecialchars($addresses['temporary']['country'] ?? 'N/A'); ?></p>
                        <?php else: ?>
                          <p class="mb-1 empty-data">No temporary address recorded</p>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Borrower Details (Placeholder) -->
            <<!-- Borrower Details Section -->
              <div class="card section-card mb-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Borrower Details</h5>
                      <button class="btn btn-sm btn-outline-primary toggle-edit" data-section="borrower">Edit</button>
                  </div>
                  <div class="card-body">
                      <!-- View Mode -->
                      <div class="view-mode" id="borrower-view">
                          <?php if ($borrower): ?>
                              <table class="table table-sm table-borderless">
                                  <tr><th>Full Name:</th><td><?= htmlspecialchars($borrower['full_name']) ?></td></tr>
                                  <tr><th>Date of Birth:</th><td><?= date('Y-m-d', strtotime($borrower['dob'])) ?></td></tr>
                                  <tr><th>Gender:</th><td><?= ucfirst($borrower['gender']) ?></td></tr>
                                  <!-- More fields... -->
                              </table>
                          <?php else: ?>
                              <div class="text-center empty-data">No borrower details added yet</div>
                          <?php endif; ?>
                      </div>
                      
                      <!-- Edit Mode -->
                      <div class="edit-mode d-none" id="borrower-edit">
                          <form id="borrower-form">
                              <div class="row">
                                  <div class="col-md-6">
                                      <div class="mb-3">
                                          <label class="form-label">Full Name (Nepali)</label>
                                          <input type="text" class="form-control nepali-input" name="full_name" 
                                                value="<?= $borrower['full_name'] ?? '' ?>" required>
                                      </div>
                                      <div class="mb-3">
                                          <label class="form-label">Date of Birth</label>
                                          <input type="date" class="form-control" name="dob" 
                                                value="<?= $borrower['dob'] ?? '' ?>" required>
                                      </div>
                                      <!-- More fields... -->
                                  </div>
                              </div>
                              <button type="submit" class="btn btn-primary">Save</button>
                              <button type="button" class="btn btn-secondary cancel-edit" data-section="borrower">Cancel</button>
                          </form>
                      </div>
                  </div>
              </div>

            <!-- Guarantor Details (Placeholder) -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Guarantor Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add</button>
              </div>
              <div class="card-body">
                <div class="text-center empty-data">No guarantors added yet</div>
              </div>
            </div>

            <!-- Security/Collateral Details (Placeholder) -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add</button>
              </div>
              <div class="card-body">
                <div class="text-center empty-data">No collateral added yet</div>
              </div>
            </div>

            <!-- Loan Details (Placeholder) -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Loan Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add</button>
              </div>
              <div class="card-body">
                <div class="text-center empty-data">No loans added yet</div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <!-- Approval Flow Section -->
            <div class="card section-card mb-4">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Approval Flow</h5>
              </div>
              <div class="card-body">
                <div class="flow-step active">
                  <div class="step-icon">1</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Data Entry</h6>
                    <p class="text-muted small mb-1">Completed by: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p class="text-muted small"><?php echo !empty($customer['created_at']) ? date('Y-m-d H:i A', strtotime($customer['created_at'])) : date('Y-m-d H:i A'); ?></p>
                  </div>
                </div>

                <div class="flow-step">
                  <div class="step-icon">2</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Verification</h6>
                    <p class="text-muted small">Pending verification</p>
                  </div>
                </div>

                <div class="flow-step">
                  <div class="step-icon">3</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Credit Approval</h6>
                    <p class="text-muted small">Pending previous steps</p>
                  </div>
                </div>

                <div class="flow-step">
                  <div class="step-icon">4</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Documentation</h6>
                    <p class="text-muted small">Pending previous steps</p>
                  </div>
                </div>

                <div class="flow-step">
                  <div class="step-icon">5</div>
                  <div>
                    <h6 class="mb-1">Disbursement</h6>
                    <p class="text-muted small">Pending previous steps</p>
                  </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                  <button class="btn btn-success" type="button" id="submitForApproval"><i class="bi bi-check-circle me-1"></i> Submit for Verification</button>
                  <button class="btn btn-outline-secondary" type="button"><i class="bi bi-send me-1"></i> Send Back for Correction</button>
                </div>
              </div>
            </div>

            <!-- Activity Log Section -->
            <div class="card section-card">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
              </div>
              <div class="card-body">
                <?php if (!empty($activities)): ?>
                  <?php foreach ($activities as $activity): ?>
                    <div class="d-flex mb-3">
                      <div class="flex-shrink-0 text-primary">
                        <i class="bi bi-<?php echo $activity['action'] === 'view' ? 'eye' : 'pencil'; ?> fs-4"></i>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1"><?php echo ucfirst($activity['action']); ?></h6>
                        <p class="text-muted small mb-0">By <?php echo htmlspecialchars($activity['username']); ?></p>
                        <p class="text-muted small"><?php echo date('Y-m-d H:i A', strtotime($activity['created_at'])); ?></p>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="text-center empty-data">No activities recorded yet</div>
                <?php endif; ?>
              </div>
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

    // Submit for approval confirmation
    document.getElementById('submitForApproval').addEventListener('click', function() {
      if (confirm('Are you sure you want to submit for approval? Once submitted, you cannot make changes.')) {
        // Here you would typically make an AJAX call to update the status
        alert('Submitted for approval successfully!');
        
        // Reload the page to reflect changes
        window.location.reload();
      }
    });
  </script>
</body>
</html>