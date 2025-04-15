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

// Check if user is Central or a specific campus user
$isCentral = isset($_SESSION['username']) && $_SESSION['username'] === 'Central';

// For non-Central users, their username is their campus
$userCampus = $isCentral ? '' : $_SESSION['username'];

// Store campus in session for consistency
$_SESSION['campus'] = $userCampus;

// Add this function before the HTML section
function getSignatories($campus) {
    try {
        $conn = getConnection();
        $sql = "SELECT * FROM signatories WHERE campus = :campus";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':campus', $campus);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Error fetching signatories: ' . $e->getMessage());
        return null;
    }
}

// Get signatories for the current campus
$signatories = getSignatories($_SESSION['username']);

// Add this function at the top of the file, after any existing includes
function getConnection() {
    try {
        $conn = new PDO(
            "mysql:host=localhost;dbname=gad_db;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}
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
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
            --scrollbar-thumb: #cccccc;
            --scrollbar-thumb-hover: #aaaaaa;
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
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
            --table-header-bg: #343a40;
            --table-subheader-bg: #2b3035;
            --proposal-bg: #2d2d2d;
            --proposal-text: #ffffff;
            --proposal-border: #404040;
            --proposal-header-bg: #343a40;
        }

        /* Select2 Theme Customization */
        .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
            color: var(--input-text) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            color: var(--input-text) !important;
            line-height: 1.5 !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }

        .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--input-bg) !important;
            color: var(--input-text) !important;
            border-color: var(--border-color) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option {
            color: var(--input-text) !important;
            background-color: var(--input-bg) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected=true] {
            background-color: var(--accent-hover) !important;
        }

        .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            background-color: var(--hover-color) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--input-placeholder) !important;
        }

        /* Dark mode specific Select2 styles */
        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
            color: var(--dark-text) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-dropdown {
            background-color: var(--dark-input) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-search__field {
            background-color: var(--dark-input) !important;
            color: var(--dark-text) !important;
            border-color: var(--dark-border) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-results__option {
            color: var(--dark-text) !important;
            background-color: var(--dark-input) !important;
        }

        [data-bs-theme="dark"] .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: var(--input-placeholder) !important;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;     /* Firefox */
            overflow: hidden;
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
            margin-top: 10px; /* Add this to align with top of the sidebar */
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
                padding: 1.5rem;
                max-height: calc(100vh - 60px);
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
            min-height: auto; /* Remove fixed min-height */
        }

        .card-body {
            padding: 1rem; /* Reduce padding */
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

/* Base styles for both screen and print */
.proposal-container {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    background: white !important;
    /* Remove border effects */
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
    box-shadow: none !important;
}

/* Table styles for both screen and print */
.proposal-container table {
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-collapse: collapse !important;
    border: none !important;
}

.proposal-container td,
.proposal-container th {
    border: 1px solid #000 !important; /* Reduced from 3px to 1px */
    padding: 8px !important;
    margin: 0 !important;
}

/* Remove spacing between sections */
.header-section,
.main-section {
    margin: 0 !important;
    padding: 0 !important;
}

.header-section table,
.main-section table {
    margin: 0 !important;
    border: none !important;
}

/* Remove any margins between rows */
.proposal-container tr {
    margin: 0 !important;
    padding: 0 !important;
}

/* Print-specific styles */
@media print {
    @page {
        size: 8.5in 13in;
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
    }
    
    /* First page footer */
    @page:first {
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        /* Different first page */
        @bottom {
            content: "";
            display: block;
            margin-bottom: 0.82cm;
            border-top: 3px solid black !important; /* Keep this border */
            width: 95%;
            margin-top: 0.5cm;
            position: fixed;
            bottom: 0.82cm;
            height: 3px;
            margin-left: 1.78cm;
            margin-right: 2.03cm;
            left: 1.78cm;
            right: 2.03cm;
        }
        @bottom-left {
            content: ""; /* Removed "Tracking Number" text */
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 0.5cm;
            position: fixed;
            bottom: 0.82cm;
            margin-left: 1.78cm;
            left: 1.78cm;
        }
        @bottom-right {
            content: ""; /* Removed page number text */
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            font-weight: bold; 
            margin-top: 0.5cm;
            position: fixed;
            bottom: 0.82cm;
            margin-right: 2.03cm;
            right: 2.03cm;
        }
    }

    /* Header settings */
    @page {
        @top {
            margin-top: 1.27cm;
        }
    }

    /* All other pages */
    @page {
        size: 8.5in 13in;
        margin-top: 1.52cm;
        margin-bottom: 2cm;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        @bottom {
            content: "";
            display: block;
            border-top: 3px solid black !important; /* Keep this border */
            margin-bottom: 0.82cm !important;
            width: 95%;
            position: fixed;
            bottom: 0.82cm;
            height: 3px;
            margin-left: 1.78cm;
            margin-right: 2.03cm;
            left: 1.78cm;
            right: 2.03cm;
        }
        @bottom-right {
            content: ""; /* Removed page number text */
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            position: fixed;
            bottom: 0.82cm;
            right: 2.03cm;
            margin-right: 2.03cm;
        }
    }

    /* Force the line to be visible */
    @bottom {
        border-top: 3px solid black !important; /* Keep this border */
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        position: fixed;
        bottom: 0.82cm;
        height: 3px;
        width: 95%;
        margin-left: 1.78cm;
        margin-right: 2.03cm;
        left: 1.78cm;
        right: 2.03cm;
    }

    /* Ensure proper spacing for the footer */
    .proposal-container {
        margin-bottom: 1.5cm !important;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
        /* Remove border */
        border: none;
        box-sizing: border-box;
        min-height: calc(100% - 2cm);
        width: calc(100% - 3.81cm);
        margin-top: 1.52cm !important;
        margin-bottom: 2cm !important;
        margin-left: 1.78cm !important;
        margin-right: 2.03cm !important;
        background-clip: padding-box;
        box-shadow: none;
    }

    .proposal-container {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        background: white !important;
    }

    .proposal-container table {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        border-collapse: collapse !important;
        page-break-inside: avoid !important;
    }

    .proposal-container td,
    .proposal-container th {
        border: 1px solid #000 !important; /* Reduced from 3px to 1px */
        padding: 8px !important;
        margin: 0 !important;
        font-size: 12pt !important;
        line-height: 1.3 !important;
    }

    /* Force background colors */
    .proposal-container th,
    .proposal-container td[style*="background-color: #f8f9fa"] {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide non-printable elements */
    .sidebar, .mobile-nav-toggle, .sidebar-backdrop, 
    .btn-group, .card, .page-title {
        display: none !important;
    }

    /* Reset main content */
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    /* Force colors and remove decorations */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
}

/* Dark mode adjustments */
[data-bs-theme="dark"] .proposal-container {
    background-color: var(--proposal-bg);
    border-color: var(--proposal-border);
}

[data-bs-theme="dark"] .proposal-container td,
[data-bs-theme="dark"] .proposal-container th {
    border-color: var(--proposal-border);
    background-color: var(--proposal-bg);
}

[data-bs-theme="dark"] .proposal-container th {
    background-color: var(--proposal-header-bg) !important;
    color: var(--proposal-text);
}

/* PDF-like container styles */
.proposal-container {
    max-width: 1200px;
    margin: 30px auto;
    background: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
    font-family: 'Times New Roman', Times, serif;
    /* Remove the border */
    border: none;
}

/* Table layout styles */
.proposal-container table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.proposal-container td,
.proposal-container th {
    padding: 12px 15px;
    line-height: 1.5;
}

/* Header section styles */
.header-section {
    margin-bottom: 30px;
}

.header-section table {
    margin-bottom: 15px !important;
}

.header-section table:first-child {
    border: 1px solid #000 !important;
}

.header-section table:first-child td {
    vertical-align: middle;
}

.header-section table:nth-child(2),
.header-section table:nth-child(3) {
    border: 1px solid #000 !important;
}

/* Main section styles */
.main-section table {
    border: 1px solid #000 !important;
    margin-bottom: 15px !important;
}

.main-section table th {
    background-color: #f8f9fa;
    font-weight: bold;
    text-align: left;
}

/* Specific table styles */
.project-team-table td:first-child {
    width: 25%;
    font-weight: bold;
    background-color: #f8f9fa;
}

/* Dark mode styles */
[data-bs-theme="dark"] .proposal-container {
    background-color: var(--proposal-bg);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

[data-bs-theme="dark"] .proposal-container table {
    border-color: var(--proposal-border) !important;
}

[data-bs-theme="dark"] .proposal-container th,
[data-bs-theme="dark"] .project-team-table td:first-child {
    background-color: var(--proposal-header-bg) !important;
    color: var(--proposal-text);
}

[data-bs-theme="dark"] .proposal-container td {
    color: var(--proposal-text);
    background-color: var(--proposal-bg) !important;
}

/* Checkbox styles */
.checkbox-container {
    text-align: center;
    padding: 10px 0;
    border-bottom: 0.5pt solid black !important;
}

.checkbox-option {
    display: inline-block;
    margin: 0 20px;
    font-size: 12pt;
}

/* Title styles */
.title-row td {
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    padding: 15px !important;
}

/* Logo styles */
.logo-cell {
    width: 15%;
    text-align: center;
    padding: 15px !important;
}

.logo-cell img {
    max-width: 80px;
    height: auto;
}

/* Reference number styles */
.reference-cell {
    width: 45%;
}

/* Signature section styles */
.signatures-table {
    width: 100%;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    page-break-inside: avoid !important;
    page-break-after: avoid !important;
}

.signatures-table td {
    text-align: center !important;
    vertical-align: bottom !important;
    height: 2.5cm !important; /* Increased height for signature space */
    padding: 8pt !important;
}

.signature-name {
    font-weight: bold !important;
    margin: 6pt 0 3pt 0 !important;
    font-size: 11pt !important;
}

.signature-position {
    font-style: italic !important;
    margin: 0 !important;
    font-size: 10pt !important;
}

.signature-line {
    border-bottom: solid 1pt black !important;
    width: 80% !important;
    margin: 25pt auto 8pt !important; /* Increased spacing */
    mso-border-bottom-alt: solid black 1pt;
}

/* Print-specific signature styles */
@media print {
    .signatures-table {
        margin: 0 !important;
        padding: 0 !important;
        page-break-inside: avoid !important;
        page-break-after: avoid !important;
    }

    .signatures-table td {
        padding: 10px !important;
    }

    .signature-name,
    .signature-position {
        margin: 0 !important;
        padding: 0 !important;
    }
}

/* Word-specific signature styles */
.WordSection1 .signatures-table,
[data-doc-type="word"] .signatures-table {
    margin: 0 !important;
    padding: 0 !important;
    border-spacing: 0 !important;
}

/* Remove footer spacing in Word */
div[style*="mso-element:footer"] {
    display: none !important;
}

/* Remove any space after the last table */
table:last-of-type {
    margin-bottom: 0 !important;
    border-bottom: 1px solid black !important;
}

/* Remove duplicate borders */
.main-section table + table {
    border-top: none !important;
}

/* Word export styles */
div[style*="mso-element:footer"] {
    border-top: 5px solid black !important;
    padding-top: 15px !important;
    margin-top: 25px !important;
    margin-bottom: 1.5cm !important;
    margin-left: 1.78cm !important;
    margin-right: 2.03cm !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
}

/* Add these styles to your existing styles section */
input[list]::-webkit-calendar-picker-indicator {
    display: none !important;
}

input[list] {
    padding-right: 30px;
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] input[list] {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-search" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>');
}

/* Add these styles to your existing styles section */
#proposalDropdown {
    position: absolute;
    width: 100%;
    z-index: 1000;
    border: 1px solid var(--border-color);
    border-radius: 0.25rem;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    background-color: var(--card-bg);
}

#proposalDropdown .dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
    color: var(--text-primary);
    white-space: normal;
    word-break: break-word;
}

#proposalDropdown .dropdown-item:hover {
    background-color: var(--accent-color);
    color: white;
}

/* Dark mode adjustments */
[data-bs-theme="dark"] #proposalDropdown {
    background-color: var(--dark-input);
    border-color: var(--dark-border);
}

