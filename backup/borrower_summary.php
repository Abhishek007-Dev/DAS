<?php
require_once "../../backend/db_connect.php";

$customer_id = $_GET['id'] ?? '';
if (empty($customer_id)) {
    die("Customer ID not provided");
}

$stmt = $conn->prepare("SELECT * FROM borrower_details WHERE customer_id = ?");
$stmt->bind_param("s", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$borrower = $result->fetch_assoc();

if ($borrower) {
    // Decode family members JSON
    $family_members = json_decode($borrower['family_members'], true);
    ?>
    <div class="borrower-summary">
        <div class="row">
            <!-- Personal Details -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Personal Details</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="40%">Full Name:</th>
                                <td><?php echo htmlspecialchars($borrower['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Date of Birth:</th>
                                <td><?php echo htmlspecialchars($borrower['dob']); ?></td>
                            </tr>
                            <tr>
                                <th>Gender:</th>
                                <td><?php echo ucfirst(htmlspecialchars($borrower['gender'])); ?></td>
                            </tr>
                            <tr>
                                <th>Relationship Status:</th>
                                <td><?php echo ucfirst(htmlspecialchars($borrower['relationship_status'])); ?></td>
                            </tr>
                            <tr>
                                <th>Citizenship No:</th>
                                <td><?php echo htmlspecialchars($borrower['citizenship_number']); ?></td>
                            </tr>
                            <tr>
                                <th>Issue Date:</th>
                                <td><?php echo htmlspecialchars($borrower['citizenship_issue_date']); ?></td>
                            </tr>
                            <tr>
                                <th>Issue District:</th>
                                <td><?php echo htmlspecialchars($borrower['citizenship_issue_district']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Address Details -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Address Details</h6>
                    </div>
                    <div class="card-body">
                        <h6 class="small fw-bold">Permanent Address</h6>
                        <p class="small mb-2">
                            <?php echo htmlspecialchars($borrower['permanent_municipality']); ?>, 
                            Ward <?php echo htmlspecialchars($borrower['permanent_ward']); ?><br>
                            <?php echo htmlspecialchars($borrower['permanent_town']); ?>, 
                            <?php echo htmlspecialchars($borrower['permanent_tole']); ?><br>
                            <?php echo htmlspecialchars($borrower['permanent_district']); ?>, 
                            <?php echo htmlspecialchars($borrower['permanent_province']); ?>
                        </p>
                        
                        <h6 class="small fw-bold mt-3">Temporary Address</h6>
                        <p class="small mb-0">
                            <?php echo htmlspecialchars($borrower['temporary_municipality']); ?>, 
                            Ward <?php echo htmlspecialchars($borrower['temporary_ward']); ?><br>
                            <?php echo htmlspecialchars($borrower['temporary_town']); ?>, 
                            <?php echo htmlspecialchars($borrower['temporary_tole']); ?><br>
                            <?php echo htmlspecialchars($borrower['temporary_district']); ?>, 
                            <?php echo htmlspecialchars($borrower['temporary_province']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Family Details -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>Family Details</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($family_members)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($family_members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($member['relation'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mb-0">No family members added</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2">
            <button class="btn btn-sm btn-outline-primary" id="editBorrowerBtn">
                <i class="bi bi-pencil me-1"></i> Edit
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="viewBorrowerBtn">
                <i class="bi bi-eye me-1"></i> View
            </button>
            <button class="btn btn-sm btn-outline-danger" id="deleteBorrowerBtn">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>
    <?php
} else {
    echo '<div class="text-center empty-data py-4">No borrower details added yet</div>';
}
?>