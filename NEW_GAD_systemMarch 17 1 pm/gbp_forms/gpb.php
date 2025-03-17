<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../login.php");
    exit();
}

$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPAS Forms - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Load jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Then load Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- Campus/Year Selection and Autocomplete -->
    <script>
        // Initialize slide variables at the top
        let activityCurrentSlide = 0;
        let participantCurrentSlide = 0;
        let budgetCurrentSlide = 0;
        let categoryCurrentSlide = 0;

        // Global arrays to store activities
        let genericActivities = [];
        let specificActivities = [];

        // Global function to da activity count
        function updateActivityCount() {
            const count = specificActivities.length;
            const countDisplay = document.getElementById('activityCount');
            if (countDisplay) {
                countDisplay.innerHTML = `
                    <div class="activity-count">
                        <i class="fas fa-list-check"></i>
                        <span>${count} ${count === 1 ? 'Activity' : 'Activities'}</span>
                    </div>
                `;
            }
        }

        // Global function to update participant counts
        function updateParticipantCounts() {
            const maleCount = parseInt($('#male_participants').val()) || 0;
            const femaleCount = parseInt($('#female_participants').val()) || 0;
            const totalCount = maleCount + femaleCount;

            // Update the display counts
            $('#male_count').text(maleCount.toLocaleString());
            $('#female_count').text(femaleCount.toLocaleString());
            $('#total_participants_count').text(totalCount.toLocaleString());

            // Update progress bars
            const totalWidth = maleCount + femaleCount;
            if (totalWidth > 0) {
                const maleWidth = (maleCount / totalWidth) * 100;
                const femaleWidth = (femaleCount / totalWidth) * 100;
                $('#male_progress').css('width', maleWidth + '%');
                $('#female_progress').css('width', femaleWidth + '%');
            } else {
                $('#male_progress').css('width', '0%');
                $('#female_progress').css('width', '0%');
            }
        }

        $(document).ready(function() {
            // Load campuses on page load
            loadCampusOptions();
            
            // Initialize autocomplete for gender issues
            initializeGenderIssueAutocomplete();

            // Initialize analytics
            showActivitySlide(0);
            showParticipantSlide(0);
            showBudgetSlide(0);
            showCategorySlide(0);
            
            // Set up navigation button event handlers
            $('.prev-activity').on('click', prevActivityStat);
            $('.next-activity').on('click', nextActivityStat);
            
            $('.prev-participant').on('click', prevParticipantStat);
            $('.next-participant').on('click', nextParticipantStat);
            
            $('.prev-budget').on('click', prevBudgetStat);
            $('.next-budget').on('click', nextBudgetStat);
            
            $('.prev-category').on('click', prevCategoryStat);
            $('.next-category').on('click', nextCategoryStat);
            
            // Set up dot navigation
            $('.analytics-dots .dot').each(function(index) {
                $(this).on('click', function() {
                    const parent = $(this).closest('.analytics-card');
                    if (parent.find('#activitySlides').length) {
                        showActivitySlide(index);
                    } else if (parent.find('#participantSlides').length) {
                        showParticipantSlide(index);
                    } else if (parent.find('#budgetSlides').length) {
                        showBudgetSlide(index);
                    } else if (parent.find('#categorySlides').length) {
                        showCategorySlide(index);
                    }
                });
            });
            
            // Start auto-rotation
            startSlideRotation();
            
            // Add touch swipe support for mobile
            setupTouchEvents();
            
            // Load analytics data immediately
            updateAnalytics();
            
            // Initialize the activity count
            updateActivityCount();

            // Add event listeners for Enter key
            $(document).on('keypress', '#genericActivity', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addGenericActivity(e);
                }
            });

            $(document).on('keypress', '#activityInput', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addActivity();
                }
            });

            // Style updates for activity items
            const activityStyles = `
                <style>
                    .program-item,
                    .activity-item {
                        background: var(--bg-secondary);
                        border-radius: 8px;
                        padding: 12px 15px;
                        margin: 8px 0;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        transition: all 0.3s ease;
                        border: 1px solid var(--border-color);
                    }

                    .program-item:hover,
                    .activity-item:hover {
                        transform: translateX(5px);
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .program-item span,
                    .activity-item span {
                        flex-grow: 1;
                        margin-right: 15px;
                        font-size: 0.95rem;
                    }

                    .program-item .btn-danger,
                    .activity-item .btn-danger {
                        padding: 4px 8px;
                        border-radius: 6px;
                        background: rgba(220, 53, 69, 0.1);
                        color: #dc3545;
                        border: none;
                        transition: all 0.3s ease;
                    }

                    .program-item .btn-danger:hover,
                    .activity-item .btn-danger:hover {
                        background: #dc3545;
                        color: white;
                    }

                    [data-bs-theme="dark"] .program-item,
                    [data-bs-theme="dark"] .activity-item {
                        background: var(--dark-input);
                        border-color: var(--dark-border);
                    }

                    [data-bs-theme="dark"] .program-item span,
                    [data-bs-theme="dark"] .activity-item span {
                        color: var(--dark-text);
                    }

                    #genericActivityList,
                    #activityList {
                        max-height: 200px;
                        overflow-y: auto;
                        padding: 5px;
                        margin-top: 10px;
                        border-radius: 8px;
                    }

                    .activity-count {
                        color: var(--text-secondary);
                        font-size: 0.9rem;
                        margin-top: 10px;
                        display: flex;
                        align-items: center;
                        gap: 5px;
                    }

                    .activity-count i {
                        color: var(--accent-color);
                    }
                </style>
            `;
            $('head').append(activityStyles);

            // Update the activity count display
            function updateActivityCount() {
                const count = specificActivities.length;
                $('#activityCount').html(`
                    <div class="activity-count">
                        <i class="fas fa-list-check"></i>
                        <span>${count} ${count === 1 ? 'Activity' : 'Activities'}</span>
                    </div>
                `);
            }

            // Initialize the activity count
            updateActivityCount();

            // Save changes button click handler
            $('#saveChangesBtn').on('click', function() {
                // Validate that at least one generic activity exists
                if (genericActivities.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please add at least one generic activity'
                    });
                    return;
                }

                // Get all form values
                const formData = {
                    category: $('#category').val(),
                    gender_issue: $('#gender_issue').val(),
                    cause_of_issue: $('#causeOfIssue').val(),
                    gad_objective: $('#gadsResult').val(),
                    relevant_agency: $('#relevantAgency').val(),
                    generic_activity: genericActivities,
                    specific_activities: specificActivities,
                    male_participants: parseInt($('#male_participants').val()) || 0,
                    female_participants: parseInt($('#female_participants').val()) || 0,
                    gad_budget: parseFloat($('#gadBudget').val()) || 0,
                    source_of_budget: $('#sourceOfBudget').val(),
                    responsible_unit: $('#responsibleUnit').val(),
                    campus: $('#campus').val(),
                    year: $('#year').val()
                };

                // Validate required fields
                if (!formData.category || !formData.gender_issue || !formData.campus || !formData.year) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Fields Missing',
                        text: 'Please fill in all required fields'
                    });
                    return;
                }

                // Get GAA data before saving
                $.ajax({
                    url: '../api/get_gaa_data.php',
                    method: 'GET',
                    data: {
                        campus_id: formData.campus,
                        year_id: formData.year
                    },
                    success: function(response) {
                        console.log("GAA data response:", response);
                        
                        // Even if no data is found, we'll proceed with default values
                        if (response.success) {
                            formData.total_gaa = response.total_gaa || '0';
                            formData.total_gad_fund = response.total_gad_fund || '0';
                        } else {
                            console.warn("Failed to get GAA data:", response.message);
                            // Use default values
                            formData.total_gaa = '0';
                            formData.total_gad_fund = '0';
                        }

                        // Save the gender issue data
                        $.ajax({
                            url: '../api/save_gender_issue.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(formData),
                            success: function(saveResponse) {
                                if (saveResponse.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Gender issue saved successfully'
                                    }).then(() => {
                                        // Reset form fields
                                        $('#category').val('');
                                        $('#gender_issue').val('');
                                        $('#causeOfIssue').val('');
                                        $('#gadsResult').val('');
                                        $('#relevantAgency').val('');
                                        $('#male_participants').val('0');
                                        $('#female_participants').val('0');
                                        $('#gadBudget').val('');
                                        $('#sourceOfBudget').val('');
                                        $('#responsibleUnit').val('');
                                        $('#campus').val('');
                                        $('#year').val('').prop('disabled', true);
                                        
                                        // Clear arrays
                                        genericActivities = [];
                                        specificActivities = [];
                                        
                                        // Update lists and counts
                                        updateGenericActivityList();
                                        updateActivityList();
                                        updateActivityCount();
                                        updateParticipantCounts();
                                        
                                        // Close modal
                                        $('#genderIssueModal').modal('hide');
                                        
                                        // Update analytics
                                        updateAnalytics();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: saveResponse.message || 'Failed to save gender issue'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error saving gender issue:", error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to save gender issue: ' + error
                                });
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching GAA data:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to get GAA data: ' + error
                        });
                    }
                });
            });

            // Add edit functionality for activities
            let editingGenericIndex = -1;
            let editingSpecificIndex = -1;

            // Function to edit generic activity
            window.editGenericActivity = function(index) {
                editingGenericIndex = index;
                const activity = genericActivities[index];
                $('#genericActivity').val(activity);
                $('#addGenericBtn').html('<i class="fas fa-check"></i>').attr('onclick', 'updateGenericActivity()');
            };

            // Function to update generic activity
            window.updateGenericActivity = function() {
                const value = $('#genericActivity').val().trim();
                if (value && editingGenericIndex !== -1) {
                    genericActivities[editingGenericIndex] = value;
                    updateGenericActivityList();
                    $('#genericActivity').val('');
                    $('#addGenericBtn').html('<i class="fas fa-plus"></i>').attr('onclick', 'addGenericActivity(event)');
                    editingGenericIndex = -1;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Generic activity has been updated',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            };

            // Function to edit specific activity
            window.editSpecificActivity = function(index) {
                editingSpecificIndex = index;
                const activity = specificActivities[index];
                $('#activityInput').val(activity);
                $('#addSpecificBtn').html('<i class="fas fa-check"></i>').attr('onclick', 'updateSpecificActivity()');
            };

            // Function to update specific activity
            window.updateSpecificActivity = function() {
                const value = $('#activityInput').val().trim();
                if (value && editingSpecificIndex !== -1) {
                    specificActivities[editingSpecificIndex] = value;
                    updateActivityList();
                    $('#activityInput').val('');
                    $('#addSpecificBtn').html('<i class="fas fa-plus"></i>').attr('onclick', 'addActivity()');
                    editingSpecificIndex = -1;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Specific activity has been updated',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            };

            // Update the activity list functions to include edit buttons
            function updateGenericActivityList() {
                const list = $('#genericActivityList');
                list.empty();
                
                genericActivities.forEach((activity, index) => {
                    const div = $('<div>').addClass('program-item').html(`
                        <span>${activity}</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="editGenericActivity(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeGenericActivity(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                    list.append(div);
                });
            }

            function updateActivityList() {
                const list = $('#activityList');
                list.empty();
                
                specificActivities.forEach((activity, index) => {
                    const div = $('<div>').addClass('activity-item').html(`
                        <span>${activity}</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="editSpecificActivity(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeActivity(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                    list.append(div);
                });
            }

            // Initialize gender issue autocomplete
            function initializeGenderIssueAutocomplete() {
                // Initialize autocomplete for both gender_issue and genderIssue fields
                // (there seem to be two different IDs used in different parts of the code)
                
                // For #gender_issue field
                $("#gender_issue").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: '../api/search_gender_issues.php',
                            dataType: 'json',
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                console.log("Gender issue search response:", data);
                                if (data.success && Array.isArray(data.data)) {
                                    // Map the array of strings to objects with label and value properties
                                    const suggestions = data.data.map(item => ({
                                        label: item,  // what is displayed in the dropdown
                                        value: item   // what is set in the input when selected
                                    }));
                                    response(suggestions);
                                } else {
                                    console.error("Invalid response format:", data);
                                    response([]);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error searching gender issues:", error);
                                response([]);
                            }
                        });
                    },
                    minLength: 1,
                    delay: 300,
                    autoFocus: true,
                    classes: {
                        "ui-autocomplete": "custom-autocomplete"
                    },
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        return false;
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div>" + item.label + "</div>")
                        .appendTo(ul);
                };
                
                // For #genderIssue field (if it exists)
                if ($("#genderIssue").length) {
                    $("#genderIssue").autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: '../api/search_gender_issues.php',
                                dataType: 'json',
                                data: {
                                    term: request.term
                                },
                                success: function(data) {
                                    console.log("Gender issue search response:", data);
                                    if (data.success && Array.isArray(data.data)) {
                                        response(data.data);
                                    } else {
                                        console.error("Invalid response format:", data);
                                        response([]);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error searching gender issues:", error);
                                    response([]);
                                }
                            });
                        },
                        minLength: 1,
                        delay: 300,
                        autoFocus: true,
                        classes: {
                            "ui-autocomplete": "custom-autocomplete"
                        }
                    });
                }
            }

            // Remove any duplicate initialization
            if (typeof initializeGenderIssueAutocomplete === 'function') {
                $(document).off('focus', '#gender_issue');
            }

            // Add event listeners for participant input changes
            $('#male_participants, #female_participants').on('input', function() {
                updateParticipantCounts();
            });

            // Initial update of participant counts
            updateParticipantCounts();
        });

        function loadCampusOptions() {
            $.ajax({
                url: '../api/get_campuses.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const campusSelect = $('#campus');
                        campusSelect.empty();
                        campusSelect.append('<option value="">All Campuses</option>');
                        
                        response.data.forEach(function(campus) {
                            campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                        });
                        
                        // Enable the campus select
                        campusSelect.prop('disabled', false);
                        
                        // Add change event handler for campus
                        campusSelect.on('change', function() {
                            loadYearOptions();
                            updateAnalytics();
                        });
                    } else {
                        console.error('Error loading campuses:', response.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load campuses: ' + (response.message || 'Unknown error')
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading campuses:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load campuses'
                    });
                }
            });
        }

        function loadYearOptions() {
            const selectedCampus = $('#campus').val();
            const yearSelect = $('#year');
            
            yearSelect.empty();
            yearSelect.append('<option value="">All Years</option>');
            
            if (!selectedCampus) {
                // If no campus is selected, load all years
                $.ajax({
                    url: '../api/get_years.php',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            response.data.forEach(function(year) {
                                yearSelect.append(`<option value="${year.year}">${year.year}</option>`);
                            });
                            
                            // Enable the year select
                            yearSelect.prop('disabled', false);
                            
                            // Update analytics when year is changed
                            yearSelect.off('change').on('change', function() {
                                updateAnalytics();
                            });
                        }
                    }
                });
            } else {
                // If a campus is selected, load years for that campus
                $.ajax({
                    url: '../api/get_years.php',
                    method: 'GET',
                    data: { campus_id: selectedCampus },
                    success: function(response) {
                        if (response.success) {
                            response.data.forEach(function(year) {
                                yearSelect.append(`<option value="${year.year}">${year.year}</option>`);
                            });
                            
                            // Enable the year select
                            yearSelect.prop('disabled', false);
                            
                            // Update analytics when year is changed
                            yearSelect.off('change').on('change', function() {
                                updateAnalytics();
                            });
                        } else {
                            console.error('Error loading years:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading years:', error);
                    }
                });
            }
        }

        function initializeGenderIssueAutocomplete() {
            // Initialize autocomplete for both gender_issue and genderIssue fields
            // (there seem to be two different IDs used in different parts of the code)
            
            // For #gender_issue field
            $("#gender_issue").autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '../api/search_gender_issues.php',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            console.log("Gender issue search response:", data);
                            if (data.success && Array.isArray(data.data)) {
                                // Map the array of strings to objects with label and value properties
                                const suggestions = data.data.map(item => ({
                                    label: item,  // what is displayed in the dropdown
                                    value: item   // what is set in the input when selected
                                }));
                                response(suggestions);
                            } else {
                                console.error("Invalid response format:", data);
                                response([]);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error searching gender issues:", error);
                            response([]);
                        }
                    });
                },
                minLength: 1,
                delay: 300,
                autoFocus: true,
                classes: {
                    "ui-autocomplete": "custom-autocomplete"
                },
                select: function(event, ui) {
                    $(this).val(ui.item.value);
                    return false;
                }
            }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.label + "</div>")
                    .appendTo(ul);
            };
            
            // For #genderIssue field (if it exists)
            if ($("#genderIssue").length) {
                $("#genderIssue").autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: '../api/search_gender_issues.php',
                            dataType: 'json',
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                console.log("Gender issue search response:", data);
                                if (data.success && Array.isArray(data.data)) {
                                    response(data.data);
                                } else {
                                    console.error("Invalid response format:", data);
                                    response([]);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error searching gender issues:", error);
                                response([]);
                            }
                        });
                    },
                    minLength: 1,
                    delay: 300,
                    autoFocus: true,
                    classes: {
                        "ui-autocomplete": "custom-autocomplete"
                    }
                });
            }
        }

        // Add function to update analytics
        function updateAnalytics() {
            const campus = $('#campus').val();
            const year = $('#year').val();
            
            console.log('Updating analytics for:', { campus, year });
            
            // Show loading indicators with spinner animation
            $('.analytics-value').each(function() {
                const $this = $(this);
                $this.html('<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>');
            });
            
            // Reset and show loading state on progress bars
            $('.progress').css('width', '0%').addClass('progress-bar-animated');
            
            // Update subtitle to show current filter
            const subtitle = campus ? 
                (year ? `Showing data for ${campus} - ${year}` : `Showing data for ${campus} - All Years`) :
                (year ? `Showing data for All Campuses - ${year}` : 'Showing data for All Campuses - All Years');
            $('.analytics-subtitle').text(`(${subtitle})`);
            
            $.ajax({
                url: '../api/get_analytics.php',
                method: 'GET',
                data: { 
                    campus: campus || '', 
                    year: year || '' 
                },
                success: function(response) {
                    console.log('Analytics API response:', response);
                    
                    if (response.success && response.data) {
                        // Remove loading animations
                        $('.progress').removeClass('progress-bar-animated');
                        
                        const data = response.data;
                        
                        // Update analytics values with animation
                        animateValue('#totalActivities', data.activities.total);
                        animateValue('#genericActivities', data.activities.generic);
                        animateValue('#specificActivities', data.activities.specific);
                        animateValue('#totalParticipants', data.participants.total);
                        animateValue('#maleParticipants', data.participants.male);
                        animateValue('#femaleParticipants', data.participants.female);
                        
                        // Update categories values
                        animateValue('#totalCategories', data.categories.total);
                        animateValue('#clientFocused', data.categories.client_focused);
                        animateValue('#orgFocused', data.categories.org_focused);
                        
                        // Animate budget with peso sign
                        animateCurrency('#totalBudget', data.budget.total);
                        animatePercentage('#budgetPercentage', data.budget.percentage);
                        
                        // Update progress bars with animation
                        updateProgressBars(data);
                        
                        // Show success toast
                        showToast('Analytics Updated', 'Analytics data has been successfully updated', 'success');
                    } else {
                        const errorMsg = response.message || 'Unknown error occurred';
                        console.error('Analytics update failed:', errorMsg);
                        handleError('Failed to update analytics: ' + errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Analytics API error:', { xhr, status, error });
                    handleError('Error updating analytics: ' + error);
                }
            });
        }
        
        // Helper function to animate number values
        function animateValue(elementId, value) {
            const element = $(elementId);
            const start = parseInt(element.text().replace(/[^0-9]/g, '')) || 0;
            const duration = 1000;
            const steps = 20;
            const increment = (value - start) / steps;
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment >= 0 && current >= value) || (increment < 0 && current <= value)) {
                    clearInterval(timer);
                    element.text(value.toLocaleString());
                } else {
                    element.text(Math.round(current).toLocaleString());
                }
            }, duration / steps);
        }
        
        // Helper function to animate currency values
        function animateCurrency(elementId, value) {
            const element = $(elementId);
            const start = parseFloat(element.text().replace(/[^0-9.]/g, '')) || 0;
            const duration = 1000;
            const steps = 20;
            const increment = (value - start) / steps;
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment >= 0 && current >= value) || (increment < 0 && current <= value)) {
                    clearInterval(timer);
                    element.text('₱' + value.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                } else {
                    element.text('₱' + Math.round(current).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                }
            }, duration / steps);
        }
        
        // Helper function to animate percentage values
        function animatePercentage(elementId, value) {
            const element = $(elementId);
            const start = parseFloat(element.text()) || 0;
            const duration = 1000;
            const steps = 20;
            const increment = (value - start) / steps;
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if ((increment >= 0 && current >= value) || (increment < 0 && current <= value)) {
                    clearInterval(timer);
                    element.text(value.toFixed(1) + '%');
                } else {
                    element.text(current.toFixed(1) + '%');
                }
            }, duration / steps);
        }
        
        // Helper function to handle errors
        function handleError(message) {
            // Remove loading states
            $('.progress').removeClass('progress-bar-animated');
            $('.analytics-value').text('N/A');
            
            // Show error toast
            showToast('Error', message, 'error');
            
            console.error(message);
        }
        
        // Helper function to show toast notifications
        function showToast(title, message, type) {
            const toast = $(`
                <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : 'success'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong>${title}</strong><br>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);
            
            $('.toast-container').append(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            toast.on('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Helper function to update progress bars
        function updateProgressBars(data) {
            console.log('Updating progress bars with data:', data);
            
            try {
                // Activities progress bars
                const totalActivities = data.activities.total || 0;
                if (totalActivities > 0) {
                    const genericPercentage = ((data.activities.generic || 0) / totalActivities) * 100;
                    const specificPercentage = ((data.activities.specific || 0) / totalActivities) * 100;
                    
                    console.log('Activities percentages:', { genericPercentage, specificPercentage });
                    
                    $('#genericProgress').css('width', genericPercentage + '%');
                    $('#specificProgress').css('width', specificPercentage + '%');
                } else {
                    $('#genericProgress, #specificProgress').css('width', '0%');
                }
                
                // Participants progress bars
                const totalParticipants = data.participants.total || 0;
                if (totalParticipants > 0) {
                    const malePercentage = ((data.participants.male || 0) / totalParticipants) * 100;
                    const femalePercentage = ((data.participants.female || 0) / totalParticipants) * 100;
                    
                    console.log('Participants percentages:', { malePercentage, femalePercentage });
                    
                    $('#maleProgress').css('width', malePercentage + '%');
                    $('#femaleProgress').css('width', femalePercentage + '%');
                } else {
                    $('#maleProgress, #femaleProgress').css('width', '0%');
                }
                
                // Categories progress bars
                const totalCategories = data.categories.total || 0;
                if (totalCategories > 0) {
                    const clientPercentage = ((data.categories.client_focused || 0) / totalCategories) * 100;
                    const orgPercentage = ((data.categories.org_focused || 0) / totalCategories) * 100;
                    
                    console.log('Categories percentages:', { clientPercentage, orgPercentage });
                    
                    $('#clientProgress').css('width', clientPercentage + '%');
                    $('#orgProgress').css('width', orgPercentage + '%');
                } else {
                    $('#clientProgress, #orgProgress').css('width', '0%');
                }
            } catch (error) {
                console.error('Error updating progress bars:', error);
                // Reset all progress bars on error
                $('#genericProgress, #specificProgress, #maleProgress, #femaleProgress, #clientProgress, #orgProgress')
                    .css('width', '0%');
            }
        }

        // Analytics Carousel Functions
        function showActivitySlide(index) {
            const slides = document.querySelectorAll('#activitySlides .analytics-slide');
            const dots = document.querySelectorAll('#activitySlides + .analytics-nav + .analytics-dots .dot');
            
            if (!slides.length || !dots.length) return;
            
            slides[activityCurrentSlide].classList.remove('active');
            dots[activityCurrentSlide].classList.remove('active');
            
            activityCurrentSlide = index;
            
            slides[activityCurrentSlide].classList.add('active');
            dots[activityCurrentSlide].classList.add('active');
        }

        function prevActivityStat() {
            const slides = document.querySelectorAll('#activitySlides .analytics-slide');
            let index = activityCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showActivitySlide(index);
        }

        function nextActivityStat() {
            const slides = document.querySelectorAll('#activitySlides .analytics-slide');
            let index = activityCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showActivitySlide(index);
        }

        // Participant Slide Functions
        function showParticipantSlide(index) {
            const slides = document.querySelectorAll('#participantSlides .analytics-slide');
            const dots = document.querySelectorAll('#participantSlides + .analytics-nav + .analytics-dots .dot');
            
            if (!slides.length || !dots.length) return;
            
            slides[participantCurrentSlide].classList.remove('active');
            dots[participantCurrentSlide].classList.remove('active');
            
            participantCurrentSlide = index;
            
            slides[participantCurrentSlide].classList.add('active');
            dots[participantCurrentSlide].classList.add('active');
        }

        function prevParticipantStat() {
            const slides = document.querySelectorAll('#participantSlides .analytics-slide');
            let index = participantCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showParticipantSlide(index);
        }

        function nextParticipantStat() {
            const slides = document.querySelectorAll('#participantSlides .analytics-slide');
            let index = participantCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showParticipantSlide(index);
        }

        // Budget Slide Functions
        function showBudgetSlide(index) {
            const slides = document.querySelectorAll('#budgetSlides .analytics-slide');
            const dots = document.querySelectorAll('#budgetSlides + .analytics-nav + .analytics-dots .dot');
            
            if (!slides.length || !dots.length) return;
            
            slides[budgetCurrentSlide].classList.remove('active');
            dots[budgetCurrentSlide].classList.remove('active');
            
            budgetCurrentSlide = index;
            
            slides[budgetCurrentSlide].classList.add('active');
            dots[budgetCurrentSlide].classList.add('active');
        }

        function prevBudgetStat() {
            const slides = document.querySelectorAll('#budgetSlides .analytics-slide');
            let index = budgetCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showBudgetSlide(index);
        }

        function nextBudgetStat() {
            const slides = document.querySelectorAll('#budgetSlides .analytics-slide');
            let index = budgetCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showBudgetSlide(index);
        }

        // Category Slide Functions
        function showCategorySlide(index) {
            const slides = document.querySelectorAll('#categorySlides .analytics-slide');
            const dots = document.querySelectorAll('#categorySlides + .analytics-nav + .analytics-dots .dot');
            
            if (!slides.length || !dots.length) return;
            
            slides[categoryCurrentSlide].classList.remove('active');
            dots[categoryCurrentSlide].classList.remove('active');
            
            categoryCurrentSlide = index;
            
            slides[categoryCurrentSlide].classList.add('active');
            dots[categoryCurrentSlide].classList.add('active');
        }

        function prevCategoryStat() {
            const slides = document.querySelectorAll('#categorySlides .analytics-slide');
            let index = categoryCurrentSlide - 1;
            if (index < 0) index = slides.length - 1;
            showCategorySlide(index);
        }

        function nextCategoryStat() {
            const slides = document.querySelectorAll('#categorySlides .analytics-slide');
            let index = categoryCurrentSlide + 1;
            if (index >= slides.length) index = 0;
            showCategorySlide(index);
        }

        // Start auto-rotation for all slides
        function startSlideRotation() {
            // Auto-rotate slides every 5 seconds
            setInterval(function() {
                nextActivityStat();
                nextParticipantStat();
                nextBudgetStat();
                nextCategoryStat();
            }, 5000);
        }

        // Setup touch events for mobile swipe
        function setupTouchEvents() {
            let touchStartX = 0;
            let touchEndX = 0;
            
            // Find all analytics cards
            const analyticsCards = document.querySelectorAll('.analytics-card');
            
            analyticsCards.forEach(function(card) {
                // Determine which type of card this is
                let type = '';
                if (card.querySelector('#activitySlides')) {
                    type = 'activity';
                } else if (card.querySelector('#participantSlides')) {
                    type = 'participant';
                } else if (card.querySelector('#budgetSlides')) {
                    type = 'budget';
                } else if (card.querySelector('#categorySlides')) {
                    type = 'category';
                }
                
                if (type) {
                    // Add touch event listeners
                    card.addEventListener('touchstart', function(e) {
                        touchStartX = e.changedTouches[0].screenX;
                    }, false);
                    
                    card.addEventListener('touchend', function(e) {
                        touchEndX = e.changedTouches[0].screenX;
                        handleSwipe(touchStartX, touchEndX, type);
                    }, false);
                }
            });
        }
        
        function handleSwipe(startX, endX, type) {
            const SWIPE_THRESHOLD = 50;
            const diff = startX - endX;
            
            if (Math.abs(diff) < SWIPE_THRESHOLD) return;
            
            if (diff > 0) {
                // Swipe left - next slide
                switch(type) {
                    case 'activity': nextActivityStat(); break;
                    case 'participant': nextParticipantStat(); break;
                    case 'budget': nextBudgetStat(); break;
                    case 'category': nextCategoryStat(); break;
                }
            } else {
                // Swipe right - previous slide
                switch(type) {
                    case 'activity': prevActivityStat(); break;
                    case 'participant': prevParticipantStat(); break;
                    case 'budget': prevBudgetStat(); break;
                    case 'category': prevCategoryStat(); break;
                }
            }
        }

        function addGenericActivity(event) {
            event.preventDefault();
            const input = $('#genericActivity');
            const value = input.val().trim();
            
            if (value) {
                genericActivities.push(value);
                updateGenericActivityList();
                input.val('');
                
                // Show success notification
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'Generic activity has been added successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Input',
                    text: 'Please enter a generic activity'
                });
            }
        }

        function addActivity() {
            const input = $('#activityInput');
            const value = input.val().trim();
            
            if (value) {
                specificActivities.push(value);
                updateActivityList();
                input.val('');
                updateActivityCount();
                
                // Show success notification
                Swal.fire({
                    icon: 'success',
                    title: 'Added!',
                    text: 'Specific activity has been added successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Empty Input',
                    text: 'Please enter a specific activity'
                });
            }
        }

        function removeGenericActivity(index) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to remove this generic activity?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    genericActivities.splice(index, 1);
                    updateGenericActivityList();
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: 'Generic activity has been removed',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function removeActivity(index) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to remove this specific activity?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    specificActivities.splice(index, 1);
                    updateActivityList();
                    updateActivityCount();
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed!',
                        text: 'Specific activity has been removed',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        }

        function updateGenericActivityList() {
            const list = $('#genericActivityList');
            list.empty();
            
            genericActivities.forEach((activity, index) => {
                const div = $('<div>').addClass('program-item').html(`
                    <span>${activity}</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editGenericActivity(${index})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeGenericActivity(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `);
                list.append(div);
            });
        }

        function updateActivityList() {
            const list = $('#activityList');
            list.empty();
            
            specificActivities.forEach((activity, index) => {
                const div = $('<div>').addClass('activity-item').html(`
                    <span>${activity}</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editSpecificActivity(${index})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeActivity(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `);
                list.append(div);
            });
        }

        // Update the saveChangesBtn click handler to include activities
        $(document).ready(function() {
            // Save changes button click handler
            $('#saveChangesBtn').on('click', function() {
                // Validate that at least one generic activity exists
                if (genericActivities.length === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please add at least one generic activity'
                    });
                    return;
                }

                // Get all form values
                const formData = {
                    category: $('#category').val(),
                    gender_issue: $('#gender_issue').val(),
                    cause_of_issue: $('#causeOfIssue').val(),
                    gad_objective: $('#gadsResult').val(),
                    relevant_agency: $('#relevantAgency').val(),
                    generic_activity: genericActivities,
                    specific_activities: specificActivities,
                    male_participants: parseInt($('#male_participants').val()) || 0,
                    female_participants: parseInt($('#female_participants').val()) || 0,
                    gad_budget: parseFloat($('#gadBudget').val()) || 0,
                    source_of_budget: $('#sourceOfBudget').val(),
                    responsible_unit: $('#responsibleUnit').val(),
                    campus: $('#campus').val(),
                    year: $('#year').val()
                };

                // Validate required fields
                if (!formData.category || !formData.gender_issue || !formData.campus || !formData.year) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Required Fields Missing',
                        text: 'Please fill in all required fields'
                    });
                    return;
                }

                // Get GAA data before saving
                $.ajax({
                    url: '../api/get_gaa_data.php',
                    method: 'GET',
                    data: {
                        campus_id: formData.campus,
                        year_id: formData.year
                    },
                    success: function(response) {
                        console.log("GAA data response:", response);
                        
                        // Even if no data is found, we'll proceed with default values
                        if (response.success) {
                            formData.total_gaa = response.total_gaa || '0';
                            formData.total_gad_fund = response.total_gad_fund || '0';
                        } else {
                            console.warn("Failed to get GAA data:", response.message);
                            // Use default values
                            formData.total_gaa = '0';
                            formData.total_gad_fund = '0';
                        }

                        // Save the gender issue data
                        $.ajax({
                            url: '../api/save_gender_issue.php',
                            method: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify(formData),
                            success: function(saveResponse) {
                                if (saveResponse.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: 'Gender issue saved successfully'
                                    }).then(() => {
                                        // Reset form fields
                                        $('#category').val('');
                                        $('#gender_issue').val('');
                                        $('#causeOfIssue').val('');
                                        $('#gadsResult').val('');
                                        $('#relevantAgency').val('');
                                        $('#male_participants').val('0');
                                        $('#female_participants').val('0');
                                        $('#gadBudget').val('');
                                        $('#sourceOfBudget').val('');
                                        $('#responsibleUnit').val('');
                                        $('#campus').val('');
                                        $('#year').val('').prop('disabled', true);
                                        
                                        // Clear arrays
                                        genericActivities = [];
                                        specificActivities = [];
                                        
                                        // Update lists and counts
                                        updateGenericActivityList();
                                        updateActivityList();
                                        updateActivityCount();
                                        updateParticipantCounts();
                                        
                                        // Close modal
                                        $('#genderIssueModal').modal('hide');
                                        
                                        // Update analytics
                                        updateAnalytics();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: saveResponse.message || 'Failed to save gender issue'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error("Error saving gender issue:", error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to save gender issue: ' + error
                                });
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching GAA data:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to get GAA data: ' + error
                        });
                    }
                });
            });

            // Add edit functionality for activities
            let editingGenericIndex = -1;
            let editingSpecificIndex = -1;

            // Function to edit generic activity
            window.editGenericActivity = function(index) {
                editingGenericIndex = index;
                const activity = genericActivities[index];
                $('#genericActivity').val(activity);
                $('#addGenericBtn').html('<i class="fas fa-check"></i>').attr('onclick', 'updateGenericActivity()');
            };

            // Function to update generic activity
            window.updateGenericActivity = function() {
                const value = $('#genericActivity').val().trim();
                if (value && editingGenericIndex !== -1) {
                    genericActivities[editingGenericIndex] = value;
                    updateGenericActivityList();
                    $('#genericActivity').val('');
                    $('#addGenericBtn').html('<i class="fas fa-plus"></i>').attr('onclick', 'addGenericActivity(event)');
                    editingGenericIndex = -1;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Generic activity has been updated',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            };

            // Function to edit specific activity
            window.editSpecificActivity = function(index) {
                editingSpecificIndex = index;
                const activity = specificActivities[index];
                $('#activityInput').val(activity);
                $('#addSpecificBtn').html('<i class="fas fa-check"></i>').attr('onclick', 'updateSpecificActivity()');
            };

            // Function to update specific activity
            window.updateSpecificActivity = function() {
                const value = $('#activityInput').val().trim();
                if (value && editingSpecificIndex !== -1) {
                    specificActivities[editingSpecificIndex] = value;
                    updateActivityList();
                    $('#activityInput').val('');
                    $('#addSpecificBtn').html('<i class="fas fa-plus"></i>').attr('onclick', 'addActivity()');
                    editingSpecificIndex = -1;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: 'Specific activity has been updated',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            };

            // Update the activity list functions to include edit buttons
            function updateGenericActivityList() {
                const list = $('#genericActivityList');
                list.empty();
                
                genericActivities.forEach((activity, index) => {
                    const div = $('<div>').addClass('program-item').html(`
                        <span>${activity}</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="editGenericActivity(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeGenericActivity(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                    list.append(div);
                });
            }

            function updateActivityList() {
                const list = $('#activityList');
                list.empty();
                
                specificActivities.forEach((activity, index) => {
                    const div = $('<div>').addClass('activity-item').html(`
                        <span>${activity}</span>
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="editSpecificActivity(${index})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeActivity(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `);
                    list.append(div);
                });
            }
        });

        // Add theme switch functionality
        function toggleTheme() {
            const htmlElement = document.documentElement;
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update theme icon
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Set initial theme from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        });

        // Handle logout
        function handleLogout(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Confirm Logout',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, logout'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        }
    </script>

    <!-- Theme Switch and DateTime Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateDateTime() {
                const now = new Date();
                const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                
                const dateElement = document.getElementById('current-date');
                const timeElement = document.getElementById('current-time');
                
                if (dateElement && timeElement) {
                    dateElement.textContent = now.toLocaleDateString('en-US', dateOptions);
                    timeElement.textContent = now.toLocaleTimeString('en-US', timeOptions);
                }
            }

            // Update time every second
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });
    </script>

    <!-- Other scripts -->
    <script src="../js/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
        }
        
        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e9ecef;
            --sidebar-bg: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --hover-color: rgba(106, 27, 154, 0.1);
            --card-bg: #ffffff;
            --border-color: #dee2e6;
            --horizontal-bar: rgba(33, 37, 41, 0.125);
            --input-placeholder: rgba(33, 37, 41, 0.75);
            --input-bg: #ffffff;
            --input-text: #212529;
            --card-title: #212529;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
        }

        /* Autocomplete Styles */
        .custom-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999;
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 4px;
        }

        .custom-autocomplete .ui-menu-item {
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .custom-autocomplete .ui-menu-item:hover {
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary);
        }

        [data-bs-theme="dark"] .custom-autocomplete {
            background: var(--bs-dark);
            border-color: var(--bs-border-color);
        }

        [data-bs-theme="dark"] .custom-autocomplete .ui-menu-item {
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .custom-autocomplete .ui-menu-item:hover {
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary);
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
            padding-top: 20px;
        }

        body.fade-out {
    opacity: 0;
}

        

        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            position: fixed;
            left: 20px;
            top: 20px;
            padding: 20px;
            background: var(--sidebar-bg);
            color: var(--text-primary);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 15px rgba(0,0,0,0.05), 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1;
        }

        .main-content {
    margin-left: calc(var(--sidebar-width) + 20px);
    padding: 15px;
    height: calc(100vh - 30px);
    max-height: calc(100vh - 30px);
    background: var(--bg-primary);
    border-radius: 20px;
    position: relative;
    overflow-y: auto;
    scrollbar-width: none;  /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
}