[data-bs-theme="dark"] #proposalDropdown .dropdown-item {
    color: var(--dark-text);
}

    /* Existing styles */
    // ... existing code ...

    /* Print styles */
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
        
        .proposal-container {
            width: 100%;
            border: 0.5px solid black;
            page-break-inside: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }
        
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        td {
            page-break-inside: avoid;
            padding: 5px;
        }
        
        .header-table, .title-table, .checkbox-table {
            border: 0.5px solid black !important;
        }
        
        .logo-cell, .reference-cell, .form-title {
            border: 0.5px solid black !important;
        }
        
        /* Other existing print styles */
        // ... existing code ...
}
    </style>
    <style>
        /* Add these as global styles */
        tr:last-child td, tr:last-child td p {
            border: none !important;
        }
        p[style*="Cc: GAD Central"], p:contains("Cc: GAD Central"), p:contains("cc: GAD Central") {
            border: none !important;
            border-bottom: none !important;
        }
        /* Override any border that might be on the "Cc: GAD Central" text */
        p[style*="italic"] {
            border: none !important;
            border-bottom: none !important;
        }
        
        @media print {
            /* Print-specific styles for Cc: GAD Central */
            tr:last-child td, tr:last-child p {
                border: none !important;
                border-bottom: none !important;
            }
        }
    </style>
    <style>
        /* Global styles for both print and Word export */
        body {
            font-family: Times New Roman, sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
        }
        
        .proposal-container {
            margin: 0.5cm;
            padding: 1cm;
            border: none !important;
        }
        
        .proposal-container table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .proposal-container td, .proposal-container th {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        
        /* Specific styles for the monitoring table */
        .monitoring-table th, .monitoring-table td {
            border: 0.1px solid black !important;
            padding: 5px !important;
            font-size: 11pt !important;
        }
        
        .monitoring-table th:last-child, .monitoring-table td:last-child {
            border-right: 4px solid black !important;
            padding: 4px !important;
        }
        
        /* Style for footer with border */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            border-top: 3px solid black;
            padding-top: 5px;
            font-size: 10pt;
            text-align: center;
        }
        
        /* Style for Cc: GAD Central - completely remove any border */
        p[style*="Cc: GAD Central"], p:contains("Cc: GAD Central"), p:contains("cc: GAD Central"), .cc-gad,
        p:contains("Required Attachment"), p:contains("Office of the College Dean") {
            border: none !important;
            border-bottom: none !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            text-decoration: none !important;
            font-style: italic !important;
            box-shadow: none !important;
            background: transparent !important;
            outline: none !important;
            text-decoration-line: none !important;
            text-decoration-style: none !important;
        }
        
        tr:has(p:contains("Cc: GAD Central")), td:has(p:contains("Cc: GAD Central")) {
            border: none !important;
            border-bottom: none !important;
            border-top: none !important;
        }
        
        /* Media query for print */
        @media print {
            @page {
                size: 8.5in 13in;
                margin: 0.3cm 0.5cm;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .proposal-container {
                margin: 0;
                padding: 0.5cm;
                border: none !important;
            }
            
            /* Specific print styles for monitoring table */
            .monitoring-table th, .monitoring-table td {
                border: 0.1px solid black !important;
            }
            
            .monitoring-table th:last-child, .monitoring-table td:last-child {
                border-right: 4px solid black !important;
            }
            
            /* Print-specific styles for Cc: GAD Central */
            p[style*="Cc: GAD Central"], p:contains("Cc: GAD Central"), p.cc-gad {
                border: none !important;
                border-bottom: none !important;
                text-decoration: none !important;
                font-style: italic !important;
            }
        }
    </style>
    <style>
        /* Final override to ensure absolutely no borders on footer text */
        div[style*="margin-top: 10px"], 
        p:contains("Cc: GAD Central"),
        p:contains("Required Attachment"),
        p:contains("Office of the College Dean") {
            border: none !important;
            border-bottom: none !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            text-decoration: none !important;
            box-shadow: none !important;
            background: transparent !important;
            outline: none !important;
        }
    </style>
    <style>
        /* Additional override for spans in the footer */
        html body div div[style*="margin-top: 10px"] span {
            border: none !important;
            border-bottom: none !important;
            border-top: none !important;
            border-left: none !important;
            border-right: none !important;
            outline: none !important;
            text-decoration: none !important;
            box-shadow: none !important;
            background-color: transparent !important;
            background: transparent !important;
            display: block;
        }
        
        /* Print-specific overrides */
        @media print {
            html body div div[style*="margin-top: 10px"] span {
                border: none !important;
                text-decoration: none !important;
                outline: none !important;
                box-shadow: none !important;
                background: transparent !important;
            }
        }
    </style>
    <style>
        /* Specific styles for dark mode proposal preview */
        [data-bs-theme="dark"] #reportPreview .proposal-container {
            background-color: #333 !important;
            color: #fff !important;
            border: 1px solid #555 !important;
        }

        [data-bs-theme="dark"] .dark-mode-proposal {
            background-color: #333 !important;
            color: #fff !important;
            border: 1px solid #555 !important;
        }

        [data-bs-theme="dark"] #reportPreview .proposal-container p,
        [data-bs-theme="dark"] #reportPreview .proposal-container li,
        [data-bs-theme="dark"] #reportPreview .proposal-container div,
        [data-bs-theme="dark"] #reportPreview .proposal-container strong,
        [data-bs-theme="dark"] #reportPreview .proposal-container ol,
        [data-bs-theme="dark"] #reportPreview .proposal-container ul,
        [data-bs-theme="dark"] .dark-mode-proposal p,
        [data-bs-theme="dark"] .dark-mode-proposal li,
        [data-bs-theme="dark"] .dark-mode-proposal div,
        [data-bs-theme="dark"] .dark-mode-proposal strong,
        [data-bs-theme="dark"] .dark-mode-proposal ol,
        [data-bs-theme="dark"] .dark-mode-proposal ul {
            color: #fff !important;
        }

        [data-bs-theme="dark"] #reportPreview table,
        [data-bs-theme="dark"] #reportPreview td,
        [data-bs-theme="dark"] #reportPreview th,
        [data-bs-theme="dark"] .dark-mode-proposal table,
        [data-bs-theme="dark"] .dark-mode-proposal td,
        [data-bs-theme="dark"] .dark-mode-proposal th {
            border-color: #777 !important;
        }

        [data-bs-theme="dark"] #reportPreview td,
        [data-bs-theme="dark"] #reportPreview th,
        [data-bs-theme="dark"] .dark-mode-proposal td,
        [data-bs-theme="dark"] .dark-mode-proposal th {
            background-color: #444 !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] #reportPreview th,
        [data-bs-theme="dark"] .dark-mode-proposal th {
            background-color: #555 !important;
        }

        [data-bs-theme="dark"] #reportPreview strong,
        [data-bs-theme="dark"] .dark-mode-proposal strong {
            color: #e1e1e1 !important;
        }

        /* Maintaining high contrast for the generated proposal */
        [data-bs-theme="dark"] #reportPreview {
            background-color: #252525 !important;
        }
        
        /* Ensure visibility for workplan chart */
        [data-bs-theme="dark"] #reportPreview table td[style*="background-color: black"],
        [data-bs-theme="dark"] .dark-mode-proposal table td[style*="background-color: black"] {
            background-color: #9c27b0 !important; /* Use accent color for better visibility */
        }
    </style>
    <style>
        /* Direct targeting of the proposal container for maximum visibility in dark mode */
        [data-bs-theme="dark"] .card-body #reportPreview {
            background-color: #252525 !important;
            padding: 20px !important;
            border-radius: 10px !important;
        }

        /* Increase default font color brightness for better readability */
        [data-bs-theme="dark"] .proposal-container,
        [data-bs-theme="dark"] .dark-mode-proposal {
            background-color: #333 !important;
            color: #fff !important;
            border: 1px solid #555 !important;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5) !important;
        }

        /* Make borders more visible */
        [data-bs-theme="dark"] .proposal-container table,
        [data-bs-theme="dark"] .dark-mode-proposal table {
            border: 2px solid #777 !important;
        }
        
        /* Make headings more visible */
        [data-bs-theme="dark"] .proposal-container strong,
        [data-bs-theme="dark"] .dark-mode-proposal strong {
            color: #bb86fc !important; /* Purple accent color for headings */
            font-weight: bold !important;
        }
        
        /* Increase contrast for table cells */
        [data-bs-theme="dark"] .proposal-container td,
        [data-bs-theme="dark"] .proposal-container th,
        [data-bs-theme="dark"] .dark-mode-proposal td,
        [data-bs-theme="dark"] .dark-mode-proposal th {
            background-color: #3a3a3a !important;
            color: #fff !important;
            border: 1px solid #777 !important;
        }

        /* Table header background */
        [data-bs-theme="dark"] .proposal-container th,
        [data-bs-theme="dark"] .dark-mode-proposal th {
            background-color: #424242 !important;
            color: #bb86fc !important;
        }
        
        /* Ensure print preview maintains style in dark mode */
        @media screen and (prefers-color-scheme: dark) {
            .proposal-container {
                background-color: #333 !important;
                color: #fff !important;
            }
        }
    </style>
    <style>
        /* Light mode specific styles - override any dark styles that might be leaking */
        [data-bs-theme="light"] .proposal-container,
        [data-bs-theme="light"] .dark-mode-proposal,
        .proposal-container,
        .dark-mode-proposal {
            background-color: white !important;
            color: black !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
        }

        /* Light mode table styles */
        [data-bs-theme="light"] .proposal-container table,
        [data-bs-theme="light"] .dark-mode-proposal table,
        .proposal-container table,
        .dark-mode-proposal table {
            border: 1px solid black !important;
        }

        [data-bs-theme="light"] .proposal-container td,
        [data-bs-theme="light"] .proposal-container th,
        [data-bs-theme="light"] .dark-mode-proposal td,
        [data-bs-theme="light"] .dark-mode-proposal th,
        .proposal-container td,
        .proposal-container th,
        .dark-mode-proposal td,
        .dark-mode-proposal th {
            background-color: white !important;
            color: black !important;
            border: 1px solid black !important;
        }

        [data-bs-theme="light"] .proposal-container strong,
        [data-bs-theme="light"] .dark-mode-proposal strong,
        .proposal-container strong,
        .dark-mode-proposal strong {
            color: black !important;
        }

        /* Fix for paragraph and list text in light mode */
        [data-bs-theme="light"] .proposal-container p,
        [data-bs-theme="light"] .proposal-container li,
        [data-bs-theme="light"] .proposal-container div,
        [data-bs-theme="light"] .proposal-container ol,
        [data-bs-theme="light"] .proposal-container ul,
        [data-bs-theme="light"] .dark-mode-proposal p,
        [data-bs-theme="light"] .dark-mode-proposal li,
        [data-bs-theme="light"] .dark-mode-proposal div,
        [data-bs-theme="light"] .dark-mode-proposal ol,
        [data-bs-theme="light"] .dark-mode-proposal ul,
        .proposal-container p,
        .proposal-container li,
        .proposal-container div,
        .proposal-container ol,
        .proposal-container ul,
        .dark-mode-proposal p,
        .dark-mode-proposal li,
        .dark-mode-proposal div,
        .dark-mode-proposal ol,
        .dark-mode-proposal ul {
            color: black !important;
        }
    </style>
    <style>
        /* Specific light-mode-proposal class styles */
        .light-mode-proposal {
            background-color: white !important;
            color: black !important;
            border: 1px solid #dee2e6 !important;
        }

        .light-mode-proposal table {
            border: 1px solid black !important;
        }

        .light-mode-proposal td,
        .light-mode-proposal th {
            background-color: white !important;
            color: black !important;
            border: 1px solid black !important;
        }

        .light-mode-proposal p,
        .light-mode-proposal li,
        .light-mode-proposal div,
        .light-mode-proposal strong,
        .light-mode-proposal ol,
        .light-mode-proposal ul {
            color: black !important;
        }

        /* Style Gantt chart cells in light mode */
        .light-mode-proposal table td[style*="background-color: black"] {
            background-color: black !important;
        }
    </style>
    <style>
        /* Apply Bootstrap system font stack to form elements only */
        .form-control, .form-select, .input-group, .btn, .form-label, label, select, input, textarea, .select2-selection, .form-check-label {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        /* Keep the original font for print elements */
        .print-content, .print-view, .print-only, .printable {
            font-family: 'Times New Roman', Times, serif !important;
        }
    </style>
    <style>
        /* Apply system font to sidebar and title elements */
        .sidebar, .sidebar *, h1, h2, h3, h4, h5, h6, .nav-link, .card-title, 
        .logo-title, .nav-item, .navbar, .navbar *, .page-title, .title, 
        .main-heading, #main-heading, .nav, .nav * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
    </style>
    <style>
        /* Preserve Font Awesome icons */
        .fas, .fab, .far, .fa, .fa-solid, .fa-regular, .fa-brands,
        [class^="fa-"], [class*=" fa-"] {
            font-family: "Font Awesome 5 Free" !important;
        }
        
        .fab, .fa-brands {
            font-family: "Font Awesome 5 Brands" !important;
        }
        
        .fa-regular, .far {
            font-family: "Font Awesome 5 Regular" !important;
        }
    </style>
    <!-- Replace with just a single font change for interface elements -->
    <style>
        /* Form interface elements use system font */
        .form-control, .btn, label, select, input, textarea, .nav-link, 
        .sidebar, .card-title, .page-title, h1, h2, h3, h4, h5, h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Font Awesome icons need their specific font */
        .fas, .fab, .far, .fa, [class^="fa-"], [class*=" fa-"] {
            font-family: "Font Awesome 5 Free";
        }
        
        .fab, .fa-brands {
            font-family: "Font Awesome 5 Brands";
        }
        
        /* Print content uses serif font */
        #reportPreview, .printable, .print-content {
            font-family: 'Times New Roman', Times, serif;
        }
    </style>
    <!-- Add just before closing head tag -->
    <style>
        /* Interface elements - System font */
        .form-control, .btn, label, input, select, textarea, .sidebar,
        .nav-link, .card-title, h1, h2, h3, h4, h5, h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Font Awesome icons */
        .fas, .fab, .far, .fa {
            font-family: "Font Awesome 5 Free";
        }
        
        .fab {
            font-family: "Font Awesome 5 Brands";
        }
        
        /* Fix for dark mode */
        [data-bs-theme="dark"] body {
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .sidebar {
            background-color: var(--sidebar-bg) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .card {
            background-color: var(--card-bg) !important;
            color: var(--text-primary) !important;
        }
        
        /* Fix for consistent border styles */
        .proposal-container table {
            border-collapse: collapse !important;
        }
        
        .proposal-container table td,
        .proposal-container table th {
            border-width: 0.1px !important;
            border-style: solid !important;
            border-color: black !important;
        }
        
        /* Print-specific fixes */
        @media print {
            .proposal-container table {
                border-collapse: collapse !important;
            }
            
            .proposal-container table td,
            .proposal-container table th {
                border-width: 0.1px !important;
                border-style: solid !important;
                border-color: black !important;
            }
            
            /* Fix for borders that might be inconsistent */
            [style*="border-top:"], [style*="border-bottom:"],
            [style*="border-left:"], [style*="border-right:"] {
                border-width: 0.1px !important;
                border-style: solid !important;
                border-color: black !important;
            }
            
            /* Make sure program/project/activity line shows up correctly */
            div[style*="display: flex"] {
                border-top: 0.1px solid black !important;
                border-bottom: 0.1px solid black !important;
            }
        }
    </style>
    <style>
        /* Fix for dark mode */
        [data-bs-theme="dark"] body {
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .sidebar {
            background-color: var(--sidebar-bg) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .card {
            background-color: var(--card-bg) !important;
            color: var(--text-primary) !important;
        }

        /* Fix for proposal preview */
        /* Light mode - force white background with black text */
        [data-bs-theme="light"] .light-mode-proposal,
        [data-bs-theme="light"] .proposal-container {
            background-color: white !important;
            color: black !important;
        }
        
        [data-bs-theme="light"] .light-mode-proposal p,
        [data-bs-theme="light"] .light-mode-proposal div,
        [data-bs-theme="light"] .light-mode-proposal td,
        [data-bs-theme="light"] .light-mode-proposal th,
        [data-bs-theme="light"] .light-mode-proposal li,
        [data-bs-theme="light"] .light-mode-proposal strong,
        [data-bs-theme="light"] .proposal-container p,
        [data-bs-theme="light"] .proposal-container div,
        [data-bs-theme="light"] .proposal-container td,
        [data-bs-theme="light"] .proposal-container th,
        [data-bs-theme="light"] .proposal-container li,
        [data-bs-theme="light"] .proposal-container strong {
            background-color: white !important;
            color: black !important;
            border-color: black !important;
        }
    </style>
    <style>
        [data-bs-theme="light"] .light-mode-proposal td,
        [data-bs-theme="light"] .light-mode-proposal th,
        [data-bs-theme="light"] .proposal-container td,
        [data-bs-theme="light"] .proposal-container th,
        [data-bs-theme="light"] .proposal-container li,
        [data-bs-theme="light"] .proposal-container strong {
            background-color: white !important;
            color: black !important;
            border-color: black !important;
        }
        
        /* Fix for Gantt chart cells in light mode */
        [data-bs-theme="light"] .light-mode-proposal table td[bgcolor="black"],
        [data-bs-theme="light"] .proposal-container table td[bgcolor="black"],
        [data-bs-theme="light"] td[style*="background-color: black"],
        [data-bs-theme="light"] td[style*="background-color:black"] {
            background-color: #6a1b9a !important; /* Use purple like in dark mode */
        }
    </style>
    <style>
        /* Print and Word export specific styles */
        @media print {
            .signatures-table {
                margin-bottom: 0 !important;
                page-break-after: avoid !important;
            }
            
            .signatures-table td {
                padding: 10px 15px !important;
                border-bottom: none !important;
            }
            
            .signature-name {
                margin-top: 30px !important;
                margin-bottom: 0 !important;
            }
            
            .signature-position {
                margin-bottom: 0 !important;
                padding-bottom: 0 !important;
            }
            
            /* Remove any forced page breaks or spacing after signatures */
            .signatures-table::after {
                display: none !important;
                content: none !important;
            }
        }

        /* Word export specific styles */
        div[style*="mso-element:footer"] {
            border-top: none !important;
            padding-top: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        /* Remove any extra space in Word */
        .WordSection1 .signatures-table,
        [data-doc-type="word"] .signatures-table {
            margin-bottom: 0 !important;
            border-bottom: none !important;
        }

        /* Ensure no page breaks or spacing after signatures in Word */
        .WordSection1 .signatures-table::after,
        [data-doc-type="word"] .signatures-table::after {
            display: none !important;
            content: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
    <style>
        /* Header and title styles */
        .header-section table,
        .title-section table {
            border: 0.5pt solid black !important;
            margin-bottom: 0 !important;
        }

        .header-section table td,
        .title-section table td {
            border: 0.5pt solid black !important;
        }

        /* Title specific styles */
        .title-section {
            border-top: 0.5pt solid black !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }

        .title-row {
            border-top: 0.5pt solid black !important;
            border-bottom: 0.5pt solid black !important;
        }

        .title-row td {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            padding: 8px !important;
            border: 0.5pt solid black !important;
        }

        /* Adjust all table borders to be thinner */
        table, th, td {
            border: 0.5pt solid black !important;
        }

        .main-section table {
            border: 0.5pt solid black !important;
        }

        /* Print-specific styles */
        @media print {
            table, th, td {
                border: 0.5pt solid black !important;
            }
            
            .header-section table,
            .title-section table {
                border: 0.5pt solid black !important;
            }
        }

        /* Logo cell adjustments */
        .logo-cell {
            width: 15%;
            text-align: center;
            padding: 8px !important;
            border: 0.5pt solid black !important;
        }

        .logo-cell img {
            max-width: 60px;
            height: auto;
        }
    </style>
    <style>
        @page {
            margin: 1.52cm 2.03cm 2cm 1.78cm;
            size: folio;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 0.5pt solid black;
        }

        td, th {
            border: 0.5pt solid black;
            padding: 8px;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            table {
                page-break-inside: auto;
                border: 0.5pt solid black !important;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            td, th {
                border: 0.5pt solid black !important;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }

        /* Header section */
        .header-table {
            margin-bottom: 0;
            border: 0.5pt solid black;
        }

        .header-table td {
            border: 0.5pt solid black;
            vertical-align: middle;
        }

        /* Title section */
        .title-table {
            margin-top: 0;
            margin-bottom: 0;
            border: 0.5pt solid black;
        }

        .title-table td {
            text-align: center;
            font-weight: bold;
            border: 0.5pt solid black;
        }

        /* Logo styles */
        .logo-cell {
            width: 15%;
            text-align: center;
        }

        .logo-cell img {
            max-width: 60px;
            height: auto;
        }
    </style>
    <style>
        /* Essential print styles */
        @page {
            margin: 1.52cm 2.03cm 2cm 1.78cm;
            size: A4;
        }

        /* Base table styles */
        .proposal-container table {
            width: 100%;
            border-collapse: collapse !important;
            border: 0.5pt solid black !important;
        }

        .proposal-container td,
        .proposal-container th {
            border: 0.5pt solid black !important;
            padding: 8px;
        }

        /* Force print borders */
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .proposal-container {
                border: none !important;
                box-shadow: none !important;
            }

            .proposal-container table {
                border: 0.5pt solid black !important;
                -webkit-box-decoration-break: clone !important;
                box-decoration-break: clone !important;
            }

            .proposal-container td,
            .proposal-container th {
                border: 0.5pt solid black !important;
                -webkit-box-decoration-break: clone !important;
                box-decoration-break: clone !important;
            }

            /* Header table specific */
            .header-table,
            .header-table td {
                border: 0.5pt solid black !important;
                -webkit-box-decoration-break: clone !important;
                box-decoration-break: clone !important;
            }

            /* Title table specific */
            .title-table,
            .title-table td {
                border: 0.5pt solid black !important;
                -webkit-box-decoration-break: clone !important;
                box-decoration-break: clone !important;
            }
        }

        /* Header table styles */
        .header-table {
            margin: 0 !important;
            border: 0.5pt solid black !important;
        }

        .header-table td {
            border: 0.5pt solid black !important;
            vertical-align: middle;
        }

        /* Title table styles */
        .title-table {
            margin: 0 !important;
            border: 0.5pt solid black !important;
        }

        .title-table td {
            border: 0.5pt solid black !important;
            text-align: center;
            font-weight: bold;
        }
    </style>
    <style>
        /* Checkbox table styles */
        .checkbox-table {
            width: 100%;
            border-collapse: collapse;
            border: none !important;
            margin: 0 !important;
        }

        .checkbox-table td {
            text-align: center !important;
            border: none !important;
            border-bottom: 0.5pt solid black !important;
            padding: 8px;
        }

        .checkbox-option {
            display: inline-block;
            margin: 0 30px;
            font-size: 12pt;
            text-align: center;
        }

        /* Print styles for checkbox table */
        @media print {
            .checkbox-table {
                width: 100% !important;
                text-align: center !important;
            }
            
            .checkbox-table td {
                text-align: center !important;
                border-bottom: 0.5pt solid black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .checkbox-option {
                display: inline-block;
                margin: 0 30px !important;
                text-align: center !important;
            }
        }

        /* Ensure centering in Word export */
        .WordSection1 .checkbox-table,
        [data-doc-type="word"] .checkbox-table {
            text-align: center !important;
        }

        .WordSection1 .checkbox-option,
        [data-doc-type="word"] .checkbox-option {
            margin: 0 30px !important;
            text-align: center !important;
        }
    </style>
    <style>
        /* Page setup */
        @page {
            size: 21.59cm 33.02cm; /* Folio paper size */
            mso-page-orientation: portrait;
            margin: 1.52cm 2.03cm 2cm 1.78cm; /* Top Right Bottom Left */
            mso-header-margin: 1.27cm;
            mso-footer-margin: 0.82cm;
            mso-gutter-margin: 0cm;
            mso-vertical-align: top;
        }
        
        /* Different first page setting */
        @page :first {
            mso-header-margin: 1.27cm;
            mso-footer-margin: 0.82cm;
            margin: 1.52cm 2.03cm 2cm 1.78cm; /* Top Right Bottom Left */
        }
        
        /* Word specific settings */
        .WordSection1 {
            page: Section1;
        }
        div.WordSection1 {page:WordSection1;}
        
        /* Set paper size explicitly */
        @page WordSection1 {
            size: 21.59cm 33.02cm;
            margin: 1.52cm 2.03cm 2cm 1.78cm;
            mso-header-margin: 1.27cm;
            mso-footer-margin: 0.82cm;
            mso-gutter-margin: 0cm;
            mso-paper-source: 0;
        }
        
        /* Basic styling */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
        }
        
        td, th {
            border: 1px solid black;
            padding: 4px;
            vertical-align: top;
        }
        
        /* Header table */
        .header-table {
            margin-bottom: 10px;
        }
        
        /* Section headers (with Roman numerals) */
        h3 {
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        /* Lists */
        ul, ol {
            margin-top: 5px;
            margin-bottom: 5px;
        }
        
        /* Checkboxes */
        input[type="checkbox"] {
            border: 1px solid black;
        }
        
        /* Footer */
        .footer {
            border-top: 1px solid black;
            margin-top: 20px;
            padding-top: 5px;
        }
    </style>
    <style>
        /* Dropdown submenu styles */
        .dropdown-submenu {
            position: relative;
        }
        
        .dropdown-submenu > .dropdown-menu {
            position: static !important;
            left: 100%;
            margin-top: -6px;
            margin-left: 0;
            border-radius: 0.25rem;
            display: none;
            padding-left: 10px;
        }
        
        .dropdown-submenu .dropdown-item {
            padding-left: 30px;
        }
        
        /* Hide the pseudo-element arrow since we're using an explicit icon */
        .dropdown-submenu > a:after {
            display: none !important;
        }
        
        /* Style for the submenu indicator icon */
        .submenu-indicator {
            font-size: 0.7rem;
            color: var(--text-primary);
            transition: transform 0.2s ease;
        }
        
        .dropdown-submenu.show .submenu-indicator {
            transform: rotate(90deg);
            color: var(--accent-color);
        }
        
        /* Add click-based display */
        .dropdown-submenu.show > .dropdown-menu {
            display: block;
        }
        
        .dropdown-submenu.pull-left {
            float: none;
        }
        
        .dropdown-submenu.pull-left > .dropdown-menu {
            left: -100%;
            margin-left: 10px;
            border-radius: 0.25rem;
        }
        /* End of dropdown submenu styles */
    </style>
    <!-- Add just before closing head tag -->
    <style>
        /* Interface elements - System font */
        .form-control, .btn, label, input, select, textarea, .sidebar,
        .nav-link, .card-title, h1, h2, h3, h4, h5, h6 {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        /* Font Awesome icons */
        .fas, .fab, .far, .fa {
            font-family: "Font Awesome 5 Free";
        }
        
        .fab {
            font-family: "Font Awesome 5 Brands";
        }
        
        /* Fix for dark mode */
        [data-bs-theme="dark"] body {
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .sidebar {
            background-color: var(--sidebar-bg) !important;
            color: var(--text-primary) !important;
        }
        
        [data-bs-theme="dark"] .card {
            background-color: var(--card-bg) !important;
            color: var(--text-primary) !important;
        }
        
        /* Fix overall body font to match dashboard.php */
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
        
        /* Keep Times New Roman only for print elements */
        .print-content, .print-view, .print-only, .printable, #reportPreview, .proposal-container {
            font-family: 'Times New Roman', Times, serif !important;
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
                <a href="../dashboard/dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                        <li><a class="dropdown-item" href="../signatory/sign.php">Signatory</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gbp.php">GPB Form</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form <i class="fas fa-chevron-right ms-2 submenu-indicator"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="../ppas_form/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">GAD Proposal Form</a></li>
                                <li><a class="dropdown-item" href="../narrative/narrative.php">Narrative Form</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">Annual GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="#">GAD Proposal Reports</a></li>
                        <li><a class="dropdown-item" href="../narrative/print_narrative.php">Narrative Reports</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- Bottom controls -->
        <div class="bottom-controls">
            <a href="../index.php" class="logout-button" onclick="handleLogout(event)">
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
            <i class="fas fa-file-alt"></i>
            <h2>Print GAD Proposal</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="campus">Campus</label>
                                <select class="form-control" id="campus" required>
                                    <option value="">Select Campus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="proposal">Proposal</label>
                                <div class="position-relative">
                                    <input type="text" 
                                          class="form-control" 
                                          id="proposal" 
                                          placeholder="Search for a proposal..." 
                                          autocomplete="off"
                                          required>
                                    <div id="proposalDropdown" class="dropdown-menu w-100" style="display:none; max-height: 200px; overflow-y: auto;"></div>
                                    <input type="hidden" id="proposal_id">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-print"></i> Generate Proposal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Proposal Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                        </button>
                       
                    </div>
                </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Proposal content will be loaded here -->
                    <div class="text-center text-muted py-5" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus, year, and proposal to generate the preview</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            loadCampusOptions();
            
            // Handle form submission
            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                const selectedProposalId = $('#proposal_id').val();
                console.log('Form submitted. Proposal ID:', selectedProposalId);
                
                if (!selectedProposalId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select a proposal first.'
                    });
                    return;
                }
                
                generateReport();
            });

            // Handle proposal search input
            let searchTimeout;
            $('#proposal').on('input', function() {
                const searchTerm = $(this).val();
                const selectedCampus = $('#campus').val();
                const selectedYear = $('#year').val();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Clear proposal ID when input changes
                $('#proposal_id').val('');
                
                if (!selectedCampus || !selectedYear) {
                    console.log('Campus or year not selected');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please select both campus and year first.'
                    });
                    return;
                }
                
                if (searchTerm.length < 1) {
                    $('#proposalDropdown').hide().empty();
                    return;
                }
                
                // Set new timeout
                searchTimeout = setTimeout(() => {
                    console.log('Searching for:', searchTerm);
                    $.ajax({
                        url: 'api/get_proposals.php',
                        method: 'GET',
                        data: {
                            search: searchTerm,
                            campus: selectedCampus,
                            year: selectedYear
                        },
                        dataType: 'json',
                        success: function(response) {
                            try {
                                console.log('Search response:', response);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                
                                // Make sure response is an object if it's a string
                                if (typeof response === 'string') {
                                    response = JSON.parse(response);
                                }
                                
                                if (response && response.status === 'success' && Array.isArray(response.data) && response.data.length > 0) {
                                    // Store proposals globally
                                    window.proposals = response.data;
                                    
                                    console.log('Found', response.data.length, 'proposals');
                                    
                                    // Add proposals to dropdown
                                    response.data.forEach(function(proposal) {
                                        const item = $('<div class="dropdown-item"></div>')
                                            .text(proposal.activity_title)
                                            .attr('data-id', proposal.id)
                                            .click(function() {
                                                // Set input value
                                                $('#proposal').val(proposal.activity_title);
                                                // Set hidden proposal_id
                                                $('#proposal_id').val(proposal.id);
                                                // Hide dropdown
                                                dropdown.hide();
                                                console.log('Selected proposal:', proposal.activity_title, 'with ID:', proposal.id);
                                            });
                                        
                                        dropdown.append(item);
                                    });
                                    
                                    // Show dropdown
                                    dropdown.show();
                                    console.log('Updated dropdown with', response.data.length, 'options');
                                } else {
                                    console.log('No proposals found - Response data:', JSON.stringify(response));
                                    // Show "no results" message
                                    dropdown.append('<div class="dropdown-item disabled">No proposals found</div>');
                                    dropdown.show();
                                }
                            } catch (error) {
                                console.error('Error processing response:', error);
                                const dropdown = $('#proposalDropdown');
                                dropdown.empty();
                                dropdown.append('<div class="dropdown-item disabled">Error processing response</div>');
                                dropdown.show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Search error:', error);
                            const dropdown = $('#proposalDropdown');
                            dropdown.empty();
                            dropdown.append('<div class="dropdown-item disabled">Error loading proposals</div>');
                            dropdown.show();
                        }
                    });
                }, 300);
            });

            // Hide dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#proposal, #proposalDropdown').length) {
                    $('#proposalDropdown').hide();
                }
            });

            // Clear form button (optional - you can add this to your HTML)
            function clearProposalForm() {
                $('#proposal').val('');
                $('#proposal_id').val('');
                $('#proposalDropdown').hide();
            }

            // Handle proposal selection
            $('#proposal').on('change', function() {
                const selectedTitle = $(this).val();
                console.log('Selected title:', selectedTitle);
                
                const proposals = window.proposals || [];
                console.log('Available proposals:', proposals);
                
                const selectedProposal = proposals.find(p => p.activity_title === selectedTitle);
                console.log('Found proposal:', selectedProposal);

                if (selectedProposal) {
                    $('#proposal_id').val(selectedProposal.id);
                    console.log('Set proposal ID to:', selectedProposal.id);
                } else {
                    $('#proposal_id').val('');
                    if (selectedTitle) {
                        console.log('No matching proposal found for title:', selectedTitle);
                    }
                }
            });

            // Handle campus change
            $('#campus').on('change', function() {
                const selectedCampus = $(this).val();
                if (selectedCampus) {
                    loadYearOptions();
                } else {
                    $('#year').html('<option value="">Select Year</option>').prop('disabled', true);
                    $('#proposal').val(null).trigger('change').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });

            // Handle year change
            $('#year').on('change', function() {
                const selectedYear = $(this).val();
                if (selectedYear) {
                    $('#proposal').prop('disabled', false).val(null).trigger('change');
                } else {
                    $('#proposal').val(null).trigger('change').prop('disabled', true);
                    $('#proposal_id').val('');
                }
            });

            // Handle dropdown submenu on hover for desktop
            /*
            if (window.matchMedia('(min-width: 992px)').matches) {
                $('.dropdown-submenu').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).children('.dropdown-menu').stop(true, true).fadeToggle(200);
                });
                
                // Close submenus when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.dropdown-submenu').length) {
                        $('.dropdown-submenu .dropdown-menu').fadeOut(200);
                    }
                });
            }
            */
            
            // Handle dropdown submenu click behavior
            document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle the submenu
                    const parentLi = this.parentElement;
                    parentLi.classList.toggle('show');
                    
                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('dropdown-menu')) {
                        if (submenu.style.display === 'block') {
                            submenu.style.display = 'none';
                        } else {
                            submenu.style.display = 'block';
                        }
                    }
                });
            });
            
            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-submenu')) {
                    const openSubmenus = document.querySelectorAll('.dropdown-submenu.show');
                    openSubmenus.forEach(menu => {
                        menu.classList.remove('show');
                        const submenu = menu.querySelector('.dropdown-menu');
                        if (submenu) {
                            submenu.style.display = 'none';
                        }
                    });
                }
            });
        });

        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        campusSelect.empty().append('<option value="">Select Campus</option>');
                        if (response.status === 'success' && response.data) {
                            console.log('Available campuses:', response.data);
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                                }
                            });
                        }
                        campusSelect.prop('disabled', false);
                        
                        // Add a change event listener to the campus dropdown
                        campusSelect.off('change').on('change', function() {
                            console.log("Campus changed to:", $(this).val());
                            // Clear previous report when campus changes
                            $('#reportPreview').empty();
                            loadYearOptions();
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:', error);
                        campusSelect.empty().append('<option value="">Error loading campuses</option>');
                    }
                });
            } else {
                campusSelect.empty().append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true);
                loadYearOptions();
            }
        }

        function loadYearOptions() {
            const yearSelect = $('#year');
            const selectedCampus = $('#campus').val();
            
            yearSelect.prop('disabled', true);
            yearSelect.html('<option value="">Loading years...</option>');
            
            $.ajax({
                url: 'api/get_proposal_years.php',
                method: 'GET',
                data: { campus: selectedCampus },
                dataType: 'json',
                success: function(response) {
                    console.log('Year response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    
                    if (response.status === 'error') {
                        console.error('API Error:', response.message);
                        yearSelect.html(`<option value="">${response.message || 'Error loading years'}</option>`);
                        
                        // Show error to user
                        Swal.fire({
                            icon: 'error',
                            title: 'Error Loading Years',
                            text: response.message || 'Failed to load year data. Please ensure you are logged in.',
                            confirmButtonColor: '#6c757d'
                        });
                        return;
                    }
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        response.data.sort((a, b) => b.year - a.year).forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });
                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.html('<option value="">No years available</option>');
                        
                        // Optional: Display friendly message about no data
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: 'No proposal years found for this campus. You may need to create proposals first.',
                            confirmButtonColor: '#6c757d'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', error);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load years. Please try again.';
                    
                    // Try to parse error message from response if possible
                    try {
                        const responseJson = JSON.parse(xhr.responseText);
                        if (responseJson && responseJson.message) {
                            errorMessage = responseJson.message;
                        }
                    } catch (e) {
                        // Handle case where response is not JSON
                        if (xhr.status === 500) {
                            errorMessage = 'Server error. Please check database connection or contact administrator.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'API endpoint not found. Please check system configuration.';
                        } else if (xhr.status === 0) {
                            errorMessage = 'Network error. Please check your connection.';
                        }
                    }
                    
                    yearSelect.html(`<option value="">Error: ${errorMessage}</option>`);
                    
                    // Show error to user
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Loading Years',
                        text: errorMessage,
                        footer: 'Status Code: ' + xhr.status,
                        confirmButtonColor: '#6c757d'
                    });
                }
            });
        }

        function generateReport() {
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            const selectedProposalId = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedProposalId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields to generate the proposal.'
                });
                return;
            }

            // Show loading state
            $('#reportPreview').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading proposal...</p>
                </div>
            `);
            
            // First, if central user, fetch the campus signatories
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            if (isCentral) {
                console.log("Central user, fetching signatories for campus:", selectedCampus);
                
                // Reset window.campusSignatories
                window.campusSignatories = null;
                
                // Don't block the whole process on the debugging API call
                try {
                    $.ajax({
                        url: 'api/check_campus_names.php',
                        method: 'GET',
                        dataType: 'json',
                        timeout: 5000, // 5 second timeout
                        success: function(response) {
                            console.log("Campus names in database:", response.data);
                            if (response.all_signatories) {
                                console.log("All signatories in database:", response.all_signatories);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error checking campus names:", error);
                            console.error("Response:", xhr.responseText);
                        }
                    });
                } catch (e) {
                    console.error("Exception in check_campus_names call:", e);
                }
                
                // First, fetch the signatories
                $.ajax({
                    url: 'api/get_campus_signatories.php',
                    method: 'GET',
                    data: { campus: selectedCampus },
                    dataType: 'json',
                    timeout: 10000, // 10 second timeout
                    success: function(sigResponse) {
                        console.log("Signatories response:", sigResponse);
                        if (sigResponse.status === 'success') {
                            // Store the signatories in the window object for use in the displayProposal function
                            window.campusSignatories = sigResponse.data;
                            console.log("Successfully set campusSignatories:", window.campusSignatories);
                            
                            // Verify the structure
                            console.log("Signatory name1:", window.campusSignatories.name1);
                            console.log("Signatory name2:", window.campusSignatories.name2);
                            console.log("Signatory name3:", window.campusSignatories.name3);
                            console.log("Signatory name4:", window.campusSignatories.name4);
                        } else {
                            console.error('Error loading signatories:', sigResponse.message);
                            window.campusSignatories = null;
                        }
                        
                        // After fetching signatories (success or failure), fetch the proposal
                        fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error loading signatories:', error);
                        console.error('Response:', xhr.responseText);
                        
                        // Create dummy signatories with "API Error" marker
                        window.campusSignatories = {
                            name1: 'API Error - Check Console',
                            name2: 'API Error - Check Console',
                            name3: 'API Error - Check Console',
                            name4: 'API Error - Check Console',
                            name5: 'API Error - Check Console',
                            campus: selectedCampus
                        };
                        
                        // Even if signatories fetch fails, continue with proposal
                        fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId);
                    }
                });
            } else {
                // Non-central users can directly fetch proposal
                fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId);
            }
        }
        
        function fetchProposalDetails(selectedCampus, selectedYear, selectedProposalId) {
            $.ajax({
                url: 'api/get_proposal_details.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear,
                    proposal_id: selectedProposalId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        displayProposal(response.data);
                    } else {
                        // Handle API error with more details
                        console.error('API Error:', response);
                        $('#reportPreview').html(`
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                <p><strong>Error:</strong> ${response.message || 'Failed to load proposal data'}</p>
                                ${response.code ? `<p><small>Error code: ${response.code}</small></p>` : ''}
                                <p class="mt-3">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify(response)})">
                                        Show Technical Details
                                    </button>
                                </p>
                            </div>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    // Try to parse the response if it's JSON
                    let errorMessage = 'Error loading proposal. Please try again.';
                    let errorDetails = '';
                    
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        if (errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                        errorDetails = JSON.stringify(errorResponse, null, 2);
                    } catch (e) {
                        errorDetails = xhr.responseText || error;
                    }
                    
                    $('#reportPreview').html(`
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <p><strong>Error:</strong> ${errorMessage}</p>
                            <p><small>Status: ${xhr.status} ${status}</small></p>
                            <p class="mt-3">
                                <button class="btn btn-sm btn-outline-secondary" onclick="showDebugInfo(${JSON.stringify({error: errorDetails})})">
                                    Show Technical Details
                                </button>
                            </p>
                        </div>
                    `);
                }
            });
        }
        
        // Debug helper function
        function showDebugInfo(data) {
            Swal.fire({
                title: 'Technical Details',
                html: `<pre style="text-align: left; max-height: 300px; overflow-y: auto;"><code>${JSON.stringify(data, null, 2)}</code></pre>`,
                width: '60%',
                confirmButtonText: 'Close'
            });
        }

        function displayProposal(data) {
            if (!data || !data.sections) {
                $('#reportPreview').html('<p>No proposal data available</p>');
                return;
            }

            const sections = data.sections;
            const now = new Date();
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            const currentTime = now.toLocaleTimeString('en-US', timeOptions);
            
            // Dynamically check the current theme state
            const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            const themeClass = isDarkMode ? 'dark-mode-proposal' : 'light-mode-proposal';
            
            // Get the selected campus
            const selectedCampus = $('#campus').val();
            
            // Fetch signatories for the selected campus when in central mode
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            
            // Log whether we have campus signatories
            if (isCentral) {
                console.log("Central user in displayProposal, campusSignatories:", window.campusSignatories);
            }
            
            // Use theme class without inline styling to allow CSS to control colors
            let html = `
            <div class="proposal-container ${themeClass}" style="margin-top: 0; padding-top: 0;">
                <!-- Header Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
                    <tr>
                        <td style="width: 15%; text-align: center; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 60px;">
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Reference No.: BatStateU-FO-ESO-09
                        </td>
                        <td style="width: 30%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Effectivity Date: August 25, 2023
                        </td>
                        <td style="width: 25%; padding: 10px; border-top: 0.1px solid black; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            Revision No.: 00
                        </td>
                    </tr>
                </table>

                <!-- Title Section -->
                <table style="width: 100%; border-collapse: collapse; margin: 0;">
                    <tr>
                        <td style="text-align: center; padding: 10px; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black;">
                            <strong>GAD PROPOSAL (INTERNAL PROGRAM/PROJECT/ACTIVITY)</strong>
                        </td>
                    </tr>
                </table>

                <!-- Checkbox Section with fixed styling -->
                <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0; border-left: 0.1px solid black; border-right: 0.1px solid black; border-top: 0.1px solid black;">
                    <tr>
                        <td style="padding: 10px 0; border: none; border-bottom: none;">
                            <div style="display: flex; width: 100%; border-bottom: 0.1px solid black; text-align: center;">
                                <div style="flex: 1; padding: 5px 10px;">☐ Program</div>
                                <div style="flex: 1; padding: 5px 10px;">☐ Project</div>
                                <div style="flex: 1; padding: 5px 10px;">☒ Activity</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Main Content -->
                <div style="padding: 20px; border: 0.1px solid black; border-top: none;">
                    <p><strong>I. Title:</strong> ${sections.title || 'N/A'}</p>

                    <p><strong>II. Date and Venue:</strong> ${sections.date_venue.venue || 'N/A'}<br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${sections.date_venue.date || 'N/A'}</p>

                    <p><strong>III. Mode of Delivery:</strong> ${sections.delivery_mode || 'N/A'}</p>

                    <p><strong>IV. Project Team:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Project Leaders:</strong> ${sections.project_team.project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_leaders.responsibilities) 
                                ? sections.project_team.project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Asst. Project Leaders:</strong> ${sections.project_team.assistant_project_leaders.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.assistant_project_leaders.responsibilities)
                                ? sections.project_team.assistant_project_leaders.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.assistant_project_leaders.responsibilities || 'N/A'}</li>`
                            }
                        </ol>

                        <p><strong>Project Staff:</strong> ${sections.project_team.project_staff.names || 'N/A'}</p>
                        <p><strong>Responsibilities:</strong></p>
                        <ol>
                            ${Array.isArray(sections.project_team.project_staff.responsibilities)
                                ? sections.project_team.project_staff.responsibilities.map(resp => `<li>${resp}</li>`).join('')
                                : `<li>${sections.project_team.project_staff.responsibilities || 'N/A'}</li>`
                            }
                        </ol>
                    </div>

                    <p><strong>V. Partner Office/College/Department:</strong> ${sections.partner_offices || 'N/A'}</p>

                    <p><strong>VI. Type of Participants:</strong></p>
                    <div style="text-align: center;">
                        <p><strong>External Type:</strong> ${sections.participants.external_type || 'N/A'}</p>
                        <table style="width: 40%; margin: 0 auto; border-collapse: collapse;">
                            <tr>
                                <th style="border: 0.1px solid black; padding: 5px; width: 30%;"></th>
                                <th style="border: 0.1px solid black; padding: 5px; text-align: center;">Total</th>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Male</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${sections.participants.male || '0'}</td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;">Female</td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;">${sections.participants.female || '0'}</td>
                            </tr>
                            <tr>
                                <td style="border: 0.1px solid black; padding: 5px;"><strong>TOTAL</strong></td>
                                <td style="border: 0.1px solid black; padding: 5px; text-align: center;"><strong>${sections.participants.total || '0'}</strong></td>
                            </tr>
                        </table>
                    </div>

                    <p><strong>VII. Rationale/Background:</strong><br>
                    ${sections.rationale || 'N/A'}</p>

                    <p><strong>VIII. Objectives:</strong></p>
                    <p><strong>General:</strong> ${sections.objectives.general || 'N/A'}</p>
                    <p><strong>Specific Objectives:</strong></p>
                    <ol>
                        ${(Array.isArray(sections.objectives.specific)) 
                            ? sections.objectives.specific.map(obj => `<li>${obj}</li>`).join('')
                            : `<li>${sections.objectives.specific || 'N/A'}</li>`
                        }
                    </ol>

                    <p><strong>IX. Description, Strategies, and Methods:</strong></p>
                    <p><strong>Description:</strong></p>
                    <div style="margin-left: 20px;">
                        <p>${sections.description || 'N/A'}</p>
                    </div>
                    
                    <p><strong>Strategies:</strong></p>
                    <ol style="margin-left: 20px;">
                        ${(Array.isArray(sections.strategies)) 
                            ? sections.strategies.map(strat => `<li>${strat}</li>`).join('')
                            : `<li>${sections.strategies || 'N/A'}</li>`
                        }
                    </ol>
                    
                    <p><strong>Methods (Activities / Schedule):</strong></p>
                    <ul>
                        ${(Array.isArray(sections.methods)) 
                            ? sections.methods.map((method, index) => {
                                if (Array.isArray(method) && method.length > 1) {
                                    const activityName = method[0];
                                    const details = method[1];
                                    if (Array.isArray(details)) {
                                        return `
                                            <li>
                                                <strong>${activityName}</strong>
                                                <ul>
                                                    ${details.map(detail => `<li>${detail}</li>`).join('')}
                                                </ul>
                                            </li>
                                        `;
                                    } else {
                                        return `<li><strong>${activityName}</strong>: ${details}</li>`;
                                    }
                                } else {
                                    return `<li>${method}</li>`;
                                }
                            }).join('')
                            : `<li>${sections.methods || 'N/A'}</li>`
                        }
                    </ul>
                    
                    <p><strong>Materials Needed:</strong></p>
                    <ul>
                        ${(Array.isArray(sections.materials)) 
                            ? sections.materials.map(material => `<li>${material}</li>`).join('')
                            : `<li>${sections.materials || 'N/A'}</li>`
                        }
                    </ul>

                    <p><strong>X. Work Plan (Timeline of Activities/Gantt Chart):</strong></p>
                    ${(Array.isArray(sections.workplan) && sections.workplan.length > 0) ? (() => {
                        // Extract all dates from workplan
                        const allDates = [];
                        sections.workplan.forEach(item => {
                            if (Array.isArray(item) && item.length > 1 && Array.isArray(item[1])) {
                                item[1].forEach(date => {
                                    if (!allDates.includes(date)) {
                                        allDates.push(date);
                                    }
                                });
                            }
                        });
                        
                        // Sort dates
                        allDates.sort();
                        
                        // Generate table
                        return `
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <th style="border: 0.1px solid black; padding: 5px; width: 40%;">Activities</th>
                                    ${allDates.map(date => 
                                        `<th style="border: 0.1px solid black; padding: 5px; text-align: center;">${date}</th>`
                                    ).join('')}
                                </tr>
                                ${sections.workplan.map(item => {
                                    if (Array.isArray(item) && item.length > 1) {
                                        const activity = item[0];
                                        const dates = Array.isArray(item[1]) ? item[1] : [item[1]];
                                        
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${activity || 'N/A'}</td>
                                                ${allDates.map(date => {
                                                    const isScheduled = dates.includes(date);
                                                    return `<td style="border: 0.1px solid black; padding: 5px; text-align: center; background-color: ${isScheduled ? 'black' : 'white'};"></td>`;
                                                }).join('')}
                                            </tr>
                                        `;
                                    } else {
                                        return `
                                            <tr>
                                                <td style="border: 0.1px solid black; padding: 5px;">${item || 'N/A'}</td>
                                                ${allDates.map(() => 
                                                    `<td style="border: 0.1px solid black; padding: 5px;"></td>`
                                                ).join('')}
                                            </tr>
                                        `;
                                    }
                                }).join('')}
                            </table>
                        `;
                    })() : `
                        <p style="font-style: italic;">No workplan data available</p>
                    `}

                    <p><strong>XI. Financial Requirements and Source of Funds:</strong></p>
                    <div style="margin-left: 20px;">
                        <p><strong>Source of Funds:</strong> ${sections.financial.source || 'N/A'}</p>
                        <p><strong>Total Budget:</strong> ₱${parseFloat(sections.financial.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>

                    <p><strong>XII. Monitoring and Evaluation Mechanics / Plan:</strong></p>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10pt;">
                        <tr>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Objectives</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Indicators</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Baseline Data</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Performance Target</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 12%;">Data Source</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 13%;">Collection Method</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 10%;">Frequency</th>
                            <th style="border: 0.1px solid black; padding: 3px; word-break: break-word; width: 14%;">Responsible</th>
                        </tr>
                        ${(Array.isArray(sections.monitoring_evaluation)) 
                            ? sections.monitoring_evaluation.map(item => {
                                if (Array.isArray(item) && item.length >= 8) {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[0] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[1] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[2] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[3] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[4] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[5] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[6] || 'N/A'}</td>
                                            <td style="border: 0.1px solid black; padding: 3px; vertical-align: top; word-break: break-word;">${item[7] || 'N/A'}</td>
                                        </tr>
                                    `;
                                } else {
                                    return `
                                        <tr>
                                            <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;" colspan="8">Invalid monitoring item</td>
                                        </tr>
                                    `;
                                }
                            }).join('')
                            : `
                                <tr>
                                    <td style="border: 0.1px solid black; padding: 3px; word-break: break-word;" colspan="8">No monitoring data available</td>
                                </tr>
                            `
                        }
                    </table>

                    <p><strong>XIII. Sustainability Plan:</strong></p>
                    <div style="margin-left: 20px;">
                        <p>${sections.sustainability || 'No sustainability plan data available.'}</p>
                        
                        ${(Array.isArray(sections.specific_plans) && sections.specific_plans.length > 0) 
                            ? `<ol>${sections.specific_plans.map(item => `<li>${item}</li>`).join('')}</ol>`
                            : ``
                        }
                    </div>
                </div>

                <!-- Signatures Section - Now outside the main content div -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 0;">
                    <tr>
                        <td style="width: 50%; border-top: 0.1px solid black; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black; padding: 10px;">
                            <p style="margin: 0;">Prepared by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center;"><strong>${(() => {
                                if (isCentral && window.campusSignatories) {
                                    console.log('Using campus signatory name1:', window.campusSignatories.name1);
                                    return window.campusSignatories.name1 || 'N/A';
                                } else {
                                    console.log('Using PHP signatory name1: <?php echo json_encode($signatories['name1'] ?? 'N/A'); ?>');
                                    return `<?php echo htmlspecialchars($signatories['name1'] ?? 'N/A'); ?>`;
                                }
                            })()}</strong></p>
                            <p style="margin: 0; text-align: center;">GAD Head Secretariat</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_____________</p>
                        </td>
                        <td style="width: 50%; border-top: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black; padding: 10px;">
                            <p style="margin: 0;">Reviewed by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center;"><strong>${(() => {
                                if (isCentral && window.campusSignatories) {
                                    console.log('Using campus signatory name2:', window.campusSignatories.name2);
                                    return window.campusSignatories.name2 || 'N/A';
                                } else {
                                    console.log('Using PHP signatory name2: <?php echo json_encode($signatories['name2'] ?? 'N/A'); ?>');
                                    return `<?php echo htmlspecialchars($signatories['name2'] ?? 'N/A'); ?>`;
                                }
                            })()}</strong></p>
                            <p style="margin: 0; text-align: center;">Head, Extension Services</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_____________</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black; padding: 10px;">
                            <p style="margin: 0;">Recommending Approval:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center;"><strong>${(() => {
                                if (isCentral && window.campusSignatories) {
                                    console.log('Using campus signatory name3:', window.campusSignatories.name3);
                                    return window.campusSignatories.name3 || 'N/A';
                                } else {
                                    console.log('Using PHP signatory name3: <?php echo json_encode($signatories['name3'] ?? 'N/A'); ?>');
                                    return `<?php echo htmlspecialchars($signatories['name3'] ?? 'N/A'); ?>`;
                                }
                            })()}</strong></p>
                            <p style="margin: 0; text-align: center;">Vice Chancellor for Research, Development and Extension Services</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_____________</p>
                        </td>
                        <td style="width: 50%; border-right: 0.1px solid black; border-bottom: 0.1px solid black; padding: 10px;">
                            <p style="margin: 0;">N/A</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center; border-left: 0.1px solid black; border-right: 0.1px solid black; border-bottom: 0.1px solid black; padding: 10px;">
                            <p style="margin: 0;">Approved by:</p>
                            <br><br><br>
                            <p style="margin: 0; text-align: center;"><strong>${(() => {
                                if (isCentral && window.campusSignatories) {
                                    console.log('Using campus signatory name4:', window.campusSignatories.name4);
                                    return window.campusSignatories.name4 || 'N/A';
                                } else {
                                    console.log('Using PHP signatory name4: <?php echo json_encode($signatories['name4'] ?? 'N/A'); ?>');
                                    return `<?php echo htmlspecialchars($signatories['name4'] ?? 'N/A'); ?>`;
                                }
                            })()}</strong></p>
                            <p style="margin: 0; text-align: center;">Chancellor</p>
                            <p style="margin: 0; text-align: center; border: none;">Date Signed:_____________</p>
                        </td>
                    </tr>
                </table>`;

            $('#reportPreview').html(html);
        }

        function printReport() {
            // Create a print window with a specific title
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            
            // Set window properties immediately to prevent about:blank
            printWindow.document.open();
            printWindow.document.title = "GAD Proposal";
            
            let reportContent = $('#reportPreview').html();
            
            // SPECIAL FIX: Remove any empty divs or spaces that might cause empty boxes
            reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
            reportContent = reportContent.replace(/<pre[\s\S]*?<\/pre>/g, '');
            
            // Always force print to be in light mode for consistent output
            const printStyles = `
                <style>
                    body {
                        background-color: white !important;
                        color: black !important;
                    }
                    
                    .proposal-container, .dark-mode-proposal, .light-mode-proposal {
                        background-color: white !important;
                        color: black !important;
                        border: none !important;
                    }
                    
                    .proposal-container p, .proposal-container div, .proposal-container td, 
                    .proposal-container th, .proposal-container li, .proposal-container ol,
                    .proposal-container ul, .proposal-container strong,
                    .dark-mode-proposal p, .dark-mode-proposal div, .dark-mode-proposal td, 
                    .dark-mode-proposal th, .dark-mode-proposal li, .dark-mode-proposal ol,
                    .dark-mode-proposal ul, .dark-mode-proposal strong,
                    .light-mode-proposal p, .light-mode-proposal div, .light-mode-proposal td, 
                    .light-mode-proposal th, .light-mode-proposal li, .light-mode-proposal ol,
                    .light-mode-proposal ul, .light-mode-proposal strong {
                        background-color: white !important;
                        color: black !important;
                        border-color: black !important;
                    }
                    
                    /* Ensure dark cells in workplan are visible in print */
                    table td[style*="background-color: black"],
                    table td[style*="background-color: #9c27b0"] {
                        background-color: black !important;
                    }
                </style>
            `;
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GAD Proposal</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    ${printStyles}
                    <style>
                        @page {
                            size: 8.5in 13in;
                            margin-top: 1.52cm;
                            margin-bottom: 2cm;
                            margin-left: 1.78cm;
                            margin-right: 2.03cm;
                        }
                        
                        body {
                            font-family: 'Times New Roman', Times, serif;
                            font-size: 12pt;
                            line-height: 1.2;
                            color: black;
                            background: white;
                            margin: 0;
                            padding: 0;
                            border: none;
                            box-sizing: border-box;
                        }
                        
                        /* Specific style for Cc: GAD Central */
                        p.cc-gad, [style*="Cc: GAD Central"], [style*="cc: GAD Central"] {
                            border: none !important;
                            border-bottom: none !important;
                            border-top: none !important;
                            text-decoration: none !important;
                        }
                        
                        /* Make sure the cell containing Cc: GAD Central has no border */
                        td.cc-gad-cell, tr.cc-gad-row td {
                            border: none !important;
                            border-bottom: none !important;
                            border-top: none !important;
                        }
                        
                        /* Make table borders 1px instead of 3px */
                        table, th, td {
                            border-width: 1px !important;
                            border-color: black !important;
                        }
                        
                        /* Exceptions for no-border elements */
                        .no-border, .no-border td, .no-border th {
                            border: none !important;
                        }
                        
                        /* Container styles */
                        .proposal-container {
                            width: 100%;
                            margin: 0;
                            padding: 0;
                            /* Remove border */
                            border: none;
                            background: white;
                            box-sizing: border-box;
                        }
                        
                        /* Remove any empty boxes that might appear at the bottom */
                        body::after {
                            display: none !important;
                            content: none !important;
                        }
                        
                        div:empty {
                            display: none !important;
                        }
                        
                        /* Table styles */
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin: 0;
                            padding: 0;
                        }

                        /* Cell styles */
                        td, th {
                            border: 1px solid black;
                            padding: 4px 6px;
                            vertical-align: top;
                            margin: 0;
                        }
                        
                        /* Remove double borders between cells */
                        tr td, tr th {
                            border-right: none;
                            border-left: none;
                        }
                        
                        tr td:first-child, tr th:first-child {
                            border-left: 0.1px solid black;
                        }
                        
                        tr td:last-child, tr th:last-child {
                            border-right: 0.1px solid black;
                        }
                        
                        /* Inner tables */
                        .inner-table {
                            border: 0.1px solid black;
                            margin: 4px 0;
                        }
                        
                        .inner-table th,
                        .inner-table td {
                            border: 0.1px solid black;
                            padding: 4px 6px;
                        }
                        
                        /* Header section */
                        .header-section table,
                        .title-table,
                        .checkbox-table {
                            border: none;
                            margin: 0;
                            padding: 0;
                        }
                        
                        .logo-cell {
                            width: 15%;
                            text-align: center;
                            vertical-align: middle;
                            border: 0.1px solid black !important;
                            padding: 4px !important;
                        }
                        
                        .logo-cell img {
                            width: 60px;
                            height: auto;
                        }
                        
                        /* Section headers */
                        .section-header {
                            background-color: #f8f9fa !important;
                            font-weight: bold;
                            text-align: center;
                            -webkit-print-color-adjust: exact !important;
                            print-color-adjust: exact !important;
                            padding: 4px 6px;
                            border-top: 0.1px solid black;
                            border-bottom: 0.1px solid black;
                        }
                        
                        /* Remove spacing between sections */
                        .section-separator {
                            border-top: none;
                        }
                        
                        /* Content sections */
                        .content-table {
                            margin: 0;
                            padding: 0;
                            width: 100%;
                        }
                        
                        .value-cell, .value-cell-full {
                            padding: 4px 6px;
                        }
                        
                        .value-cell p, .value-cell-full p {
                            margin: 2px 0;
                        }
                        
                        /* Signatures section */
                        .signatures-table {
                            border: 0.1px solid black;
                            margin: 0;
                        }
                        
                        .signature-cell {
                            border: 0.1px solid black !important;
                            width: 25%;
                            text-align: center;
                            padding: 8px 6px;
                            vertical-align: top;
                        }
                        
                        .signature-line {
                            border-bottom: 0.1px solid black;
                            width: 80%;
                            margin: 20px auto 4px;
                        }
                        
                        .signature-name {
                            margin: 2px 0;
                            font-weight: bold;
                        }
                        
                        .signature-position {
                            margin: 2px 0;
                            font-style: italic;
                        }
                        
                        /* Lists */
                        ul {
                            margin: 2px 0 2px 20px;
                            padding: 0;
                        }
                        
                        li {
                            margin-bottom: 2px;
                        }
                        
                        /* When printing */
                        @media print {
                            body {
                                margin: 0;
                                padding: 0;
                            }
                            
                            .proposal-container {
                                margin: 0;
                                padding: 0;
                            }
                            
                            .no-print {
                                display: none;
                            }
                            
                            td, th {
                                padding: 4px 6px;
                            }
                            
                            .section-header {
                                padding: 4px 6px;
                            }
                            
                            .signature-cell {
                                padding: 8px 6px;
                            }
                            
                            /* Force background colors */
                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                            
                            /* First page footer */
                            @page:first {
                                margin-bottom: 3cm !important;
                                @bottom-left {
                                    content: "Tracking Number___________________";
                                    font-family: 'Times New Roman', Times, serif;
                                    font-size: 12pt;
                                    position: fixed;
                                    bottom: 1cm;
                                    left: 2cm;
                                    margin-left: 2cm;
                                }
                                @bottom-right {
                                    content: "Page 1 of " counter(pages);
                                    font-family: 'Times New Roman', Times, serif;
                                    font-size: 12pt;
                                    position: fixed;
                                    bottom: 1cm;
                                    right: 2cm;
                                    margin-right: 2cm;
                                }
                                @bottom {
                                    content: "";
                                    display: block;
                                    border-top: 5px solid black !important;
                                    margin-bottom: 2cm;
                                    width: 80%;
                                    position: fixed;
                                    bottom: 2cm;
                                    height: 5px;
                                    margin-left: 2cm;
                                    margin-right: 2cm;
                                    left: 2cm;
                                    right: 2cm;
                                }
                            }
                            
                            /* All other pages - show page numbers and line */
                            @page {
                                margin-bottom: 3cm !important;
                                border: 1px solid black !important; /* Add border to each page */
                                @bottom-right {
                                    content: "Page " counter(page) " of " counter(pages);
                                    font-family: 'Times New Roman', Times, serif;
                                    font-size: 12pt;
                                    position: fixed;
                                    bottom: 1cm;
                                    right: 2cm;
                                    margin-right: 2cm;
                                }
                                @bottom {
                                    content: "";
                                    display: block;
                                    border-top: 5px solid black !important;
                                    margin-bottom: 2cm;
                                    width: 80%;
                                    position: fixed;
                                    bottom: 2cm;
                                    height: 5px;
                                    margin-left: 2cm;
                                    margin-right: 2cm;
                                    left: 2cm;
                                    right: 2cm;
                                }
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="WordSection1">
                        ${reportContent}
                    </div>
                    
                    <!-- Footer with tracking number and page numbers -->
                    <div style="mso-element:footer" id="f1">
                        <table width="100%" style="border: none; border-collapse: collapse;">
                            <tr>
                                <td style="border: none; padding-top: 5px;">
                                    <p style="margin: 0;"></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                // Add event listener to close the window after printing is complete
                printWindow.addEventListener('afterprint', function() {
                    printWindow.close();
                });
            }, 250);
        }

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
            
            // Update the proposal preview if it exists
            if (document.getElementById('reportPreview').innerHTML.trim() !== '') {
                // If there's a proposal selected, re-run the generation to apply the new theme
                if ($('#proposal_id').val()) {
                    loadSelectedProposal();
                }
            }
        }

        // Function to reload the current proposal with the current theme
        function loadSelectedProposal() {
            const proposalId = $('#proposal_id').val();
            const selectedCampus = $('#campus').val();
            const selectedYear = $('#year').val();
            
            if (proposalId && selectedCampus && selectedYear) {
                fetchProposalDetails(selectedCampus, selectedYear, proposalId);
            }
        }

        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);
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
                    }, 10); // Changed from 50 to 10 - make it super fast
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            
            // Update date
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            
            // Update time - remove seconds and use numeric hour format
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        function exportToWord() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();
            const selectedProposalId = $('#proposal_id').val();

            if (!selectedCampus || !selectedYear || !selectedProposalId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select all required fields: campus, year, and proposal.'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Exporting to Word',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                // Get report content
                let reportContent = $('#reportPreview').html();
                
                // Process the report content to be Word-friendly
                // Fix checkboxes to display in one row
                reportContent = reportContent.replace(/<input[^>]*type=["']checkbox["'][^>]*>/g, 
                    '<span style="font-family:Wingdings; font-size:12pt;">☐</span>');
                
                // Fix any empty divs
                reportContent = reportContent.replace(/<div[^>]*>\s*<\/div>/g, '');
                
                // Create a Word document with proper formatting
                const html = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office"
                          xmlns:w="urn:schemas-microsoft-com:office:word"
                          xmlns="http://www.w3.org/TR/REC-html40">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                        <title>GAD Proposal</title>
                        <style>
                            @page {
                                mso-page-border-surround-header: yes;
                                mso-page-border-surround-footer: yes;
                                mso-page-border-display: yes;
                            }
                            
                            @page Section1 {
                                size: 8.5in 13in;
                                margin: 1.52cm 2.03cm 3cm 1.78cm;
                                mso-header-margin: 1.27cm;
                                mso-footer-margin: 1.5cm;
                                mso-footer: f1;
                                mso-paper-source: 0;
                                mso-border-top: solid black 1.0pt;
                                mso-border-left: solid black 1.0pt;
                                mso-border-right: solid black 1.0pt;
                                mso-border-bottom: solid black 1.0pt;
                            }
                            
                            div.Section1 {
                                page: Section1;
                            }
                            
                            body {
                                font-family: 'Times New Roman', serif;
                                font-size: 12pt;
                            }
                            
                            table {
                                border-collapse: collapse;
                                width: 100%;
                            }
                            
                            td, th {
                                border: 1px solid black;
                                padding: 4pt 6pt;
                                vertical-align: top;
                            }
                            
                            /* Footer styling */
                            p.MsoFooter {
                                margin: 0;
                                font-family: 'Times New Roman', serif;
                                font-size: 12pt;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="Section1">
                            ${reportContent}
                        </div>
                        
                        <div style="mso-element:footer" id="f1">
                            <hr align="center" width="100%" size="5" color="black" />
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:none;">
                                <tr>
                                    <td width="50%" style="border:none; padding:5px 0 0 0;">
                                        <p class="MsoFooter">Tracking Number</p>
                                    </td>
                                    <td width="50%" align="right" style="border:none; padding:5px 0 0 0;">
                                        <p class="MsoFooter">Page <span style="mso-field-code:PAGE"></span> of <span style="mso-field-code:NUMPAGES"></span></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="border:none; padding:0;">
                                        <p class="MsoFooter">Page <span style="mso-field-code:PAGE"></span> of <span style="mso-field-code:NUMPAGES"></span></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </body>
                    </html>
                `;

                // Create a blob and trigger download
                const blob = new Blob([html], { type: 'application/msword' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `GAD_Proposal_${selectedCampus}_${selectedYear}.doc`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                Swal.close();
            } catch (error) {
                console.error('Word export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export to Word. Please try again.'
                });
            }
        }
    </script>
</body>
</html>