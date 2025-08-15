<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'maker') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not found in session. Please log in again.");
}

$userId = $_SESSION['user_id'];

require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";



// Check if the user has the 'maker' role
// This will redirect to login if the role is not allowed
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
  

// Generate a unique customer ID starting from CUST-20240100
function generateCustomerId($conn) {
    $result = $conn->query("SELECT customer_id FROM individual_profile ORDER BY id DESC LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = substr($row['customer_id'], 5); // Remove 'CUST-' prefix
        $newId = (int)$lastId + 1;
        return 'CUST-' . str_pad($newId, 8, '0', STR_PAD_LEFT);
    } else {
        return 'CUST-20240100';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerId = generateCustomerId($conn);

    $fullName = $_POST['full_name'] ?? null;
    $citizenshipNumber = $_POST['citizenship_number'] ?? null;
    $phoneNumber = $_POST['phone_number'] ?? null;
    $email = $_POST['email'] ?? null;
    $accountNumber = $_POST['account_number'] ?? null;
    $province = $_POST['province'] ?? null;
    $branch = $_POST['branch'] ?? null;

    // Validate required fields
    if (empty($fullName) || empty($citizenshipNumber) || empty($phoneNumber)) {
        die("Error: Required fields are missing.");
    }

    // Check for duplicates
    $stmt = $conn->prepare("SELECT * FROM individual_profile WHERE full_name = ? AND citizenship_number = ? AND phone_number = ?");
    $stmt->bind_param("sss", $fullName, $citizenshipNumber, $phoneNumber);
    $stmt->execute();
    $duplicateResult = $stmt->get_result();

    if ($duplicateResult->num_rows > 0) {
        die("Error: An individual with the same name, citizenship number, and phone already exists.");
    }
    $stmt->close();

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO individual_profile 
        (customer_id, full_name, citizenship_number, phone_number, email, account_number, province, branch, created_by, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

    $stmt->bind_param(
        "ssssssssi",
        $customerId,
        $fullName,
        $citizenshipNumber,
        $phoneNumber,
        $email,
        $accountNumber,
        $province,
        $branch,
        $userId
    );

    if ($stmt->execute()) {
        header("Location: customer_profile.php?customer_id=" . urlencode($customerId) . "&message=Customer created successfully");
        exit();
    } else {
        error_log("MySQL error: " . $stmt->error);
        die("Error occurred while creating the customer.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Individual Customer | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../../assets/css/dash.css">
  <style>
    .form-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .required-field::after {
      content: " *";
      color: red;
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
          <li><a class="nav-link active" href="create_individual.php"><i class="bi bi-person-plus me-2"></i>Individual Customer</a></li>
          <li><a class="nav-link" href="create_corporate.php"><i class="bi bi-building me-2"></i>Corporate Customer</a></li>
          <li><a class="nav-link" href="create_co_borrower.php"><i class="bi bi-people me-2"></i>Co-Borrower</a></li>
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
            <li class="breadcrumb-item"><a href="maker_dashboard.php"><i class="bi bi-house-door"></i></a></li>
            <li class="breadcrumb-item"><a href="#">Create</a></li>
            <li class="breadcrumb-item active" aria-current="page">Individual Customer</li>
          </ol>
        </nav>

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3><i class="bi bi-person-plus me-2"></i>Create Individual Customer</h3>
        </div>

     <!-- Form Container -->
                    <div class="form-container">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="border-bottom pb-2">Basic Information</h5>
                        </div>
                        </div>

                        <!-- First Row with 4 columns -->
                        <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="full_name" class="form-label required-field">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                        </div>
                        <div class="col-md-3">
                            <label for="citizenship_number" class="form-label required-field">Citizenship Number</label>
                            <input type="text" class="form-control" id="citizenship_number" name="citizenship_number" required>
                        </div>
                        <div class="col-md-3">
                            <label for="phone_number" class="form-label required-field">Phone Number</label>
                            <input type="tel" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="col-md-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        </div>

                        <!-- Second Row with 4 columns -->
                        <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="account_number" class="form-label">Account Number (Optional)</label>
                            <input type="text" class="form-control" id="account_number" name="account_number">
                        </div>
                        <div class="col-md-3">
                            <label for="province" class="form-label required-field">Province</label>
                            <select class="form-select" id="province" name="province" required>
                            <option value="">Select Province</option>
                            <option value="Province 1">Province 1</option>
                            <option value="Province 2">Province 2</option>
                            <option value="Bagmati">Bagmati</option>
                            <option value="Gandaki">Gandaki</option>
                            <option value="Lumbini">Lumbini</option>
                            <option value="Karnali">Karnali</option>
                            <option value="Sudurpaschim">Sudurpaschim</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="branch" class="form-label required-field">Branch</label>
                            <select class="form-select" id="branch" name="branch" required>
                            <option value="">Select Branch</option>
                            <option value="Kathmandu">Kathmandu</option>
                            <option value="Pokhara">Pokhara</option>
                            <option value="Biratnagar">Biratnagar</option>
                            <option value="Birgunj">Birgunj</option>
                            <option value="Butwal">Butwal</option>
                            <option value="Nepalgunj">Nepalgunj</option>
                            <option value="Dharan">Dharan</option>
                            </select>
                        </div>
                        <!-- Empty column to fill the 4-col grid -->
                        <div class="col-md-3"></div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-outline-secondary me-md-2">Reset</button>
                        <button type="submit" class="btn btn-primary">Create Customer</button>
                        </div>
                    </form>
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
  </script>
</body>
</html>