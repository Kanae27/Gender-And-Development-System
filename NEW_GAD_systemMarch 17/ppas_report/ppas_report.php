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
        
        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }
        
        .dropdown-submenu .dropdown-item {
            padding-left: 30px;
        }
        
        .dropdown-submenu > a:after {
            display: block;
            content: "\f105";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            float: right;
            width: 10px;
            margin-top: 5px;
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

/* Table header styling for dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td,
[data-bs-theme="dark"] #reportTable thead tr:nth-child(2) td {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Special styling for the logo cell in dark mode */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

[data-bs-theme="dark"] #reportTable thead tr:first-child h4,
[data-bs-theme="dark"] #reportTable thead tr:first-child div {
    color: #f8f9fa !important;
}
        
/* Exception for the logo cell text color */
[data-bs-theme="dark"] #reportTable thead tr:first-child td:first-child div {
    color: #f8f9fa !important;
}

/* Override for all print styles in dark mode */
[data-bs-theme="dark"] #reportTable,
[data-bs-theme="dark"] #reportTable * {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Even more specific overrides for table rows and cells in dark mode */
[data-bs-theme="dark"] #reportTable tr,
[data-bs-theme="dark"] #reportTable tr td,
[data-bs-theme="dark"] #reportTable tr th,
[data-bs-theme="dark"] #reportTable tbody tr,
[data-bs-theme="dark"] #reportTable tbody td,
[data-bs-theme="dark"] #reportTable tbody th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

/* Dark mode styling for table headers - use more specific selectors to override Bootstrap */
[data-bs-theme="dark"] #reportTable thead tr th,
[data-bs-theme="dark"] .table-bordered > thead > tr > th,
[data-bs-theme="dark"] .table > thead > tr > th,
[data-bs-theme="dark"] .table > :not(caption) > * > th {
    background-color: #212529 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* Column headers styling for dark mode */
[data-bs-theme="dark"] #reportTable thead tr:nth-child(3) th {
    background-color: #212529 !important;
    color: #f8f9fa !important;
    font-weight: 600 !important;
}

/* Print-specific styles */
@media print {
    /* Remove scrollbars */
    html, body {
        overflow: visible !important;
    }
    
    .main-content {
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
    }
    
    .table-responsive {
        overflow: visible !important;
    }
    
    /* Fix table width for printing */
    #reportTable {
        width: 100% !important;
        table-layout: fixed !important;
    }
    
    /* Hide elements not needed in print */
    .sidebar, .card-header, .btn, .page-title, 
    .mobile-nav-toggle, .theme-switch-button {
        display: none !important;
    }
    
    /* Reset margins for printing */
    .card, .card-body {
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    #reportTable thead tr:first-child td,
    #reportTable thead tr:nth-child(2) td,
    #reportTable thead tr:first-child h4,
    #reportTable thead tr:first-child div {
        color: #000 !important;
        background-color: #fff !important;
    }
    
    /* Reset all dark mode styles when printing */
    [data-bs-theme="dark"] #reportTable,
    [data-bs-theme="dark"] #reportTable * {
        color: #000 !important;
        background-color: #fff !important;
        border-color: #000 !important;
    }
    
    [data-bs-theme="dark"] #reportTable thead tr th {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}

/* Dark mode additional fixes for bootstrap tables */
[data-bs-theme="dark"] .table {
    --bs-table-bg: #343a40 !important;
    --bs-table-striped-bg: #2c3034 !important;
    --bs-table-striped-color: #fff !important;
    --bs-table-active-bg: #373b3e !important;
    --bs-table-active-color: #fff !important;
    --bs-table-hover-bg: #323539 !important;
    --bs-table-hover-color: #fff !important;
    border-color: #495057 !important;
    color: #fff !important;
}

[data-bs-theme="dark"] .table-bordered {
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .table-bordered > :not(caption) > * {
    border-color: #495057 !important;
}

[data-bs-theme="dark"] .table-bordered > :not(caption) > * > * {
    border-color: #495057 !important;
    background-color: #343a40 !important;
}

/* Force header cells to be dark */
[data-bs-theme="dark"] .table th {
    background-color: #212529 !important;
    color: #f8f9fa !important;
}

/* Different color for header rows */
[data-bs-theme="dark"] #reportTable > thead > tr > td {
    background-color: #212529 !important;
    color: #f8f9fa !important;
}

