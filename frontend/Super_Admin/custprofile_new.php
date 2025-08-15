<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') 
{
    header("Location: ../login.php");
    exit();
}
require_once "../../backend/db_connect.php";
require_once "../../backend/auth_check.php";

// Set the session timeout duration (in seconds)
$timeout_duration = 900; // 15 minutes

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Profile | Document Automation System</title>
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
    /* Form styles */
    .form-section {
      display: none;
    }
    .form-section.active {
      display: block;
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
    .empty-data {
      color: #6c757d;
      font-style: italic;
    }
    .collateral-type-section {
      display: none;
    }
    .collateral-type-section.active {
      display: block;
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

        <!-- Borrower Details Section -->
        <div class="card section-card mb-4" id="borrowerSection">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Borrower Details</h5>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="addBorrowerDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-plus-circle me-1"></i> Add
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" id="createNewBorrower">Create New</a></li>
                <li><a class="dropdown-item" href="#" id="chooseExistingBorrower">Choose Existing</a></li>
              </ul>
            </div>
          </div>
          
          <!-- Borrower Summary Table -->
          <div class="card-body" id="borrowerSummary">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Citizenship No</th>
                    <th>Father's Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>John Doe</td>
                    <td>1990-05-15</td>
                    <td>Male</td>
                    <td>12345/06789</td>
                    <td>Robert Doe</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Borrower Form (Hidden by default) -->
          <div class="card-body" id="borrowerFormContainer" style="display: none;">
            <form id="borrowerForm">
              <!-- Personal Details Section -->
              <div class="form-section active" id="personalDetailsSection">
                <h5 class="mb-4">Personal Details</h5>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="borrowerFullName" class="form-label">Full Name (English) / पुरा नाम (अंग्रेजीमा)</label>
                    <input type="text" class="form-control" id="borrowerFullName" placeholder="Enter full name in English" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="borrowerFullNameNepali" class="form-label">Full Name (Nepali) / पुरा नाम (नेपालीमा)</label>
                    <input type="text" class="form-control nepali-field" id="borrowerFullNameNepali" placeholder="नेपालीमा पुरा नाम लेख्नुहोस्" required>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <label for="borrowerDob" class="form-label">Date of Birth / जन्म मिति</label>
                    <input type="date" class="form-control" id="borrowerDob" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerGender" class="form-label">Gender / लिंग</label>
                    <select class="form-select" id="borrowerGender" required>
                      <option value="">Select Gender</option>
                      <option value="male">Male / पुरुष</option>
                      <option value="female">Female / महिला</option>
                      <option value="other">Other / अन्य</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerRelationship" class="form-label">Relationship Status / वैवाहिक स्थिति</label>
                    <select class="form-select" id="borrowerRelationship" required>
                      <option value="">Select Status</option>
                      <option value="single">Single / अविवाहित</option>
                      <option value="married">Married / विवाहित</option>
                      <option value="divorced">Divorced / छुट्टिएको</option>
                      <option value="widowed">Widowed / विधुर/विधवा</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3" id="borrowerSpouseContainer" style="display: none;">
                    <label for="borrowerSpouseName" class="form-label">Spouse Name / पतिको नाम</label>
                    <input type="text" class="form-control nepali-field" id="borrowerSpouseName" placeholder="पतिको नाम लेख्नुहोस्">
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label for="borrowerCitizenshipNo" class="form-label">Citizenship Number / नागरिकता नम्बर</label>
                    <input type="text" class="form-control" id="borrowerCitizenshipNo" placeholder="e.g. 12345/06789" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="borrowerCitizenshipIssueDate" class="form-label">Issue Date / जारी मिति</label>
                    <input type="date" class="form-control" id="borrowerCitizenshipIssueDate" required>
                  </div>
                  <div class="col-md-4 mb-3">
                    <label for="borrowerCitizenshipIssueDistrict" class="form-label">Issue District / जारी जिल्ला</label>
                    <input type="text" class="form-control nepali-field" id="borrowerCitizenshipIssueDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्" required>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="borrowerCitizenshipIssueAuthority" class="form-label">Issue Authority / जारी प्राधिकरण</label>
                    <input type="text" class="form-control nepali-field" id="borrowerCitizenshipIssueAuthority" placeholder="प्राधिकरणको नाम लेख्नुहोस्" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="borrowerPhone" class="form-label">Phone Number / फोन नम्बर</label>
                    <input type="tel" class="form-control" id="borrowerPhone" placeholder="e.g. 9841234567" required>
                  </div>
                </div>
                
                <div class="form-navigation">
                  <button type="button" class="btn btn-outline-secondary" id="cancelBorrowerForm">Cancel</button>
                  <button type="button" class="btn btn-primary next-section ms-auto" data-next="addressDetailsSection">Next</button>
                </div>
              </div>
              
              <!-- Address Details Section -->
              <div class="form-section" id="addressDetailsSection">
                <h5 class="mb-4">Address Details</h5>
                
                <h6>Permanent Address / स्थायी ठेगाना</h6>
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentCountry" class="form-label">Country / देश</label>
                    <select class="form-select" id="borrowerPermanentCountry" required>
                      <option value="Nepal">Nepal / नेपाल</option>
                      <option value="India">India / भारत</option>
                      <option value="Other">Other / अन्य</option>
                    </select>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentProvince" class="form-label">Province / प्रदेश</label>
                    <select class="form-select" id="borrowerPermanentProvince" required>
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
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentDistrict" class="form-label">District / जिल्ला</label>
                    <input type="text" class="form-control nepali-field" id="borrowerPermanentDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentMunicipality" class="form-label">Municipality/VDC / नगरपालिका/गा.वि.स.</label>
                    <input type="text" class="form-control nepali-field" id="borrowerPermanentMunicipality" placeholder="नगरपालिका/गा.वि.स. को नाम लेख्नुहोस्" required>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentWard" class="form-label">Ward No. / वडा नं.</label>
                    <input type="number" class="form-control" id="borrowerPermanentWard" placeholder="वडा नं. लेख्नुहोस्" required>
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentTown" class="form-label">Town/Village / शहर/गाउँ</label>
                    <input type="text" class="form-control nepali-field" id="borrowerPermanentTown" placeholder="शहर/गाउँको नाम लेख्नुहोस्">
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentTole" class="form-label">Tole / टोल</label>
                    <input type="text" class="form-control nepali-field" id="borrowerPermanentTole" placeholder="टोलको नाम लेख्नुहोस्">
                  </div>
                  <div class="col-md-3 mb-3">
                    <label for="borrowerPermanentStreet" class="form-label">Street No. / सडक नं.</label>
                    <input type="text" class="form-control" id="borrowerPermanentStreet" placeholder="सडक नं. लेख्नुहोस्">
                  </div>
                </div>
                
                <h6 class="mt-4">Temporary Address / अस्थायी ठेगाना</h6>
                <div class="address-copy-checkbox form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="borrowerSameAsPermanent">
                  <label class="form-check-label" for="borrowerSameAsPermanent">
                    Same as Permanent Address / स्थायी ठेगाना जस्तै
                  </label>
                </div>
                
                <div id="borrowerTemporaryAddressFields">
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryCountry" class="form-label">Country / देश</label>
                      <select class="form-select" id="borrowerTemporaryCountry" required>
                        <option value="Nepal">Nepal / नेपाल</option>
                        <option value="India">India / भारत</option>
                        <option value="Other">Other / अन्य</option>
                      </select>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryProvince" class="form-label">Province / प्रदेश</label>
                      <select class="form-select" id="borrowerTemporaryProvince" required>
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
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryDistrict" class="form-label">District / जिल्ला</label>
                      <input type="text" class="form-control nepali-field" id="borrowerTemporaryDistrict" placeholder="जिल्लाको नाम लेख्नुहोस्" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryMunicipality" class="form-label">Municipality/VDC / नगरपालिका/गा.वि.स.</label>
                      <input type="text" class="form-control nepali-field" id="borrowerTemporaryMunicipality" placeholder="नगरपालिका/गा.वि.स. को नाम लेख्नुहोस्" required>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryWard" class="form-label">Ward No. / वडा नं.</label>
                      <input type="number" class="form-control" id="borrowerTemporaryWard" placeholder="वडा नं. लेख्नुहोस्" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryTown" class="form-label">Town/Village / शहर/गाउँ</label>
                      <input type="text" class="form-control nepali-field" id="borrowerTemporaryTown" placeholder="शहर/गाउँको नाम लेख्नुहोस्">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryTole" class="form-label">Tole / टोल</label>
                      <input type="text" class="form-control nepali-field" id="borrowerTemporaryTole" placeholder="टोलको नाम लेख्नुहोस्">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label for="borrowerTemporaryStreet" class="form-label">Street No. / सडक नं.</label>
                      <input type="text" class="form-control" id="borrowerTemporaryStreet" placeholder="सडक नं. लेख्नुहोस्">
                    </div>
                  </div>
                </div>
                
                <div class="form-navigation">
                  <button type="button" class="btn btn-outline-secondary prev-section" data-prev="personalDetailsSection">Previous</button>
                  <button type="button" class="btn btn-primary next-section ms-auto" data-next="familyDetailsSection">Next</button>
                </div>
              </div>
              
              <!-- Family Details Section -->
              <div class="form-section" id="familyDetailsSection">
                <h5 class="mb-4">Family Details</h5>
                <p class="text-muted">Add family members (at least father's name is required) / परिवारका सदस्यहरू थप्नुहोस् (कम्तिमा बुबाको नाम आवश्यक छ)</p>
                
                <div id="borrowerFamilyMembersContainer">
                  <!-- Father -->
                  <div class="family-member-row row mb-3">
                    <div class="col-md-5">
                      <input type="text" class="form-control nepali-field member-name" placeholder="Father's Name / बुबाको नाम" required>
                    </div>
                    <div class="col-md-5">
                      <select class="form-select member-relation">
                        <option value="father">Father / बुबा</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-outline-danger remove-family-member" disabled>
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Mother -->
                  <div class="family-member-row row mb-3">
                    <div class="col-md-5">
                      <input type="text" class="form-control nepali-field member-name" placeholder="Mother's Name / आमाको नाम">
                    </div>
                    <div class="col-md-5">
                      <select class="form-select member-relation">
                        <option value="mother">Mother / आमा</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-outline-danger remove-family-member">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                  
                  <!-- Grandfather -->
                  <div class="family-member-row row mb-3">
                    <div class="col-md-5">
                      <input type="text" class="form-control nepali-field member-name" placeholder="Grandfather's Name / बाजेको नाम">
                    </div>
                    <div class="col-md-5">
                      <select class="form-select member-relation">
                        <option value="grandfather">Grandfather / बाजे</option>
                      </select>
                    </div>
                    <div class="col-md-2">
                      <button type="button" class="btn btn-outline-danger remove-family-member">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
                
                <div class="mb-4">
                  <button type="button" class="btn btn-outline-primary" id="addBorrowerFamilyMember">
                    <i class="bi bi-plus-circle"></i> Add Family Member / परिवार सदस्य थप्नुहोस्
                  </button>
                </div>
                
                <div class="form-navigation">
                  <button type="button" class="btn btn-outline-secondary prev-section" data-prev="addressDetailsSection">Previous</button>
                  <button type="submit" class="btn btn-success ms-auto">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Guarantor Details Section -->
        <div class="card section-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Guarantor Details</h5>
            <button class="btn btn-sm btn-outline-primary" id="addGuarantorBtn">
              <i class="bi bi-plus-circle me-1"></i> Add
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Full Name</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Citizenship No</th>
                    <th>Father's Name</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Jane Smith</td>
                    <td>1985-08-20</td>
                    <td>Female</td>
                    <td>54321/09876</td>
                    <td>William Smith</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="text-center empty-data">No guarantors added yet</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Security/Collateral Details Section -->
        <div class="card section-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security/Collateral Details</h5>
            <button class="btn btn-sm btn-outline-primary" id="addCollateralBtn">
              <i class="bi bi-plus-circle me-1"></i> Add
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Owner Name</th>
                    <th>Type</th>
                    <th>Municipality/VDC</th>
                    <th>Ward No.</th>
                    <th>Kitta No.</th>
                    <th>Area</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>John Doe</td>
                    <td>Land</td>
                    <td>Kathmandu</td>
                    <td>5</td>
                    <td>123</td>
                    <td>500 sq.ft</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="7" class="text-center empty-data">No collateral added yet</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Loan Details Section -->
        <div class="card section-card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Loan Details</h5>
            <button class="btn btn-sm btn-outline-primary" id="addLoanBtn">
              <i class="bi bi-plus-circle me-1"></i> Add
            </button>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm">
                <thead>
                  <tr>
                    <th>Loan Type</th>
                    <th>Loan Limit</th>
                    <th>Interest Rate</th>
                    <th>Approved Date</th>
                    <th>Tenure</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Home Loan</td>
                    <td>NPR 2,500,000</td>
                    <td>10.5%</td>
                    <td>2023-06-15</td>
                    <td>5 years</td>
                    <td>
                      <button class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></button>
                      <button class="btn btn-sm btn-outline-warning me-1"><i class="bi bi-pencil"></i></button>
                      <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="text-center empty-data">No loans added yet</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Approval Flow Section -->
        <div class="card section-card">
          <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Approval Flow</h5>
          </div>
          <div class="card-body">
            <div class="flow-step active">
              <div class="step-icon">1</div>
              <div class="mb-3">
                <h6 class="mb-1">Data Entry</h6>
                <p class="text-muted small mb-1">Completed by: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p class="text-muted small"><?php echo date('Y-m-d H:i A'); ?></p>
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
    document.addEventListener('DOMContentLoaded', function() {
      // Borrower Form Functionality
      const borrowerFormContainer = document.getElementById('borrowerFormContainer');
      const borrowerSummary = document.getElementById('borrowerSummary');
      const borrowerSections = document.querySelectorAll('#borrowerFormContainer .form-section');
      
      // Show borrower form when "Create New" is clicked
      document.getElementById('createNewBorrower').addEventListener('click', function(e) {
        e.preventDefault();
        borrowerSummary.style.display = 'none';
        borrowerFormContainer.style.display = 'block';
      });
      
      // Show existing borrower list when "Choose Existing" is clicked
      document.getElementById('chooseExistingBorrower').addEventListener('click', function(e) {
        e.preventDefault();
        alert('This would show a list of existing borrowers to choose from');
      });
      
      // Cancel borrower form
      document.getElementById('cancelBorrowerForm').addEventListener('click', function() {
        borrowerSummary.style.display = 'block';
        borrowerFormContainer.style.display = 'none';
        // Reset form to first section
        document.querySelector('#borrowerFormContainer .form-section.active').classList.remove('active');
        document.getElementById('personalDetailsSection').classList.add('active');
      });
      
      // Show/hide spouse name based on relationship status
      document.getElementById('borrowerRelationship').addEventListener('change', function() {
        const spouseContainer = document.getElementById('borrowerSpouseContainer');
        if (this.value === 'married') {
          spouseContainer.style.display = 'block';
        } else {
          spouseContainer.style.display = 'none';
          document.getElementById('borrowerSpouseName').value = '';
        }
      });
      
      // Same as permanent address checkbox
      document.getElementById('borrowerSameAsPermanent').addEventListener('change', function() {
        const tempAddressFields = document.getElementById('borrowerTemporaryAddressFields');
        if (this.checked) {
          tempAddressFields.style.display = 'none';
          
          // Copy values from permanent to temporary
          document.getElementById('borrowerTemporaryCountry').value = document.getElementById('borrowerPermanentCountry').value;
          document.getElementById('borrowerTemporaryProvince').value = document.getElementById('borrowerPermanentProvince').value;
          document.getElementById('borrowerTemporaryDistrict').value = document.getElementById('borrowerPermanentDistrict').value;
          document.getElementById('borrowerTemporaryMunicipality').value = document.getElementById('borrowerPermanentMunicipality').value;
          document.getElementById('borrowerTemporaryWard').value = document.getElementById('borrowerPermanentWard').value;
          document.getElementById('borrowerTemporaryTown').value = document.getElementById('borrowerPermanentTown').value;
          document.getElementById('borrowerTemporaryTole').value = document.getElementById('borrowerPermanentTole').value;
          document.getElementById('borrowerTemporaryStreet').value = document.getElementById('borrowerPermanentStreet').value;
        } else {
          tempAddressFields.style.display = 'block';
        }
      });
      
      // Add borrower family member
      document.getElementById('addBorrowerFamilyMember').addEventListener('click', function() {
        const container = document.getElementById('borrowerFamilyMembersContainer');
        const newRow = document.createElement('div');
        newRow.className = 'family-member-row row mb-3';
        newRow.innerHTML = `
          <div class="col-md-5">
            <input type="text" class="form-control nepali-field member-name" placeholder="Name / नाम">
          </div>
          <div class="col-md-5">
            <select class="form-select member-relation">
              <option value="">Select Relation</option>
              <option value="father">Father / बुबा</option>
              <option value="mother">Mother / आमा</option>
              <option value="grandfather">Grandfather / बाजे</option>
              <option value="grandmother">Grandmother / बज्यै</option>
              <option value="spouse">Spouse / पति/पत्नी</option>
              <option value="son">Son / छोरा</option>
              <option value="daughter">Daughter / छोरी</option>
              <option value="brother">Brother / दाजु/भाइ</option>
              <option value="sister">Sister / दिदी/बहिनी</option>
            </select>
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
      
      // Remove borrower family member (event delegation)
      document.getElementById('borrowerFamilyMembersContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-family-member') || 
            e.target.closest('.remove-family-member')) {
              const button = e.target.classList.contains('remove-family-member') ? 
                e.target : e.target.closest('.remove-family-member');
              const row = button.closest('.family-member-row');
              
              // Don't remove the first row (father)
              const rows = document.querySelectorAll('.family-member-row');
              if (rows.length > 1 && !row.querySelector('.member-relation').value === 'father') {
                row.remove();
                
                // Disable remove button if only one row left
                if (rows.length === 2) { // Because we haven't removed it yet
                  document.querySelector('.remove-family-member').disabled = true;
                }
              }
            }
      });
      
      // Form section navigation
      document.querySelectorAll('.next-section').forEach(button => {
        button.addEventListener('click', function() {
          const currentSection = document.querySelector('#borrowerFormContainer .form-section.active');
          const nextSectionId = this.getAttribute('data-next');
          const nextSection = document.getElementById(nextSectionId);
          
          // Validate current section before proceeding
          if (validateSection(currentSection.id)) {
            currentSection.classList.remove('active');
            nextSection.classList.add('active');
          }
        });
      });
      
      document.querySelectorAll('.prev-section').forEach(button => {
        button.addEventListener('click', function() {
          const currentSection = document.querySelector('#borrowerFormContainer .form-section.active');
          const prevSectionId = this.getAttribute('data-prev');
          const prevSection = document.getElementById(prevSectionId);
          
          currentSection.classList.remove('active');
          prevSection.classList.add('active');
        });
      });
      
      // Validate form section
      function validateSection(sectionId) {
        let isValid = true;
        
        if (sectionId === 'personalDetailsSection') {
          // Validate personal details
          const fullName = document.getElementById('borrowerFullName').value;
          const citizenshipNo = document.getElementById('borrowerCitizenshipNo').value;
          
          if (!fullName) {
            alert('Please enter full name');
            isValid = false;
          } else if (!citizenshipNo) {
            alert('Please enter citizenship number');
            isValid = false;
          }
        } else if (sectionId === 'addressDetailsSection') {
          // Validate address details
          const permanentDistrict = document.getElementById('borrowerPermanentDistrict').value;
          const permanentMunicipality = document.getElementById('borrowerPermanentMunicipality').value;
          
          if (!permanentDistrict) {
            alert('Please enter permanent district');
            isValid = false;
          } else if (!permanentMunicipality) {
            alert('Please enter permanent municipality');
            isValid = false;
          }
        } else if (sectionId === 'familyDetailsSection') {
          // Validate family details
          const fatherName = document.querySelector('.member-relation[value="father"]') ? 
            document.querySelector('.member-relation[value="father"]').closest('.family-member-row').querySelector('.member-name').value : '';
          
          if (!fatherName) {
            alert('Please enter father\'s name');
            isValid = false;
          }
        }
        
        return isValid;
      }
      
      // Borrower form submission
      document.getElementById('borrowerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all sections
        if (validateSection('personalDetailsSection') && 
            validateSection('addressDetailsSection') && 
            validateSection('familyDetailsSection')) {
              
          // Here you would typically send the data to the server
          alert('Borrower details submitted successfully!');
          
          // Hide form and show summary
          borrowerFormContainer.style.display = 'none';
          borrowerSummary.style.display = 'block';
          
          // Reset form to first section
          document.querySelector('#borrowerFormContainer .form-section.active').classList.remove('active');
          document.getElementById('personalDetailsSection').classList.add('active');
        }
      });
      
      // Submit for approval
      document.getElementById('submitForApproval').addEventListener('click', function() {
        if (confirm('Are you sure you want to submit for approval? Once submitted, you cannot make changes.')) {
          alert('Submitted for approval successfully!');
          // Here you would typically update the status in the database
        }
      });
      
      // Add guarantor button
      document.getElementById('addGuarantorBtn').addEventListener('click', function() {
        alert('This would open the guarantor form');
      });
      
      // Add collateral button
      document.getElementById('addCollateralBtn').addEventListener('click', function() {
        alert('This would open the collateral form');
      });
      
      // Add loan button
      document.getElementById('addLoanBtn').addEventListener('click', function() {
        alert('This would open the loan form');
      });
    });
  </script>
</body>
</html>