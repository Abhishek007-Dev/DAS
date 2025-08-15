

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo htmlspecialchars($customer['full_name'] ?? 'Customer Profile'); ?> | Document Automation System</title>
  <link rel="icon" type="image/x-icon" href="/DAS/assets/images/sbifavicon.ico">
  <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/dash.css">
  <style>
    .profile-main-card {
      border-left: 4px solid #7b145d;
      transition: all 0.3s ease;
    }
    .profile-main-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .status-badge {
      font-size: 0.8rem;
      padding: 5px 10px;
      border-radius: 20px;
    }
    .empty-data {
      color: #6c757d;
      font-style: italic;
    }
    .profile-avatar {
      width: 80px;
      height: 80px;
      object-fit: cover;
    }
    .activity-timestamp {
      font-size: 0.8rem;
      color: #6c757d;
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
    .detail-card {
      transition: transform 0.2s;
    }
    .detail-card:hover {
      transform: translateY(-3px);
    }
    #borrowerFormContainer {
      transition: all 0.3s ease;
    }
    .form-modified {
      background-color: #fff8e1;
    }
    .action-buttons .btn {
      margin-right: 5px;
      margin-bottom: 5px;
    }
    /* Loader and Form Progress Styles */
#loaderOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

.form-progress {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.progress {
    height: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    background-color: #7b145d;
    transition: width 0.3s ease;
}

.form-step {
    display: none;
}

.form-step.active {
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
          <?php  //echo htmlspecialchars($_SESSION['maker1']); ?>
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

        <!-- Borrower Details -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Borrower Details</h5>
            <button class="btn btn-sm btn-outline-primary">Add</button>
          </div>
          <div class="card-body">
            <div class="text-center empty-data">No borrower details added yet</div>
          </div>
        </div> 

        <!-- Guarantor Details -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Guarantor Details</h5>
            <button class="btn btn-sm btn-outline-primary">Add</button>
          </div>
          <div class="card-body">
            <div class="text-center empty-data py-4">No guarantors added yet</div>
          </div>
        </div>

        <!-- Security/Collateral Details -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Security Details</h5>
            <button class="btn btn-sm btn-outline-primary">Add</button>
          </div>
          <div class="card-body">
            <div class="text-center empty-data py-4">No collateral added yet</div>
          </div>
        </div>

        <!-- Loan Details -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Loan Details</h5>
            <button class="btn btn-sm btn-outline-primary">Add</button>
          </div>
          <div class="card-body">
            <div class="text-center empty-data py-4">No loans added yet</div>
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

  <!-- Confirmation Modal -->
  <div class="modal fade" id="confirmNavigationModal" tabindex="-1" aria-labelledby="confirmNavigationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmNavigationModalLabel">Unsaved Changes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          You have unsaved changes in the form. If you leave now, all your changes will be lost. Are you sure you want to continue?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmLeave">Leave Anyway</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteBorrowerModal" tabindex="-1" aria-labelledby="deleteBorrowerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteBorrowerModalLabel">Confirm Deletion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          Are you sure you want to delete these borrower details? This action cannot be undone.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBorrower">Delete</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JS -->
  <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/dashboard.js"></script>
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

// Submit for approval confirmation
document.getElementById('submitForApproval').addEventListener('click', function() {
    if (confirm('Are you sure you want to submit for approval? Once submitted, you cannot make changes.')) {
        showLoader();
        $.ajax({
            url: '../../backend/update_customer_status.php',
            method: 'POST',
            data: {
                customer_id: '<?php echo $customer_id; ?>',
                status: 'submitted'
            },
            success: function(response) {
                hideLoader();
                if (response.success) {
                    alert('Submitted for approval successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to update status'));
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                alert('Error: ' + error);
            }
        });
    }
});

// Delete customer confirmation
document.getElementById('deleteCustomerBtn').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        showLoader();
        $.ajax({
            url: '../../backend/delete_customer.php',
            method: 'POST',
            data: {
                customer_id: '<?php echo $customer_id; ?>'
            },
            success: function(response) {
                hideLoader();
                if (response.success) {
                    alert('Customer deleted successfully!');
                    window.location.href = 'dashboard.php';
                } else {
                    alert('Error: ' + (response.message || 'Failed to delete customer'));
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                alert('Error: ' + error);
            }
        });
    }
});

