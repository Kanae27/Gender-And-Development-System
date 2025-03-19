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
    <script src="../js/common.js"></script>
    <!-- Immediate theme loading to prevent flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Add jQuery UI CSS and JS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            opacity: 1;
            transition: opacity 0.05s ease-in-out; /* Changed from 0.05s to 0.01s - make it super fast */
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
            min-height: 465px;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
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

        /* Dark Theme Colors */
        [data-bs-theme="dark"] {
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
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
        }

        /* Form Controls */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1 1 200px;
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

        /* Form Pagination Styles */
        .step-indicator {
            font-size: 0.9rem;
            color: var(--text-secondary);
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-indicator.active {
            color: var(--accent-color);
            font-weight: 600;
        }

        .step-indicator::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--accent-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .step-indicator.active::after {
            transform: scaleX(1);
        }

        .progress {
            background-color: var(--border-color);
            overflow: hidden;
            border-radius: 2px;
        }

        .progress-bar {
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }

        .form-page {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .form-page.fade-out {
            opacity: 0;
            transform: translateX(-20px);
        }

        .form-page.fade-in {
            opacity: 1;
            transform: translateX(0);
        }

        /* Navigation Button Styles */
        #prevBtn, #nextBtn, #submitBtn {
            min-width: 120px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        #prevBtn:hover, #nextBtn:hover, #submitBtn:hover {
            transform: translateY(-2px);
        }

        #prevBtn i, #nextBtn i, #submitBtn i {
            transition: transform 0.3s ease;
        }

        #prevBtn:hover i {
            transform: translateX(-3px);
        }

        #nextBtn:hover i, #submitBtn:hover i {
            transform: translateX(3px);
        }

        /* Dark theme adjustments */
        [data-bs-theme="dark"] .step-indicator {
            color: var(--text-secondary);
        }

        [data-bs-theme="dark"] .step-indicator.active {
            color: var(--accent-color);
        }

        [data-bs-theme="dark"] .progress {
            background-color: var(--dark-border);
        }

        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 9999;
        }
        .personnel-item {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            position: relative;
            color: var(--text-primary);
        }
        .personnel-item .remove-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Select2 Dark Mode Styles */
        [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--accent-color) !important;
            border: 1px solid var(--accent-hover) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-dropdown {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-search__field {
            background-color: var(--dark-input) !important;
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .select2-results__option {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .select2-results__option[aria-selected="true"] {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            border-color: var(--dark-border) !important;
        }

        /* Dark mode dropdown styles */
        [data-bs-theme="dark"] .ui-autocomplete {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .ui-autocomplete .ui-menu-item-wrapper {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
            background-color: var(--accent-color) !important;
            color: white !important;
            border: none;
        }

        /* Form select and input styles for dark mode */
        [data-bs-theme="dark"] select.form-select,
        [data-bs-theme="dark"] input.form-control {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            color: var(--text-primary) !important;
        }

        /* Dropdown menu styles for dark mode */
        [data-bs-theme="dark"] .dropdown-menu {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Autocomplete dropdown specific styles */
        [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item strong {
            color: var(--accent-color) !important;
        }

        [data-bs-theme="dark"] .ui-autocomplete .autocomplete-item small {
            color: var(--text-secondary) !important;
        }

        /* Input group button styles for dark mode */
        [data-bs-theme="dark"] .input-group-text,
        [data-bs-theme="dark"] .btn-outline-secondary {
            border-color: var(--dark-border) !important;
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Select2 dropdown specific styles for dark mode */
        [data-bs-theme="dark"] .select2-dropdown {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-results__option {
            color: var(--text-primary) !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-hover) !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            color: var(--text-primary) !important;
        }

        /* Input placeholder color for dark mode */
        [data-bs-theme="dark"] input::placeholder,
        [data-bs-theme="dark"] select::placeholder {
            color: var(--input-placeholder) !important;
            opacity: 0.7;
        }

        /* Focused input styles for dark mode */
        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            border-color: var(--accent-color) !important;
            box-shadow: 0 0 0 0.25rem rgba(156, 39, 176, 0.25) !important;
        }
    </style>
</head>
<body>

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
                        <li><a class="dropdown-item" href="#">PPAs Form</a></li>
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
            <h2>PPAS Management</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">PPAS Form</h5>
            </div>
            <div class="card-body">
                <form id="ppasForm">
                    <!-- Form Pages Navigation -->
                    <div class="mb-4">
                        <div class="progress" style="height: 3px;">
                            <div class="progress-bar" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="step-indicator active" data-step="1">Basic Info</span>
                            <span class="step-indicator" data-step="2">Personnel</span>
                            <span class="step-indicator" data-step="3">Beneficiaries</span>
                            <span class="step-indicator" data-step="4">Budget & SDGs</span>
                        </div>
                    </div>

                    <!-- Page 1: Basic Information -->
                    <div class="form-page" id="page1">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                    <?php
                                    $currentYear = date('Y');
                                    for($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                                        echo "<option value='$i'>$i</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quarter" class="form-label">Quarter</label>
                                <select class="form-select" id="quarter" name="quarter" required>
                                    <option value="">Select Quarter</option>
                                    <option value="Q1">Q1</option>
                                    <option value="Q2">Q2</option>
                                    <option value="Q3">Q3</option>
                                    <option value="Q4">Q4</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="program" class="form-label">Program</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="program" name="program" placeholder="Search or add new program..." required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="addNewProgram()">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="project" class="form-label">Project</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="project" name="project" placeholder="Search or add new project..." required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="addNewProject()">
                                        <i class="fas fa-plus"></i> New
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="Start typing to search titles..." required>
                            </div>

                            <div class="col-md-12">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>

                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" required>
                            </div>

                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" required>
                            </div>

                            <div class="col-md-3">
                                <label for="startTime" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="startTime" name="startTime" required>
                            </div>

                            <div class="col-md-3">
                                <label for="endTime" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="endTime" name="endTime" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label d-block">Break Time</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="hasLunchBreak" name="hasLunchBreak">
                                    <label class="form-check-label" for="hasLunchBreak">Lunch Break (1 hr)</label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="totalDuration" class="form-label">Total Duration (Hours)</label>
                                <input type="text" class="form-control form-control-lg bg-light" id="totalDuration" name="totalDuration" readonly>
                                <input type="hidden" id="rawTotalDuration" name="rawTotalDuration">
                            </div>
                        </div>
                    </div>

                    <!-- Page 2: Personnel Section -->
                    <div class="form-page" id="page2" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">Personnel Involved</h5>
                            </div>

                            <!-- Project Leader -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Project Leader</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control personnel-autocomplete" data-role="projectLeader" placeholder="Search personnel...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="projectLeaderGender" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="projectLeaderAcademicRank" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="projectLeaderMonthlySalary" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate per Hour</label>
                                                <input type="text" class="form-control" id="projectLeaderHourlyRate" readonly>
                                            </div>
                                        </div>
                                        <div id="projectLeaderList" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assistant Project Leader -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Assistant Project Leader</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control personnel-autocomplete" data-role="asstProjectLeader" placeholder="Search personnel...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="asstProjectLeaderGender" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="asstProjectLeaderAcademicRank" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="asstProjectLeaderMonthlySalary" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate per Hour</label>
                                                <input type="text" class="form-control" id="asstProjectLeaderHourlyRate" readonly>
                                            </div>
                                        </div>
                                        <div id="asstProjectLeaderList" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Project Staff -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Project Staff/Coordinator</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control personnel-autocomplete" data-role="projectStaff" placeholder="Search personnel...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="projectStaffGender" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="projectStaffAcademicRank" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="projectStaffMonthlySalary" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate per Hour</label>
                                                <input type="text" class="form-control" id="projectStaffHourlyRate" readonly>
                                            </div>
                                        </div>
                                        <div id="projectStaffList" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Other Internal Participants -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Other Internal Participants</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control personnel-autocomplete" data-role="otherParticipants" placeholder="Search personnel...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Gender</label>
                                                <input type="text" class="form-control" id="otherParticipantsGender" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Academic Rank</label>
                                                <input type="text" class="form-control" id="otherParticipantsAcademicRank" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Monthly Salary</label>
                                                <input type="text" class="form-control" id="otherParticipantsMonthlySalary" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Rate per Hour</label>
                                                <input type="text" class="form-control" id="otherParticipantsHourlyRate" readonly>
                                            </div>
                                        </div>
                                        <div id="otherParticipantsList" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 3: Beneficiaries Section -->
                    <div class="form-page" id="page3" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">Beneficiaries</h5>
                            </div>

                            <!-- Internal Beneficiaries -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Internal</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <h6>Students</h6>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label for="internalMaleStudents" class="form-label">Male</label>
                                                        <input type="number" class="form-control" id="internalMaleStudents" name="internalMaleStudents" min="0">
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="internalFemaleStudents" class="form-label">Female</label>
                                                        <input type="number" class="form-control" id="internalFemaleStudents" name="internalFemaleStudents" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Faculty</h6>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <label for="internalMaleFaculty" class="form-label">Male</label>
                                                        <input type="number" class="form-control" id="internalMaleFaculty" name="internalMaleFaculty" readonly>
                                                    </div>
                                                    <div class="col-6">
                                                        <label for="internalFemaleFaculty" class="form-label">Female</label>
                                                        <input type="number" class="form-control" id="internalFemaleFaculty" name="internalFemaleFaculty" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Other Gender</h6>
                                                <div class="row g-2">
                                                    <div class="col-12">
                                                        <label for="internalOtherGender" class="form-label">Count</label>
                                                        <input type="number" class="form-control" id="internalOtherGender" name="internalOtherGender" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6>Total Internal</h6>
                                                <div class="row g-2">
                                                    <div class="col-4">
                                                        <label for="totalInternalMale" class="form-label">Male</label>
                                                        <input type="number" class="form-control" id="totalInternalMale" name="totalInternalMale" readonly>
                                                    </div>
                                                    <div class="col-4">
                                                        <label for="totalInternalFemale" class="form-label">Female</label>
                                                        <input type="number" class="form-control" id="totalInternalFemale" name="totalInternalFemale" readonly>
                                                    </div>
                                                    <div class="col-4">
                                                        <label for="totalInternalOther" class="form-label">Other</label>
                                                        <input type="number" class="form-control" id="totalInternalOther" name="totalInternalOther" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- External Beneficiaries -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">External</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="externalType" class="form-label">Type</label>
                                                <input type="text" class="form-control" id="externalType" name="externalType">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="externalMale" class="form-label">Male</label>
                                                <input type="number" class="form-control" id="externalMale" name="externalMale" min="0">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="externalFemale" class="form-label">Female</label>
                                                <input type="number" class="form-control" id="externalFemale" name="externalFemale" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Total Beneficiaries -->
                            <div class="col-12">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Total Beneficiaries</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="totalMaleBeneficiaries" class="form-label">Total Male</label>
                                                <input type="number" class="form-control" id="totalMaleBeneficiaries" name="totalMaleBeneficiaries" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="totalFemaleBeneficiaries" class="form-label">Total Female</label>
                                                <input type="number" class="form-control" id="totalFemaleBeneficiaries" name="totalFemaleBeneficiaries" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="totalOtherBeneficiaries" class="form-label">Total Other</label>
                                                <input type="number" class="form-control" id="totalOtherBeneficiaries" name="totalOtherBeneficiaries" readonly>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <label for="grandTotalBeneficiaries" class="form-label">Grand Total Beneficiaries</label>
                                                <input type="number" class="form-control form-control-lg bg-light" id="grandTotalBeneficiaries" name="grandTotalBeneficiaries" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 4: Budget & SDGs Section -->
                    <div class="form-page" id="page4" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">Budget and SDGs</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="approvedBudget" class="form-label">Approved Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="approvedBudget" name="approvedBudget" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="sourceOfBudget" class="form-label">Source of Budget/Fund</label>
                                <select class="form-select" id="sourceOfBudget" name="sourceOfBudget" required>
                                    <option value="">Select Source</option>
                                    <option value="GAA">GAA</option>
                                    <option value="Income">Income</option>
                                    <option value="Grants">Grants</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="sdgs" class="form-label">SDGs</label>
                                <select class="form-select" id="sdgs" name="sdgs[]" multiple required>
                                    <option value="1">No Poverty</option>
                                    <option value="2">Zero Hunger</option>
                                    <option value="3">Good Health and Well-being</option>
                                    <option value="4">Quality Education</option>
                                    <option value="5">Gender Equality</option>
                                    <option value="6">Clean Water and Sanitation</option>
                                    <option value="7">Affordable and Clean Energy</option>
                                    <option value="8">Decent Work and Economic Growth</option>
                                    <option value="9">Industry, Innovation and Infrastructure</option>
                                    <option value="10">Reduced Inequalities</option>
                                    <option value="11">Sustainable Cities and Communities</option>
                                    <option value="12">Responsible Consumption and Production</option>
                                    <option value="13">Climate Action</option>
                                    <option value="14">Life Below Water</option>
                                    <option value="15">Life on Land</option>
                                    <option value="16">Peace, Justice and Strong Institutions</option>
                                    <option value="17">Partnerships for the Goals</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" id="prevBtn" onclick="navigateForm(-1)" style="display: none;">
                            <i class="fas fa-arrow-left"></i> Previous
                        </button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary" id="nextBtn" onclick="navigateForm(1)">
                                Next <i class="fas fa-arrow-right"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                Submit <i class="fas fa-check"></i>
                            </button>
                        </div>
                    </div>

                   
                    
                </form>
            </div>
        </div>
    </div>
    <script>
        // Theme and datetime handling functions
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // New function to validate date range
        function validateDateRange() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (startDate && endDate) {
                const startDateObj = new Date(startDate);
                const endDateObj = new Date(endDate);
                
                if (endDateObj < startDateObj) {
                    Swal.fire({
                        title: 'Invalid Date Range',
                        text: 'End date cannot be earlier than start date',
                        icon: 'warning',
                        confirmButtonColor: '#6a1b9a'
                    });
                    document.getElementById('endDate').value = startDate;
                }
            }
        }

        // Add event listener for date validation
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            if (startDateInput && endDateInput) {
                // When start date changes, update end date if empty or if end date is before start date
                startDateInput.addEventListener('change', function() {
                    if (!endDateInput.value || new Date(endDateInput.value) < new Date(startDateInput.value)) {
                        endDateInput.value = startDateInput.value;
                        // Recalculate duration when we auto-populate end date
                        calculateDuration();
                    }
                });
                
                endDateInput.addEventListener('change', validateDateRange);
                
                // Set tooltips for date fields
                startDateInput.setAttribute('title', 'Select the starting date of the event');
                startDateInput.setAttribute('data-bs-toggle', 'tooltip');
                startDateInput.setAttribute('data-bs-placement', 'top');
                
                endDateInput.setAttribute('title', 'Select the ending date of the event');
                endDateInput.setAttribute('data-bs-toggle', 'tooltip');
                endDateInput.setAttribute('data-bs-placement', 'top');
                
                // Add tooltip for total duration field
                const totalDurationInput = document.getElementById('totalDuration');
                if (totalDurationInput) {
                    totalDurationInput.setAttribute('title', 'Total Duration = Number of Days × Daily Hours (minus breaks if selected)');
                    totalDurationInput.setAttribute('data-bs-toggle', 'tooltip');
                    totalDurationInput.setAttribute('data-bs-placement', 'top');
                }
                
                // Initialize Bootstrap tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            }
        });

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        // Personnel management variables
        let personnelList = [];
        let addedPersonnel = {
            projectLeader: [],
            asstProjectLeader: [],
            projectStaff: [],
            otherParticipants: []
        };

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', async function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Initialize global addedPersonnel object
            window.addedPersonnel = {
                projectLeader: [],
                asstProjectLeader: [],
                projectStaff: [],
                otherParticipants: []
            };

            // Initialize Select2 for SDGs
            $('#sdgs').select2({
                placeholder: 'Select SDGs',
                width: '100%'
            });

            // Load personnel data
            await loadPersonnelData();
            
            // Add event listeners for year and quarter selection to check existing data
            const yearSelect = document.getElementById('year');
            const quarterSelect = document.getElementById('quarter');
            
            // Function to check if a form with the selected year and quarter exists
            async function checkExistingForm() {
                const year = yearSelect.value;
                const quarter = quarterSelect.value;
                
                if (year && quarter) {
                    try {
                        const response = await fetch('check_existing_ppas.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ year, quarter })
                        });
                        
                        const result = await response.json();
                        
                        if (result.exists) {
                            Swal.fire({
                                title: 'Warning',
                                text: `A PPAS form for ${quarter} ${year} already exists. Only one quarter per year can have saved data.`,
                                icon: 'warning',
                                confirmButtonColor: '#6a1b9a'
                            });
                        }
                    } catch (error) {
                        console.error('Error checking existing form:', error);
                    }
                }
            }
            
            yearSelect.addEventListener('change', checkExistingForm);
            quarterSelect.addEventListener('change', checkExistingForm);
        });

        // Calculate duration between start and end time, taking into account date range
        function calculateDuration() {
            const startTime = document.getElementById('startTime').value;
            const endTime = document.getElementById('endTime').value;
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const hasLunchBreak = document.getElementById('hasLunchBreak').checked;

            if (startTime && endTime && startDate && endDate) {
                // Calculate days between dates (inclusive)
                const start = new Date(startDate);
                const end = new Date(endDate);
                const daysDiff = Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1; // +1 to include both start and end days
                
                // Calculate daily duration in hours
                let startTimeObj = new Date(`1970-01-01T${startTime}`);
                let endTimeObj = new Date(`1970-01-01T${endTime}`);
                
                // If end time is before start time, assume it's the next day
                if (endTimeObj < startTimeObj) {
                    endTimeObj = new Date(`1970-01-02T${endTime}`);
                }
                
                // Calculate daily duration in hours
                let dailyDuration = (endTimeObj - startTimeObj) / (1000 * 60 * 60);
                
                // Calculate break time per day in hours
                let breakDuration = 0;
                if (hasLunchBreak) breakDuration += 1; // 1 hour lunch break
                
                // Calculate net daily duration
                let netDailyDuration = Math.max(0, dailyDuration - breakDuration);
                
                // Calculate total duration for the entire event
                let totalDuration = netDailyDuration * daysDiff;
                
                // Store the raw total duration in the hidden field
                document.getElementById('rawTotalDuration').value = totalDuration.toFixed(2);
                
                // Format the output string
                let durationString = `${totalDuration.toFixed(2)} (${daysDiff} day${daysDiff > 1 ? 's' : ''} × ${netDailyDuration.toFixed(2)} hrs/day)`;
                
                // Add lunch break detail if applicable
                if (hasLunchBreak) {
                    durationString += ` | Lunch breaks: ${daysDiff} hr${daysDiff > 1 ? 's' : ''}`;
                }
                
                // Update the duration field
                document.getElementById('totalDuration').value = durationString;
            }
        }

        // Initialize event listeners when the document is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get all the relevant input elements
            const startTimeInput = document.getElementById('startTime');
            const endTimeInput = document.getElementById('endTime');
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const lunchBreakCheckbox = document.getElementById('hasLunchBreak');

            // Add event listeners for time inputs
            startTimeInput.addEventListener('input', calculateDuration);
            endTimeInput.addEventListener('input', calculateDuration);
            startDateInput.addEventListener('change', calculateDuration);
            endDateInput.addEventListener('change', calculateDuration);

            // Add event listener for lunch break checkbox
            lunchBreakCheckbox.addEventListener('change', calculateDuration);

            // Initial calculation
            calculateDuration();
        });

        // Function to fetch personnel data from the database
        async function loadPersonnelData() {
            try {
                const response = await fetch('get_personnel.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                personnelList = data;
            } catch (error) {
                console.error('Error fetching personnel data:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load personnel data. Please refresh the page or contact support.',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                personnelList = [];
            }
        }

        // Initialize autocomplete for personnel selection
        $(document).ready(function() {
            $('.personnel-autocomplete').each(function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        // Only search if we have at least 2 characters
                        if (request.term.length < 2) {
                            response([]);
                            return;
                        }
                        
                        $.ajax({
                            url: 'search_personnel.php',
                            dataType: 'json',
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                response(data);
                            },
                            error: function() {
                                response([]);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        const role = $(this).data('role');
                        updatePersonnelInfo(role, ui.item);
                        addPersonnelToList(role, ui.item);
                        $(this).val('').focus();
                        return false;
                    },
                    focus: function() {
                        return false; // Prevent value inserted on focus
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    return $("<li>")
                        .append("<div class='autocomplete-item'>" + 
                               "<strong>" + item.name + "</strong><br>" +
                               "<small>" + item.academic_rank + " • " + item.gender + "</small>" +
                               "</div>")
                        .appendTo(ul);
                };
            });

            // Add CSS for autocomplete items
            $("<style>")
                .prop("type", "text/css")
                .html(`
                    .autocomplete-item {
                        padding: 5px;
                    }
                    .autocomplete-item strong {
                        color: var(--accent-color);
                    }
                    .autocomplete-item small {
                        color: var(--text-secondary);
                    }
                    .ui-autocomplete {
                        max-height: 200px;
                        overflow-y: auto;
                        overflow-x: hidden;
                        border-radius: 8px;
                        border: 1px solid var(--border-color);
                        background: var(--card-bg);
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                    .ui-autocomplete .ui-menu-item {
                        border-bottom: 1px solid var(--border-color);
                    }
                    .ui-autocomplete .ui-menu-item:last-child {
                        border-bottom: none;
                    }
                    .ui-autocomplete .ui-menu-item-wrapper {
                        padding: 8px 12px;
                    }
                    .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
                        background: var(--hover-color);
                        border: none;
                        margin: 0;
                    }
                `)
                .appendTo("head");
        });

        function updatePersonnelInfo(role, person) {
            $(`#${role}Gender`).val(person.gender);
            $(`#${role}AcademicRank`).val(person.academic_rank);
            $(`#${role}MonthlySalary`).val(person.monthly_salary);
            $(`#${role}HourlyRate`).val(person.hourly_rate);
        }

        function addPersonnelToList(role, person) {
            // Validate person object
            if (!person || !person.id || !person.name) {
                console.error('Invalid person data:', person);
                return;
            }

            // Check if person is already added in ANY role
            let isAlreadyAdded = false;
            let existingRole = '';
            
            Object.keys(window.addedPersonnel).forEach(r => {
                if (Array.isArray(window.addedPersonnel[r])) {
                    if (window.addedPersonnel[r].some(p => p.id === person.id)) {
                        isAlreadyAdded = true;
                        existingRole = r;
                    }
                }
            });
            
            if (isAlreadyAdded) {
                // Format the role name for display
                let formattedRole = existingRole;
                if (existingRole === 'projectLeader') formattedRole = 'Project Leader';
                else if (existingRole === 'asstProjectLeader') formattedRole = 'Assistant Project Leader';
                else if (existingRole === 'projectStaff') formattedRole = 'Project Staff';
                else if (existingRole === 'otherParticipants') formattedRole = 'Other Participants';
                
                Swal.fire({
                    title: 'Already Added',
                    text: `This person is already added as ${formattedRole}.`,
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }

            // Add to array
            window.addedPersonnel[role].push({
                id: person.id,
                name: person.name,
                gender: person.gender || 'Not specified',
                academic_rank: person.academic_rank || 'Not specified',
                monthly_salary: person.monthly_salary || '0.00',
                hourly_rate: person.hourly_rate || '0.00'
            });

            // Determine gender category for display
            let genderCategory = person.gender || 'Not specified';
            const genderLower = String(person.gender).toLowerCase();
            if (genderLower === 'gay' || genderLower === 'lesbian' || genderLower === 'other') {
                genderCategory = `${person.gender} (Other)`;
            }

            // Create HTML element with safe values
            const personElement = `
                <div class="personnel-item" data-id="${person.id}">
                    <div class="row align-items-center">
                        <div class="col-md-4"><strong>${person.name}</strong></div>
                        <div class="col-md-2">${genderCategory}</div>
                        <div class="col-md-3">${person.academic_rank || 'Not specified'}</div>
                        <div class="col-md-2">₱${person.monthly_salary || '0.00'}</div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removePersonnel('${role}', ${person.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Add to list
            $(`#${role}List`).append(personElement);
            
            // Update faculty counts
            updateFacultyCount();
        }

        function removePersonnel(role, personId) {
            // Find the index of the person in the array
            const index = window.addedPersonnel[role].findIndex(p => p.id === personId);
            
            if (index !== -1) {
                // Remove from array
                window.addedPersonnel[role].splice(index, 1);
                
                // Remove from DOM
                $(`#${role}List .personnel-item[data-id="${personId}"]`).remove();
                
                // Update faculty counts
                updateFacultyCount();
            }
        }

        // Replace all existing updateFacultyCount functions with this one
        function updateFacultyCount() {
            console.log("Updating faculty count...");
            console.log("Current addedPersonnel:", window.addedPersonnel);
            
            let maleFaculty = 0;
            let femaleFaculty = 0;
            let otherGenderFaculty = 0;
            
            // Count all personnel by gender
            Object.keys(window.addedPersonnel).forEach(role => {
                window.addedPersonnel[role].forEach(person => {
                    const gender = String(person.gender || '').toLowerCase();
                    console.log(`Processing person: ${person.name}, Gender: ${gender}`);
                    
                    if (gender === 'male') {
                        maleFaculty++;
                    } else if (gender === 'female') {
                        femaleFaculty++;
                    } else if (gender === 'gay' || gender === 'lesbian' || gender === 'other') {
                        otherGenderFaculty++;
                        console.log(`Counted as other gender: ${person.name}, Gender: ${gender}`);
                    }
                });
            });
            
            console.log(`Total internal - Male: ${maleFaculty}, Female: ${femaleFaculty}, Other: ${otherGenderFaculty}`);
            
            // Update the input fields
            $('#internalMaleFaculty').val(maleFaculty);
            $('#internalFemaleFaculty').val(femaleFaculty);
            $('#internalOtherGender').val(otherGenderFaculty);
            
            // Update total internal counts
            updateTotalInternal();
        }

        // Make sure we have the correct updateTotalInternal function
        function updateTotalInternal() {
            const maleFaculty = parseInt($('#internalMaleFaculty').val()) || 0;
            const femaleFaculty = parseInt($('#internalFemaleFaculty').val()) || 0;
            const otherGenderFaculty = parseInt($('#internalOtherGender').val()) || 0;
            
            const maleStudents = parseInt($('#internalMaleStudents').val()) || 0;
            const femaleStudents = parseInt($('#internalFemaleStudents').val()) || 0;
            
            $('#totalInternalMale').val(maleFaculty + maleStudents);
            $('#totalInternalFemale').val(femaleFaculty + femaleStudents);
            $('#totalInternalOther').val(otherGenderFaculty);
            
            console.log(`Updated total internal - Male: ${maleFaculty + maleStudents}, Female: ${femaleFaculty + femaleStudents}, Other: ${otherGenderFaculty}`);
            
            // Update the grand total beneficiaries
            updateTotalBeneficiaries();
        }

        // Function to calculate total beneficiaries
        function updateTotalBeneficiaries() {
            // Get internal totals
            const totalInternalMale = parseInt($('#totalInternalMale').val()) || 0;
            const totalInternalFemale = parseInt($('#totalInternalFemale').val()) || 0;
            const totalInternalOther = parseInt($('#totalInternalOther').val()) || 0;
            
            // Get external totals
            const externalMale = parseInt($('#externalMale').val()) || 0;
            const externalFemale = parseInt($('#externalFemale').val()) || 0;
            
            // Calculate totals by gender
            const totalMale = totalInternalMale + externalMale;
            const totalFemale = totalInternalFemale + externalFemale;
            const totalOther = totalInternalOther;
            
            // Update the total fields
            $('#totalMaleBeneficiaries').val(totalMale);
            $('#totalFemaleBeneficiaries').val(totalFemale);
            $('#totalOtherBeneficiaries').val(totalOther);
            
            // Calculate and update grand total
            const grandTotal = totalMale + totalFemale + totalOther;
            $('#grandTotalBeneficiaries').val(grandTotal);
            
            console.log(`Updated total beneficiaries - Male: ${totalMale}, Female: ${totalFemale}, Other: ${totalOther}, Grand Total: ${grandTotal}`);
        }

        // Add event listeners for student count inputs
        $(document).ready(function() {
            $('#internalMaleStudents, #internalFemaleStudents').on('input', updateTotalInternal);
            $('#internalOtherGender').on('input', updateTotalInternal);
            
            // Initialize autocomplete for personnel search if not already initialized
            if (!$('.personnel-autocomplete').data('ui-autocomplete')) {
                $('.personnel-autocomplete').each(function() {
                    $(this).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: 'search_personnel.php',
                                dataType: 'json',
                                data: {
                                    term: request.term
                                },
                                success: function(data) {
                                    console.log('Personnel search results:', data);
                                    response(data);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error fetching personnel:', error);
                                    response([]);
                                }
                            });
                        },
                        minLength: 2,
                        select: function(event, ui) {
                            if (ui.item.id) { // Only process if it's a valid selection
                                const role = $(this).data('role');
                                
                                // Update info fields
                                $(`#${role}Gender`).val(ui.item.gender);
                                $(`#${role}AcademicRank`).val(ui.item.academic_rank);
                                $(`#${role}MonthlySalary`).val(ui.item.monthly_salary);
                                $(`#${role}HourlyRate`).val(ui.item.hourly_rate);
                                
                                // Add to personnel list
                                addPersonnelToList(role, ui.item);
                                
                                // Clear the input field
                                $(this).val('');
                            }
                            return false;
                        }
                    }).autocomplete('instance')._renderItem = function(ul, item) {
                        return $('<li>')
                            .append(`<div class="autocomplete-item">
                                <div class="name">${item.name}</div>
                                <div class="details">
                                    <span class="gender">${item.gender || 'Not specified'}</span> | 
                                    <span class="rank">${item.academic_rank || 'Not specified'}</span>
                                </div>
                            </div>`)
                            .appendTo(ul);
                    };
                });
            }
        });

        // Add event listener for other gender input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('internalOtherGender').addEventListener('input', updateTotalInternal);
        });

        // Form submission handler
        document.getElementById('ppasForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;

            try {
                // Check if a form with the same year and quarter already exists
                const checkResponse = await fetch('check_existing_ppas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ year, quarter })
                });
                
                const checkResult = await checkResponse.json();
                
                if (checkResult.exists) {
                    Swal.fire({
                        title: 'Error',
                        text: `A PPAS form for ${quarter} ${year} already exists. Only one quarter per year can have saved data.`,
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                    return; // Stop form submission
                }

                // Continue with form submission if no existing form found
                const formData = {
                    year: year,
                    quarter: quarter,
                    title: document.getElementById('title').value,
                    location: document.getElementById('location').value,
                    startDate: document.getElementById('startDate').value,
                    endDate: document.getElementById('endDate').value,
                    startTime: document.getElementById('startTime').value,
                    endTime: document.getElementById('endTime').value,
                    hasLunchBreak: document.getElementById('hasLunchBreak').checked,
                    hasAMBreak: false, // Default to false for now
                    hasPMBreak: false, // Default to false for now
                    totalDuration: document.getElementById('totalDuration').value,
                    rawTotalDuration: document.getElementById('rawTotalDuration').value,
                    personnel: window.addedPersonnel, // Use the global window.addedPersonnel object
                    beneficiaries: {
                        internalMaleStudents: document.getElementById('internalMaleStudents').value,
                        internalFemaleStudents: document.getElementById('internalFemaleStudents').value,
                        internalMaleFaculty: document.getElementById('internalMaleFaculty').value,
                        internalFemaleFaculty: document.getElementById('internalFemaleFaculty').value,
                        externalType: document.getElementById('externalType').value,
                        externalMale: document.getElementById('externalMale').value,
                        externalFemale: document.getElementById('externalFemale').value
                    },
                    approvedBudget: document.getElementById('approvedBudget').value,
                    sourceOfBudget: document.getElementById('sourceOfBudget').value,
                    sdgs: $('#sdgs').val()
                };
                
                // Debug log to verify personnel data is being sent correctly
                console.log("Submitting form with personnel data:", window.addedPersonnel);

                const response = await fetch('save_ppas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'PPAS form has been saved successfully.',
                        icon: 'success',
                        confirmButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    throw new Error(result.message || 'Failed to save PPAS form');
                }
            } catch (error) {
                Swal.fire({
                    title: 'Error!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        });

        function handleLogout(event) {
            event.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out of the system",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel',
                backdrop: `
                    rgba(0,0,0,0.7)
                `,
                allowOutsideClick: true,
                customClass: {
                    container: 'swal-blur-container',
                    popup: 'logout-swal'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');
                    
                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 10);
                }
            });
        }

        // Form Navigation Variables
        let currentPage = 1;
        const totalPages = 4;

        function updateProgressBar() {
            const progress = (currentPage / totalPages) * 100;
            document.querySelector('.progress-bar').style.width = `${progress}%`;
            document.querySelector('.progress-bar').setAttribute('aria-valuenow', progress);
        }

        function updateStepIndicators() {
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                if (index + 1 === currentPage) {
                    indicator.classList.add('active');
                } else {
                    indicator.classList.remove('active');
                }
            });
        }

        function showPage(pageNumber) {
            document.querySelectorAll('.form-page').forEach(page => {
                page.style.display = 'none';
            });
            document.getElementById(`page${pageNumber}`).style.display = 'block';
        }

        function validateCurrentPage() {
            const currentPageElement = document.getElementById(`page${currentPage}`);
            const requiredFields = currentPageElement.querySelectorAll('[required]:not([type="hidden"])');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value && field.offsetParent !== null) {  // Check if field is visible
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Additional validation for page 1 - date range
            if (currentPage === 1) {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                
                if (startDate && endDate) {
                    const startDateObj = new Date(startDate);
                    const endDateObj = new Date(endDate);
                    
                    if (endDateObj < startDateObj) {
                        isValid = false;
                        Swal.fire({
                            title: 'Invalid Date Range',
                            text: 'End date cannot be earlier than start date',
                            icon: 'warning',
                            confirmButtonColor: '#6a1b9a'
                        });
                        document.getElementById('endDate').classList.add('is-invalid');
                    }
                }
            }

            return isValid;
        }

        function navigateForm(direction) {
            if (direction === 1 && !validateCurrentPage()) {
                Swal.fire({
                    title: 'Required Fields',
                    text: 'Please fill in all required fields before proceeding.',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }

            const newPage = currentPage + direction;
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                showPage(currentPage);
                updateProgressBar();
                updateStepIndicators();
                updateNavigationButtons();
            }
        }

        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            prevBtn.style.display = currentPage === 1 ? 'none' : 'inline-flex';
            nextBtn.style.display = currentPage === totalPages ? 'none' : 'inline-flex';
            submitBtn.style.display = currentPage === totalPages ? 'inline-flex' : 'none';
        }

        // Add click event listeners to step indicators
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                if (validateCurrentPage()) {
                    currentPage = index + 1;
                    showPage(currentPage);
                    updateProgressBar();
                    updateStepIndicators();
                    updateNavigationButtons();
                }
            });
        });

        // Initialize form navigation
        document.addEventListener('DOMContentLoaded', function() {
            showPage(1);
            updateProgressBar();
            updateStepIndicators();
            updateNavigationButtons();
        });

        // Initialize autocomplete for title field
        $(document).ready(function() {
            // Initialize title autocomplete
            $('#title').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: 'search_titles.php',
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function(data) {
                            response(data.map(item => ({
                                label: item.title,
                                value: item.title
                            })));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    $(this).val(ui.item.value);
                    return false;
                }
            }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div class='autocomplete-item'>" + 
                           "<strong>" + item.label + "</strong>" +
                           "</div>")
                    .appendTo(ul);
            };

            // Add specific styles for title autocomplete
            $("<style>")
                .prop("type", "text/css")
                .html(`
                    #title-autocomplete-list {
                        max-height: 300px;
                        overflow-y: auto;
                    }
                    .ui-autocomplete {
                        z-index: 9999 !important;
                    }
                    .ui-menu-item .autocomplete-item {
                        white-space: normal;
                        word-wrap: break-word;
                    }
                `)
                .appendTo("head");
        });

        // Add after the existing document.ready function
        $(document).ready(function() {
            // Initialize program autocomplete
            $('#program').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: 'search_programs.php',
                        dataType: 'json',
                        data: { term: request.term },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2
            });

            // Initialize project autocomplete
            $('#project').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: 'search_projects.php',
                        dataType: 'json',
                        data: { term: request.term },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 2
            });
        });

        // Function to add new program
        function addNewProgram() {
            Swal.fire({
                title: 'Add New Program',
                input: 'text',
                inputLabel: 'Program Name',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'add_program.php',
                        method: 'POST',
                        data: { program_name: result.value },
                        success: function(response) {
                            if (response.success) {
                                $('#program').val(result.value);
                                Swal.fire('Success', 'Program added successfully', 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Function to add new project
        function addNewProject() {
            Swal.fire({
                title: 'Add New Project',
                input: 'text',
                inputLabel: 'Project Name',
                showCancelButton: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to write something!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'add_project.php',
                        method: 'POST',
                        data: { project_name: result.value },
                        success: function(response) {
                            if (response.success) {
                                $('#project').val(result.value);
                                Swal.fire('Success', 'Project added successfully', 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });
        }

        // Add this code to your existing JavaScript section

        // Make sure jQuery UI is properly loaded
        $(document).ready(function() {
            // Initialize personnel autocomplete with simpler implementation
            $('.personnel-autocomplete').each(function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        // Show loading indicator
                        $('.ui-autocomplete-loading').addClass('spinner-border spinner-border-sm');
                        
                        $.ajax({
                            url: 'search_personnel.php',
                            dataType: 'json',
                            data: { term: request.term },
                            success: function(data) {
                                // Hide loading indicator
                                $('.ui-autocomplete-loading').removeClass('spinner-border spinner-border-sm');
                                
                                // Process and display results
                                if (Array.isArray(data) && data.length > 0) {
                                    response($.map(data, function(item) {
                                        return {
                                            id: item.id,
                                            label: item.name + ' - ' + item.academic_rank,
                                            value: item.name,
                                            name: item.name,
                                            gender: item.gender,
                                            academic_rank: item.academic_rank,
                                            monthly_salary: item.monthly_salary,
                                            hourly_rate: item.hourly_rate
                                        };
                                    }));
                                } else {
                                    response([{ label: 'No matching personnel found', value: '' }]);
                                }
                            },
                            error: function() {
                                // Hide loading indicator
                                $('.ui-autocomplete-loading').removeClass('spinner-border spinner-border-sm');
                                response([{ label: 'Error fetching data', value: '' }]);
                            }
                        });
                    },
                    minLength: 1, // Show results after typing just 1 character
                    select: function(event, ui) {
                        if (ui.item.id) { // Only process if it's a valid selection
                            const role = $(this).data('role');
                            
                            // Update info fields
                            $(`#${role}Gender`).val(ui.item.gender);
                            $(`#${role}AcademicRank`).val(ui.item.academic_rank);
                            $(`#${role}MonthlySalary`).val(ui.item.monthly_salary);
                            $(`#${role}HourlyRate`).val(ui.item.hourly_rate);
                            
                            // Determine gender category for display
                            let genderCategory = ui.item.gender || 'Not specified';
                            const genderLower = String(ui.item.gender).toLowerCase();
                            if (genderLower === 'gay' || genderLower === 'lesbian' || genderLower === 'other') {
                                genderCategory = `${ui.item.gender} (Other)`;
                            }
                            
                            // Add to personnel list
                            const person = {
                                id: ui.item.id,
                                name: ui.item.name,
                                gender: ui.item.gender,
                                academic_rank: ui.item.academic_rank,
                                monthly_salary: ui.item.monthly_salary,
                                hourly_rate: ui.item.hourly_rate
                            };
                            
                            // Check if already added
                            if (addedPersonnel[role].some(p => p.id === person.id)) {
                                Swal.fire({
                                    title: 'Already Added',
                                    text: 'This person is already added to this role.',
                                    icon: 'warning',
                                    confirmButtonColor: '#6a1b9a'
                                });
                            } else {
                                // Add to array
                                addedPersonnel[role].push(person);
                                
                                // Create HTML element
                                const personElement = `
                                    <div class="personnel-item" data-id="${person.id}">
                                        <div class="row">
                                            <div class="col-md-4">${person.name}</div>
                                            <div class="col-md-2">${genderCategory}</div>
                                            <div class="col-md-3">${person.academic_rank}</div>
                                            <div class="col-md-2">${person.monthly_salary}</div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removePersonnel('${role}', ${person.id})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                
                                // Add to list
                                $(`#${role}List`).append(personElement);
                                
                                // Update faculty counts
                                updateFacultyCount();
                            }
                            
                            // Clear the input field
                            $(this).val('');
                        }
                        return false;
                    }
                }).autocomplete("instance")._renderItem = function(ul, item) {
                    // Don't allow selection of the "no results" item
                    if (!item.id) {
                        return $("<li class='ui-state-disabled'>")
                            .append("<div>" + item.label + "</div>")
                            .appendTo(ul);
                    }
                    
                    return $("<li>")
                        .append("<div class='autocomplete-item'>" + 
                               "<strong>" + item.name + "</strong><br>" +
                               "<small>" + item.academic_rank + " • " + item.gender + "</small>" +
                               "</div>")
                        .appendTo(ul);
                };
            });
            
            // Add CSS for autocomplete items
            $("<style>")
                .prop("type", "text/css")
                .html(`
                    .ui-autocomplete {
                        max-height: 200px;
                        overflow-y: auto;
                        overflow-x: hidden;
                        z-index: 9999 !important;
                        border-radius: 0.375rem;
                        border: 1px solid var(--border-color);
                        background: var(--card-bg);
                        padding: 0.5rem 0;
                        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                    }
                    .ui-autocomplete .ui-menu-item {
                        padding: 0;
                        border-bottom: 1px solid var(--border-color);
                    }
                    .ui-autocomplete .ui-menu-item:last-child {
                        border-bottom: none;
                    }
                    .ui-autocomplete .ui-menu-item-wrapper {
                        padding: 0.5rem 1rem;
                    }
                    .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
                        background-color: var(--accent-color);
                        border: none;
                        margin: 0;
                        color: white;
                    }
                    .ui-autocomplete .ui-state-disabled {
                        opacity: 0.6;
                        padding: 0.5rem 1rem;
                        font-style: italic;
                    }
                    .autocomplete-item {
                        padding: 0.25rem 0;
                    }
                    .autocomplete-item strong {
                        color: var(--accent-color);
                    }
                    .autocomplete-item small {
                        color: var(--text-secondary);
                    }
                    .ui-helper-hidden-accessible {
                        display: none;
                    }
                `)
                .appendTo("head");
        });

        // Safe version of updateFacultyCount function
        function updateFacultyCount() {
            let maleFaculty = 0;
            let femaleFaculty = 0;
            
            try {
                // Count from all personnel categories
                Object.keys(addedPersonnel).forEach(role => {
                    if (Array.isArray(addedPersonnel[role])) {
                        addedPersonnel[role].forEach(person => {
                            if (person && person.gender) {
                                const gender = String(person.gender).toLowerCase();
                                if (gender.includes('male') && !gender.includes('female')) {
                                    maleFaculty++;
                                } else if (gender.includes('female')) {
                                    femaleFaculty++;
                                }
                            }
                        });
                    }
                });
            } catch (error) {
                console.error('Error in updateFacultyCount:', error);
            }
            
            // Update faculty counts
            document.getElementById('internalMaleFaculty').value = maleFaculty;
            document.getElementById('internalFemaleFaculty').value = femaleFaculty;
            
            // Update total internal counts
            updateTotalInternal();
        }

        // Safe version of removePersonnel function
        function removePersonnel(role, personId) {
            try {
                // Remove from array
                addedPersonnel[role] = addedPersonnel[role].filter(p => p.id !== personId);
                
                // Remove from DOM
                $(`#${role}List .personnel-item[data-id="${personId}"]`).remove();
                
                // Update faculty counts
                updateFacultyCount();
            } catch (error) {
                console.error('Error in removePersonnel:', error);
            }
        }

        // Initialize addedPersonnel object if not already defined
        if (typeof addedPersonnel === 'undefined') {
            window.addedPersonnel = {
                projectLeader: [],
                asstProjectLeader: [],
                projectStaff: [],
                otherParticipants: []
            };
            console.log("Initialized addedPersonnel object");
        }

        // Initialize the addedPersonnel object if it doesn't exist
        if (typeof window.addedPersonnel === 'undefined') {
            window.addedPersonnel = {
                projectLeader: [],
                asstProjectLeader: [],
                projectStaff: [],
                otherParticipants: []
            };
            console.log("Initialized addedPersonnel object");
        }

        // Function to check if a person is already added in any role
        function isPersonnelAlreadyAdded(personId) {
            let isAdded = false;
            let existingRole = '';
            
            Object.keys(window.addedPersonnel).forEach(role => {
                if (Array.isArray(window.addedPersonnel[role])) {
                    const foundPerson = window.addedPersonnel[role].find(p => p.id === personId);
                    if (foundPerson) {
                        isAdded = true;
                        existingRole = role;
                    }
                }
            });
            
            return { isAdded, existingRole };
        }

        // Format role name for display
        function formatRoleName(role) {
            switch(role) {
                case 'projectLeader': return 'Project Leader';
                case 'asstProjectLeader': return 'Assistant Project Leader';
                case 'projectStaff': return 'Project Staff';
                case 'otherParticipants': return 'Other Participants';
                default: return role;
            }
        }

        // Function to add personnel to the list
        function addPersonnelToList(role, person) {
            // Validate person object
            if (!person || !person.id || !person.name) {
                console.error('Invalid person data:', person);
                return;
            }

            // Check if person is already added in ANY role
            let isAlreadyAdded = false;
            let existingRole = '';
            
            Object.keys(window.addedPersonnel).forEach(r => {
                if (Array.isArray(window.addedPersonnel[r])) {
                    if (window.addedPersonnel[r].some(p => p.id === person.id)) {
                        isAlreadyAdded = true;
                        existingRole = r;
                    }
                }
            });
            
            if (isAlreadyAdded) {
                // Format the role name for display
                let formattedRole = existingRole;
                if (existingRole === 'projectLeader') formattedRole = 'Project Leader';
                else if (existingRole === 'asstProjectLeader') formattedRole = 'Assistant Project Leader';
                else if (existingRole === 'projectStaff') formattedRole = 'Project Staff';
                else if (existingRole === 'otherParticipants') formattedRole = 'Other Participants';
                
                Swal.fire({
                    title: 'Already Added',
                    text: `This person is already added as ${formattedRole}.`,
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }

            // Add to array
            window.addedPersonnel[role].push({
                id: person.id,
                name: person.name,
                gender: person.gender || 'Not specified',
                academic_rank: person.academic_rank || 'Not specified',
                monthly_salary: person.monthly_salary || '0.00',
                hourly_rate: person.hourly_rate || '0.00'
            });

            // Determine gender category for display
            let genderCategory = person.gender || 'Not specified';
            const genderLower = String(person.gender).toLowerCase();
            if (genderLower === 'gay' || genderLower === 'lesbian' || genderLower === 'other') {
                genderCategory = `${person.gender} (Other)`;
            }

            // Create HTML element with safe values
            const personElement = `
                <div class="personnel-item" data-id="${person.id}">
                    <div class="row align-items-center">
                        <div class="col-md-4"><strong>${person.name}</strong></div>
                        <div class="col-md-2">${genderCategory}</div>
                        <div class="col-md-3">${person.academic_rank || 'Not specified'}</div>
                        <div class="col-md-2">₱${person.monthly_salary || '0.00'}</div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removePersonnel('${role}', ${person.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Add to list
            $(`#${role}List`).append(personElement);
            
            // Update faculty counts
            updateFacultyCount();
        }

        // Function to remove personnel from the list
        function removePersonnel(role, personId) {
            // Find the index of the person in the array
            const index = window.addedPersonnel[role].findIndex(p => p.id === personId);
            
            if (index !== -1) {
                // Remove from array
                window.addedPersonnel[role].splice(index, 1);
                
                // Remove from DOM
                $(`#${role}List .personnel-item[data-id="${personId}"]`).remove();
                
                // Update faculty counts
                updateFacultyCount();
            }
        }

        // Initialize autocomplete for personnel search
        $(document).ready(function() {
            $('.personnel-autocomplete').each(function() {
                $(this).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: 'search_personnel.php',
                            dataType: 'json',
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                console.log('Personnel search results:', data);
                                response(data);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error fetching personnel:', error);
                                response([]);
                            }
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        if (ui.item.id) { // Only process if it's a valid selection
                            const role = $(this).data('role');
                            
                            // Update info fields
                            $(`#${role}Gender`).val(ui.item.gender);
                            $(`#${role}AcademicRank`).val(ui.item.academic_rank);
                            $(`#${role}MonthlySalary`).val(ui.item.monthly_salary);
                            $(`#${role}HourlyRate`).val(ui.item.hourly_rate);
                            
                            // Add to personnel list
                            addPersonnelToList(role, ui.item);
                            
                            // Clear the input field
                            $(this).val('');
                        }
                        return false;
                    },
                    close: function(event, ui) {
                        // If no selection was made, clear the input field
                        if (!$(this).data('ui-autocomplete').selectedItem) {
                            $(this).val('');
                        }
                    }
                }).autocomplete('instance')._renderItem = function(ul, item) {
                    return $('<li>')
                        .append(`<div class="autocomplete-item">
                            <div class="name">${item.name}</div>
                            <div class="details">
                                <span class="gender">${item.gender || 'Not specified'}</span> | 
                                <span class="rank">${item.academic_rank || 'Not specified'}</span>
                            </div>
                        </div>`)
                        .appendTo(ul);
                };
            });
            
            // Add event handler to clear fields when clicking on a personnel search field
            $('.personnel-autocomplete').on('focus', function() {
                const role = $(this).data('role');
                $(`#${role}Gender`).val('');
                $(`#${role}AcademicRank`).val('');
                $(`#${role}MonthlySalary`).val('');
                $(`#${role}HourlyRate`).val('');
            });
        });

        // Function to update faculty count
        function updateFacultyCount() {
            console.log("Updating faculty count...");
            console.log("Current addedPersonnel:", addedPersonnel);
            
            let maleFaculty = 0;
            let femaleFaculty = 0;
            let otherGenderFaculty = 0;
            
            // Count all personnel by gender
            Object.keys(addedPersonnel).forEach(role => {
                addedPersonnel[role].forEach(person => {
                    const gender = String(person.gender || '').toLowerCase();
                    console.log(`Processing person: ${person.name}, Gender: ${gender}`);
                    
                    if (gender === 'male') {
                        maleFaculty++;
                    } else if (gender === 'female') {
                        femaleFaculty++;
                    } else if (gender === 'gay' || gender === 'lesbian' || gender === 'other') {
                        otherGenderFaculty++;
                    }
                });
            });
            
            console.log(`Total internal - Male: ${maleFaculty}, Female: ${femaleFaculty}, Other: ${otherGenderFaculty}`);
            
            // Update the input fields
            $('#internalMaleFaculty').val(maleFaculty);
            $('#internalFemaleFaculty').val(femaleFaculty);
            $('#internalOtherGender').val(otherGenderFaculty);
            
            // Update total internal counts
            updateTotalInternal();
        }

        // Function to update total internal counts
        function updateTotalInternal() {
            const maleFaculty = parseInt($('#internalMaleFaculty').val()) || 0;
            const femaleFaculty = parseInt($('#internalFemaleFaculty').val()) || 0;
            const otherGenderFaculty = parseInt($('#internalOtherGender').val()) || 0;
            
            const maleStudents = parseInt($('#internalMaleStudents').val()) || 0;
            const femaleStudents = parseInt($('#internalFemaleStudents').val()) || 0;
            
            $('#totalInternalMale').val(maleFaculty + maleStudents);
            $('#totalInternalFemale').val(femaleFaculty + femaleStudents);
            $('#totalInternalOther').val(otherGenderFaculty);
        }

        // Add event listeners for student count inputs
        $(document).ready(function() {
            $('#internalMaleStudents, #internalFemaleStudents').on('input', updateTotalInternal);
            $('#internalOtherGender').on('input', updateTotalInternal);
            
            // Initialize autocomplete for personnel search if not already initialized
            if (!$('.personnel-autocomplete').data('ui-autocomplete')) {
                $('.personnel-autocomplete').each(function() {
                    $(this).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: 'search_personnel.php',
                                dataType: 'json',
                                data: {
                                    term: request.term
                                },
                                success: function(data) {
                                    console.log('Personnel search results:', data);
                                    response(data);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error fetching personnel:', error);
                                    response([]);
                                }
                            });
                        },
                        minLength: 2,
                        select: function(event, ui) {
                            if (ui.item.id) { // Only process if it's a valid selection
                                const role = $(this).data('role');
                                
                                // Update info fields
                                $(`#${role}Gender`).val(ui.item.gender);
                                $(`#${role}AcademicRank`).val(ui.item.academic_rank);
                                $(`#${role}MonthlySalary`).val(ui.item.monthly_salary);
                                $(`#${role}HourlyRate`).val(ui.item.hourly_rate);
                                
                                // Add to personnel list
                                addPersonnelToList(role, ui.item);
                                
                                // Clear the input field
                                $(this).val('');
                            }
                            return false;
                        }
                    }).autocomplete('instance')._renderItem = function(ul, item) {
                        return $('<li>')
                            .append(`<div class="autocomplete-item">
                                <div class="name">${item.name}</div>
                                <div class="details">
                                    <span class="gender">${item.gender || 'Not specified'}</span> | 
                                    <span class="rank">${item.academic_rank || 'Not specified'}</span>
                                </div>
                            </div>`)
                            .appendTo(ul);
                    };
                });
            }
        });

        // Fix for the "Identifier 'addedPersonnel' has already been declared" error
        // Instead of redeclaring, check if it exists and initialize if needed
        if (typeof window.addedPersonnel === 'undefined') {
            window.addedPersonnel = {
                projectLeader: [],
                asstProjectLeader: [],
                projectStaff: [],
                otherParticipants: []
            };
            console.log("Initialized addedPersonnel object");
        } else {
            console.log("Using existing addedPersonnel object");
        }

        // Function to check if a person is already added in any role
        function isPersonnelAlreadyAdded(personId) {
            let isAdded = false;
            let existingRole = '';
            
            Object.keys(window.addedPersonnel).forEach(role => {
                if (Array.isArray(window.addedPersonnel[role])) {
                    const foundPerson = window.addedPersonnel[role].find(p => p.id === personId);
                    if (foundPerson) {
                        isAdded = true;
                        existingRole = role;
                    }
                }
            });
            
            return { isAdded, existingRole };
        }

        // Format role name for display
        function formatRoleName(role) {
            switch(role) {
                case 'projectLeader': return 'Project Leader';
                case 'asstProjectLeader': return 'Assistant Project Leader';
                case 'projectStaff': return 'Project Staff';
                case 'otherParticipants': return 'Other Participants';
                default: return role;
            }
        }

        // Override the existing addPersonnelToList function with our improved version
        window.addPersonnelToList = function(role, person) {
            // Validate person object
            if (!person || !person.id || !person.name) {
                console.error('Invalid person data:', person);
                return;
            }
            
            // Check if person is already added in any role
            const { isAdded, existingRole } = isPersonnelAlreadyAdded(person.id);
            
            if (isAdded) {
                Swal.fire({
                    title: 'Already Added',
                    text: `This person is already added as ${formatRoleName(existingRole)}.`,
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }

            // Add to array
            window.addedPersonnel[role].push({
                id: person.id,
                name: person.name,
                gender: person.gender || 'Not specified',
                academic_rank: person.academic_rank || 'Not specified',
                monthly_salary: person.monthly_salary || '0.00',
                hourly_rate: person.hourly_rate || '0.00'
            });

            // Determine gender category for display
            let genderCategory = person.gender || 'Not specified';
            const genderLower = String(person.gender).toLowerCase();
            if (genderLower === 'gay' || genderLower === 'lesbian' || genderLower === 'other') {
                genderCategory = `${person.gender} (Other)`;
            }

            // Create HTML element with safe values
            const personElement = `
                <div class="personnel-item" data-id="${person.id}">
                    <div class="row align-items-center">
                        <div class="col-md-4"><strong>${person.name}</strong></div>
                        <div class="col-md-2">${genderCategory}</div>
                        <div class="col-md-3">${person.academic_rank || 'Not specified'}</div>
                        <div class="col-md-2">₱${person.monthly_salary || '0.00'}</div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="removePersonnel('${role}', ${person.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Add to list
            $(`#${role}List`).append(personElement);
            
            // Reset the personnel information fields
            $(`#${role}Gender`).val('');
            $(`#${role}AcademicRank`).val('');
            $(`#${role}MonthlySalary`).val('');
            $(`#${role}HourlyRate`).val('');
            
            // Update faculty counts
            updateFacultyCount();
        };

        // Override the existing removePersonnel function
        window.removePersonnel = function(role, personId) {
            // Find the index of the person in the array
            const index = window.addedPersonnel[role].findIndex(p => p.id === personId);
            
            if (index !== -1) {
                // Remove from array
                window.addedPersonnel[role].splice(index, 1);
                
                // Remove from DOM
                $(`#${role}List .personnel-item[data-id="${personId}"]`).remove();
                
                // Update faculty counts
                updateFacultyCount();
            }
        };

        // Override the existing updateFacultyCount function
        window.updateFacultyCount = function() {
            console.log("Updating faculty count...");
            
            let maleFaculty = 0;
            let femaleFaculty = 0;
            let otherGenderFaculty = 0;
            
            // Count all personnel by gender
            Object.keys(window.addedPersonnel).forEach(role => {
                if (Array.isArray(window.addedPersonnel[role])) {
                    window.addedPersonnel[role].forEach(person => {
                        const gender = String(person.gender || '').toLowerCase();
                        console.log(`Processing person: ${person.name}, Gender: ${gender}`);
                        
                        if (gender === 'male') {
                            maleFaculty++;
                        } else if (gender === 'female') {
                            femaleFaculty++;
                        } else if (gender === 'gay' || gender === 'lesbian' || gender === 'other') {
                            otherGenderFaculty++;
                            console.log(`Counted as other gender: ${person.name}, Gender: ${gender}`);
                        }
                    });
                }
            });
            
            console.log(`Total internal - Male: ${maleFaculty}, Female: ${femaleFaculty}, Other: ${otherGenderFaculty}`);
            
            // Update the input fields
            $('#internalMaleFaculty').val(maleFaculty);
            $('#internalFemaleFaculty').val(femaleFaculty);
            $('#internalOtherGender').val(otherGenderFaculty);
            
            // Update total internal counts
            updateTotalInternal();
        };

        // Override the existing updateTotalInternal function
        window.updateTotalInternal = function() {
            const maleFaculty = parseInt($('#internalMaleFaculty').val()) || 0;
            const femaleFaculty = parseInt($('#internalFemaleFaculty').val()) || 0;
            const otherGenderFaculty = parseInt($('#internalOtherGender').val()) || 0;
            
            const maleStudents = parseInt($('#internalMaleStudents').val()) || 0;
            const femaleStudents = parseInt($('#internalFemaleStudents').val()) || 0;
            
            $('#totalInternalMale').val(maleFaculty + maleStudents);
            $('#totalInternalFemale').val(femaleFaculty + femaleStudents);
            $('#totalInternalOther').val(otherGenderFaculty);
            
            console.log(`Updated total internal - Male: ${maleFaculty + maleStudents}, Female: ${femaleFaculty + femaleStudents}, Other: ${otherGenderFaculty}`);
            
            // Update the grand total beneficiaries
            updateTotalBeneficiaries();
        };

        // Add event listeners for student count inputs
        $(document).ready(function() {
            $('#internalMaleStudents, #internalFemaleStudents').on('input', updateTotalInternal);
            $('#internalOtherGender').on('input', updateTotalInternal);
            
            // Initialize autocomplete for personnel search if not already initialized
            if (!$('.personnel-autocomplete').data('ui-autocomplete')) {
                $('.personnel-autocomplete').each(function() {
                    $(this).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: 'search_personnel.php',
                                dataType: 'json',
                                data: {
                                    term: request.term
                                },
                                success: function(data) {
                                    console.log('Personnel search results:', data);
                                    response(data);
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error fetching personnel:', error);
                                    response([]);
                                }
                            });
                        },
                        minLength: 2,
                        select: function(event, ui) {
                            if (ui.item.id) { // Only process if it's a valid selection
                                const role = $(this).data('role');
                                
                                // Update info fields
                                $(`#${role}Gender`).val(ui.item.gender);
                                $(`#${role}AcademicRank`).val(ui.item.academic_rank);
                                $(`#${role}MonthlySalary`).val(ui.item.monthly_salary);
                                $(`#${role}HourlyRate`).val(ui.item.hourly_rate);
                                
                                // Add to personnel list
                                window.addPersonnelToList(role, ui.item);
                                
                                // Clear the input field
                                $(this).val('');
                            }
                            return false;
                        }
                    }).autocomplete('instance')._renderItem = function(ul, item) {
                        return $('<li>')
                            .append(`<div class="autocomplete-item">
                                <div class="name">${item.name}</div>
                                <div class="details">
                                    <span class="gender">${item.gender || 'Not specified'}</span> | 
                                    <span class="rank">${item.academic_rank || 'Not specified'}</span>
                                </div>
                            </div>`)
                            .appendTo(ul);
                    };
                });
            }
        });

        // Add event listeners for beneficiary count inputs
        $(document).ready(function() {
            // Add event listeners to all beneficiary input fields
            $('#internalMaleStudents, #internalFemaleStudents, #internalOtherGender, #externalMale, #externalFemale').on('input', function() {
                updateTotalInternal();
            });
            
            // Initialize totals
            updateTotalInternal();
        });

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        
        // Add tooltip for total duration field
        const totalDurationInput = document.getElementById('totalDuration');
        if (totalDurationInput) {
            totalDurationInput.setAttribute('title', 'Total Duration = Number of Days × Daily Hours (minus breaks if selected)');
            totalDurationInput.setAttribute('data-bs-toggle', 'tooltip');
            totalDurationInput.setAttribute('data-bs-placement', 'top');
            new bootstrap.Tooltip(totalDurationInput);
        }
    </script>
</body>
</html>