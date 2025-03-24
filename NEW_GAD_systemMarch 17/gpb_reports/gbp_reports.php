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
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PPAS Forms - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <!-- Add XLSX library for Excel export -->
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <!-- Add html2pdf library for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            --dark-bg: #212529;
            --dark-input: #2b3035;
            --dark-text: #e9ecef;
            --dark-border: #495057;
            --dark-sidebar: #2d2d2d;
            --table-header-bg: #343a40;
            --table-subheader-bg: #2b3035;
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

/* Print Styles */
@media print {
    @page {
        size: landscape;
        margin: 0;
    }

    /* Remove browser headers/footers */
    @page :first {
        margin-top: 0;
        margin-bottom: 0;
    }

    @page :left {
        margin-left: 0;
    }

    @page :right {
        margin-right: 0;
    }

    /* Hide all default headers/footers */
    html {
        margin: 0 !important;
        padding: 0 !important;
    }

    body {
        margin: 1cm !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        overflow: visible !important;
    }

    /* Hide scrollbars when printing */
    body::-webkit-scrollbar,
    .main-content::-webkit-scrollbar,
    #reportPreview::-webkit-scrollbar,
    .table-responsive::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    /* Hide browser-generated content */
    @top-left, @top-center, @top-right, @bottom-left, @bottom-center, @bottom-right {
        content: none !important;
    }

    /* Table styles */
    .table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 10pt !important;
        overflow: visible !important;
    }
    
    .table-responsive {
        overflow: visible !important;
    }

    /* Headers on first page only */
    .table thead {
        display: none !important;
    }

    .table tbody:first-of-type thead {
        display: table-header-group !important;
    }

    /* Style first page headers */
    .table tbody:first-of-type tr:first-child th {
        background-color: #e9ecef !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Hide headers on subsequent pages */
    .table tbody:not(:first-of-type) thead {
        display: none !important;
    }

    /* Hide the date/time and report title */
    .print-header {
        display: none !important;
    }

    /* Basic table cell styles */
    .table th, .table td {
        border: 1px solid black !important;
        padding: 4px !important;
        vertical-align: middle !important;
    }

    /* Category headers */
    .category-header {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-weight: bold !important;
        text-align: center !important;
    }

    /* Hide unnecessary elements */
    .sidebar, .btn-group, .page-title, .mobile-nav-toggle, 
    .theme-switch-button, .datetime-container, .card-title {
        display: none !important;
    }

    /* Content area */
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        overflow: visible !important;
    }

    #reportPreview {
        overflow: visible !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }

    .card-body {
        padding: 0 !important;
        overflow: visible !important;
    }

    /* Prevent row breaks */
    tr {
        page-break-inside: avoid !important;
    }

    /* Report header */
    .report-header {
        page-break-after: avoid !important;
    }

    /* Hide about:blank text */
    title {
        display: none !important;
    }
}

/* Adjust form spacing */
#reportForm .form-group {
    margin-bottom: 0.5rem;
}

/* Adjust table spacing */
.table td, .table th {
    padding: 0.5rem;
}

/* Make preview area more compact */
#reportPreview {
    margin-top: 0.5rem;
}

/* Adjust button group spacing */
.btn-group {
    gap: 0.5rem;
}

/* Dropdown submenu styles */
.dropdown-submenu {
    position: relative;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    left: 100%;
    margin-top: -1px;
    display: none;
}

.dropdown-submenu:hover > .dropdown-menu {
    display: block;
}

/* Arrow for submenu */
.dropdown-submenu > a::after {
    display: block;
    content: "";
    float: right;
    width: 0;
    height: 0;
    border-color: transparent;
    border-style: solid;
    border-width: 5px 0 5px 5px;
    border-left-color: #cccccc;
    margin-top: 5px;
    margin-right: -10px;
}

/* Dark mode submenu styles */
[data-bs-theme="dark"] .dropdown-submenu > a::after {
    border-left-color: #666;
}

[data-bs-theme="dark"] .dropdown-submenu:hover > a::after {
    border-left-color: #fff;
}

