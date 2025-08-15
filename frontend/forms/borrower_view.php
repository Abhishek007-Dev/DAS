<?php
// partials/borrower_view.php
$borrower_id = $_GET['borrower_id'] ?? '';
$borrower = getBorrowerDetails($borrower_id); // Implement this function
?>

<div class="borrower-view-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <button type="button" class="btn btn-outline-secondary" id="borrowerViewBackBtn">
            <i class="bi bi-arrow-left me-1"></i> Back
        </button>
        <div>
            <span class="badge <?= getStatusBadgeClass($borrower['status']) ?>">
                <?= ucfirst($borrower['status']) ?>
            </span>
        </div>
    </div>
    
    <!-- Display all borrower data in view-only format -->
    <div class="view-mode">
        <fieldset class="mb-4">
            <legend class="fw-bold mb-3"><i class="bi bi-person-lines-fill me-2"></i>Personal Details</legend>
            
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Full Name:</th><td><?= htmlspecialchars($borrower['full_name']) ?></td></tr>
                        <tr><th>Date of Birth:</th><td><?= date('Y-m-d', strtotime($borrower['dob'])) ?></td></tr>
                        <tr><th>Gender:</th><td><?= ucfirst($borrower['gender']) ?></td></tr>
                    </table>
                </div>
                <!-- More fields... -->
            </div>
        </fieldset>
        
        <!-- Display addresses and family members similarly -->
    </div>
</div>

<script>
$('#borrowerViewBackBtn').click(function() {
    $('#borrowerFormContainer').addClass('d-none').empty();
    $('#borrowerSummary').removeClass('d-none');
});
</script>