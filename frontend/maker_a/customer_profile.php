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

// Record view activity
$user_id = $_SESSION['user_id'];
$activity_query = "INSERT INTO activity_log (user_id, customer_id, action, description) 
                   VALUES (?, ?, 'view', 'Viewed customer profile')";
$stmt = mysqli_prepare($conn, $activity_query);
mysqli_stmt_bind_param($stmt, "is", $user_id, $customer_id);
mysqli_stmt_execute($stmt);

// Get last activity
$last_activity_query = "SELECT al.*, u.username 
                       FROM activity_log al
                       JOIN users u ON al.user_id = u.id
                       WHERE al.customer_id = ?
                       ORDER BY al.created_at DESC LIMIT 1";
$stmt = mysqli_prepare($conn, $last_activity_query);
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$last_activity_result = mysqli_stmt_get_result($stmt);
$last_activity = mysqli_fetch_assoc($last_activity_result);

// Check if customer has borrower data
$borrower_query = "SELECT * FROM borrower_details WHERE customer_id = ?";
$stmt = mysqli_prepare($conn, $borrower_query);
mysqli_stmt_bind_param($stmt, "s", $customer_id);
mysqli_stmt_execute($stmt);
$borrower_result = mysqli_stmt_get_result($stmt);
$has_borrower = mysqli_num_rows($borrower_result) > 0;
$borrower_data = $has_borrower ? mysqli_fetch_assoc($borrower_result) : null;

// Check if we're in edit mode
$edit_mode = isset($_GET['edit_borrower']) && $_GET['edit_borrower'] == 'true';