.dropdown-menu .dropdown-submenu-left {
    right: 100%;
    left: auto;
}

/* Ensure submenus are visible in mobile view */
@media (max-width: 991px) {
    .dropdown-submenu .dropdown-menu {
        position: static;
        margin-left: 15px;
    }
}

/* Dark mode report preview styles */
[data-bs-theme="dark"] #reportPreview {
    color: var(--dark-text);
}

[data-bs-theme="dark"] #reportPreview .report-header {
    color: var(--dark-text);
}

[data-bs-theme="dark"] #reportPreview .table {
    color: var(--dark-text);
    border-color: var(--dark-border);
}

[data-bs-theme="dark"] #reportPreview .table th {
    background-color: var(--table-header-bg) !important;
    color: var(--dark-text);
    border-color: var(--dark-border) !important;
}

[data-bs-theme="dark"] #reportPreview .table td {
    background-color: var(--dark-sidebar);
    color: var(--dark-text);
    border-color: var(--dark-border) !important;
}

[data-bs-theme="dark"] #reportPreview .category-header {
    background-color: var(--table-subheader-bg) !important;
    color: var(--dark-text);
    border-color: var(--dark-border) !important;
}

[data-bs-theme="dark"] #reportPreview .report-footer {
    color: var(--dark-text);
}

[data-bs-theme="dark"] #reportPreview hr {
    border-color: var(--dark-border);
}

[data-bs-theme="dark"] #reportPreview .text-end,
[data-bs-theme="dark"] #reportPreview .text-center {
    color: var(--dark-text);
}