// Borrower form handling - MERGED VERSION
$(document).ready(function() {
    const customerId = '<?php echo $customer_id; ?>';
    const formContainer = $('#borrowerFormContainer');
    const summaryContainer = $('#borrowerSummary');
    let formModified = false;
    let currentFormStep = 0;
    const totalFormSteps = 4; // Adjust based on your form sections

    // Show/hide loader functions
    function showLoader() {
        $('#loaderOverlay').removeClass('d-none').addClass('d-flex');
    }

    function hideLoader() {
        $('#loaderOverlay').removeClass('d-flex').addClass('d-none');
    }

    // Add Borrower Button
    $('#addBorrowerBtn').click(function() {
        loadBorrowerForm();
    });

    // Edit Borrower Button
    $('#editBorrowerBtn').click(function() {
        loadBorrowerForm(true);
    });

    // View Borrower Button
    $('#viewBorrowerBtn').click(function() {
        window.location.href = `view_borrower.php?customer_id=${customerId}`;
    });

    // Delete Borrower Button
    $('#deleteBorrowerBtn').click(function() {
        if (confirm('Are you sure you want to delete these borrower details?')) {
            showLoader();
            $.ajax({
                url: '../../backend/delete_borrower.php',
                method: 'POST',
                data: { customer_id: customerId },
                success: function(response) {
                    hideLoader();
                    if (response.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete'));
                    }
                },
                error: function() {
                    hideLoader();
                    alert('Error deleting borrower details');
                }
            });
        }
    });

    // Load the borrower form
    function loadBorrowerForm(isEdit = false) {
        showLoader();
        formContainer.empty().load('../forms/borrower_form.php?id=' + customerId + (isEdit ? '&edit=true' : ''), function() {
            hideLoader();
            summaryContainer.addClass('d-none');
            formContainer.removeClass('d-none');
            initFormProgress();
            initFormNavigation();
        });
    }

    // Initialize form progress tracker
    function initFormProgress() {
        // Create progress bar HTML
        const progressHtml = `
            <div class="form-progress mb-4">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 small text-muted">
                    <span>Personal Details</span>
                    <span>Address</span>
                    <span>Family</span>
                    <span>Review</span>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="cancelFormBtn">
                <i class="bi bi-arrow-left me-1"></i> Cancel
            </button>
        `;
        formContainer.prepend(progressHtml);

        // Update progress based on current step
        updateFormProgress();
    }

    // Update form progress
    function updateFormProgress() {
        const progress = Math.min(100, Math.max(0, (currentFormStep / totalFormSteps) * 100));
        $('.progress-bar').css('width', progress + '%');
    }

    // Initialize form navigation and validation
    function initFormNavigation() {
        // Track form modifications
        formContainer.on('input change', 'input, select, textarea', function() {
            formModified = true;
        });

        // Cancel button
        $('#cancelFormBtn').click(function() {
            if (formModified) {
                if (confirm('You have unsaved changes. Discard them?')) {
                    closeForm();
                }
            } else {
                closeForm();
            }
        });

        // Form submission
        $('#borrowerForm').submit(function(e) {
            e.preventDefault();
            saveBorrowerForm();
        });

        // Navigation between form steps
        $(document).on('click', '.form-next-btn', function() {
            if (validateCurrentStep()) {
                currentFormStep++;
                updateFormProgress();
                showFormStep(currentFormStep);
            }
        });

        $(document).on('click', '.form-prev-btn', function() {
            currentFormStep--;
            updateFormProgress();
            showFormStep(currentFormStep);
        });
    }

    // Validate current form step
    function validateCurrentStep() {
        // Implement validation logic for current step
        let isValid = true;
        
        // Example validation for step 0 (Personal Details)
        if (currentFormStep === 0) {
            const fullName = $('#full_name').val();
            const dob = $('#dob').val();
            
            if (!fullName || !dob) {
                isValid = false;
                alert('Please fill all required fields');
            }
        }
        
        return isValid;
    }

    // Show specific form step
    function showFormStep(step) {
        // Hide all steps
        $('.form-step').removeClass('active');
        // Show current step
        $(`.form-step-${step}`).addClass('active');
    }

    // Close the form
    function closeForm() {
        formContainer.addClass('d-none');
        summaryContainer.removeClass('d-none');
        formModified = false;
        currentFormStep = 0;
    }

    // Save borrower form
    function saveBorrowerForm() {
        showLoader();
        const formData = $('#borrowerForm').serialize();
        
        $.ajax({
            url: '../../backend/save_borrower.php',
            method: 'POST',
            data: formData,
            success: function(response) {
                hideLoader();
                try {
                    const data = typeof response === 'object' ? response : JSON.parse(response);
                    if (data.success) {
                        alert('Borrower details saved successfully!');
                        closeForm();
                        // Reload the summary section
                        summaryContainer.load(' #borrowerSummary > *');
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save data'));
                    }
                } catch (e) {
                    alert('Error parsing server response');
                }
            },
            error: function(xhr, status, error) {
                hideLoader();
                alert('Error: ' + error);
            }
        });
    }

    // Initialize view based on URL parameters
    function initView() {
        if (<?php echo $edit_mode ? 'true' : 'false'; ?>) {
            loadBorrowerForm(true);
        } else if (<?php echo $has_borrower ? 'true' : 'false'; ?>) {
            // Show summary by default
        } else {
            // Don't automatically show form - let user click "Add"
        }
    }

    initView();
});

// Existing navigation prevention code
window.addEventListener('beforeunload', function(e) {
    const formModified = $('#borrowerFormContainer').is(':visible') && 
                         $('#borrowerForm').find('.form-modified').length > 0;
    if (formModified) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
    }
});

$(document).ready(function() {
    const customerId = '<?php echo $customer_id; ?>';
    const formContainer = $('#borrowerFormContainer');
    const summaryContainer = $('#borrowerSummary');
    
    // Add Borrower Button
    $('#addBorrowerBtn').click(function() {
        loadBorrowerForm();
    });

    // Load the borrower form
    function loadBorrowerForm(isEdit = false) {
        showLoader();
        formContainer.load('../forms/borrower_form.php?id=' + customerId, function() {
            hideLoader();
            summaryContainer.addClass('d-none');
            formContainer.removeClass('d-none');
            initFormProgress();
            initFormNavigation();
        });
    }

    // Rest of your existing code...
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
</body>
</html>