/* Hide scrollbar for Chrome, Safari and Opera */
.main-content::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Chrome, Safari and Opera */
body::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for Firefox */
html {
    scrollbar-width: none;
}

        .nav-link {
            color: var(--text-primary);
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 5px;
            position: relative;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--accent-color);
            border-radius: 0 2px 2px 0;
        }

        /* Add hover state for active nav links in dark mode */
        [data-bs-theme="dark"] .nav-link.active:hover {
            color: white;
        }

        .nav-item {
            position: relative;
        }

        .nav-item .dropdown-menu {
            position: static !important;
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            padding: 8px 0;
            margin: 5px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-width: 200px;
            transform: none !important;
            display: none;
            overflow: visible;
            max-height: none;
        }

        .nav-item .dropdown-menu.show {
            display: block;
        }

        .nav-item .dropdown-menu .dropdown-item {
            padding: 8px 48px;
            color: var(--text-primary);
            position: relative;
            opacity: 0.85;
            background: transparent;
        }

        .nav-item .dropdown-menu .dropdown-item::before {
            content: '•';
            position: absolute;
            left: 35px;
            color: var(--accent-color);
        }

        .nav-item .dropdown-menu .dropdown-item:hover {
            background: var(--hover-color);
            color: white;
            opacity: 1;
        }

        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .logo-container {
            padding: 20px 0;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .logo-image {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            margin-bottom: -25px;
        }

        .logo-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .datetime-container {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--horizontal-bar);
        }

        .datetime-container .date {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .datetime-container .time {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--accent-color);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.4) transparent;
            overflow-x: hidden; 
        }

        .nav-content::-webkit-scrollbar {
            width: 5px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.4);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.7);
        }

        .nav-link:focus,
        .dropdown-toggle:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .dropdown-menu {
            outline: none !important;
            border: none !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .dropdown-item:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Bottom controls container */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;
            align-items: center;
        }

        /* Logout button styles */
        .logout-button {
            flex: 1;
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Theme switch button */
        .theme-switch-button {
            width: 46.5px;
            height: 50px;
            padding: 12px 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

                /* Light theme specific styles for bottom controls */
                [data-bs-theme="light"] .logout-button,
        [data-bs-theme="light"] .theme-switch-button {
            background: #f2f2f2;
            border-width: 1.5px;
        }

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .theme-switch {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .theme-switch-button:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 8px 12px rgba(0, 0, 0, 0.15),
                0 3px 6px rgba(0, 0, 0, 0.1),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        .theme-switch-button:active {
            transform: translateY(0);
            box-shadow: 
                0 4px 6px rgba(0, 0, 0, 0.1),
                0 2px 4px rgba(0, 0, 0, 0.06),
                inset 0 1px 2px rgba(255, 255, 255, 0.2);
        }

        /* Theme switch button icon size */
        .theme-switch-button i {
            font-size: 1rem; 
        }

        .theme-switch-button:hover i {
            transform: scale(1.1);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 1.5rem;
        }

        .page-title i {
            color: var(--accent-color);
            font-size: 2.2rem;
        }

        .page-title h2 {
            margin: 0;
            font-weight: 600;
        }

        .show>.nav-link {
            background: transparent !important;
            color: var(--accent-color) !important;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 240px;
            }

            body {
                padding: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                left: 0;
                top: 0;
                height: 100vh;
                position: fixed;
                padding-top: 70px;
                border-radius: 0;
                box-shadow: 5px 0 25px rgba(0,0,0,0.1);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 70px 15px 15px 15px;
                border-radius: 0;
                box-shadow: none;
            }

            .mobile-nav-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--card-bg);
                border: none;
                border-radius: 8px;
                color: var(--text-primary);
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                cursor: pointer;
            }

            .mobile-nav-toggle:hover {
                background: var(--hover-color);
                color: var(--accent-color);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .theme-switch {
                position: fixed;
                bottom: 30px;
                right: 30px;
            }

        }

        @media (max-width: 576px) {
            :root {
                --sidebar-width: 100%;
            }

            .sidebar {
                left: 0;
                top: 0;
                width: 100%;
                height: 100vh;
                padding-top: 60px;
            }

            .mobile-nav-toggle {
                width: 40px;
                height: 40px;
                top: 10px;
                left: 10px;
            }

            .theme-switch {
                top: 10px;
                right: 10px;
            }

            .theme-switch-button {
                padding: 8px 15px;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                margin-top: 10px;
            }

            .page-title h2 {
                font-size: 1.5rem;
            }
        }

        /* Modern Card Styles */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            position: relative;
            min-height: auto; /* Changed from 465px to auto */
        }

        .card-body {
            padding: 1rem;
            display: flex;
            flex-direction: column;
        }

        #ppasForm {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        #ppasForm.row {
            flex: 1;
        }

        #ppasForm .col-12.text-end {
            margin-top: auto !important;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;  /* Updated to match personnel list */
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #2d2d2d;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #2d2d2d;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;  /* Updated to match sidebar-bg */
        }

        /* Dark mode card */
        [data-bs-theme="dark"] .card {
            background-color: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .card-header {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            overflow: hidden;
        }

        /* Fix for card header corners */
        .card-header {
            border-top-left-radius: inherit !important;
            border-top-right-radius: inherit !important;
            padding-bottom: 0.5rem !important;
        }

        .card-title {
            margin-bottom: 0;
            font-size: 1.1rem; /* Slightly smaller font size */
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px; /* Reduced from 20px */
        }

        .form-group {
            flex: 1 1 200px;
            margin-bottom: 10px; /* Reduced from 0.75rem */
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 25px;
            margin-bottom: 20px;
        }

        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
            border-color: #dc3545 !important;
        }

        #editBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state */
        #addBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