// Address Section 


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
  <link rel="stylesheet" href="../../assets/css/profile.css">
  <style>
    
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

        <!-- Main Profile Section -->
        <div class="card profile-main-card shadow-sm mb-4">
          <div class="card-body">
            <div class="row">
              <!-- Avatar and Status Column -->
              <div class="col-md-2 text-center border-end">
                <img src="/DAS/assets/images/avatar2.png" alt="Customer Avatar" class="rounded-circle profile-avatar mb-2" width="80" height="80">

                <!-- Verification Status Badge -->
                <?php
                $status_class = [
                    'draft' => 'bg-secondary',
                    'submitted' => 'bg-primary',
                    'approved' => 'bg-success',
                    'returned' => 'bg-warning text-dark'
                ];
                $status = $customer['verification_status'] ?? 'draft';
                ?>
                <div class="mt-2">
                  <span class="badge <?php echo $status_class[$status]; ?> px-3 py-2 text-uppercase small">
                    <?php echo ucfirst($status); ?>
                  </span>
                </div>

                <!-- Last Activity -->
                <?php if ($last_activity): ?>
                <div class="mt-4 small text-muted">
                  <strong>Last Activity</strong>
                  <div><?php echo ucfirst($last_activity['action']); ?></div>
                  <div class="activity-timestamp"><?php echo date('Y-m-d H:i A', strtotime($last_activity['created_at'])); ?></div>
                </div>
                <?php endif; ?>
              </div>

              <!-- Customer Details Column -->
              <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap">
                  <div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($customer['full_name'] ?? 'N/A'); ?></h4>
                    <p class="text-muted mb-0"><i class="bi bi-person-badge me-2"></i>Customer ID: <strong><?php echo htmlspecialchars($customer['customer_id'] ?? 'N/A'); ?></strong></p>
                  </div>

                  <div class="d-flex gap-2 mt-2 mt-md-0">
                    <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button>
                    <div class="dropdown">
                      <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text me-2"></i>Generate Report</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" id="deleteCustomerBtn"><i class="bi bi-trash me-2"></i>Delete</a></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Contact & Location Badges -->
                <div class="d-flex flex-wrap gap-2 mb-4">
                  <span class="badge bg-light text-dark"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($customer['phone_number'] ?? 'N/A'); ?></span>
                  <?php if (!empty($customer['email'])): ?>
                    <span class="badge bg-light text-dark"><i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($customer['email']); ?></span>
                  <?php endif; ?>
                  <span class="badge bg-light text-dark"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($customer['province'] ?? 'N/A'); ?></span>
                  <span class="badge bg-light text-dark"><i class="bi bi-building me-1"></i><?php echo htmlspecialchars($customer['branch'] ?? 'N/A'); ?></span>
                </div>

                <!-- Meta Details -->
                <div class="row">
                  <div class="col-md-6">
                   <table class="table table-sm table-borderless">
                    <tr>
                      <th width="40%" class="text-muted">Created On:</th>
                      <td><?php echo !empty($customer['created_at']) ? date('Y-m-d H:i A', strtotime($customer['created_at'])) : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Last Updated:</th>
                      <td><?php echo !empty($customer['updated_at']) ? date('Y-m-d H:i A', strtotime($customer['updated_at'])) : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                    <tr>
                      <th class="text-muted">Updated By:</th>
                      <td><?php echo !empty($customer['updated_by']) ? htmlspecialchars($customer['updated_by']) : '<span class="text-muted">-</span>'; ?></td>
                    </tr>
                  </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

          <!-- Borrower Section -->
          <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Borrower Details</h5>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#borrowerModal">
                <i class="bi bi-person-plus me-1"></i>Add
              </button>
            </div>
            <div class="card-body" id="borrowerContent">
              <?php
              // Fetch borrower details for this customer
              $borrower_sql = "SELECT b.*, 
                  pa.district AS perm_district,
                  f.name AS father_name
                FROM borrower_details b
                LEFT JOIN addresses pa 
                  ON b.id = pa.profile_id AND pa.profile_type = 'borrower' AND pa.address_type = 'permanent'
                LEFT JOIN family_details f 
                  ON b.id = f.profile_id AND f.profile_type = 'borrower' AND f.relation = 'Father Name'
                WHERE b.customer_id = ?
              ";


              $stmt = mysqli_prepare($conn, $borrower_sql);
              mysqli_stmt_bind_param($stmt, "s", $customer_id);
              mysqli_stmt_execute($stmt);
              $result = mysqli_stmt_get_result($stmt);

              if (mysqli_num_rows($result) > 0): ?>
                <div class="table-responsive" id="summaryTableContainer">
                  <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>S.N</th>
                        <th>Full Name</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Citizenship No.</th>
                        <th>Father Name</th>
                        <th>District</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php $sn = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                          <td><?= $sn++ ?></td>
                          <td><?= htmlspecialchars($row['full_name'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($row['dob'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($row['gender'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($row['citizenship_number'] ?? '-') ?></td>
                          <td><?= htmlspecialchars($row['father_name'] ?? '-') ?></td>
                          <td>
                            <?php
                            $address = [];
                            if (!empty($row['perm_country'])) $address[] = $row['perm_country'];
                            if (!empty($row['perm_province'])) $address[] = $row['perm_province'];
                            if (!empty($row['perm_district'])) $address[] = $row['perm_district'];
                            if (!empty($row['perm_municipality'])) $address[] = $row['perm_municipality'];
                            if (!empty($row['perm_ward'])) $address[] = 'Ward ' . $row['perm_ward'];
                            echo $address ? implode(', ', $address) : '-';
                            ?>
                          </td>
                          <td>
                            <button class="btn btn-sm btn-outline-info me-1" title="View" onclick="openBorrowerModal(<?= $row['id'] ?>, true)"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-outline-primary me-1" title="Edit" onclick="openBorrowerModal(<?= $row['id'] ?>, false)"><i class="bi bi-pencil-square"></i></button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    </tbody>
                  </table>
                </div>
              <?php else: ?>
                <div class="text-center empty-data" id="noDataMsg">No borrower details added yet</div>
              <?php endif; ?>
            </div>
          </div>

  <!-- Modal Form -->
  <div class="modal fade" id="borrowerModal" tabindex="-1" aria-labelledby="borrowerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <form id="borrowerForm">
          <div class="modal-header">
            <h5 class="modal-title" id="borrowerModalLabel">Add Borrower Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <!-- Borrower Form Fields (same as your form) -->
            <!-- Personal Details -->
              <h4 class="mt-4 mb-3">Personal Details</h4>
            <div class="row g-3">
  <div class="col-md-3">
    <label class="form-label">Full Name *</label>
    <input type="text" class="form-control" id="full_name" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Date of Birth *</label>
    <input type="date" class="form-control" id="dob" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Gender *</label>
    <select class="form-select" id="gender" required>
      <option value="">Select</option>
      <option>Male</option>
      <option>Female</option>
      <option>Other</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Relationship Status *</label>
    <select class="form-select" id="relationship_status" required>
      <option value="">Select</option>
      <option>Single</option>
      <option>Married</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Citizenship Number *</label>
    <input type="text" class="form-control" id="citizenship_number" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Issue Date *</label>
    <input type="date" class="form-control" id="citizenship_issue_date" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Issue Authority *</label>
    <input type="text" class="form-control" id="citizenship_issue_authority" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Issue District *</label>
    <input type="text" class="form-control" id="citizenship_issue_district" required>
  </div>
</div>

  <!-- Address Details -->
  <h4 class="mt-4 mb-3">Address Details</h4>
  <h6>Permanent Address</h6>
  <div class="row g-3">
    <div class="col-md-3">
      <label class="form-label" for="perm_country">Country *</label>
      <select class="form-select" id="perm_country" name="perm_country" required>
        <option value="Nepal">Nepal</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_province">Province *</label>
      <select class="form-select" id="perm_province" name="perm_province" required>
        <option value="">Select Province</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_district">District *</label>
      <select class="form-select" id="perm_district" name="perm_district" required>
        <option value="">Select District</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_municipality">Municipality/VDC *</label>
      <select class="form-select" id="perm_municipality" name="perm_municipality" required>
        <option value="">Select Municipality/VDC</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_ward">Ward No. *</label>
      <select class="form-select" id="perm_ward" name="perm_ward" required>
        <option value="">Select Ward</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_town_village">Town/Village</label>
      <input type="text" class="form-control" id="perm_town_village">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_tole">Tole</label>
      <input type="text" class="form-control" id="perm_tole">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="perm_street_number">Street Number</label>
      <input type="text" class="form-control" id="perm_street_number">
    </div>
  </div>

  <div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" id="same_address">
    <label class="form-check-label" for="same_address">Temporary address is same as permanent address</label>
  </div>

  <h6 class="mt-3">Temporary Address</h6>
  <div class="row g-3">
    <div class="col-md-3">
      <label class="form-label" for="temp_country">Country *</label>
      <select class="form-select" id="temp_country" name="temp_country" required>
        <option value="Nepal">Nepal</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_province">Province *</label>
      <select class="form-select" id="temp_province" name="temp_province" required>
        <option value="">Select Province</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_district">District *</label>
      <select class="form-select" id="temp_district" name="temp_district" required>
        <option value="">Select District</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_municipality">Municipality/VDC *</label>
      <select class="form-select" id="temp_municipality" name="temp_municipality" required>
        <option value="">Select Municipality/VDC</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_ward">Ward No. *</label>
      <select class="form-select" id="temp_ward" name="temp_ward" required>
        <option value="">Select Ward</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_town_village">Town/Village</label>
      <input type="text" class="form-control" id="temp_town_village">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_tole">Tole</label>
      <input type="text" class="form-control" id="temp_tole">
    </div>
    <div class="col-md-3">
      <label class="form-label" for="temp_street_number">Street Number</label>
      <input type="text" class="form-control" id="temp_street_number">
    </div>
  </div>

  <!-- Family Details -->
  <h4 class="mt-4 mb-3">Family Details</h4>
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Grandfather Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Grandmother Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Father Name</label><input type="text" class="form-control" id="father"></div>
    <div class="col-md-3"><label class="form-label">Mother Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Spouse Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Son Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Daughter Name</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Father In Law</label><input type="text" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Mother In Law</label><input type="text" class="form-control"></div>
  </div>

</div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save Borrower</button>
            <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Discard</button>
            <button type="button" class="btn btn-warning">Draft</button>
          </div>
        </form>
      </div>
    </div>
  </div>

        <!-- Guarantor Details -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Guarantor Details</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#guarantorModal">
              <i class="fa fa-plus me-1"></i>Add
            </button>
          </div>
          <div class="card-body" id="guarantorContent">
            <div class="text-center empty-data" id="noGuarantorMsg">No guarantor details added yet</div>
            <div class="table-responsive d-none" id="guarantorSummaryTableContainer">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>S.N</th>
                    <th>Full Name</th>
                    <th>Date of Birth</th>
                    <th>Citizenship No.</th>
                    <th>Issue District</th>
                    <th>Father Name</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="guarantorTableBody">
                  <!-- Guarantor rows will be added here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Guarantor Modal -->
        <div class="modal fade" id="guarantorModal" tabindex="-1" aria-labelledby="guarantorModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <form id="guarantorForm">
                <div class="modal-header">
                  <h5 class="modal-title" id="guarantorModalLabel">Add Guarantor Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <!-- Personal Details -->
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label class="form-label">Full Name *</label>
                      <input type="text" class="form-control" id="g_fullName" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Date of Birth *</label>
                      <input type="date" class="form-control" id="g_dob" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Gender *</label>
                      <select class="form-select" id="g_gender" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Relationship Status *</label>
                      <select class="form-select" id="g_relationship" required>
                        <option value="">Select</option>
                        <option>Single</option>
                        <option>Married</option>
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Citizenship Number *</label>
                      <input type="text" class="form-control" id="g_citizenship" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Issue Date *</label>
                      <input type="date" class="form-control" id="g_issueDate" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Issue Authority *</label>
                      <input type="text" class="form-control" id="g_issueAuthority" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Issue District *</label>
                      <input type="text" class="form-control" id="g_issueDistrict" required>
                    </div>
                  </div>
                  <!-- Address Details -->
                  <h4 class="mt-4 mb-3">Address Details</h4>
                  <h6>Permanent Address</h6>
                  <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Country *</label><input type="text" class="form-control" id="g_permCountry" required></div>
                    <div class="col-md-3"><label class="form-label">Province *</label><input type="text" class="form-control" id="g_permProvince" required></div>
                    <div class="col-md-3"><label class="form-label">District *</label><input type="text" class="form-control" id="g_permDistrict" required></div>
                    <div class="col-md-3"><label class="form-label">Municipality/VDC *</label><input type="text" class="form-control" id="g_permMunicipality" required></div>
                    <div class="col-md-3"><label class="form-label">Ward No. *</label><input type="text" class="form-control" id="g_permWard" required></div>
                    <div class="col-md-3"><label class="form-label">Town/Village</label><input type="text" class="form-control" id="g_permTown"></div>
                    <div class="col-md-3"><label class="form-label">Tole</label><input type="text" class="form-control" id="g_permTole"></div>
                    <div class="col-md-3"><label class="form-label">Street Number</label><input type="text" class="form-control" id="g_permStreet"></div>
                  </div>
                  <!-- Checkbox -->
                  <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" id="g_sameAddress">
                    <label class="form-check-label" for="g_sameAddress">Temporary address is same as permanent address</label>
                  </div>
                  <!-- Temporary Address -->
                  <h6 class="mt-3">Temporary Address</h6>
                  <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Country *</label><input type="text" class="form-control g_temp" id="g_tempCountry" required></div>
                    <div class="col-md-3"><label class="form-label">Province *</label><input type="text" class="form-control g_temp" id="g_tempProvince" required></div>
                    <div class="col-md-3"><label class="form-label">District *</label><input type="text" class="form-control g_temp" id="g_tempDistrict" required></div>
                    <div class="col-md-3"><label class="form-label">Municipality/VDC *</label><input type="text" class="form-control g_temp" id="g_tempMunicipality" required></div>
                    <div class="col-md-3"><label class="form-label">Ward No. *</label><input type="text" class="form-control g_temp" id="g_tempWard" required></div>
                    <div class="col-md-3"><label class="form-label">Town/Village</label><input type="text" class="form-control g_temp" id="g_tempTown"></div>
                    <div class="col-md-3"><label class="form-label">Tole</label><input type="text" class="form-control g_temp" id="g_tempTole"></div>
                    <div class="col-md-3"><label class="form-label">Street Number</label><input type="text" class="form-control g_temp" id="g_tempStreet"></div>
                  </div>
                  <!-- Family Details -->
                  <h4 class="mt-4 mb-3">Family Details</h4>
                  <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Grandfather Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Grandmother Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Father Name</label><input type="text" class="form-control" id="g_father"></div>
                    <div class="col-md-3"><label class="form-label">Mother Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Spouse Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Son Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Daughter Name</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Father In Law</label><input type="text" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label">Mother In Law</label><input type="text" class="form-control"></div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-primary">Save Guarantor</button>
                  <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Discard</button>
                  <button type="button" class="btn btn-warning">Draft</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Security/Collateral Details -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Collateral Details</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#collateralModal">
              <i class="fa fa-plus me-1"></i>Add
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive d-none" id="collateralTableSection">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>S.N</th>
                    <th>Collateral Type</th>
                    <th>Owner Name</th>
                    <th>Municipality/VDC</th>
                    <th>Wada No.</th>
                    <th>Kitta No.</th>
                    <th>Area</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="collateralTableBody"></tbody>
              </table>
            </div>
            <div class="text-center empty-data py-4" id="noCollateralMsg">No collateral added yet</div>
          </div>
        </div>

        <!-- Collateral Modal -->
        <div class="modal fade" id="collateralModal" tabindex="-1" aria-labelledby="collateralModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <form id="collateralForm">
                <div class="modal-header">
                  <h5 class="modal-title" id="collateralModalLabel">Add Collateral</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label class="form-label">Collateral Type</label>
                    <select id="collateralType" class="form-select" required>
                      <option value="">Select</option>
                      <option value="Land">Land</option>
                      <option value="FD">FD Receipt</option>
                      <option value="Shares">Shares</option>
                      <option value="Debentures">Debentures</option>
                    </select>
                  </div>
                  <!-- Land Fields -->
                  <div id="landFields" class="d-none">
                    <div class="row g-3">
                      <div class="col-md-4"><label>Province</label><input type="text" class="form-control" id="landProvince"></div>
                      <div class="col-md-4"><label>District</label><input type="text" class="form-control" id="landDistrict"></div>
                      <div class="col-md-4"><label>Municipality/VDC</label><input type="text" class="form-control" id="landMunicipality"></div>
                      <div class="col-md-2"><label>Wada No.</label><input type="text" class="form-control" id="landWada"></div>
                      <div class="col-md-2"><label>Sheet No.</label><input type="text" class="form-control" id="landSheet"></div>
                      <div class="col-md-2"><label>Kitta No.</label><input type="text" class="form-control" id="landKitta"></div>
                      <div class="col-md-3"><label>Area (sq.ft.)</label><input type="text" class="form-control" id="landArea"></div>
                      <div class="col-md-3"><label>Malpot Office</label><input type="text" class="form-control" id="landMalpot"></div>
                      <div class="col-md-6">
                        <label>Registered Owner</label>
                        <select class="form-select" id="landOwner">
                          <!-- Options will be populated by JS -->
                        </select>
                      </div>
                    </div>
                  </div>
                  <!-- Placeholder for other types -->
                  <div id="fdFields" class="d-none text-muted mt-3">FD Receipt form will be added later.</div>
                  <div id="sharesFields" class="d-none text-muted mt-3">Shares form will be added later.</div>
                  <div id="debentureFields" class="d-none text-muted mt-3">Debentures form will be added later.</div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">Save</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Loan Details Section -->
        <div class="card mb-4 shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Loan Details</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#loanModal">
              <i class="fa fa-plus me-1"></i>Add
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive d-none" id="loanTableSection">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>S.N</th>
                    <th>Loan Type</th>
                    <th>Loan Purpose</th>
                    <th>Loan Limit</th>
                    <th>Interest Rate</th>
                    <th>Loan Amount</th>
                    <th>Approved Date</th>
                    <th>Tenure</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="loanTableBody"></tbody>
              </table>
            </div>
            <div class="text-center empty-data py-4" id="noLoanMsg">No loans added yet</div>
          </div>
        </div>

        <!-- Loan Modal -->
        <div class="modal fade" id="loanModal" tabindex="-1" aria-labelledby="loanModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
              <form id="loanForm">
                <div class="modal-header">
                  <h5 class="modal-title" id="loanModalLabel">Add Loan Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label>Loan Type</label>
                      <select class="form-select" id="loanType" required>
                        <option value="">Select</option>
                        <option>Personal</option>
                        <option>Home</option>
                        <option>Business</option>
                        <option>Education</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label>Loan Purpose</label>
                      <input type="text" class="form-control" id="loanPurpose" required>
                    </div>
                    <div class="col-md-4">
                      <label>Loan Limit (Rs.)</label>
                      <input type="number" class="form-control" id="loanLimit" required>
                    </div>
                    <div class="col-md-4">
                      <label>Interest Rate (%)</label>
                      <input type="number" step="0.01" class="form-control" id="interestRate" required>
                    </div>
                    <div class="col-md-4">
                      <label>Loan Amount (Rs.)</label>
                      <input type="number" class="form-control" id="loanAmount" required>
                    </div>
                    <div class="col-md-4">
                      <label>Approved Date</label>
                      <input type="date" class="form-control" id="approvedDate" required>
                    </div>
                    <div class="col-md-4">
                      <label>Tenure (months)</label>
                      <input type="number" class="form-control" id="tenure" required>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success">Save</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Approval Flow Section -->
        <div class="card mb-4">
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
                <h6 class="mb-1">Submitted</h6>
                <p class="text-muted small">Pending verification</p>
              </div>
            </div>

            <div class="flow-step">
              <div class="step-icon">3</div>
              <div class="mb-3">
                <h6 class="mb-1">Reviewer</h6>
                <p class="text-muted small">Pending previous steps</p>
              </div>
            </div>

            <div class="flow-step">
              <div class="step-icon">4</div>
              <div class="mb-3">
                <h6 class="mb-1">Approved/Returned</h6>
                <p class="text-muted small">Pending previous steps</p>
              </div>
            </div>

            <div class="flow-step">
              <div class="step-icon">5</div>
              <div>
                <h6 class="mb-1">Document Generated</h6>
                <p class="text-muted small">Pending previous steps</p>
              </div>
            </div>

            <div class="d-grid gap-2 mt-4">
              <button class="btn btn-success" type="button" id="submitForApproval"><i class="bi bi-check-circle me-1"></i> Submit for Verification</button>
              <button class="btn btn-outline-secondary" type="button"><i class="bi bi-send me-1"></i> Send Back for Correction</button>
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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../assets/js/dashboard.js"></script>
  <script src="../../assets/js/profile.js"></script>
  <script>
    // Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Set current date and time in footer
function updateDateTime() {
    const now = new Date();
    document.getElementById('datetime').textContent = now.toLocaleString();
}
setInterval(updateDateTime, 1000);
updateDateTime();

let borrowerList = [];
  let editIndex = null;

  const form = document.getElementById('borrowerForm');
  const tableBody = document.getElementById('borrowerTableBody');
  const noDataMsg = document.getElementById('noDataMsg');
  const tableContainer = document.getElementById('summaryTableContainer');

  form.addEventListener('submit', function(e) {
  e.preventDefault();

  // Collect personal details
  const data = {
    customer_id: "<?php echo htmlspecialchars($customer_id); ?>",
    full_name: document.getElementById('full_name').value.trim(),
    dob: document.getElementById('dob').value,
    gender: document.getElementById('gender').value,
    relationship_status: document.getElementById('relationship_status').value,
    citizenship_number: document.getElementById('citizenship_number').value,
    citizenship_issue_date: document.getElementById('citizenship_issue_date').value,
    citizenship_issue_authority: document.getElementById('citizenship_issue_authority').value,
    citizenship_issue_district: document.getElementById('citizenship_issue_district').value
  };

  // Collect permanent address
  const permAddress = {
    type: 'permanent',
    country: document.getElementById('perm_country').value.trim(),
    province: document.getElementById('perm_province').value.trim(),
    district: document.getElementById('perm_district').value.trim(),
    municipality_vdc: document.getElementById('perm_municipality').value.trim(),
    ward_number: document.getElementById('perm_ward').value.trim(),
    town_village: document.getElementById('perm_town_village').value.trim(),
    tole: document.getElementById('perm_tole').value.trim(),
    street_number: document.getElementById('perm_street_number').value.trim()
  };

  // Collect temporary address
  const tempAddress = {
    type: 'temporary',
    country: document.getElementById('temp_country').value.trim(),
    province: document.getElementById('temp_province').value.trim(),
    district: document.getElementById('temp_district').value.trim(),
    municipality_vdc: document.getElementById('temp_municipality').value.trim(),
    ward_number: document.getElementById('temp_ward').value.trim(),
    town_village: document.getElementById('temp_town_village').value.trim(),
    tole: document.getElementById('temp_tole').value.trim(),
    street_number: document.getElementById('temp_street_number').value.trim()
  };

  // Collect family details
  const familyDetails = [
    { relation: 'Grandfather Name', name: document.querySelector('input[placeholder="Grandfather Name"], input[aria-label="Grandfather Name"], input.form-control:nth-of-type(1)').value.trim() },
    { relation: 'Grandmother Name', name: document.querySelector('input[placeholder="Grandmother Name"], input[aria-label="Grandmother Name"], input.form-control:nth-of-type(2)').value.trim() },
    { relation: 'Father Name', name: document.getElementById('father').value.trim() },
    { relation: 'Mother Name', name: document.querySelector('input[placeholder="Mother Name"], input[aria-label="Mother Name"], input.form-control:nth-of-type(4)').value.trim() },
    { relation: 'Spouse Name', name: document.querySelector('input[placeholder="Spouse Name"], input[aria-label="Spouse Name"], input.form-control:nth-of-type(5)').value.trim() },
    { relation: 'Son Name', name: document.querySelector('input[placeholder="Son Name"], input[aria-label="Son Name"], input.form-control:nth-of-type(6)').value.trim() },
    { relation: 'Daughter Name', name: document.querySelector('input[placeholder="Daughter Name"], input[aria-label="Daughter Name"], input.form-control:nth-of-type(7)').value.trim() },
    { relation: 'Father In Law', name: document.querySelector('input[placeholder="Father In Law"], input[aria-label="Father In Law"], input.form-control:nth-of-type(8)').value.trim() },
    { relation: 'Mother In Law', name: document.querySelector('input[placeholder="Mother In Law"], input[aria-label="Mother In Law"], input.form-control:nth-of-type(9)').value.trim() }
  ].filter(fd => fd.name); // Only send non-empty names

  // Simple validation
  if (!data.full_name || !data.dob || !data.citizenship_number) {
    alert("Please fill in all required fields.");
    return;
  }

  // Show loader
  document.getElementById('loaderOverlay').classList.remove('d-none');

  // AJAX POST to backend
  $.ajax({
    url: '../../backend/save_borrower.php',
    type: 'POST',
    data: {
      ...data,
      family_details: JSON.stringify(familyDetails),
      addresses: JSON.stringify([permAddress, tempAddress])
    },
    success: function(response) {
      document.getElementById('loaderOverlay').classList.add('d-none');
      try {
        const res = JSON.parse(response);
        if (res.success) {
          alert('Borrower & family details saved!');
          location.reload();
        } else {
          alert('Error: ' + res.message);
        }
      } catch (e) {
        alert('Unexpected response from server.');
      }
    },
    error: function() {
      document.getElementById('loaderOverlay').classList.add('d-none');
      alert('Failed to save borrower.');
    }
  });
});

  function renderTable() {
    if (borrowerList.length === 0) {
      noDataMsg.classList.remove('d-none');
      tableContainer.classList.add('d-none');
      return;
    }

    tableBody.innerHTML = '';
    borrowerList.forEach((b, i) => {
      const row = `<tr>
        <td>${i + 1}</td>
        <td>${b.fullName}</td>
        <td>${b.dob}</td>
        <td>${b.citizenship}</td>
        <td>${b.issueDistrict}</td>
        <td>${b.fatherName}</td>
        <td>
          <button class="btn btn-sm btn-outline-info me-1" onclick="showBorrower(${i}, true)"><i class="fa fa-eye"></i></button>
          <button class="btn btn-sm btn-outline-primary" onclick="showBorrower(${i}, false)"><i class="fa fa-edit"></i></button>
        </td>
      </tr>`;
      tableBody.insertAdjacentHTML('beforeend', row);
    });

    noDataMsg.classList.add('d-none');
    tableContainer.classList.remove('d-none');
  }

  function fillForm(data) {
    document.getElementById('fullName').value = data.fullName;
    document.getElementById('dob').value = data.dob;
    document.getElementById('gender').value = data.gender;
    document.getElementById('relationship').value = data.relationship;
    document.getElementById('citizenship').value = data.citizenship;
    document.getElementById('issueDate').value = data.issueDate;
    document.getElementById('issueAuthority').value = data.issueAuthority;
    document.getElementById('issueDistrict').value = data.issueDistrict;
    document.getElementById('fatherName').value = data.fatherName;
  }

  function setFormReadOnly(readOnly) {
    const fields = ['fullName','dob','gender','relationship','citizenship','issueDate','issueAuthority','issueDistrict','fatherName'];
    fields.forEach(id => {
      const el = document.getElementById(id);
      if (readOnly) {
        el.setAttribute('readonly', 'readonly');
        el.setAttribute('disabled', 'disabled');
      } else {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
      }
    });
  }

  function populateAddressFields(addressType, data) {
    // addressType: 'perm' or 'temp'
    $('#' + addressType + '_province').val(data.province).trigger('change');
    // Wait for districts to load
    setTimeout(function () {
        $('#' + addressType + '_district').val(data.district).trigger('change');
        // Wait for municipalities to load
        setTimeout(function () {
            $('#' + addressType + '_municipality').val(data.municipality_vdc).trigger('change');
            // Wait for wada to load
            setTimeout(function () {
                $('#' + addressType + '_wada').val(data.ward_no);
            }, 300);
        }, 300);
    }, 300);
}

// When opening modal for view/edit
window.showBorrower = function(index, isView) {
    fillForm(borrowerList[index]);
    setFormReadOnly(isView);
    editIndex = isView ? null : index;

    // Example: Fill permanent address
    const permAddress = borrowerList[index].permAddress; // Make sure your data structure has this
    if (permAddress) {
        populateAddressFields('perm', permAddress);
    }
    // Example: Fill temporary address
    const tempAddress = borrowerList[index].tempAddress;
    if (tempAddress) {
        populateAddressFields('temp', tempAddress);
    }

    const modal = new bootstrap.Modal(document.getElementById('borrowerModal'));
    modal.show();
  }


  // Address copy functionality
document.getElementById('same_address').addEventListener('change', function() {
  const fields = [
    'country', 'province', 'district', 'municipality', 'ward', 'town_village', 'tole', 'street_number'
  ];
  if (this.checked) {
    fields.forEach(field => {
      // For dropdowns, trigger change to load dependent fields
      if (['province', 'district', 'municipality'].includes(field)) {
        $('#temp_' + field).val($('#perm_' + field).val()).trigger('change');
      } else {
        document.getElementById('temp_' + field).value = document.getElementById('perm_' + field).value;
      }
    });
    // For ward, copy value directly after municipality is loaded
    setTimeout(function() {
      $('#temp_ward').val($('#perm_ward').val());
    }, 400);
  } else {
    fields.forEach(field => {
      document.getElementById('temp_' + field).value = '';
    });
  }
});


// Guarantor section
let guarantorList = [];
let g_editIndex = null;

const g_form = document.getElementById('guarantorForm');
const g_tableBody = document.getElementById('guarantorTableBody');
const g_noDataMsg = document.getElementById('noGuarantorMsg');
const g_tableContainer = document.getElementById('guarantorSummaryTableContainer');

g_form.addEventListener('submit', function(e) {
  e.preventDefault();
  const data = {
    fullName: document.getElementById('g_fullName').value.trim(),
    dob: document.getElementById('g_dob').value,
    gender: document.getElementById('g_gender').value,
    relationship: document.getElementById('g_relationship').value,
    citizenship: document.getElementById('g_citizenship').value,
    issueDate: document.getElementById('g_issueDate').value,
    issueAuthority: document.getElementById('g_issueAuthority').value,
    issueDistrict: document.getElementById('g_issueDistrict').value,
    fatherName: document.getElementById('g_father').value.trim()
  };
  if (!data.fullName || !data.dob || !data.citizenship) {
    alert("Please fill in all required fields.");
    return;
  }
  if (g_editIndex !== null) {
    guarantorList[g_editIndex] = data;
    g_editIndex = null;
  } else {
    guarantorList.push(data);
  }
  renderGuarantorTable();
  g_form.reset();
  const modal = bootstrap.Modal.getInstance(document.getElementById('guarantorModal'));
  modal.hide();
});

function renderGuarantorTable() {
  if (guarantorList.length === 0) {
    g_noDataMsg.classList.remove('d-none');
    g_tableContainer.classList.add('d-none');
    return;
  }
  g_tableBody.innerHTML = '';
  guarantorList.forEach((g, i) => {
    const row = `<tr>
      <td>${i + 1}</td>
      <td>${g.fullName}</td>
      <td>${g.dob}</td>
      <td>${g.citizenship}</td>
      <td>${g.issueDistrict}</td>
      <td>${g.fatherName}</td>
      <td>
        <button class="btn btn-sm btn-outline-info me-1" onclick="showGuarantor(${i}, true)"><i class="fa fa-eye"></i></button>
        <button class="btn btn-sm btn-outline-primary" onclick="showGuarantor(${i}, false)"><i class="fa fa-edit"></i></button>
      </td>
    </tr>`;
    g_tableBody.insertAdjacentHTML('beforeend', row);
  });
  g_noDataMsg.classList.add('d-none');
  g_tableContainer.classList.remove('d-none');
}

function g_fillForm(data) {
  document.getElementById('g_fullName').value = data.fullName;
  document.getElementById('g_dob').value = data.dob;
  document.getElementById('g_gender').value = data.gender;
  document.getElementById('g_relationship').value = data.relationship;
  document.getElementById('g_citizenship').value = data.citizenship;
  document.getElementById('g_issueDate').value = data.issueDate;
  document.getElementById('g_issueAuthority').value = data.issueAuthority;
  document.getElementById('g_issueDistrict').value = data.issueDistrict;
  document.getElementById('g_father').value = data.fatherName;
}

function g_setFormReadOnly(readOnly) {
  const fields = ['g_fullName','g_dob','g_gender','g_relationship','g_citizenship','g_issueDate','g_issueAuthority','g_issueDistrict','g_father'];
  fields.forEach(id => {
    const el = document.getElementById(id);
    if (readOnly) {
      el.setAttribute('readonly', 'readonly');
      el.setAttribute('disabled', 'disabled');
    } else {
      el.removeAttribute('readonly');
      el.removeAttribute('disabled');
    }
  });
}

window.showGuarantor = function(index, isView) {
  g_fillForm(guarantorList[index]);
  g_setFormReadOnly(isView);
  g_editIndex = isView ? null : index;
  const modal = new bootstrap.Modal(document.getElementById('guarantorModal'));
  modal.show();
}

// --- ADD: Guarantor "same as permanent address" checkbox ---
document.getElementById('g_sameAddress').addEventListener('change', function() {
  const fields = ['Country','Province','District','Municipality','Ward','Town','Tole','Street'];
  if (this.checked) {
    fields.forEach(f => {
      document.getElementById('g_temp' + f).value =
        document.getElementById('g_perm' + f).value;
    });
  } else {
    fields.forEach(f => {
      document.getElementById('g_temp' + f).value = '';
    });
  }
});

// Collateral Section Logic
let collateralList = [];
let collateralEditIndex = null;

document.getElementById('collateralType').addEventListener('change', function() {
  const v = this.value;
  document.getElementById('landFields').classList.toggle('d-none', v !== 'Land');
  document.getElementById('fdFields').classList.toggle('d-none', v !== 'FD');
  document.getElementById('sharesFields').classList.toggle('d-none', v !== 'Shares');
  document.getElementById('debentureFields').classList.toggle('d-none', v !== 'Debentures');
});

document.getElementById('collateralForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const type = document.getElementById('collateralType').value;
  let data = {
    type: type,
    owner: document.getElementById('landOwner').value || '-',
    municipality: document.getElementById('landMunicipality').value || '-',
    wada: document.getElementById('landWada').value || '-',
    kitta: document.getElementById('landKitta').value || '-',
    area: document.getElementById('landArea').value || '-'
  };
  if (collateralEditIndex !== null) {
    collateralList[collateralEditIndex] = data;
    collateralEditIndex = null;
  } else {
    collateralList.push(data);
  }
  renderCollateralTable();
  this.reset();
  document.getElementById('landFields').classList.add('d-none');
  bootstrap.Modal.getInstance(document.getElementById('collateralModal')).hide();
});

function renderCollateralTable() {
  const tbody = document.getElementById('collateralTableBody');
  tbody.innerHTML = '';
  if (collateralList.length === 0) {
    document.getElementById('collateralTableSection').classList.add('d-none');
    document.getElementById('noCollateralMsg').classList.remove('d-none');
    return;
  }
  collateralList.forEach((c, i) => {
    tbody.innerHTML += `<tr>
      <td>${i+1}</td>
      <td>${c.type || '-'}</td>
      <td>${c.owner || '-'}</td>
      <td>${c.municipality || '-'}</td>
      <td>${c.wada || '-'}</td>
      <td>${c.kitta || '-'}</td>
      <td>${c.area || '-'}</td>
      <td>
        <button class="btn btn-sm btn-outline-info me-1" onclick="viewCollateral(${i})"><i class="fa fa-eye"></i></button>
        <button class="btn btn-sm btn-outline-primary me-1" onclick="editCollateral(${i})"><i class="fa fa-edit"></i></button>
        <button class="btn btn-sm btn-outline-danger" onclick="deleteCollateral(${i})"><i class="fa fa-trash"></i></button>
      </td>
    </tr>`;
  });
  document.getElementById('collateralTableSection').classList.remove('d-none');
  document.getElementById('noCollateralMsg').classList.add('d-none');
}

window.viewCollateral = function(i) {
  alert('View Collateral:\n' + JSON.stringify(collateralList[i], null, 2));
};
window.editCollateral = function(i) {
  const c = collateralList[i];
  document.getElementById('collateralType').value = c.type;
  document.getElementById('collateralType').dispatchEvent(new Event('change'));
  document.getElementById('landOwner').value = c.owner;
  document.getElementById('landMunicipality').value = c.municipality;
  document.getElementById('landWada').value = c.wada;
  document.getElementById('landKitta').value = c.kitta;
  document.getElementById('landArea').value = c.area;
  collateralEditIndex = i;
  new bootstrap.Modal(document.getElementById('collateralModal')).show();
};
window.deleteCollateral = function(i) {
  if (confirm('Delete this collateral?')) {
    collateralList.splice(i, 1);
    renderCollateralTable();
  }
};


$(document).ready(function () {
    // Fetch provinces for both Permanent and Temporary Address
    $.ajax({
        url: '?action=fetch_province',
        method: 'GET',
        success: function (response) {
            $('#perm_province, #temp_province').append(response);
        }
    });

    // Handle changes for Permanent Address
    $('#perm_province').on('change', function () {
        fetchDistricts('#perm_district', $(this).val());
        // Reset downstream fields
        $('#perm_municipality').html('<option value="">Select Municipality/VDC</option>');
        $('#perm_wada').html('<option value="">Select Wada</option>');
    });

    $('#perm_district').on('change', function () {
        fetchMunicipalities('#perm_municipality', $(this).val());
        // Reset downstream field
        $('#perm_wada').html('<option value="">Select Wada</option>');
    });

    $('#perm_municipality').on('change', function () {
        fetchWada('#perm_wada', $(this).val());
    });

    // Handle changes for Temporary Address
    $('#temp_province').on('change', function () {
        fetchDistricts('#temp_district', $(this).val());
        // Reset downstream fields
        $('#temp_municipality').html('<option value="">Select Municipality/VDC</option>');
        $('#temp_wada').html('<option value="">Select Wada</option>');
    });

    $('#temp_district').on('change', function () {
        fetchMunicipalities('#temp_municipality', $(this).val());
        // Reset downstream field
        $('#temp_wada').html('<option value="">Select Wada</option>');
    });

    $('#temp_municipality').on('change', function () {
        fetchWada('#temp_wada', $(this).val());
    });

    // Copy address logic
    $('#copy_address').on('change', function () {
        if ($(this).is(':checked')) {
            // Copy all fields
            $('#temp_country').val($('#perm_country').val());
            $('#temp_province').val($('#perm_province').val()).trigger('change');
            
            setTimeout(function () {
                $('#temp_district').val($('#perm_district').val()).trigger('change');
            }, 200);

            setTimeout(function () {
                $('#temp_municipality').val($('#perm_municipality').val()).trigger('change');
            }, 400);

            setTimeout(function () {
                $('#temp_wada').val($('#perm_wada').val());
                $('#temptown_village').val($('#permtown_village').val());
                $('#temptole').val($('#permtole').val());
                $('#tempstreet_number').val($('#permstreet_number').val());
            }, 600);
        } else {
            // Reset all fields
            $('#temp_province, #temp_district, #temp_municipality, #temp_wada').val('');
            $('#temptown_village, #temptole, #tempstreet_number').val('');
        }
    });

    // Helper functions for AJAX requests
    function fetchDistricts(target, province) {
        if (province) {
            $.ajax({
                url: '?action=fetch_district',
                method: 'GET',
                data: { province: province },
                success: function (response) {
                    $(target).html('<option value="">Select District</option>' + response);
                }
            });
        }
    }

    function fetchMunicipalities(target, district) {
        if (district) {
            $.ajax({
                url: '?action=fetch_municipality',
                method: 'GET',
                data: { district: district },
                success: function (response) {
                    $(target).html('<option value="">Select Municipality/VDC</option>' + response);
                }
            });
        }
    }

    function fetchWada(target, municipality) {
        if (municipality) {
            $.ajax({
                url: '?action=fetch_wada',
                method: 'GET',
                data: { municipality: municipality },
                success: function (response) {
                    $(target).html('<option value="">Select Wada</option>' + response);
                }
            });
        }
    }
});

function openBorrowerModal(borrowerId, isViewMode) {
    $('#loaderOverlay').removeClass('d-none');
    $.ajax({
        url: '../../backend/get_borrower.php',
        type: 'GET',
        data: { id: borrowerId },
        success: function(response) {
            let data;
            try {
                data = typeof response === 'string' ? JSON.parse(response) : response;
            } catch (e) {
                alert('Failed to parse borrower details.');
                $('#loaderOverlay').addClass('d-none');
                return;
            }
            // Fill personal details
            $('#full_name').val(data.full_name || '');
            $('#dob').val(data.dob || '');
            $('#gender').val(data.gender || '');
            $('#relationship_status').val(data.relationship_status || '');
            $('#citizenship_number').val(data.citizenship_number || '');
            $('#citizenship_issue_date').val(data.citizenship_issue_date || '');
            $('#citizenship_issue_authority').val(data.citizenship_issue_authority || '');
            $('#citizenship_issue_district').val(data.citizenship_issue_district || '');

            // Fill permanent address
            $('#perm_country').val(data.perm_country || 'Nepal');
            $('#perm_province').val(data.perm_province || '').trigger('change');
            setTimeout(() => {
                $('#perm_district').val(data.perm_district || '').trigger('change');
                setTimeout(() => {
                    $('#perm_municipality').val(data.perm_municipality || '').trigger('change');
                    setTimeout(() => {
                        $('#perm_ward').val(data.perm_ward || '');
                    }, 300);
                }, 300);
            }, 300);
            $('#perm_town_village').val(data.perm_town_village || '');
            $('#perm_tole').val(data.perm_tole || '');
            $('#perm_street_number').val(data.perm_street_number || '');

            // Fill temporary address
            $('#temp_country').val(data.temp_country || 'Nepal');
            $('#temp_province').val(data.temp_province || '').trigger('change');
            setTimeout(() => {
                $('#temp_district').val(data.temp_district || '').trigger('change');
                setTimeout(() => {
                    $('#temp_municipality').val(data.temp_municipality || '').trigger('change');
                    setTimeout(() => {
                        $('#temp_ward').val(data.temp_ward || '');
                    }, 300);
                }, 300);
            }, 300);
            $('#temp_town_village').val(data.temp_town_village || '');
            $('#temp_tole').val(data.temp_tole || '');
            $('#temp_street_number').val(data.temp_street_number || '');

            // Fill family details
            $('input[placeholder="Grandfather Name"]').val(data.grandfather_name || '');
            $('input[placeholder="Grandmother Name"]').val(data.grandmother_name || '');
            $('#father').val(data.father_name || '');
            $('input[placeholder="Mother Name"]').val(data.mother_name || '');
            $('input[placeholder="Spouse Name"]').val(data.spouse_name || '');
            $('input[placeholder="Son Name"]').val(data.son_name || '');
            $('input[placeholder="Daughter Name"]').val(data.daughter_name || '');
            $('input[placeholder="Father In Law"]').val(data.father_in_law || '');
            $('input[placeholder="Mother In Law"]').val(data.mother_in_law || '');

            // Set fields readonly/disabled for view mode, editable for edit mode
            $('#borrowerForm input, #borrowerForm select').each(function() {
                if (isViewMode) {
                    $(this).prop('readonly', true).prop('disabled', true);
                } else {
                    $(this).prop('readonly', false).prop('disabled', false);
                }
            });

            // Show/hide Save button based on mode
            if (isViewMode) {
                $('#borrowerForm button[type="submit"]').hide();
                $('#borrowerForm button.btn-warning').hide();
            } else {
                $('#borrowerForm button[type="submit"]').show();
                $('#borrowerForm button.btn-warning').show();
            }

            // Store borrower ID in form for update
            $('#borrowerForm').data('borrower-id', borrowerId);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('borrowerModal'));
            modal.show();
            $('#loaderOverlay').addClass('d-none');
        },
        error: function() {
            $('#loaderOverlay').addClass('d-none');
            alert('Failed to load borrower details.');
        }
    });
}

// Handle edit save
$('#viewEditBorrowerForm').on('submit', function(e) {
  e.preventDefault();
 

  const borrowerId = $(this).data('borrower-id');
  const payload = {
    id: borrowerId,
    full_name: $('#edit_full_name').val(),
    dob: $('#edit_dob').val(),
    gender: $('#edit_gender').val(),
    relationship_status: $('#edit_relationship_status').val(),
    citizenship_number: $('#edit_citizenship_number').val(),
    citizenship_issue_date: $('#edit_citizenship_issue_date').val(),
    citizenship_issue_authority: $('#edit_citizenship_issue_authority').val(),
    citizenship_issue_district: $('#edit_citizenship_issue_district').val(),
    perm_country: $('#edit_perm_country').val(),
    perm_province: $('#edit_perm_province').val(),
    perm_district: $('#edit_perm_district').val(),
    perm_municipality: $('#edit_perm_municipality').val(),
    perm_ward: $('#edit_perm_wada').val(),
    permtown_village: $('#edit_permtown_village').val(),
    permtole: $('#edit_permtole').val(),
    permstreet_number: $('#edit_permstreet_number').val(),
    temp_country: $('#edit_temp_country').val(),
    temp_province: $('#edit_temp_province').val(),
    temp_district: $('#edit_temp_district').val(),
    temp_municipality: $('#edit_temp_municipality').val(),
    temp_ward: $('#edit_temp_wada').val(),
    temptown_village: $('#edit_temptown_village').val(),
    temptole: $('#edit_temptole').val(),
    tempstreet_number: $('#edit_tempstreet_number').val(),
    grandfather_name: $('#edit_grandfather').val(),
    grandmother_name: $('#edit_grandmother').val(),
    father_name: $('#edit_father').val(),
    mother_name: $('#edit_mother').val(),
    spouse_name: $('#edit_spouse').val(),
    son_name: $('#edit_son').val(),
    daughter_name: $('#edit_daughter').val(),
    father_in_law: $('#edit_father_in_law').val(),
    mother_in_law: $('#edit_mother_in_law').val()
  };
  $.ajax({
    url: '../../backend/update_borrower.php',
    type: 'POST',
    data: payload,
    success: function(response) {
      try {
        const res = JSON.parse(response);
        if (res.success) {
          alert('Borrower details updated!');
          location.reload();
        } else {
          alert('Error: ' + res.message);
        }
      } catch (e) {
        alert('Unexpected response from server.');
      }
    },
    error: function() {
      alert('Failed to update borrower.');
    }
  });
});

function initializeAddressDropdowns() {
  // Load provinces
  $.ajax({
    url: '../../backend/get_provinces.php',
    method: 'GET',
    success: function(response) {
      $('#perm_province, #temp_province').html('<option value="">Select Province</option>' + response);
    }
  });

  // Province change handler
  $('#perm_province, #temp_province').on('change', function() {
    const province = $(this).val();
    const prefix = this.id.startsWith('perm') ? 'perm' : 'temp';
    if (province) {
      $.ajax({
        url: '../../backend/get_districts.php',
        method: 'GET',
        data: { province: province },
        success: function(response) {
          $(`#${prefix}_district`).html('<option value="">Select District</option>' + response);
        }
      });
    }
  });

  // District change handler
  $('#perm_district, #temp_district').on('change', function() {
    const district = $(this).val();
    const prefix = this.id.startsWith('perm') ? 'perm' : 'temp';
    if (district) {
      $.ajax({
        url: '../../backend/get_municipalities.php',
        method: 'GET',
        data: { district: district },
        success: function(response) {
          $(`#${prefix}_municipality`).html('<option value="">Select Municipality/VDC</option>' + response);
        }
      });
    }
  });

  // Municipality change handler
  $('#perm_municipality, #temp_municipality').on('change', function() {
    const municipality = $(this).val();
    const prefix = this.id.startsWith('perm') ? 'perm' : 'temp';
    if (municipality) {
      $.ajax({
        url: '../../backend/get_wards.php',
        method: 'GET',
        data: { municipality: municipality },
        success: function(response) {
          $(`#${prefix}_ward`).html('<option value="">Select Ward</option>' + response);
        }
      });
    }
  });
}

// Call the initialization when DOM is ready
$(document).ready(function() {
  initializeAddressDropdowns();
});

// Populate ward dropdowns (example for 1-20 wards)
function populateWardDropdown(prefix) {
  let options = '<option value="">Select Ward</option>';
  for (let i = 1; i <= 20; i++) {
    options += `<option value="${i}">${i}</option>`;
  }
  document.getElementById(prefix + '_ward').innerHTML = options;
}

// Call this after municipality is selected
$('#perm_municipality_vdc, #temp_municipality_vdc').on('change', function() {
  const prefix = this.id.startsWith('perm') ? 'perm' : 'temp';
  populateWardDropdown(prefix);
});
  </script>
  <!-- Loader Overlay -->
<div id="loaderOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 9999;">
    <div class="d-flex justify-content-center align-items-center h-100">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

  <!-- View/Edit Borrower Modal -->
<div class="modal fade" id="viewEditBorrowerModal" tabindex="-1" aria-labelledby="viewEditBorrowerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="viewEditBorrowerForm">
        <div class="modal-header">
          <h5 class="modal-title" id="viewEditBorrowerModalLabel">Borrower Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Same fields as Add Borrower Modal, but with IDs prefixed by "edit_" -->
          <!-- Personal Details -->
          <h4 class="mt-4 mb-3">Personal Details</h4>
          <div class="row g-3">
            <div class="col-md-3">
              <label for="edit_full_name" class="form-label">Full Name *</label>
              <input type="text" class="form-control" id="edit_full_name" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Date of Birth *</label>
              <input type="date" class="form-control" id="edit_dob" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Gender *</label>
              <select class="form-select" id="edit_gender" required>
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
                <option>Other</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Relationship Status *</label>
              <select class="form-select" id="edit_relationship_status" required>
                <option value="">Select</option>
                <option>Single</option>
                <option>Married</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Citizenship Number *</label>
              <input type="text" class="form-control" id="edit_citizenship_number" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Issue Date *</label>
              <input type="date" class="form-control" id="edit_citizenship_issue_date" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Issue Authority *</label>
              <input type="text" class="form-control" id="edit_citizenship_issue_authority" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Issue District *</label>
              <input type="text" class="form-control" id="edit_citizenship_issue_district" required>
            </div>
          </div>
          <!-- Address Details -->
          <h4 class="mt-4 mb-3">Address Details</h4>
          <h6>Permanent Address</h6>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label" for="edit_perm_country">Country *</label>
              <select class="form-select" id="edit_perm_country" required>
                <option value="Nepal">Nepal</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_perm_province">Province *</label>
              <select class="form-select" id="edit_perm_province" required>
                <option value="">Select Province</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_perm_district">District *</label>
              <select class="form-select" id="edit_perm_district" required>
                <option value="">Select District</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_perm_municipality">Municipality/VDC *</label>
              <select class="form-select" id="edit_perm_municipality" required>
                <option value="">Select Municipality/VDC</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_perm_wada">Ward No. *</label>
              <select class="form-select" id="edit_perm_wada" required>
                <option value="">Select Ward</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_permtown_village">Town/Village</label>
              <input type="text" class="form-control" id="edit_permtown_village">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_permtole">Tole</label>
              <input type="text" class="form-control" id="edit_permtole">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_permstreet_number">Street Number</label>
              <input type="text" class="form-control" id="edit_permstreet_number">
            </div>
          </div>
          <h6 class="mt-3">Temporary Address</h6>
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label" for="edit_temp_country">Country *</label>
              <select class="form-select" id="edit_temp_country" required>
                <option value="Nepal">Nepal</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temp_province">Province *</label>
              <select class="form-select" id="edit_temp_province" required>
                <option value="">Select Province</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temp_district">District *</label>
              <select class="form-select" id="edit_temp_district" required>
                <option value="">Select District</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temp_municipality">Municipality/VDC *</label>
              <select class="form-select" id="edit_temp_municipality" required>
                <option value="">Select Municipality/VDC</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temp_wada">Ward No. *</label>
              <select class="form-select" id="edit_temp_wada" required>
                <option value="">Select Ward</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temptown_village">Town/Village</label>
              <input type="text" class="form-control" id="edit_temptown_village">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_temptole">Tole</label>
              <input type="text" class="form-control" id="edit_temptole">
            </div>
            <div class="col-md-3">
              <label class="form-label" for="edit_tempstreet_number">Street Number</label>
              <input type="text" class="form-control" id="edit_tempstreet_number">
            </div>
          </div>
          <!-- Family Details -->
          <h4 class="mt-4 mb-3">Family Details</h4>
          <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Grandfather Name</label><input type="text" class="form-control" id="edit_grandfather"></div>
            <div class="col-md-3"><label class="form-label">Grandmother Name</label><input type="text" class="form-control" id="edit_grandmother"></div>
            <div class="col-md-3"><label class="form-label">Father Name</label><input type="text" class="form-control" id="edit_father"></div>
            <div class="col-md-3"><label class="form-label">Mother Name</label><input type="text" class="form-control" id="edit_mother"></div>
            <div class="col-md-3"><label class="form-label">Spouse Name</label><input type="text" class="form-control" id="edit_spouse"></div>
            <div class="col-md-3"><label class="form-label">Son Name</label><input type="text" class="form-control" id="edit_son"></div>
            <div class="col-md-3"><label class="form-label">Daughter Name</label><input type="text" class="form-control" id="edit_daughter"></div>
            <div class="col-md-3"><label class="form-label">Father In Law</label><input type="text" class="form-control" id="edit_father_in_law"></div>
            <div class="col-md-3"><label class="form-label">Mother In Law</label><input type="text" class="form-control" id="edit_mother_in_law"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" id="editBorrowerSaveBtn">Save Changes</button>
          <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>