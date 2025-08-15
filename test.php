<form id="borrowerForm" data-customer-id="<?php echo htmlspecialchars($customer_id); ?>">
    <!-- Step 1: Personal Details -->
    <div class="form-step form-step-0">
        <fieldset>
            <legend class="fw-bold mb-3"><i class="bi bi-person-lines-fill me-2"></i>Personal Details</legend>
            <!-- Your personal details fields here -->
             <div class="row g-3">
                <!-- Full Name -->
                <div class="col-md-4">
                    <label for="full_name" class="form-label">Full Name (Nepali)</label>
                    <input type="text" class="form-control nepali-input" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($borrower['full_name'] ?? ''); ?>" required>
                </div>
                
                <!-- Date of Birth -->
                <div class="col-md-4">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" 
                           value="<?php echo htmlspecialchars($borrower['dob'] ?? ''); ?>" required>
                </div>
                
                <!-- Gender -->
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="gender_male" 
                                   value="male" <?php echo (isset($borrower['gender']) && $borrower['gender'] == 'male' ? 'checked' : ''); ?> required>
                            <label class="form-check-label" for="gender_male">Male</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="gender_female" 
                                   value="female" <?php echo (isset($borrower['gender']) && $borrower['gender'] == 'female' ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="gender_female">Female</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="gender" id="gender_other" 
                                   value="other" <?php echo (isset($borrower['gender']) && $borrower['gender'] == 'other' ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="gender_other">Other</label>
                        </div>
                    </div>
                </div>
                
                <!-- Relationship Status -->
                <div class="col-md-4">
                    <label for="relationship_status" class="form-label">Relationship Status</label>
                    <select class="form-select" id="relationship_status" name="relationship_status" required>
                        <option value="" disabled>Select status</option>
                        <option value="single" <?php echo (isset($borrower['relationship_status']) && $borrower['relationship_status'] == 'single' ? 'selected' : ''); ?>>Single</option>
                        <option value="married" <?php echo (isset($borrower['relationship_status']) && $borrower['relationship_status'] == 'married' ? 'selected' : ''); ?>>Married</option>
                        <option value="divorced" <?php echo (isset($borrower['relationship_status']) && $borrower['relationship_status'] == 'divorced' ? 'selected' : ''); ?>>Divorced</option>
                        <option value="widowed" <?php echo (isset($borrower['relationship_status']) && $borrower['relationship_status'] == 'widowed' ? 'selected' : ''); ?>>Widowed</option>
                    </select>
                </div>
                
                <!-- Citizenship Number -->
                <div class="col-md-4">
                    <label for="citizenship_number" class="form-label">Citizenship Number</label>
                    <input type="text" class="form-control" id="citizenship_number" name="citizenship_number" 
                           value="<?php echo htmlspecialchars($borrower['citizenship_number'] ?? ''); ?>" required>
                </div>
                
                <!-- Citizenship Issue Date -->
                <div class="col-md-4">
                    <label for="citizenship_issue_date" class="form-label">Issue Date</label>
                    <input type="date" class="form-control" id="citizenship_issue_date" name="citizenship_issue_date" 
                           value="<?php echo htmlspecialchars($borrower['citizenship_issue_date'] ?? ''); ?>" required>
                </div>
                
                <!-- Citizenship Issue Authority -->
                <div class="col-md-4">
                    <label for="citizenship_issue_authority" class="form-label">Issue Authority</label>
                    <input type="text" class="form-control" id="citizenship_issue_authority" name="citizenship_issue_authority" 
                           value="<?php echo htmlspecialchars($borrower['citizenship_issue_authority'] ?? ''); ?>" required>
                </div>
                
                <!-- Citizenship Issue District -->
                <div class="col-md-4">
                    <label for="citizenship_issue_district" class="form-label">Issue District</label>
                    <input type="text" class="form-control" id="citizenship_issue_district" name="citizenship_issue_district" 
                           value="<?php echo htmlspecialchars($borrower['citizenship_issue_district'] ?? ''); ?>" required>
                </div>
            </div>
        </fieldset>
        <div class="d-flex justify-content-end mt-4">
            <button type="button" class="btn btn-secondary form-next-btn">Next <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- Step 2: Address Details -->
    <div class="form-step form-step-1 d-none">
        <fieldset>
            <legend class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Address Details</legend>
            <!-- Your address fields here -->
              <h6 class="mb-3">Permanent Address</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="permanent_country" class="form-label">Country</label>
                    <select class="form-select" id="permanent_country" name="permanent_country" required>
                        <option value="Nepal" selected>Nepal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="permanent_province" class="form-label">Province</label>
                    <select class="form-select" id="permanent_province" name="permanent_province" required>
                        <option value="" disabled>Select Province</option>
                        <option value="1" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '1' ? 'selected' : ''); ?>>Province 1</option>
                        <option value="2" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '2' ? 'selected' : ''); ?>>Madhesh</option>
                        <option value="3" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '3' ? 'selected' : ''); ?>>Bagmati</option>
                        <option value="4" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '4' ? 'selected' : ''); ?>>Gandaki</option>
                        <option value="5" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '5' ? 'selected' : ''); ?>>Lumbini</option>
                        <option value="6" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '6' ? 'selected' : ''); ?>>Karnali</option>
                        <option value="7" <?php echo (isset($borrower['permanent_province']) && $borrower['permanent_province'] == '7' ? 'selected' : ''); ?>>Sudurpaschim</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="permanent_district" class="form-label">District</label>
                    <input type="text" class="form-control" id="permanent_district" name="permanent_district" 
                           value="<?php echo htmlspecialchars($borrower['permanent_district'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="permanent_municipality" class="form-label">Municipality/VDC</label>
                    <input type="text" class="form-control nepali-input" id="permanent_municipality" name="permanent_municipality" 
                           value="<?php echo htmlspecialchars($borrower['permanent_municipality'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="permanent_ward" class="form-label">Ward No.</label>
                    <input type="text" class="form-control" id="permanent_ward" name="permanent_ward" 
                           value="<?php echo htmlspecialchars($borrower['permanent_ward'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="permanent_town" class="form-label">Town/Village</label>
                    <input type="text" class="form-control nepali-input" id="permanent_town" name="permanent_town"
                           value="<?php echo htmlspecialchars($borrower['permanent_town'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="permanent_tole" class="form-label">Tole</label>
                    <input type="text" class="form-control nepali-input" id="permanent_tole" name="permanent_tole"
                           value="<?php echo htmlspecialchars($borrower['permanent_tole'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="permanent_street" class="form-label">Street No.</label>
                    <input type="text" class="form-control" id="permanent_street" name="permanent_street"
                           value="<?php echo htmlspecialchars($borrower['permanent_street'] ?? ''); ?>">
                </div>
            </div>
            
            <!-- Temporary Address -->
            <h6 class="mt-4 mb-3">Temporary Address</h6>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="same_as_permanent">
                <label class="form-check-label" for="same_as_permanent">Same as Permanent Address</label>
            </div>
            
            <div class="row g-3" id="temporary_address_fields">
                <div class="col-md-4">
                    <label for="temporary_country" class="form-label">Country</label>
                    <select class="form-select" id="temporary_country" name="temporary_country">
                        <option value="Nepal" selected>Nepal</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="temporary_province" class="form-label">Province</label>
                    <select class="form-select" id="temporary_province" name="temporary_province">
                        <option value="" disabled>Select Province</option>
                        <option value="1" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '1' ? 'selected' : ''); ?>>Province 1</option>
                        <option value="2" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '2' ? 'selected' : ''); ?>>Madhesh</option>
                        <option value="3" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '3' ? 'selected' : ''); ?>>Bagmati</option>
                        <option value="4" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '4' ? 'selected' : ''); ?>>Gandaki</option>
                        <option value="5" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '5' ? 'selected' : ''); ?>>Lumbini</option>
                        <option value="6" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '6' ? 'selected' : ''); ?>>Karnali</option>
                        <option value="7" <?php echo (isset($borrower['temporary_province']) && $borrower['temporary_province'] == '7' ? 'selected' : ''); ?>>Sudurpaschim</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="temporary_district" class="form-label">District</label>
                    <input type="text" class="form-control" id="temporary_district" name="temporary_district"
                           value="<?php echo htmlspecialchars($borrower['temporary_district'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="temporary_municipality" class="form-label">Municipality/VDC</label>
                    <input type="text" class="form-control nepali-input" id="temporary_municipality" name="temporary_municipality"
                           value="<?php echo htmlspecialchars($borrower['temporary_municipality'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="temporary_ward" class="form-label">Ward No.</label>
                    <input type="text" class="form-control" id="temporary_ward" name="temporary_ward"
                           value="<?php echo htmlspecialchars($borrower['temporary_ward'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="temporary_town" class="form-label">Town/Village</label>
                    <input type="text" class="form-control nepali-input" id="temporary_town" name="temporary_town"
                           value="<?php echo htmlspecialchars($borrower['temporary_town'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="temporary_tole" class="form-label">Tole</label>
                    <input type="text" class="form-control nepali-input" id="temporary_tole" name="temporary_tole"
                           value="<?php echo htmlspecialchars($borrower['temporary_tole'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="temporary_street" class="form-label">Street No.</label>
                    <input type="text" class="form-control" id="temporary_street" name="temporary_street"
                           value="<?php echo htmlspecialchars($borrower['temporary_street'] ?? ''); ?>">
                </div>
            </div>
        </fieldset>
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary form-prev-btn"><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-secondary form-next-btn">Next <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- Step 3: Family Details -->
    <div class="form-step form-step-2 d-none">
        <fieldset>
            <legend class="fw-bold mb-3"><i class="bi bi-people-fill me-2"></i>Family Details</legend>
            <!-- Your family details fields here -->
              <div id="family_members_container">
                <?php if (isset($borrower['family_members']) && !empty($borrower['family_members'])): ?>
                    <?php 
                    $family_members = json_decode($borrower['family_members'], true);
                    foreach ($family_members as $index => $member): ?>
                    <div class="row family-member mb-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control nepali-input" name="family_name[]" 
                                   value="<?php echo htmlspecialchars($member['name']); ?>" placeholder="Name" required>
                        </div>
                        <div class="col-md-5">
                            <select class="form-select" name="family_relation[]" required>
                                <option value="father" <?php echo $member['relation'] == 'father' ? 'selected' : ''; ?>>Father</option>
                                <option value="mother" <?php echo $member['relation'] == 'mother' ? 'selected' : ''; ?>>Mother</option>
                                <option value="spouse" <?php echo $member['relation'] == 'spouse' ? 'selected' : ''; ?>>Spouse</option>
                                <option value="grandfather" <?php echo $member['relation'] == 'grandfather' ? 'selected' : ''; ?>>Grandfather</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-family">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="row family-member mb-3">
                        <div class="col-md-5">
                            <input type="text" class="form-control nepali-input" name="family_name[]" placeholder="Name" required>
                        </div>
                        <div class="col-md-5">
                            <select class="form-select" name="family_relation[]" required>
                                <option value="father">Father</option>
                                <option value="mother">Mother</option>
                                <option value="spouse">Spouse</option>
                                <option value="grandfather">Grandfather</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-family" disabled>
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" class="btn btn-outline-primary btn-sm" id="add_family_member">
                <i class="bi bi-plus-circle me-1"></i> Add Family Member
            </button>
        </fieldset>
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary form-prev-btn"><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="button" class="btn btn-secondary form-next-btn">Next <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
    </div>

    <!-- Step 4: Review -->
    <div class="form-step form-step-3 d-none">
        <fieldset>
            <legend class="fw-bold mb-3"><i class="bi bi-check-circle me-2"></i>Review Details</legend>
            <!-- Summary of all entered data -->
            <div id="formReviewSummary"></div>
        </fieldset>
        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn btn-outline-secondary form-prev-btn"><i class="bi bi-arrow-left me-1"></i> Back</button>
            <button type="submit" class="btn btn-primary">Save Borrower Details</button>
        </div>
    </div>
</form>