#viewBtn {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

#viewBtn:hover {
    background: #0d6efd;
    color: white;
}

/* Optional: Add disabled state for view button */
#viewBtn.disabled {
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Add these styles for disabled buttons */
.btn-disabled {
    border-color: #6c757d !important;
    background: rgba(108, 117, 125, 0.1) !important;
    color: #6c757d !important;
    opacity: 0.65 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
}

/* Dark mode styles */
[data-bs-theme="dark"] .btn-disabled {
    background-color: #495057 !important;
    border-color: #495057 !important;
    color: #adb5bd !important;
}

.swal-blur-container {
    backdrop-filter: blur(5px);
}

.analytics-section {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    background-color: #f8f9fa;
    margin-top: 20px;
}

[data-bs-theme="dark"] .analytics-section {
    border-color: #2d2d2d;
    background-color: #2d2d2d;
    color: #ffffff;
}

/* Ensure chart text is visible in dark mode */
[data-bs-theme="dark"] #analyticsChart {
    color: #ffffff;
}

[data-bs-theme="dark"] .form-control {
    background-color: var(--dark-input);
    border-color: var(--dark-border);
    color: #ffffff;
}

[data-bs-theme="dark"] .form-control:focus {
    background-color: var(--dark-input);
    border-color: var(--accent-color);
    color: #ffffff;
}