/* Style for total row in dark mode */
[data-bs-theme="dark"] #reportPreview .table tr:last-child td {
    background-color: var(--table-header-bg);
    color: var(--dark-text);
    font-weight: bold;
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
                        <li><a class="dropdown-item" href="../academic_wrank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="../personnel_list/personnel_list.php">Personnel List</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="../gbp_forms/gpb.php">GPB Form</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="../ppas_form/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">Create Proposal</a></li>
                                <li><a class="dropdown-item" href="../ppas_proposal/proposal_list.php">View All Proposals</a></li>
                                <li><a class="dropdown-item" href="../narrative/narrative.php">Narrative Report</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i> Reports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">GPB Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
                        <li><a class="dropdown-item" href="../ppas_report/ppas_report.php">Quarterly Reports</a></li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- Bottom controls -->
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
            <i class="fas fa-file-alt"></i>
            <h2>GPB Reports</h2>
        </div>

        <!-- Report Generation Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="reportForm">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="campus">Campus</label>
                                <select class="form-control" id="campus" required>
                                    <option value="">Select Campus</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select class="form-control" id="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-print"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Preview -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title">Report Preview</h5>
                    <div class="btn-group">
                        <button class="btn btn-outline-primary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button class="btn btn-outline-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button class="btn btn-outline-info" onclick="exportToWord()">
                            <i class="fas fa-file-word"></i> Word
                        </button>
                        <button class="btn btn-outline-danger" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                <div id="reportPreview" class="table-responsive">
                    <!-- Report content will be loaded here -->
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <p>Select a campus and year to generate the report</p>
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
                loadReport();
            });

            // Handle dropdown submenu on hover for desktop
            if (window.matchMedia('(min-width: 992px)').matches) {
                $('.dropdown-submenu').hover(
                    function() {
                        $(this).children('.dropdown-menu').stop(true, true).fadeIn(200);
                    },
                    function() {
                        $(this).children('.dropdown-menu').stop(true, true).fadeOut(200);
                    }
                );
            }

            // Handle dropdown submenu on click for mobile
            $('.dropdown-submenu > a').on('click', function(e) {
                if (window.matchMedia('(max-width: 991px)').matches) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).next('.dropdown-menu').toggle();
                }
            });

            // Close other submenus when opening a new one
            $('.dropdown-submenu').on('show.bs.dropdown', function() {
                $('.dropdown-submenu .dropdown-menu').not($(this).children('.dropdown-menu')).hide();
            });
        });

        function loadCampusOptions() {
            const campusSelect = $('#campus');
            campusSelect.prop('disabled', true);
            
            // Remove any existing change handlers to prevent duplicates
            campusSelect.off('change');
            
            // Check if user is Central or regular user
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const userCampus = "<?php echo $userCampus ?>";
            
            if (isCentral) {
                // Default list of all BatStateU campuses to ensure all are shown
                const allCampuses = [
                    "Alangilan", 
                    "Pablo Borbon", 
                    "ARASOF-Nasugbu", 
                    "Balayan", 
                    "Lemery", 
                    "Lipa", 
                    "Lobo", 
                    "Mabini", 
                    "Malvar", 
                    "Rosario", 
                    "San Juan"
                ];
                
                // Central users can see all campuses via API
                $.ajax({
                    url: 'api/get_campuses.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log('Campus response:', response);
                        if (response.status === 'success' && response.data) {
                            // Empty the select and add the placeholder
                            campusSelect.empty().append('<option value="">Select Campus</option>');
                            
                            // Track campuses from API response
                            const apiCampuses = [];
                            
                            // Add campuses from API response first
                            response.data.forEach(function(campus) {
                                if (campus.name && campus.name !== 'null' && campus.name !== 'Default Campus') {
                                    campusSelect.append(`<option value="${campus.name}">${campus.name}</option>`);
                                    apiCampuses.push(campus.name);
                                }
                            });
                            
                            // Add any missing campuses from our default list
                            allCampuses.forEach(function(campus) {
                                if (!apiCampuses.includes(campus)) {
                                    campusSelect.append(`<option value="${campus}">${campus}</option>`);
                                }
                            });
                        } else {
                            // API failed, use our default list
                            console.error('Failed to load campuses from API, using default list');
                            campusSelect.empty().append('<option value="">Select Campus</option>');
                            
                            // Add all campuses from our default list
                            allCampuses.forEach(function(campus) {
                                campusSelect.append(`<option value="${campus}">${campus}</option>`);
                            });
                        }
                        
                        // Add change event handler
                        campusSelect.on('change', function() {
                            const selectedCampus = $(this).val();
                            const yearSelect = $('#year');
                            
                            // Clear and disable year dropdown
                            yearSelect.empty().append('<option value="">Select Year</option>');
                            yearSelect.prop('disabled', true);
                            
                            if (selectedCampus) {
                                loadYearOptions();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading campuses:');
                        console.error('Status:', status);
                        console.error('Error:', error);
                        
                        // On error, use our default list
                        console.log('Using fallback campus list');
                        campusSelect.empty().append('<option value="">Select Campus</option>');
                        
                        // Add all campuses from our default list
                        allCampuses.forEach(function(campus) {
                            campusSelect.append(`<option value="${campus}">${campus}</option>`);
                        });
                        
                        // Add change event handler
                        campusSelect.on('change', function() {
                            const selectedCampus = $(this).val();
                            const yearSelect = $('#year');
                            
                            // Clear and disable year dropdown
                            yearSelect.empty().append('<option value="">Select Year</option>');
                            yearSelect.prop('disabled', true);
                            
                            if (selectedCampus) {
                                loadYearOptions();
                            }
                        });
                    },
                    complete: function() {
                        campusSelect.prop('disabled', false);
                    }
                });
            } else {
                // Non-central users can only see their own campus
                campusSelect.empty();
                campusSelect.append(`<option value="${userCampus}" selected>${userCampus}</option>`);
                campusSelect.prop('disabled', true); // Disable selection for non-central users
                
                // Automatically load years for the user's campus
                loadYearOptions();
            }
        }

        function loadYearOptions() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const yearSelect = $('#year');
            
            // Show loading state
            yearSelect.prop('disabled', true);
            yearSelect.empty().append('<option value="">Loading years...</option>');
            
            // Debug log
            console.log('Loading years for campus:', selectedCampus);
            
            $.ajax({
                url: 'api/get_years.php',
                method: 'GET',
                data: { campus_id: selectedCampus }, // Fixed parameter name to match API expectation
                dataType: 'json',
                success: function(response) {
                    console.log('Years response:', response);
                    yearSelect.empty().append('<option value="">Select Year</option>');
                    
                    if (response.status === 'success' && response.data && response.data.length > 0) {
                        // Sort years in descending order
                        const years = response.data.sort((a, b) => {
                            const yearA = parseInt(a.year);
                            const yearB = parseInt(b.year);
                            return yearB - yearA;
                        });
                        
                        years.forEach(function(yearData) {
                            yearSelect.append(`<option value="${yearData.year}">${yearData.year}</option>`);
                        });
                        
                        yearSelect.prop('disabled', false);
                    } else {
                        yearSelect.empty().append('<option value="">No years available</option>');
                        yearSelect.prop('disabled', true);
                        
                        // Inform the user
                        Swal.fire({
                            icon: 'info',
                            title: 'No Data Available',
                            text: `No report data is available for ${selectedCampus} campus. Please select a different campus or contact the administrator to add data.`
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading years:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    yearSelect.empty().append('<option value="">Error loading years</option>');
                    yearSelect.prop('disabled', true);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load available years. Please try again or contact support.'
                    });
                }
            });
        }

        function loadReport() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            var selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            var selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
                });
                return;
            }

            console.log('Loading report for campus:', selectedCampus, 'year:', selectedYear);

            $.ajax({
                url: 'api/get_gpb_report.php',
                method: 'GET',
                data: {
                    campus: selectedCampus,
                    year: selectedYear
                },
                success: function(response) {
                    console.log('Report response:', response);
                    
                    if (response.status === 'success' && response.data) {
                        // Process and display the report data
                        displayReport(response.data);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to load report data'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Report error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    
                    let errorMessage = 'Failed to load the report. Please try again.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        console.error('Failed to parse error response:', e);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                }
            });
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Define categories for GAD activities
        const allCategories = [
            'Client-Focused',
            'Organization-Focused',
            'Attributed Programs'
        ];

        function displayReport(data) {
            const preview = $('#reportPreview');
            
            // Create report HTML
            let html = `
                <div class="report-header text-center mb-4">
                    <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="width: 100px; height: 100px; margin-bottom: 10px;">
                    <div class="header-text" style="line-height: 1.6;">
                        <p class="mb-0" style="font-size: 14pt;">Republic of the Philippines</p>
                        <p class="mb-0" style="font-size: 16pt; font-weight: bold;">BATANGAS STATE UNIVERSITY</p>
                        <p class="mb-0" style="font-size: 14pt;">The National Engineering University</p>
                        <p class="mb-0" style="font-size: 14pt;">Batangas City</p>
                    </div>
                    <div style="margin-top: 20px;">
                        <hr style="border: none; border-top: 3px solid black; width: 100%; margin: 15px auto;">
                        <h4 style="font-size: 16pt; font-weight: bold;">ANNUAL GENDER AND DEVELOPMENT (GAD) PLAN AND BUDGET</h4>
                        <h5 style="font-size: 14pt;">FY ${data[0]?.year || ''}</h5>
                        <h6 style="font-size: 14pt;">${data[0]?.campus || ''}</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="reportTable">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="text-align: center; background-color: #e9ecef;">Gender Issue and/or GAD Mandate</th>
                                <th style="text-align: center; background-color: #e9ecef;">Cause of the Gender Issue</th>
                                <th style="text-align: center; background-color: #e9ecef;">GAD Result Statement/GAD Objective</th>
                                <th style="text-align: center; background-color: #e9ecef;">Relevant Agency MFO/PAP</th>
                                <th style="text-align: center; background-color: #e9ecef;">GAD Activity</th>
                                <th style="text-align: center; background-color: #e9ecef;">Output Performance Indicators and Target</th>
                                <th style="text-align: center; background-color: #e9ecef;">GAD Budget</th>
                                <th style="text-align: center; background-color: #e9ecef;">Source of Budget</th>
                                <th style="text-align: center; background-color: #e9ecef;">Responsible Unit/Office</th>
                            </tr>
                            <tr style="background-color: #f8f9fa;">
                                <th style="text-align: center; background-color: #e9ecef;">(1)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(2)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(3)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(4)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(5)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(6)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(7)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(8)</th>
                                <th style="text-align: center; background-color: #e9ecef;">(9)</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            if (!data || data.length === 0) {
                html += '<tr><td colspan="9" class="text-center">No data available</td></tr>';
            } else {
                let totalBudget = 0;
                
                // Process each category
                allCategories.forEach(category => {
                    // Add category header
                    html += `
                        <tr>
                            <td colspan="9" class="category-header">${category}</td>
                        </tr>
                    `;
                    
                    // Filter data for current category
                    const categoryData = data.filter(item => item.category === category);
                    
                    if (categoryData.length === 0) {
                        // Add empty rows for categories with no data
                        html += `
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        `;
                    } else {
                        // Add data rows
                        categoryData.forEach(item => {
                            totalBudget += parseFloat(item.gad_budget) || 0;
                            
                            // Process activities
                            let genericActivitiesList = '';
                            if (item.generic_activity) {
                                const cleanGeneric = item.generic_activity.replace(/[\[\]"]/g, '').split(',').filter(act => act.trim());
                                genericActivitiesList = cleanGeneric.map((act, i) => `G${i + 1}. ${act.trim()}`).join('<br>');
                            }
                            
                            let specificActivitiesList = '';
                            if (item.specific_activities) {
                                const cleanSpecific = item.specific_activities
                                    .replace(/[\[\]"]/g, '')
                                    .split(/[,\n;]/)
                                    .filter(act => act.trim())
                                    .map((act, i) => `S${i + 1}. ${act.trim()}`);
                                specificActivitiesList = cleanSpecific.join('<br>');
                            }
                            
                            const genericCount = item.generic_activity ? 
                                item.generic_activity.replace(/[\[\]"]/g, '').split(',').filter(act => act.trim()).length : 0;
                            const specificCount = item.specific_activities ? 
                                item.specific_activities.replace(/[\[\]"]/g, '').split(/[,\n;]/).filter(act => act.trim()).length : 0;
                            
                            html += `
                                <tr>
                                    <td>${item.gender_issue || ''}</td>
                                    <td>${item.cause_of_issue || ''}</td>
                                    <td>${item.gad_objective || ''}</td>
                                    <td>${item.relevant_agency || ''}</td>
                                    <td>
                                        ${genericActivitiesList}
                                        ${genericActivitiesList && specificActivitiesList ? '<br>' : ''}
                                        ${specificActivitiesList}
                                    </td>
                                    <td>
                                        ${genericCount > 0 ? `Generic Activities: ${genericCount} activities<br>` : ''}
                                        ${specificCount > 0 ? `Specific Activities: ${specificCount} activities<br>` : ''}
                                        <br>
                                        No. of participants in activities: at least ${item.male_participants || '0'} male and ${item.female_participants || '0'} female stakeholders participants.
                                    </td>
                                    <td>₱${parseFloat(item.gad_budget || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td>${item.source_of_budget || ''}</td>
                                    <td>${item.responsible_unit || ''}</td>
                                </tr>
                            `;
                        });
                    }
                });

                // Add total row
                html += `
                    <tr>
                        <td colspan="6" class="text-end"><strong>Total GAD Budget:</strong></td>
                        <td colspan="3"><strong>₱${totalBudget.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong></td>
                    </tr>
                `;
            }

            html += `
                        </tbody>
                    </table>
                </div>
                <div class="report-footer mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Prepared by:</strong></p>
                            <p class="mt-4">____________________________</p>
                            <p>GAD Focal Person</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <p><strong>Approved by:</strong></p>
                            <p class="mt-4">____________________________</p>
                            <p>Head of Agency</p>
                        </div>
                    </div>
                </div>
            `;

            preview.html(html);
        }

        function printReport() {
            const printWindow = window.open('', '_blank');
            const reportContent = $('#reportPreview').html();
            
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>GPB Report</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @page {
                            size: landscape;
                            margin: 1cm;
                        }
                        
                        @media print {
                            .table-bordered td, .table-bordered th {
                                border: 1px solid #000 !important;
                            }
                            
                            .table thead th {
                                font-size: 12px;
                                vertical-align: middle;
                                text-align: center;
                            }
                            
                            /* Style for first page header */
                            .table thead:first-of-type tr:first-child th {
                                background-color: #e9ecef !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                            
                            /* Style for subsequent pages headers - hide text but keep structure */
                            .table thead:not(:first-of-type) tr:first-child th {
                                color: transparent !important;
                                background-color: #e9ecef !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                            
                            /* Style for second row (numbers) */
                            .table thead tr:last-child th {
                                background-color: #f8f9fa !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                            
                            .table td {
                                font-size: 12px;
                                padding: 4px;
                                vertical-align: middle;
                            }
                            
                            .table tr.bg-light td {
                                background-color: #f8f9fa !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                            }
                            
                            .category-header {
                                font-size: 14px !important;
                                font-weight: bold !important;
                                text-align: center !important;
                                background-color: #f8f9fa !important;
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                                color-adjust: exact !important;
                                padding: 8px 4px !important;
                            }
                            
                            .report-header {
                                margin-bottom: 20px;
                            }
                            
                            .report-header h4 {
                                font-size: 16px;
                                font-weight: bold;
                                margin-bottom: 5px;
                            }
                            
                            .report-header h5, .report-header h6 {
                                font-size: 14px;
                                margin-bottom: 5px;
                            }
                            
                            .report-footer {
                                margin-top: 30px;
                                font-size: 12px;
                            }
                            
                            .report-footer p {
                                margin-bottom: 5px;
                            }
                            
                            /* Add page break control */
                            thead {
                                display: table-header-group;
                            }
                            
                            tfoot {
                                display: table-footer-group;
                            }
                            
                            tr {
                                page-break-inside: avoid;
                            }

                            /* Ensure subsequent page headers maintain height */
                            .table thead:not(:first-of-type) tr:first-child th::after {
                                content: '.';
                                visibility: hidden;
                                display: block;
                                height: 0;
                            }
                        }
                    </style>
                </head>
                <body class="p-4">
                    ${reportContent}
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }

        function exportToExcel() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
                });
                return;
            }

            // Create Excel workbook
            let table = document.getElementById('reportTable');
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please generate a report first.'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Exporting to Excel',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Get report header information
            const reportTitle = `GPB Report - ${selectedCampus} - FY ${selectedYear}`;
            const reportHeader = $('.report-header').clone();
            reportHeader.find('img').remove(); // Remove logo for Excel export

            try {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.table_to_sheet(table);

                // Add title and header information
                XLSX.utils.sheet_add_aoa(ws, [[reportTitle]], { origin: 'A1' });
                
                // Style the worksheet
                ws['!cols'] = [
                    { wch: 20 }, // Column A
                    { wch: 20 }, // Column B
                    { wch: 20 }, // Column C
                    { wch: 20 }, // Column D
                    { wch: 25 }, // Column E
                    { wch: 25 }, // Column F
                    { wch: 15 }, // Column G
                    { wch: 15 }, // Column H
                    { wch: 20 }  // Column I
                ];

                XLSX.utils.book_append_sheet(wb, ws, 'GPB Report');

                // Save the file
                XLSX.writeFile(wb, `GPB_Report_${selectedCampus}_${selectedYear}.xlsx`);

                Swal.close();
            } catch (error) {
                console.error('Excel export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export to Excel. Please try again.'
                });
            }
        }

        function exportToWord() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
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
                const content = $('#reportPreview').clone();
                
                // Create Word document HTML
                const html = `
                    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                    <head>
                        <meta charset='utf-8'>
                        <title>GPB Report</title>
                        <style>
                            table {
                                border-collapse: collapse;
                                width: 100%;
                            }
                            th, td {
                                border: 1px solid black;
                                padding: 8px;
                                text-align: left;
                            }
                            th {
                                background-color: #e9ecef;
                            }
                            .category-header {
                                background-color: #f8f9fa;
                                font-weight: bold;
                                text-align: center;
                            }
                            @page {
                                size: landscape;
                                mso-page-orientation: landscape;
                                margin: 1cm;
                            }
                            .report-header {
                                text-align: center;
                                margin-bottom: 20px;
                            }
                            .report-footer {
                                margin-top: 20px;
                            }
                        </style>
                    </head>
                    <body>
                        ${content.html()}
                    </body>
                    </html>
                `;

                // Create blob and download
                const blob = new Blob([html], { type: 'application/msword' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `GPB_Report_${selectedCampus}_${selectedYear}.doc`;
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

        function exportToPDF() {
            const isCentral = <?php echo $isCentral ? 'true' : 'false' ?>;
            const selectedCampus = isCentral ? $('#campus').val() : "<?php echo $userCampus ?>";
            const selectedYear = $('#year').val();

            if (!selectedCampus || !selectedYear) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selection Required',
                    text: 'Please select' + (isCentral ? ' both a campus and' : '') + ' a year to generate the report.'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Exporting to PDF',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const element = document.getElementById('reportPreview');
                
                // Create a clone of the element to modify for PDF
                const pdfContent = element.cloneNode(true);
                
                // Update header styling
                const header = pdfContent.querySelector('.report-header');
                if (header) {
                    header.style.textAlign = 'center';
                    header.style.marginBottom = '30px';
                    header.style.backgroundColor = 'white';
                    
                    // Update logo size and styling
                    const logo = header.querySelector('img');
                    if (logo) {
                        logo.style.width = '120px';
                        logo.style.height = '120px';
                        logo.style.marginBottom = '15px';
                    }
                    
                    // Update text styling
                    const headerText = header.querySelector('.header-text');
                    if (headerText) {
                        headerText.style.lineHeight = '1.6';
                        headerText.style.marginBottom = '20px';
                    }
                    
                    // Style all header paragraphs
                    header.querySelectorAll('p').forEach(p => {
                        p.style.margin = '5px 0';
                        if (p.textContent.includes('BATANGAS STATE UNIVERSITY')) {
                            p.style.fontWeight = 'bold';
                            p.style.fontSize = '16pt';
                        } else {
                            p.style.fontSize = '14pt';
                        }
                    });
                    
                    // Style the GAD title
                    const gadTitle = header.querySelector('h4');
                    if (gadTitle) {
                        gadTitle.style.fontWeight = 'bold';
                        gadTitle.style.fontSize = '16pt';
                        gadTitle.style.margin = '20px 0 10px';
                    }
                    
                    // Style year and campus
                    header.querySelectorAll('h5, h6').forEach(element => {
                        element.style.fontSize = '14pt';
                        element.style.margin = '5px 0';
                    });
                }

                // Style table headers
                const tableHeaders = pdfContent.querySelectorAll('thead th');
                tableHeaders.forEach(th => {
                    th.style.backgroundColor = '#e9ecef';
                    th.style.color = '#000000';
                    th.style.textAlign = 'center';
                    th.style.verticalAlign = 'middle';
                    th.style.padding = '10px';
                    th.style.fontWeight = 'bold';
                });

                const opt = {
                    margin: [15, 10],
                    filename: `GPB_Report_${selectedCampus}_${selectedYear}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { 
                        scale: 2,
                        logging: false,
                        letterRendering: true,
                        useCORS: true,
                        backgroundColor: '#ffffff'
                    },
                    jsPDF: { 
                        unit: 'mm', 
                        format: 'a3', 
                        orientation: 'landscape'
                    },
                    pagebreak: { mode: 'avoid-all' }
                };

                // Generate PDF
                html2pdf().set(opt).from(pdfContent).save().then(() => {
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'PDF has been generated successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(error => {
                    console.error('PDF generation error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'Failed to generate PDF. Please try again.'
                    });
                });
            } catch (error) {
                console.error('PDF export error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to export to PDF. Please try again.'
                });
            }
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

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
    </script>
</body>
</html>