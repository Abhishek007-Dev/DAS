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
  <title>Customer Profile | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <!-- Bootstrap & Icons -->
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
    /* Multi-step form styles */
    .form-step {
      display: none;
    }
    .form-step.active {
      display: block;
    }
    .step-indicator {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .step {
      text-align: center;
      position: relative;
      flex: 1;
    }
    .step-number {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: #dee2e6;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 10px;
      font-weight: bold;
      color: #6c757d;
    }
    .step.active .step-number {
      background-color: #7b145d;
      color: white;
    }
    .step.completed .step-number {
      background-color: #28a745;
      color: white;
    }
    .step-title {
      font-size: 14px;
      color: #6c757d;
    }
    .step.active .step-title {
      color: #7b145d;
      font-weight: bold;
    }
    .step.completed .step-title {
      color: #28a745;
    }
    .step-line {
      position: absolute;
      top: 20px;
      left: -50%;
      width: 100%;
      height: 2px;
      background-color: #dee2e6;
      z-index: -1;
    }
    .step:first-child .step-line {
      display: none;
    }
    .step-line.completed {
      background-color: #28a745;
    }
    .form-navigation {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }
    .address-copy-checkbox {
      margin-bottom: 20px;
    }
    .family-member-row {
      margin-bottom: 15px;
    }
    .remove-family-member {
      color: #dc3545;
      cursor: pointer;
    }
    .form-summary {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      margin-top: 20px;
    }
    .summary-section {
      margin-bottom: 20px;
    }
    .summary-section h5 {
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    .nepali-field {
      direction: ltr;
      text-align: left;
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
        <a class="nav-link" href="#"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
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
            <li class="breadcrumb-item"><a href="#"><i class="bi bi-house-door"></i></a></li>
            <li class="breadcrumb-item"><a href="#">Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile</li>
          </ol>
        </nav>

        <!-- Profile Header Section -->
        <div class="card profile-header mb-4">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-2 text-center">
                <img src="/DAS/assets/images/user-avatar.png" alt="Customer Avatar" class="rounded-circle mb-3" width="100" height="100">
                <div>
                  <span class="status-badge bg-success text-white">Active</span>
                </div>
              </div>
              <div class="col-md-6">
                <h3 class="mb-1">John Doe</h3>
                <p class="text-muted mb-2"><i class="bi bi-person-badge me-2"></i>Customer ID: CUS-2023-00142</p>
                <div class="d-flex flex-wrap gap-2">
                  <span class="badge bg-light text-dark"><i class="bi bi-telephone me-1"></i> +977 9841234567</span>
                  <span class="badge bg-light text-dark"><i class="bi bi-envelope me-1"></i> john.doe@example.com</span>
                  <span class="badge bg-light text-dark"><i class="bi bi-geo-alt me-1"></i> Kathmandu, Nepal</span>
                </div>
              </div>
              <div class="col-md-4">
                <div class="d-flex justify-content-end gap-2">
                  <button class="btn btn-outline-primary" id="editBorrowerBtn"><i class="bi bi-pencil-square me-1"></i> Edit</button>
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

        <!-- Borrower Form Modal -->
        <div class="modal fade" id="borrowerFormModal" tabindex="-1" aria-labelledby="borrowerFormModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="borrowerFormModalLabel">Edit Borrower Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <!-- Multi-step form -->
                <form id="borrowerForm">
                  <!-- Step indicator -->
                  <div class="step-indicator">
                    <div class="step active" data-step="1">
                      <div class="step-number">1</div>
                      <div class="step-line"></div>
                      <div class="step-title">Personal Details</div>
                    </div>
                    <div class="step" data-step="2">
                      <div class="step-number">2</div>
                      <div class="step-line"></div>
                      <div class="step-title">Address Details</div>
                    </div>
                    <div class="step" data-step="3">
                      <div class="step-number">3</div>
                      <div class="step-line"></div>
                      <div class="step-title">Family Details</div>
                    </div>
                    <div class="step" data-step="4">
                      <div class="step-number">4</div>
                      <div class="step-line"></div>
                      <div class="step-title">Summary</div>
                    </div>
                  </div>

                  <!-- Step 1: Personal Details -->
                  <div class="form-step active" id="step1">
                    <h5 class="mb-4">Personal Details</h5>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="fullName" class="form-label">Full Name (English) / पुरा नाम (अंग्रेजीमा)</label>
                        <input type="text" class="form-control" id="fullName" placeholder="Enter full name in English">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="fullNameNepali" class="form-label">Full Name (Nepali) / पुरा नाम (नेपालीमा)</label>
                        <input type="text" class="form-control nepali-field" id="fullNameNepali" placeholder="नेपालीमा पुरा नाम लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="dateOfBirth" class="form-label">Date of Birth / जन्म मिति</label>
                        <input type="date" class="form-control" id="dateOfBirth">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender / लिंग</label>
                        <select class="form-select" id="gender">
                          <option value="">Select Gender</option>
                          <option value="male">Male / पुरुष</option>
                          <option value="female">Female / महिला</option>
                          <option value="other">Other / अन्य</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="relationshipStatus" class="form-label">Relationship Status / वैवाहिक स्थिति</label>
                        <select class="form-select" id="relationshipStatus">
                          <option value="">Select Status</option>
                          <option value="single">Single / अविवाहित</option>
                          <option value="married">Married / विवाहित</option>
                          <option value="divorced">Divorced / छुट्टिएको</option>
                          <option value="widowed">Widowed / विधुर/विधवा</option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3" id="spouseNameContainer" style="display: none;">
                        <label for="spouseName" class="form-label">Spouse Name / पतिको नाम</label>
                        <input type="text" class="form-control nepali-field" id="spouseName" placeholder="पतिको नाम लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="citizenshipNumber" class="form-label">Citizenship Number / नागरिकता नम्बर</label>
                        <input type="text" class="form-control" id="citizenshipNumber" placeholder="e.g. 12345/06789">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="citizenshipIssueDate" class="form-label">Citizenship Issue Date / नागरिकता जारी मिति</label>
                        <input type="date" class="form-control" id="citizenshipIssueDate">
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="citizenshipIssueDistrict" class="form-label">Citizenship Issue District / नागरिकता जारी जिल्ला</label>
                        <input type="text" class="form-control nepali-field" id="citizenshipIssueDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="citizenshipIssueAuthority" class="form-label">Citizenship Issue Authority / नागरिकता जारी प्राधिकरण</label>
                        <input type="text" class="form-control nepali-field" id="citizenshipIssueAuthority" placeholder="प्राधिकरणको नाम लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="phoneNumber" class="form-label">Phone Number / फोन नम्बर</label>
                        <input type="tel" class="form-control" id="phoneNumber" placeholder="e.g. 9841234567">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email / इमेल</label>
                        <input type="email" class="form-control" id="email" placeholder="e.g. example@domain.com">
                      </div>
                    </div>
                    
                    <div class="form-navigation">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="button" class="btn btn-primary next-step ms-auto" data-next="2">Next</button>
                    </div>
                  </div>

                  <!-- Step 2: Address Details -->
                  <div class="form-step" id="step2">
                    <h5 class="mb-4">Address Details</h5>
                    
                    <h6>Permanent Address / स्थायी ठेगाना</h6>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="permanentCountry" class="form-label">Country / देश</label>
                        <select class="form-select" id="permanentCountry">
                          <option value="Nepal">Nepal / नेपाल</option>
                          <option value="India">India / भारत</option>
                          <option value="Other">Other / अन्य</option>
                        </select>
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="permanentProvince" class="form-label">Province / प्रदेश</label>
                        <select class="form-select" id="permanentProvince">
                          <option value="">Select Province</option>
                          <option value="1">Province 1 / प्रदेश १</option>
                          <option value="2">Province 2 / प्रदेश २</option>
                          <option value="3">Bagmati / बागमती</option>
                          <option value="4">Gandaki / गण्डकी</option>
                          <option value="5">Lumbini / लुम्बिनी</option>
                          <option value="6">Karnali / कर्णाली</option>
                          <option value="7">Sudurpaschim / सुदूरपश्चिम</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label for="permanentDistrict" class="form-label">District / जिल्ला</label>
                        <input type="text" class="form-control nepali-field" id="permanentDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label for="permanentMunicipality" class="form-label">Municipality/VDC / नगरपालिका/गा.वि.स.</label>
                        <input type="text" class="form-control nepali-field" id="permanentMunicipality" placeholder="नगरपालिका/गा.वि.स. को नाम लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label for="permanentWard" class="form-label">Ward No. / वडा नं.</label>
                        <input type="number" class="form-control" id="permanentWard" placeholder="वडा नं. लेख्नुहोस्">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label for="permanentTown" class="form-label">Town/Village / शहर/गाउँ</label>
                        <input type="text" class="form-control nepali-field" id="permanentTown" placeholder="शहर/गाउँको नाम लेख्नुहोस्">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label for="permanentTole" class="form-label">Tole / टोल</label>
                        <input type="text" class="form-control nepali-field" id="permanentTole" placeholder="टोलको नाम लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="mb-4">
                      <label for="permanentStreet" class="form-label">Street No. / सडक नं.</label>
                      <input type="text" class="form-control" id="permanentStreet" placeholder="सडक नं. लेख्नुहोस्">
                    </div>
                    
                    <h6>Temporary Address / अस्थायी ठेगाना</h6>
                    <div class="address-copy-checkbox form-check mb-3">
                      <input class="form-check-input" type="checkbox" id="sameAsPermanent">
                      <label class="form-check-label" for="sameAsPermanent">
                        Same as Permanent Address / स्थायी ठेगाना जस्तै
                      </label>
                    </div>
                    
                    <div id="temporaryAddressFields">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="temporaryCountry" class="form-label">Country / देश</label>
                          <select class="form-select" id="temporaryCountry">
                            <option value="Nepal">Nepal / नेपाल</option>
                            <option value="India">India / भारत</option>
                            <option value="Other">Other / अन्य</option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label for="temporaryProvince" class="form-label">Province / प्रदेश</label>
                          <select class="form-select" id="temporaryProvince">
                            <option value="">Select Province</option>
                            <option value="1">Province 1 / प्रदेश १</option>
                            <option value="2">Province 2 / प्रदेश २</option>
                            <option value="3">Bagmati / बागमती</option>
                            <option value="4">Gandaki / गण्डकी</option>
                            <option value="5">Lumbini / लुम्बिनी</option>
                            <option value="6">Karnali / कर्णाली</option>
                            <option value="7">Sudurpaschim / सुदूरपश्चिम</option>
                          </select>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label for="temporaryDistrict" class="form-label">District / जिल्ला</label>
                          <input type="text" class="form-control nepali-field" id="temporaryDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्">
                        </div>
                        <div class="col-md-6 mb-3">
                          <label for="temporaryMunicipality" class="form-label">Municipality/VDC / नगरपालिका/गा.वि.स.</label>
                          <input type="text" class="form-control nepali-field" id="temporaryMunicipality" placeholder="नगरपालिका/गा.वि.स. को नाम लेख्नुहोस्">
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-md-4 mb-3">
                          <label for="temporaryWard" class="form-label">Ward No. / वडा नं.</label>
                          <input type="number" class="form-control" id="temporaryWard" placeholder="वडा नं. लेख्नुहोस्">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="temporaryTown" class="form-label">Town/Village / शहर/गाउँ</label>
                          <input type="text" class="form-control nepali-field" id="temporaryTown" placeholder="शहर/गाउँको नाम लेख्नुहोस्">
                        </div>
                        <div class="col-md-4 mb-3">
                          <label for="temporaryTole" class="form-label">Tole / टोल</label>
                          <input type="text" class="form-control nepali-field" id="temporaryTole" placeholder="टोलको नाम लेख्नुहोस्">
                        </div>
                      </div>
                      
                      <div class="mb-4">
                        <label for="temporaryStreet" class="form-label">Street No. / सडक नं.</label>
                        <input type="text" class="form-control" id="temporaryStreet" placeholder="सडक नं. लेख्नुहोस्">
                      </div>
                    </div>
                    
                    <div class="form-navigation">
                      <button type="button" class="btn btn-outline-secondary prev-step" data-prev="1">Previous</button>
                      <button type="button" class="btn btn-primary next-step ms-auto" data-next="3">Next</button>
                    </div>
                  </div>

                  <!-- Step 3: Family Details -->
                  <div class="form-step" id="step3">
                    <h5 class="mb-4">Family Details</h5>
                    <p class="text-muted">Add at least one family member / कम्तिमा एउटा परिवार सदस्य थप्नुहोस्</p>
                    
                    <div id="familyMembersContainer">
                      <!-- Family member rows will be added here -->
                      <div class="family-member-row row mb-3">
                        <div class="col-md-5">
                          <input type="text" class="form-control nepali-field member-name" placeholder="Name / नाम">
                        </div>
                        <div class="col-md-5">
                          <input type="text" class="form-control nepali-field member-relation" placeholder="Relation / नाता">
                        </div>
                        <div class="col-md-2">
                          <button type="button" class="btn btn-outline-danger remove-family-member" disabled>
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <div class="mb-4">
                      <button type="button" class="btn btn-outline-primary" id="addFamilyMember">
                        <i class="bi bi-plus-circle"></i> Add Family Member / परिवार सदस्य थप्नुहोस्
                      </button>
                    </div>
                    
                    <div class="form-navigation">
                      <button type="button" class="btn btn-outline-secondary prev-step" data-prev="2">Previous</button>
                      <button type="button" class="btn btn-primary next-step ms-auto" data-next="4">Next</button>
                    </div>
                  </div>

                  <!-- Step 4: Summary -->
                  <div class="form-step" id="step4">
                    <h5 class="mb-4">Summary</h5>
                    <p class="text-muted">Please review all the information before submitting / पेश गर्नु अघि सबै जानकारी समीक्षा गर्नुहोस्</p>
                    
                    <div class="form-summary">
                      <div class="summary-section">
                        <h5>Personal Details</h5>
                        <div class="row">
                          <div class="col-md-6">
                            <p><strong>Full Name:</strong> <span id="summaryFullName"></span></p>
                            <p><strong>Date of Birth:</strong> <span id="summaryDob"></span></p>
                            <p><strong>Gender:</strong> <span id="summaryGender"></span></p>
                            <p><strong>Relationship Status:</strong> <span id="summaryRelationship"></span></p>
                          </div>
                          <div class="col-md-6">
                            <p><strong>Citizenship Number:</strong> <span id="summaryCitizenshipNo"></span></p>
                            <p><strong>Citizenship Issue Date:</strong> <span id="summaryCitizenshipDate"></span></p>
                            <p><strong>Phone:</strong> <span id="summaryPhone"></span></p>
                            <p><strong>Email:</strong> <span id="summaryEmail"></span></p>
                          </div>
                        </div>
                      </div>
                      
                      <div class="summary-section">
                        <h5>Address Details</h5>
                        <div class="row">
                          <div class="col-md-6">
                            <h6>Permanent Address</h6>
                            <p id="summaryPermanentAddress"></p>
                          </div>
                          <div class="col-md-6">
                            <h6>Temporary Address</h6>
                            <p id="summaryTemporaryAddress"></p>
                          </div>
                        </div>
                      </div>
                      
                      <div class="summary-section">
                        <h5>Family Details</h5>
                        <div id="summaryFamilyMembers"></div>
                      </div>
                    </div>
                    
                    <div class="form-navigation">
                      <button type="button" class="btn btn-outline-secondary prev-step" data-prev="3">Previous</button>
                      <button type="submit" class="btn btn-success ms-auto">Submit</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Profile Sections -->
        <div class="row">
          <div class="col-md-8">
            <!-- Borrower Details -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Borrower Details</h5>
                <button class="btn btn-sm btn-outline-primary" id="editBorrowerBtn2">Edit</button>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                      <tr>
                        <th width="40%">Full Name:</th>
                        <td>John Doe</td>
                      </tr>
                      <tr>
                        <th>Citizenship No:</th>
                        <td>12345/06789</td>
                      </tr>
                      <tr>
                        <th>Date of Birth:</th>
                        <td>1990-05-15</td>
                      </tr>
                      <tr>
                        <th>Gender:</th>
                        <td>Male</td>
                      </tr>
                    </table>
                  </div>
                  <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                      <tr>
                        <th width="40%">Mobile:</th>
                        <td>+977 9841234567</td>
                      </tr>
                      <tr>
                        <th>Email:</th>
                        <td>john.doe@example.com</td>
                      </tr>
                      <tr>
                        <th>Occupation:</th>
                        <td>Business Owner</td>
                      </tr>
                      <tr>
                        <th>Marital Status:</th>
                        <td>Married</td>
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
                        <p class="mb-1">123 Main Street</p>
                        <p class="mb-1">Kathmandu-10, Ward No. 5</p>
                        <p class="mb-1">Bagmati Province, Nepal</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="card bg-light">
                      <div class="card-header bg-light">Temporary Address</div>
                      <div class="card-body">
                        <p class="mb-1">456 Business Road</p>
                        <p class="mb-1">Pokhara-15, Ward No. 8</p>
                        <p class="mb-1">Gandaki Province, Nepal</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Guarantor Details -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Guarantor Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add Guarantor</button>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Relationship</th>
                        <th>Contact</th>
                        <th>Citizenship No</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Jane Smith</td>
                        <td>Spouse</td>
                        <td>+977 9847654321</td>
                        <td>54321/09876</td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                      </tr>
                      <tr>
                        <td>Acme Corporation</td>
                        <td>Business Partner</td>
                        <td>+977 1-4567890</td>
                        <td>COMP-2020-1234</td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Security Details -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add Security</button>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Owner</th>
                        <th>Valuation</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>Land</td>
                        <td>500 sq.ft in Kathmandu</td>
                        <td>John Doe</td>
                        <td>NPR 5,000,000</td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                      </tr>
                      <tr>
                        <td>Vehicle</td>
                        <td>Toyota Corolla 2020</td>
                        <td>John Doe</td>
                        <td>NPR 3,500,000</td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Loan Details -->
            <div class="card section-card mb-4">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Loan Details</h5>
                <button class="btn btn-sm btn-outline-primary">Add Loan</button>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Loan ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Interest</th>
                        <th>Status</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>LN-2023-001</td>
                        <td>Home Loan</td>
                        <td>NPR 2,500,000</td>
                        <td>10.5%</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        </td>
                      </tr>
                      <tr>
                        <td>LN-2022-045</td>
                        <td>Business Loan</td>
                        <td>NPR 1,800,000</td>
                        <td>12%</td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                          <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                          <button class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <!-- Workflow Section -->
            <div class="card section-card mb-4">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Approval Flow</h5>
              </div>
              <div class="card-body">
                <div class="flow-step active">
                  <div class="step-icon">1</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Data Entry</h6>
                    <p class="text-muted small mb-1">Completed by: Admin User</p>
                    <p class="text-muted small">2023-06-15 10:30 AM</p>
                  </div>
                </div>

                <div class="flow-step active">
                  <div class="step-icon">2</div>
                  <div class="mb-3">
                    <h6 class="mb-1">Verification</h6>
                    <p class="text-muted small mb-1">Pending verification</p>
                    <button class="btn btn-sm btn-primary">Verify Now</button>
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
                  <button class="btn btn-success" type="button"><i class="bi bi-check-circle me-1"></i> Submit for Verification</button>
                  <button class="btn btn-outline-secondary" type="button"><i class="bi bi-send me-1"></i> Send Back for Correction</button>
                </div>
              </div>
            </div>

            <!-- Activity Log -->
            <div class="card section-card">
              <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h5>
              </div>
              <div class="card-body">
                <div class="d-flex mb-3">
                  <div class="flex-shrink-0 text-primary">
                    <i class="bi bi-file-earmark-plus fs-4"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Profile Created</h6>
                    <p class="text-muted small mb-0">By Admin User</p>
                    <p class="text-muted small">2023-06-15 10:30 AM</p>
                  </div>
                </div>
                <div class="d-flex mb-3">
                  <div class="flex-shrink-0 text-success">
                    <i class="bi bi-pencil-square fs-4"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Basic Info Updated</h6>
                    <p class="text-muted small mb-0">By John Doe</p>
                    <p class="text-muted small">2023-06-16 02:15 PM</p>
                  </div>
                </div>
                <div class="d-flex">
                  <div class="flex-shrink-0 text-info">
                    <i class="bi bi-shield-lock fs-4"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <h6 class="mb-1">Security Added</h6>
                    <p class="text-muted small mb-0">Land property</p>
                    <p class="text-muted small">2023-06-17 11:20 AM</p>
                  </div>
                </div>
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

    // Multi-step form functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Show modal when edit button is clicked
      const editBtns = document.querySelectorAll('#editBorrowerBtn, #editBorrowerBtn2');
      editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const modal = new bootstrap.Modal(document.getElementById('borrowerFormModal'));
          modal.show();
        });
      });

      // Form navigation
      const formSteps = document.querySelectorAll('.form-step');
      const steps = document.querySelectorAll('.step');
      
      // Next step button
      document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
          const currentStep = document.querySelector('.form-step.active');
          const nextStepId = this.getAttribute('data-next');
          const nextStep = document.getElementById(`step${nextStepId}`);
          
          // Validate current step before proceeding
          if (validateStep(currentStep.id)) {
            currentStep.classList.remove('active');
            nextStep.classList.add('active');
            
            // Update step indicator
            updateStepIndicator(nextStepId);
            
            // Update summary if going to step 4
            if (nextStepId === '4') {
              updateSummary();
            }
          }
        });
      });
      
      // Previous step button
      document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
          const currentStep = document.querySelector('.form-step.active');
          const prevStepId = this.getAttribute('data-prev');
          const prevStep = document.getElementById(`step${prevStepId}`);
          
          currentStep.classList.remove('active');
          prevStep.classList.add('active');
          
          // Update step indicator
          updateStepIndicator(prevStepId);
        });
      });
      
      // Update step indicator
      function updateStepIndicator(activeStep) {
        steps.forEach(step => {
          step.classList.remove('active', 'completed');
          const stepNumber = step.getAttribute('data-step');
          
          if (stepNumber === activeStep) {
            step.classList.add('active');
          } else if (stepNumber < activeStep) {
            step.classList.add('completed');
            const stepLine = step.querySelector('.step-line');
            if (stepLine) stepLine.classList.add('completed');
          }
        });
      }
      
      // Validate step
      function validateStep(stepId) {
        let isValid = true;
        
        if (stepId === 'step1') {
          // Validate personal details
          const fullName = document.getElementById('fullName').value;
          const citizenshipNumber = document.getElementById('citizenshipNumber').value;
          
          if (!fullName) {
            alert('Please enter full name');
            isValid = false;
          } else if (!citizenshipNumber) {
            alert('Please enter citizenship number');
            isValid = false;
          }
        } else if (stepId === 'step2') {
          // Validate address details
          const permanentDistrict = document.getElementById('permanentDistrict').value;
          
          if (!permanentDistrict) {
            alert('Please enter permanent district');
            isValid = false;
          }
        } else if (stepId === 'step3') {
          // Validate family details
          const familyMembers = document.querySelectorAll('.member-name');
          let hasValidMember = false;
          
          familyMembers.forEach(member => {
            if (member.value) {
              hasValidMember = true;
            }
          });
          
          if (!hasValidMember) {
            alert('Please add at least one family member');
            isValid = false;
          }
        }
        
        return isValid;
      }
      
      // Update summary
      function updateSummary() {
        // Personal details
        document.getElementById('summaryFullName').textContent = document.getElementById('fullName').value;
        document.getElementById('summaryDob').textContent = document.getElementById('dateOfBirth').value;
        document.getElementById('summaryGender').textContent = document.getElementById('gender').options[document.getElementById('gender').selectedIndex].text;
        document.getElementById('summaryRelationship').textContent = document.getElementById('relationshipStatus').options[document.getElementById('relationshipStatus').selectedIndex].text;
        document.getElementById('summaryCitizenshipNo').textContent = document.getElementById('citizenshipNumber').value;
        document.getElementById('summaryCitizenshipDate').textContent = document.getElementById('citizenshipIssueDate').value;
        document.getElementById('summaryPhone').textContent = document.getElementById('phoneNumber').value;
        document.getElementById('summaryEmail').textContent = document.getElementById('email').value;
        
        // Address details
        const permanentAddress = `
          ${document.getElementById('permanentCountry').options[document.getElementById('permanentCountry').selectedIndex].text}, 
          ${document.getElementById('permanentProvince').options[document.getElementById('permanentProvince').selectedIndex].text}, 
          ${document.getElementById('permanentDistrict').value}, 
          ${document.getElementById('permanentMunicipality').value}, 
          Ward No. ${document.getElementById('permanentWard').value}, 
          ${document.getElementById('permanentTown').value}, 
          ${document.getElementById('permanentTole').value}, 
          Street No. ${document.getElementById('permanentStreet').value}
        `;
        document.getElementById('summaryPermanentAddress').textContent = permanentAddress;
        
        let temporaryAddress;
        if (document.getElementById('sameAsPermanent').checked) {
          temporaryAddress = "Same as permanent address";
        } else {
          temporaryAddress = `
            ${document.getElementById('temporaryCountry').options[document.getElementById('temporaryCountry').selectedIndex].text}, 
            ${document.getElementById('temporaryProvince').options[document.getElementById('temporaryProvince').selectedIndex].text}, 
            ${document.getElementById('temporaryDistrict').value}, 
            ${document.getElementById('temporaryMunicipality').value}, 
            Ward No. ${document.getElementById('temporaryWard').value}, 
            ${document.getElementById('temporaryTown').value}, 
            ${document.getElementById('temporaryTole').value}, 
            Street No. ${document.getElementById('temporaryStreet').value}
          `;
        }
        document.getElementById('summaryTemporaryAddress').textContent = temporaryAddress;
        
        // Family details
        const familyMembers = document.querySelectorAll('.family-member-row');
        let familyHtml = '';
        
        familyMembers.forEach(member => {
          const name = member.querySelector('.member-name').value;
          const relation = member.querySelector('.member-relation').value;
          
          if (name && relation) {
            familyHtml += `<p><strong>${name}</strong> - ${relation}</p>`;
          }
        });
        
        document.getElementById('summaryFamilyMembers').innerHTML = familyHtml;
      }
      
      // Same as permanent address checkbox
      document.getElementById('sameAsPermanent').addEventListener('change', function() {
        const tempAddressFields = document.getElementById('temporaryAddressFields');
        
        if (this.checked) {
          tempAddressFields.style.display = 'none';
          
          // Copy values from permanent to temporary
          document.getElementById('temporaryCountry').value = document.getElementById('permanentCountry').value;
          document.getElementById('temporaryProvince').value = document.getElementById('permanentProvince').value;
          document.getElementById('temporaryDistrict').value = document.getElementById('permanentDistrict').value;
          document.getElementById('temporaryMunicipality').value = document.getElementById('permanentMunicipality').value;
          document.getElementById('temporaryWard').value = document.getElementById('permanentWard').value;
          document.getElementById('temporaryTown').value = document.getElementById('permanentTown').value;
          document.getElementById('temporaryTole').value = document.getElementById('permanentTole').value;
          document.getElementById('temporaryStreet').value = document.getElementById('permanentStreet').value;
        } else {
          tempAddressFields.style.display = 'block';
        }
      });
      
      // Show/hide spouse name based on relationship status
      document.getElementById('relationshipStatus').addEventListener('change', function() {
        const spouseContainer = document.getElementById('spouseNameContainer');
        
        if (this.value === 'married') {
          spouseContainer.style.display = 'block';
        } else {
          spouseContainer.style.display = 'none';
          document.getElementById('spouseName').value = '';
        }
      });
      
      // Add family member
      document.getElementById('addFamilyMember').addEventListener('click', function() {
        const container = document.getElementById('familyMembersContainer');
        const newRow = document.createElement('div');
        newRow.className = 'family-member-row row mb-3';
        newRow.innerHTML = `
          <div class="col-md-5">
            <input type="text" class="form-control nepali-field member-name" placeholder="Name / नाम">
          </div>
          <div class="col-md-5">
            <input type="text" class="form-control nepali-field member-relation" placeholder="Relation / नाता">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger remove-family-member">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        `;
        container.appendChild(newRow);
        
        // Enable remove button for all rows if there's more than one
        const removeButtons = document.querySelectorAll('.remove-family-member');
        if (removeButtons.length > 1) {
          removeButtons.forEach(button => {
            button.disabled = false;
          });
        }
      });
      
      // Remove family member (event delegation)
      document.getElementById('familyMembersContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-family-member') || 
            e.target.closest('.remove-family-member')) {
              const button = e.target.classList.contains('remove-family-member') ? 
                e.target : e.target.closest('.remove-family-member');
              const row = button.closest('.family-member-row');
              
              // Don't remove the last row
              const rows = document.querySelectorAll('.family-member-row');
              if (rows.length > 1) {
                row.remove();
                
                // Disable remove button if only one row left
                if (rows.length === 2) { // Because we haven't removed it yet
                  document.querySelector('.remove-family-member').disabled = true;
                }
              }
            }
      });
      
      // Form submission
      document.getElementById('borrowerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Here you would typically send the data to the server
        alert('Form submitted successfully!');
        
        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('borrowerFormModal'));
        modal.hide();
        
        // Reset form
        this.reset();
        document.querySelector('.form-step.active').classList.remove('active');
        document.getElementById('step1').classList.add('active');
        updateStepIndicator('1');
        
        // Reset family members to one row
        const container = document.getElementById('familyMembersContainer');
        container.innerHTML = `
          <div class="family-member-row row mb-3">
            <div class="col-md-5">
              <input type="text" class="form-control nepali-field member-name" placeholder="Name / नाम">
            </div>
            <div class="col-md-5">
              <input type="text" class="form-control nepali-field member-relation" placeholder="Relation / नाता">
            </div>
            <div class="col-md-2">
              <button type="button" class="btn btn-outline-danger remove-family-member" disabled>
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        `;
      });
    });
  </script>
</body>
</html>