/* NEW: Additional stronger specificity rules to override Bootstrap styles */
[data-bs-theme="dark"] #reportTable thead tr th,
[data-bs-theme="dark"] #reportTable thead th {
    background-color: #212529 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Even more specific rules for the first row headers */
[data-bs-theme="dark"] #reportTable > thead > tr:first-child th,
[data-bs-theme="dark"] #reportTable > thead > tr:first-child td, 
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(2) th,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(2) td,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(3) th,
[data-bs-theme="dark"] #reportTable > thead > tr:nth-child(3) td {
    background-color: #212529 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Force all cells to have the correct background */
[data-bs-theme="dark"] table.table, 
[data-bs-theme="dark"] table.table thead, 
[data-bs-theme="dark"] table.table tbody, 
[data-bs-theme="dark"] table.table tr, 
[data-bs-theme="dark"] table.table td, 
[data-bs-theme="dark"] table.table th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
    border-color: #495057 !important;
}

/* NEW: Override specific Bootstrap classes that might be causing the issue */
[data-bs-theme="dark"] .table-light,
[data-bs-theme="dark"] .table-light > td,
[data-bs-theme="dark"] .table-light > th {
    background-color: #343a40 !important;
    color: #f8f9fa !important;
}

/* NEW: Specific override for header cells */
[data-bs-theme="dark"] #reportTable tr th {
    background-color: #212529 !important;
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
                                <li><a class="dropdown-item" href="../ppas_proposal/gad_proposal.php">PPAS Proposal</a></li>
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
                        <li><a class="dropdown-item" href="../gpb_reports/gbp_reports.php">GPB Reports</a></li>
                        <li><a class="dropdown-item" href="#">PPAs Reports</a></li>
                        <li><a class="dropdown-item" href="../ps_atrib/ps.php">PSA Reports</a></li>
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
            <i class="fas fa-chart-bar"></i>
            <h2>PPAS Reports</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Generate PPAS Report</h5>
            </div>
            <div class="card-body">
                <form id="reportForm">
                    <div class="row mb-3">
                        <?php if ($isCentral): ?>
                        <div class="col-md-4">
                            <label for="campus" class="form-label">Campus</label>
                            <select class="form-select" id="campus" name="campus" required>
                                <option value="">Select Campus</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year</label>
                            <select class="form-select" id="year" name="year" required>
                                <option value="">Select Year</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="quarter" class="form-label">Quarter</label>
                            <select class="form-select" id="quarter" name="quarter" required>
                                <option value="">Select Quarter</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" onclick="generateReport()">
                                <i class="fas fa-sync-alt me-2"></i>Generate Report
                            </button>
                            <button type="button" class="btn btn-success ms-2" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export to Excel
                            </button>
                            <button type="button" class="btn btn-danger ms-2" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf me-2"></i>Export to PDF
                            </button>
                            <button type="button" class="btn btn-info ms-2" onclick="printReport()">
                                <i class="fas fa-print me-2"></i>Print Report
                            </button>
                        </div>
                    </div>
                </form>

                <div id="reportPreview" class="mt-4" style="display: none;">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="reportTable">
                            <thead>
                                <tr>
                                    <td colspan="3" class="align-middle">
                                        <img src="../images/BatStateU-NEU-Logo.png" alt="BatStateU Logo" style="height: 80px; float: left; margin-right: 10px;">
                                        <div>Reference No.: BatStateU-FO-ESO-03</div>
                                        <div>Effectivity Date: January 03, 2024</div>
                                        <div>Revision No.: 02</div>
                                    </td>
                                    <td colspan="8" class="text-center align-middle">
                                        <h4>Quarterly Report of GAD Programs, Projects and Activities (PPAs)</h4>
                                        <div class="quarter-label"></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="11">Campus: <?php echo $_SESSION['username']; ?></td>
                                </tr>
                                <tr class="text-center">
                                    <th style="width: 10%">Gender Issue/s*</th>
                                    <th style="width: 10%">Title of Implemented PPAs</th>
                                    <th style="width: 8%">Date/Duration (number of hours)</th>
                                    <th style="width: 8%">No. of Beneficiaries (Male and Female)</th>
                                    <th style="width: 10%">Type of Beneficiaries</th>
                                    <th style="width: 10%">Location</th>
                                    <th style="width: 12%">Personnel Involved</th>
                                    <th style="width: 8%">Approved Budget</th>
                                    <th style="width: 8%">Actual Cost</th>
                                    <th style="width: 8%">PS Attribution</th>
                                    <th style="width: 8%">Source of Fund</th>
                                </tr>
                            </thead>
                            <tbody id="reportBody">
                                <!-- Report data will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
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
            
            // Handle dropdown submenu
            document.querySelectorAll('.dropdown-submenu > a').forEach(function(element) {
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    
                    // Toggle the submenu
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
            
            loadCampusOptions();
            loadAvailablePeriods();
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

        // Function to load campus options (for Central admin)
        function loadCampusOptions() {
            <?php if ($isCentral): ?>
            fetch('../api/get_campus_list.php')
                .then(response => response.json())
                .then(data => {
                    const campusSelect = document.getElementById('campus');
                    data.forEach(campus => {
                        const option = document.createElement('option');
                        option.value = campus.id;
                        option.textContent = campus.campus_name;
                        campusSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading campus options:', error));
            <?php endif; ?>
        }

        // Function to generate report
        function generateReport() {
            const campus = <?php echo $isCentral ? "document.getElementById('campus').value" : "'{$_SESSION['username']}'" ?>;
            const year = document.getElementById('year').value;
            const quarter = document.getElementById('quarter').value;

            if ((!campus && <?php echo $isCentral ? 'true' : 'false' ?>) || !year || !quarter) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields',
                    text: 'Please fill in all required fields'
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Generating Report',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fetch report data
            fetch(`get_ppas_report.php?campus=${encodeURIComponent(campus)}&year=${encodeURIComponent(year)}&quarter=${encodeURIComponent(quarter)}`)
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.message || 'Failed to fetch report data');
                    }

                    const reportBody = document.getElementById('reportBody');
                    reportBody.innerHTML = '';

                    if (response.data.length === 0) {
                        reportBody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center">No data available for the selected period</td>
                            </tr>
                        `;
                    } else {
                        response.data.forEach(item => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${item.gender_issue || ''}</td>
                                <td>${item.title}</td>
                                <td>${item.date_conducted}<br>${item.duration} hours</td>
                                <td>Male: ${parseInt(item.participants.students.male) + parseInt(item.participants.faculty.male) + parseInt(item.participants.external.male)}<br>
                                    Female: ${parseInt(item.participants.students.female) + parseInt(item.participants.faculty.female) + parseInt(item.participants.external.female)}</td>
                                <td>Internal:<br>- Students<br>- Faculty<br>External:<br>${item.participants.external.type || 'N/A'}</td>
                                <td>${item.location}</td>
                                <td>${item.personnel.map(p => `- ${p}`).join('<br>')}</td>
                                <td>₱${parseFloat(item.budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>₱${parseFloat(item.actual_cost).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>₱${item.ps_attribution}</td>
                                <td>${item.source_of_budget}</td>
                            `;
                            reportBody.appendChild(row);
                        });
                    }

                    document.getElementById('reportPreview').style.display = 'block';
                    Swal.close();
                })
                .catch(error => {
                    console.error('Error generating report:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to generate report. Please try again.'
                    });
                });
        }

        // Function to export to Excel
        function exportToExcel() {
            const table = document.getElementById('reportTable');
            if (!table || table.rows.length <= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'Please generate a report first'
                });
                return;
            }

            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(table);
            
            // Style the worksheet
            ws['!cols'] = [
                { wch: 30 }, // Title
                { wch: 20 }, // Location
                { wch: 25 }, // Date Conducted
                { wch: 15 }, // Duration
                { wch: 15 }, // Budget
                { wch: 15 }, // Source
                { wch: 30 }, // Personnel
                { wch: 40 }  // Participants
            ];

            XLSX.utils.book_append_sheet(wb, ws, 'PPAS Report');
            XLSX.writeFile(wb, `PPAS_Report_${document.getElementById('year').value}_Q${document.getElementById('quarter').value}.xlsx`);
        }

        // Function to export to PDF
        function exportToPDF() {
            const table = document.getElementById('reportTable');
            if (!table || table.rows.length <= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'Please generate a report first'
                });
                return;
            }

            const element = document.getElementById('reportPreview');
            const opt = {
                margin: 1,
                filename: `PPAS_Report_${document.getElementById('year').value}_Q${document.getElementById('quarter').value}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'a3', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save();
        }

        // Function to print report
        function printReport() {
            const table = document.getElementById('reportTable');
            if (!table || table.rows.length <= 2) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Data',
                    text: 'Please generate a report first'
                });
                return;
            }

            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>PPAS Report</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        @media print {
                            @page {
                                size: landscape;
                                margin: 1cm;
                            }
                            
                            body {
                                margin: 0;
                                padding: 0;
                                overflow: visible !important;
                            }
                            
                            .container-fluid {
                                width: 100%;
                                padding: 0;
                                margin: 0;
                                overflow: visible !important;
                            }
                            
                            .table-responsive {
                                overflow: visible !important;
                            }
                            
                            table {
                                width: 100% !important;
                                table-layout: fixed !important;
                                border-collapse: collapse;
                                border: 1.5px solid black;
                                margin: 0 !important;
                                page-break-inside: auto;
                            }
                            
                            tr {
                                page-break-inside: avoid;
                                page-break-after: auto;
                            }
                            
                            th, td {
                                border: 1.5px solid black !important;
                                padding: 8px;
                                font-size: 12px;
                                word-wrap: break-word;
                                overflow-wrap: break-word;
                            }
                            
                            thead th {
                                border-bottom: 1.5px solid black !important;
                            }
                            
                            img {
                                max-height: 80px;
                            }
                            
                            /* Force background and colors for printing */
                            * {
                                -webkit-print-color-adjust: exact !important;
                                print-color-adjust: exact !important;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container-fluid">
                            ${document.getElementById('reportPreview').innerHTML}
                        </div>
                    </body>
                    </html>
            `);
            
            // Add the print trigger script separately to avoid template literal issues
            printWindow.document.write('<script>window.onload = function() { window.print(); window.onafterprint = function() { window.close(); }; };<\/script>');
            printWindow.document.close();
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadCampusOptions();
            loadAvailablePeriods();
        });

        function loadAvailablePeriods() {
            const username = '<?php echo $_SESSION["username"]; ?>';
            $.ajax({
                url: 'get_available_periods.php',
                method: 'GET',
                data: { campus: username },
                success: function(response) {
                    if (response.success) {
                        const yearSelect = $('#year');
                        yearSelect.empty().append('<option value="">Select Year</option>');
                        
                        // Add years
                        Object.keys(response.data).sort((a, b) => b - a).forEach(year => {
                            yearSelect.append(`<option value="${year}">${year}</option>`);
                        });

                        // Handle year change
                        yearSelect.on('change', function() {
                            const selectedYear = $(this).val();
                            const quarterSelect = $('#quarter');
                            quarterSelect.empty().append('<option value="">Select Quarter</option>');
                            
                            if (selectedYear && response.data[selectedYear]) {
                                response.data[selectedYear].sort().forEach(quarter => {
                                    const quarterLabel = {
                                        'Q1': '1st Quarter',
                                        'Q2': '2nd Quarter',
                                        'Q3': '3rd Quarter',
                                        'Q4': '4th Quarter'
                                    }[quarter] || quarter;
                                    quarterSelect.append(`<option value="${quarter}">${quarterLabel}</option>`);
                                });
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading periods:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load available periods'
                    });
                }
            });
        }
    </script>

    <!-- Add necessary libraries -->
    <script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</body>
</html>