[data-bs-theme="dark"] .modal-content {
    background-color: var(--dark-sidebar);
    color: #ffffff;
}

[data-bs-theme="dark"] .modal-header {
    border-bottom-color: var(--dark-border);
}

[data-bs-theme="dark"] .modal-footer {
    border-top-color: var(--dark-border);
}

[data-bs-theme="dark"] label {
    color: #ffffff;
}

[data-bs-theme="dark"] select.form-control option {
    background-color: var(--dark-input);
    color: #ffffff;
}

[data-bs-theme="dark"] .program-item,
[data-bs-theme="dark"] .activity-item {
    color: #ffffff;
    background-color: var(--dark-input);
    padding: 8px;
    margin: 5px 0;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-group {
    display: inline-flex;
    gap: 5px;
}

.program-item,
.activity-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px;
    margin: 5px 0;
    border-radius: 4px;
    background-color: var(--bg-secondary);
}

[data-bs-theme="dark"] .program-item,
[data-bs-theme="dark"] .activity-item {
    background-color: var(--dark-input);
}

.program-item span,
.activity-item span {
    flex-grow: 1;
    margin-right: 10px;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 5px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background-color: var(--input-bg);
    color: var(--input-text);
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-dropdown {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: var(--input-bg);
    color: var(--input-text);
    border: 1px solid var(--border-color);
}

.select2-container--default .select2-results__option {
    color: var(--text-primary);
}

.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--accent-color);
    color: white;
}

[data-bs-theme="dark"] .select2-container--default .select2-selection--single {
    background-color: var(--dark-input);
    border-color: var(--dark-border);
    color: white;
}

[data-bs-theme="dark"] .select2-dropdown {
    background-color: var(--dark-sidebar);
    border-color: var(--dark-border);
}

[data-bs-theme="dark"] .select2-search__field {
    background-color: var(--dark-input) !important;
    color: white !important;
}

[data-bs-theme="dark"] .select2-results__option {
    color: white;
}

/* Autocomplete styling */
.ui-autocomplete {
    max-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
    z-index: 9999 !important;
    background-color: white !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.25rem !important;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
    padding: 5px 0 !important;
    width: auto !important;
    min-width: 200px !important;
}
.ui-menu-item {
    padding: 0 !important;
}
.ui-menu-item-wrapper {
    padding: 8px 15px !important;
    border: none !important;
    cursor: pointer !important;
}
.ui-menu-item-wrapper.ui-state-active {
    background-color: #007bff !important;
    color: white !important;
    margin: 0 !important;
}
        
        /* Add your custom styles here */
        body {
            padding-top: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
        }
        
        /* Reduce empty space in container */
        .container {
            max-width: 95%;
            padding: 0 15px;
        }
        
        /* Make the card more compact */
        .card-body {
            padding: 1rem;
        }
        
        /* Reduce spacing between form elements */
        .form-group {
            margin-bottom: 0.75rem;
        }
        
        /* Adjust table content section */
        .table-content-section {
            min-height: 100px; /* Reduced from 300px */
            max-height: 400px; /* Reduced from 500px */
            overflow-y: auto;
        }
        
        /* Make modal more compact */
        .modal-body {
            padding: 1rem;
        }
        
        /* Reduce spacing in analytics section */
        .analytics-section {
            margin-top: 1rem;
        }
        
        .analytics-section .card {
            margin-bottom: 1rem;
        }
        
        /* Adjust canvas height */
        #analyticsChart {
            height: 200px !important;
        }
        
        /* Autocomplete styling */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999 !important;
            background-color: white !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2) !important;
            padding: 5px 0 !important;
            width: auto !important;
            min-width: 200px !important;
        }
        .ui-menu-item {
            padding: 0 !important;
        }
        .ui-menu-item-wrapper {
            padding: 8px 15px !important;
            border: none !important;
            cursor: pointer !important;
        }
        .ui-menu-item-wrapper.ui-state-active {
            background-color: #007bff !important;
            color: white !important;
            margin: 0 !important;
        }

        /* Analytics Cards Styles */
        .analytics-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            min-height: 180px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .analytics-card-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .analytics-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        /* Icon colors and backgrounds */
        .analytics-icon.activities { 
            background: rgba(25, 135, 84, 0.1); 
            color: #198754; 
        }

        .analytics-icon.participants { 
            background: rgba(13, 110, 253, 0.1); 
            color: #0d6efd; 
        }

        .analytics-icon.male { 
            background: rgba(13, 110, 253, 0.1); 
            color: #0d6efd; 
        }

        .analytics-icon.female { 
            background: rgba(214, 51, 132, 0.1); 
            color: #d63384; 
        }

        .analytics-icon.budget { 
            background: rgba(255, 193, 7, 0.1); 
            color: #ffc107; 
        }

        .analytics-icon.categories { 
            background: rgba(111, 66, 193, 0.1); 
            color: #6f42c1; 
        }

        .analytics-icon.client { 
            background: rgba(32, 201, 151, 0.1); 
            color: #20c997; 
        }

        .analytics-icon.org { 
            background: rgba(102, 16, 242, 0.1); 
            color: #6610f2; 
        }

        .analytics-info {
            flex: 1;
        }

        .analytics-label {
            display: block;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .analytics-value {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.2;
        }

        .analytics-value.percentage {
            color: #198754;
        }

        .analytics-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .analytics-nav:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .analytics-nav.prev { left: 10px; }
        .analytics-nav.next { right: 10px; }

        .analytics-dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
        }

        .analytics-dots .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--text-secondary);
            opacity: 0.5;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .analytics-dots .dot.active {
            opacity: 1;
            background: var(--accent-color);
            transform: scale(1.2);
        }

        .analytics-slide {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .analytics-slide.active {
            display: block;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0;
                transform: translateY(10px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dark theme adjustments */
        [data-bs-theme="dark"] .analytics-card {
            background: var(--dark-sidebar);
        }

        [data-bs-theme="dark"] .analytics-nav {
            background: var(--dark-input);
            color: var(--dark-text);
            border-color: var(--dark-border);
        }   

        [data-bs-theme="dark"] .analytics-nav:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        [data-bs-theme="dark"] .analytics-value {
            color: var(--dark-text);
        }

        [data-bs-theme="dark"] .analytics-label {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .analytics-card {
                min-height: 160px;
            }

            .analytics-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }

            .analytics-value {
                font-size: 1.5rem;
            }

            .analytics-label {
                font-size: 0.85rem;
            }
        }

        /* Progress bar styles */
        .progress-container {
            margin: 1rem 0;
        }

        .progress-bar {
            background: var(--bg-secondary);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin: 0.5rem 0;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .progress {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease-in-out;
        }

        /* Progress bar colors */
        #genericProgress, #specificProgress {
            background: linear-gradient(to right, #6a1b9a, #9c27b0);
        }

        #maleProgress {
            background: linear-gradient(to right, #1565c0, #42a5f5);
        }

        #femaleProgress {
            background: linear-gradient(to right, #c2185b, #f06292);
        }

        #clientProgress {
            background: linear-gradient(to right, #00796b, #4db6ac);
        }

        #orgProgress {
            background: linear-gradient(to right, #e65100, #ff9800);
        }

        /* Add hover effect to progress bars */
        .progress-container:hover .progress {
            opacity: 0.9;
        }

        /* Add these to your existing styles */
        .analytics-section {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .analytics-section h5 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        /* Dark theme specific adjustments */
        [data-bs-theme="dark"] .analytics-section {
            background: var(--dark-sidebar);
            border-color: var(--dark-border);
        }

        [data-bs-theme="dark"] .progress-bar {
            background: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] .progress {
            background: var(--accent-color);
        }

        /* Analytics title styles */
        .analytics-title {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .analytics-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: normal;
        }

        /* Add these styles in the existing <style> section */
        .bg-pink {
            background-color: #e83e8c;
        }

        .progress {
            height: 8px;
            margin-top: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        #male_count, #female_count, #total_participants_count {
            font-weight: bold;
            color: var(--accent-color);
        }

        [data-bs-theme="dark"] .progress {
            background-color: rgba(255, 255, 255, 0.1);
        }

        [data-bs-theme="dark"] #male_count,
        [data-bs-theme="dark"] #female_count,
        [data-bs-theme="dark"] #total_participants_count {
            color: var(--accent-color);
        }

        /* Autocomplete styling improvements */
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            z-index: 9999 !important;
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
        }

        .ui-menu-item {
            padding: 8px 15px !important;
            cursor: pointer !important;
            color: var(--text-primary) !important;
        }

        .ui-menu-item-wrapper.ui-state-active {
            background: var(--accent-color) !important;
            color: white !important;
            border: none !important;
            margin: 0 !important;
        }

        [data-bs-theme="dark"] .ui-autocomplete {
            background: var(--dark-sidebar) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .ui-menu-item {
            color: var(--dark-text) !important;
        }
    </style>
</head>
<body>
    <!-- Toast container for notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
    
    <!-- Mobile Navigation Toggle -->
    <button class="mobile-nav-toggle d-lg-none">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <div class="logo-title">GAD SYSTEM</div>
            <div class="logo-image">
                <img src="../images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
        <div class="datetime-container">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="nav-content">
            <nav class="nav flex-column">
                <a href="../dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gpb.php">GPB Form</a></li>
                        <li><a class="dropdown-item" href="../ppas_forms/ppas.php">PPAs Form</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">                       
                        <li><a class="dropdown-item" href="#">GPB Reports</a></li>
                        <li><a class="dropdown-item" href="#">PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="#">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="#">Quarterly Reports</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- Add inside the sidebar div, after the nav-content div (around line 1061) -->
        <div class="bottom-controls">
            <a href="#" class="logout-button" onclick="handleLogout(event)">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
            <button class="theme-switch-button" onclick="toggleTheme()">
                <i class="fas fa-sun" id="theme-icon"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <i class="fas fa-users-gear"></i>
            <h2>GPB Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">GPB Form</h5>
            </div>
            <div class="card-body">
                <form id="gpbDataEntryForm">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="campus">Campus</label>
                            <select id="campus" class="form-control" onchange="loadYearOptions()">
                                <option value="">Select Campus</option>
                                <!-- Campus options will be loaded from target table -->
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="year">Year</label>
                            <select id="year" class="form-control" disabled>
                                <option value="">Select Year</option>
                                <!-- Year options will be loaded from target table based on selected campus -->
                            </select>
                        </div>
                    </div>
                    <h5>Table Content (Per Gender Issue/GAD Mandate)</h5>
                    <div class="d-flex justify-content-end mb-3">
                        <button type="button" class="btn-icon" data-bs-toggle="modal" data-bs-target="#genderIssueModal">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>

                    <!-- Gender Issue Modal -->
                    <div class="modal fade" id="genderIssueModal" tabindex="-1" role="dialog" aria-labelledby="genderIssueModalLabel">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="genderIssueModalLabel">Add Gender Issue</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="genderIssueForm">
                                        <div class="form-group">
                                            <label for="category">Category</label>
                                            <select id="category" class="form-control">
                                                <option value="">Select Category</option>
                                                <option value="Client-Focused">Client-Focused</option>
                                                <option value="Organization-Focused">Organization-Focused</option>
                                                <option value="Gender Issue">Gender Issue</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="gender_issue">Gender Issue/GAD Mandate</label>
                                            <input type="text" id="gender_issue" name="gender_issue" class="form-control" placeholder="Type to search gender issues">
                                        </div>
                                        <div class="form-group">
                                            <label for="causeOfIssue">Cause of Gender Issue</label>
                                            <input type="text" id="causeOfIssue" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="gadsResult">GAD Result/GAD Objective</label>
                                            <input type="text" id="gadsResult" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="relevantAgency">Relevant Agency MFO/MPO</label>
                                            <select id="relevantAgency" class="form-control">
                                                <option value="Agency A">Agency A</option>
                                                <option value="Agency B">Agency B</option>
                                                <!-- Add more agencies as needed -->
                                            </select>
                                        </div>
                                        <h6>GAD Activity</h6>
                                        <div class="form-group">
                                            <label for="genericActivity">Generic Activity/Program <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-2 mb-2">
                                                <input type="text" id="genericActivity" name="genericActivity" class="form-control" placeholder="Enter generic activity (required)">
                                                <button type="button" class="btn-icon" id="addGenericBtn" onclick="addGenericActivity(event)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <small class="form-text text-muted">This field is required and will be saved exactly as typed.</small>
                                            <div id="genericActivityList"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="activityInput">Specific Activities</label>
                                            <div class="d-flex gap-2 mb-2">
                                                <input type="text" id="activityInput" class="form-control" placeholder="Enter specific activity">
                                                <button type="button" class="btn-icon" id="addSpecificBtn" onclick="addActivity()">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <p>Number of Activities: <span id="activityCount">0</span></p>
                                            <div id="activityList"></div>
                                        </div>
                                        <div class="form-group">
                                            <label>No. of Participants</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label for="male_participants">Male</label>
                                                    <input type="number" id="male_participants" class="form-control" min="0" value="0">
                                                    <div class="mt-2">
                                                        <span id="male_count">0</span> participants
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="female_participants">Female</label>
                                                    <input type="number" id="female_participants" class="form-control" min="0" value="0">
                                                    <div class="mt-2">
                                                        <span id="female_count">0</span> participants
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label>Total Participants: <span id="total_participants_count">0</span></label>
                                                <div class="progress">
                                                    <div id="male_progress" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                                    <div id="female_progress" class="progress-bar bg-pink" role="progressbar" style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="gadBudget">GAD Budget</label>
                                            <input type="number" id="gadBudget" class="form-control" step="0.01">
                                        </div>
                                        <div class="form-group">
                                            <label for="sourceOfBudget">Source of Budget/Fund</label>
                                            <select id="sourceOfBudget" class="form-control">
                                                <option value="Source A">Source A</option>
                                                <option value="Source B">Source B</option>
                                                <!-- Add more sources as needed -->
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="responsibleUnit">Responsible Unit/Office</label>
                                            <select id="responsibleUnit" class="form-control">
                                                <option value="Unit A">Unit A</option>
                                                <option value="Unit B">Unit B</option>
                                                <!-- Add more units as needed -->
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                               
                                    <button type="button" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
                                </div>
                            </div>
                        </div>
                    </div>
      
                </form>
            </div>
        </div>

        <div class="analytics-section">
            <div class="row mb-3">
                <div class="col-12">
                    <h4 class="analytics-title">Analytics Overview <span class="analytics-subtitle">(Showing all data by default)</span></h4>
                </div>
            </div>
         
            <div class="row g-4">
                <!-- Activities Card -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card">
                        <div class="analytics-nav prev" onclick="prevActivityStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="activitySlides">
                            <!-- Total Activities -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon activities">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Total Activities</span>
                                        <h3 class="analytics-value" id="totalActivities">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Generic Activities -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon activities-icon">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="genericActivities">0</h3>
                                        <p class="analytics-label">Generic Activities</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="genericProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Specific Activities -->
                            <div class="analytics-slide" data-index="2">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon activities-icon">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="specificActivities">0</h3>
                                        <p class="analytics-label">Specific Activities</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="specificProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextActivityStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showActivitySlide(0)"></span>
                            <span class="dot" onclick="showActivitySlide(1)"></span>
                            <span class="dot" onclick="showActivitySlide(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Participants Card -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card">
                        <div class="analytics-nav prev" onclick="prevParticipantStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="participantSlides">
                            <!-- Total Participants -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon participants">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Total Participants</span>
                                        <h3 class="analytics-value" id="totalParticipants">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Male Participants -->
                            <div class="analytics-slide">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon participants-icon">
                                        <i class="fas fa-male"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="maleParticipants">0</h3>
                                        <p class="analytics-label">Male Participants</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="maleProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Female Participants -->
                            <div class="analytics-slide">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon participants-icon">
                                        <i class="fas fa-female"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="femaleParticipants">0</h3>
                                        <p class="analytics-label">Female Participants</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="femaleProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextParticipantStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showParticipantSlide(0)"></span>
                            <span class="dot" onclick="showParticipantSlide(1)"></span>
                            <span class="dot" onclick="showParticipantSlide(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Budget Card -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card">
                        <div class="analytics-nav prev" onclick="prevBudgetStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="budgetSlides">
                            <!-- Total Budget -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon budget">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Total GAD Budget</span>
                                        <h3 class="analytics-value" id="totalBudget">₱0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Budget Percentage -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon budget">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Budget Percentage</span>
                                        <h3 class="analytics-value percentage" id="budgetPercentage">0%</h3>
                                        <p class="subtitle">of Total GAA</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextBudgetStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showBudgetSlide(0)"></span>
                            <span class="dot" onclick="showBudgetSlide(1)"></span>
                        </div>
                    </div>
                </div>

                <!-- Categories Card -->
                <div class="col-md-6 col-xl-3">
                    <div class="analytics-card">
                        <div class="analytics-nav prev" onclick="prevCategoryStat()">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="analytics-slides" id="categorySlides">
                            <!-- Total Categories -->
                            <div class="analytics-slide active" data-index="0">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon categories">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <span class="analytics-label">Total Categories</span>
                                        <h3 class="analytics-value" id="totalCategories">0</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Client Focused -->
                            <div class="analytics-slide" data-index="1">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon client">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="clientFocused">0</h3>
                                        <p class="analytics-label">Client-Focused</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="clientProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                            <!-- Organization Focused -->
                            <div class="analytics-slide" data-index="2">
                                <div class="analytics-card-content">
                                    <div class="analytics-icon org">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="analytics-info">
                                        <h3 class="analytics-value" id="orgFocused">0</h3>
                                        <p class="analytics-label">Organization-Focused</p>
                                    </div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress" id="orgProgress" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="analytics-nav next" onclick="nextCategoryStat()">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="analytics-dots">
                            <span class="dot active" onclick="showCategorySlide(0)"></span>
                            <span class="dot" onclick="showCategorySlide(1)"></span>
                            <span class="dot" onclick="showCategorySlide(2)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>