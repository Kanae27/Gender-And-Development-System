<?php
session_start();

// Debug session information
error_log("Session data in ppas.php: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("User not logged in - redirecting to login");
    header("Location: ../index.php");
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
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <i class="fas fa-file-alt"></i>
            <h2>GAD Proposal Form</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Gender and Development (GAD) Activity Proposal</h5>
            </div>
            <div class="card-body">
                <form id="gadProposalForm">
                    <!-- Page 1: Basic Information -->
                    <div class="form-page" id="page1">
                        <div class="row g-3">
                            

                            <div class="col-md-6">
                                <label for="year" class="form-label">Year</label>
                                <select class="form-select" id="year" name="year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quarter" class="form-label">Quarter</label>
                                <select class="form-select" id="quarter" name="quarter" required disabled>
                                    <option value="">Select Quarter</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="titleSelector" class="form-label">Available Activities</label>
                                <select class="form-select" id="titleSelector" name="titleSelector" required disabled>
                                    <option value="">Select Activity</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="activityTitle" class="form-label">I. Title</label>
                                <input type="text" class="form-control" id="activityTitle" name="activityTitle" required>
                            </div>

                            <div class="col-md-6">
                                <label for="startDate" class="form-label">II. Start Date</label>
                                <input type="date" class="form-control" id="startDate" name="startDate" required>
                            </div>

                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" name="endDate" required>
                            </div>

                            <div class="col-md-12">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" class="form-control" id="venue" name="venue" requred>
                            </div>

                            <div class="col-md-12">
                                <label for="deliveryMode" class="form-label">III. Mode of delivery</label>
                                <select class="form-select" id="deliveryMode" name="deliveryMode" required>
                                    <option value="">Select Mode</option>
                                    <option value="online">Online</option>
                                    <option value="face-to-face">Face-to-face</option>
                                    <option value="hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Page 2: Project Team -->
                    <div class="form-page" id="page2" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">IV. Project Team</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="projectLeaders" class="form-label">Project Leaders</label>
                                <input type="text" class="form-control" id="projectLeaders" name="projectLeaders" placeholder="Populated from PPAS data" readonly>
                                <div id="projectLeadersList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Project Leaders are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label for="leaderResponsibilities" class="form-label">Responsibilities</label>
                                <textarea class="form-control" id="leaderResponsibilities" name="leaderResponsibilities" rows="2"></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="assistantProjectLeaders" class="form-label">Assistant Project Leaders</label>
                                <input type="text" class="form-control" id="assistantProjectLeaders" name="assistantProjectLeaders" placeholder="Populated from PPAS data" readonly>
                                <div id="assistantProjectLeadersList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Assistant Project Leaders are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label for="assistantResponsibilities" class="form-label">Responsibilities</label>
                                <textarea class="form-control" id="assistantResponsibilities" name="assistantResponsibilities" rows="2"></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="projectStaff" class="form-label">Project Staff</label>
                                <input type="text" class="form-control" id="projectStaff" name="projectStaff" placeholder="Populated from PPAS data" readonly>
                                <div id="projectStaffList" class="mt-2"></div>
                                <small class="text-muted personnel-note" style="display: none;">Project Staff are populated from PPAS data and cannot be modified</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Staff Responsibilities</label>
                                <div class="staff-responsibilities-container">
                                    <textarea class="form-control" id="staffResponsibilities" name="staffResponsibilities" rows="6" placeholder="Enter responsibilities (press Enter for new numbered item)"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Page 3: Partners and Participants -->
                    <div class="form-page" id="page3" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">V. Partner Office/College/Department</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="partnerOffices" class="form-label">V. Partner Office/College/Department</label>
                                <input type="text" class="form-control" id="partnerOffices" name="partnerOffices" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">VI. Type of Participants</h5>
                                <p>Children from the families in Cuenca</p>
                            </div>

                            <div class="col-md-4">
                                <label for="maleBeneficiaries" class="form-label">Male</label>
                                <input type="number" class="form-control" id="maleBeneficiaries" name="maleBeneficiaries" value="50" min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="femaleBeneficiaries" class="form-label">Female</label>
                                <input type="number" class="form-control" id="femaleBeneficiaries" name="femaleBeneficiaries" value="50" min="0">
                            </div>
                            <div class="col-md-4">
                                <label for="totalBeneficiaries" class="form-label">Total</label>
                                <input type="number" class="form-control" id="totalBeneficiaries" name="totalBeneficiaries" value="100" min="0" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Page 4: Rationale and Objectives -->
                    <div class="form-page" id="page4" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">VII. Rationale/Background</h5>
                            </div>

                            <div class="col-md-12">
                                <textarea class="form-control" id="rationale" name="rationale" rows="5" required></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <h5 class="mb-3">VIII. Objectives</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="specificObjectives" class="form-label">Specific Objectives</label>
                                <textarea class="form-control" id="specificObjectives" name="specificObjectives" rows="5" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Page 5: Activities and Methods -->
                    <div class="form-page" id="page5" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">IX. Description, Strategies, and Methods</h5>
                            </div>

                            <div class="col-md-12">
                                <label for="strategies" class="form-label">Strategies</label>
                                <textarea class="form-control" id="strategies" name="strategies" rows="3"></textarea>
                            </div>

                            <div class="col-md-12 mt-3">
                                <label class="form-label">Methods (Activities / Schedule)</label>
                                <div id="activitiesContainer">
                                    <!-- Activities will be added here dynamically -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addActivity()">
                                    <i class="fas fa-plus"></i> Add Activity
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 6: Work Plan -->
                    <div class="form-page" id="page6" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">X. Work Plan (Timeline of Activities/Gantt Chart)</h5>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="workPlanTable">
                                        <thead>
                                            <tr>
                                                <th>Activities</th>
                                                <!-- Date columns will be generated dynamically -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control form-control-sm" placeholder="Add activity"></td>
                                                <!-- Checkboxes will be generated dynamically -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addWorkPlanRowBtn">
                                    <i class="fas fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 7: Budget -->
                    <div class="form-page" id="page7" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XI. Financial Requirements and Source of Funds</h5>
                            </div>

                            <div class="col-md-6">
                                <label for="budgetSource" class="form-label">Source of Budget</label>
                                <select class="form-select" id="budgetSource" name="budgetSource" required>
                                    <option value="">Select Source</option>
                                    <option value="GAA">GAA</option>
                                    <option value="Income">Income</option>
                                    <option value="External">External</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="totalBudget" class="form-label">Total Budget</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="totalBudget" name="totalBudget" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="budgetBreakdown" class="form-label">Budget Breakdown</label>
                                <textarea class="form-control" id="budgetBreakdown" name="budgetBreakdown" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Page 8: Monitoring and Evaluation -->
                    <div class="form-page" id="page8" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XII. Monitoring and Evaluation Mechanics / Plan</h5>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Objectives</th>
                                                <th>Performance Indicators</th>
                                                <th>Baseline Data</th>
                                                <th>Performance Target</th>
                                                <th>Data Source</th>
                                                <th>Collection Method</th>
                                                <th>Frequency of Data Collection</th>
                                                <th>Office/Persons Responsible</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                                <td><textarea class="form-control" rows="2"></textarea></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addMonitoringRowBtn">
                                    <i class="fas fa-plus"></i> Add Row
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Page 9: Sustainability Plan -->
                    <div class="form-page" id="page9" style="display: none;">
                        <div class="row g-3">
                            <div class="col-12">
                                <h5 class="mb-3">XIII. Sustainability Plan</h5>
                            </div>

                            <div class="col-md-12">
                                <textarea class="form-control" id="sustainabilityPlan" name="sustainabilityPlan" rows="6" required></textarea>
                            </div>
                        </div>

                        <!-- Add Print Button -->
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary" onclick="printProposal()" id="printBtn" style="display: none;">
                                <i class="fas fa-print"></i> Print Proposal
                            </button>
                            <button type="button" class="btn btn-info ms-2" onclick="printHtmlVersion()" id="printHtmlBtn" style="display: none;">
                                <i class="fas fa-file-alt"></i> HTML Print
                            </button>
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
        let currentPage = 1;
        const totalPages = 9;
        // Create an object to track the personnel selections
        let selectedPersonnel = {
            projectLeaders: [],
            assistantProjectLeaders: [],
            projectStaff: []
        };

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
                if (!field.value && field.offsetParent !== null) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

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
                
                // Show/hide navigation buttons
                document.getElementById('prevBtn').style.display = currentPage === 1 ? 'none' : 'block';
                document.getElementById('nextBtn').style.display = currentPage === totalPages ? 'none' : 'block';
                document.getElementById('submitBtn').style.display = currentPage === totalPages ? 'block' : 'none';
            }
        }

        // Calculate total beneficiaries
        function updateTotalBeneficiaries() {
            const male = parseInt(document.getElementById('maleBeneficiaries').value) || 0;
            const female = parseInt(document.getElementById('femaleBeneficiaries').value) || 0;
            document.getElementById('totalBeneficiaries').value = male + female;
        }

        // Form submission handler
        function handleFormSubmit(e) {
            e.preventDefault();
            
            try {
                const form = document.getElementById('gadProposalForm');
                const formData = new FormData(form);
                
                // Add PPAS ID if available
                const ppasId = document.getElementById('ppasId')?.value;
                if (ppasId) {
                    formData.append('ppasId', ppasId);
                }
                
                // Show loading indicator
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save your GAD proposal',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Collect activities data
                const activitiesContainer = document.getElementById('activitiesContainer');
                const activityItems = activitiesContainer.querySelectorAll('.activity-item');
                
                activityItems.forEach((item, index) => {
                    const titleInput = item.querySelector('input[type="text"]');
                    const detailsTextarea = item.querySelector('textarea');
                    
                    if (titleInput && titleInput.value.trim() !== '') {
                        formData.append(`activity_title_${index + 1}`, titleInput.value);
                        if (detailsTextarea) {
                            formData.append(`activity_details_${index + 1}`, detailsTextarea.value);
                        }
                    }
                });
                
                // Handle hidden personnel fields
                for (const role in selectedPersonnel) {
                    const personnelIds = selectedPersonnel[role].map(p => p.id).join(',');
                    formData.append(`${role}Hidden`, personnelIds);
                    
                    // Also include the display names for clarity in the form data
                    const personnelNames = selectedPersonnel[role].map(p => p.name).join(', ');
                    formData.append(role, personnelNames);
                }
                
                // Add debug info
                console.log("Form data being submitted:");
                for (const pair of formData.entries()) {
                    console.log(pair[0], pair[1]);
                }
                
                fetch('save_gad_proposal.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 500) {
                            console.error('Server error 500. Database or server configuration issue.');
                            throw new Error('Database server error. Please contact administrator or check database configuration.');
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('Server returned non-JSON response:', text);
                            throw new Error('Server returned invalid response format');
                        });
                    }
                    
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        // Add hidden input for proposal ID
                        let proposalIdInput = document.getElementById('currentProposalId');
                        if (!proposalIdInput) {
                            proposalIdInput = document.createElement('input');
                            proposalIdInput.type = 'hidden';
                            proposalIdInput.id = 'currentProposalId';
                            form.appendChild(proposalIdInput);
                        }
                        proposalIdInput.value = result.proposalId;
                        
                        // Show print button
                        document.getElementById('printBtn').style.display = 'inline-block';
                        document.getElementById('printHtmlBtn').style.display = 'inline-block';
                        
                        Swal.fire({
                            title: 'Success!',
                            text: 'GAD Proposal has been saved successfully. You can now print the proposal.',
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        throw new Error(result.message || 'Failed to save GAD Proposal');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An unknown error occurred',
                        icon: 'error',
                        confirmButtonColor: '#d33'
                    });
                });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'An unknown error occurred',
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
            }
        }

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

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

        // Activities Management
        function addActivity() {
            const container = document.getElementById('activitiesContainer');
            const activityNum = container.children.length + 1;
            
            const activityDiv = document.createElement('div');
            activityDiv.className = 'activity-item mb-3';
            activityDiv.innerHTML = `
                <div class="input-group mb-2">
                    <span class="input-group-text">Activity ${activityNum}</span>
                    <input type="text" class="form-control" placeholder="Enter activity title" name="activity_title_${activityNum}">
                    <button type="button" class="btn btn-outline-danger" onclick="removeActivity(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <textarea class="form-control" rows="3" placeholder="Enter activity details" name="activity_details_${activityNum}"></textarea>
            `;
            container.appendChild(activityDiv);
        }

        function removeActivity(button) {
            const activityItem = button.closest('.activity-item');
            if (activityItem) {
                activityItem.remove();
                renumberActivities();
            }
        }

        function renumberActivities() {
            const activities = document.querySelectorAll('.activity-item');
            activities.forEach((activity, index) => {
                const span = activity.querySelector('.input-group-text');
                if (span) {
                    span.textContent = `Activity ${index + 1}`;
                }
            });
        }

        // Function to generate dates for work plan
        function generateWorkPlanDates() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            
            if (!startDateInput || !endDateInput || !startDateInput.value || !endDateInput.value) {
                return;
            }
            
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            // Clear existing table header
            const thead = document.querySelector('#workPlanTable thead tr');
            if (!thead) return;
            
            // Keep only the first column (Activities)
            while (thead.children.length > 1) {
                thead.removeChild(thead.lastChild);
            }
            
            // Generate date columns (up to 10 days maximum to keep it reasonable)
            const maxDays = 10;
            const dayCount = Math.min(Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1, maxDays);
            
            const dates = [];
            for (let i = 0; i < dayCount; i++) {
                const date = new Date(startDate);
                date.setDate(date.getDate() + i);
                dates.push(date);
                
                const th = document.createElement('th');
                th.textContent = formatDate(date);
                thead.appendChild(th);
            }
            
            // Update all existing rows
            const tbody = document.querySelector('#workPlanTable tbody');
            if (tbody) {
                Array.from(tbody.rows).forEach(row => {
                    // Remove all cells except the first one
                    while (row.cells.length > 1) {
                        row.deleteCell(1);
                    }
                    
                    // Add new checkbox cells
                    for (let i = 0; i < dayCount; i++) {
                        const cell = row.insertCell();
                        cell.innerHTML = '<input type="checkbox" class="form-check-input">';
                    }
                });
            }
        }

        // Format date as "DD (Month)"
        function formatDate(date) {
            const day = date.getDate();
            const month = date.toLocaleString('default', { month: 'short' });
            return `${day} (${month})`;
        }

        // Add work plan row
        function addWorkPlanRow() {
            const tbody = document.querySelector('#workPlanTable tbody');
            if (!tbody) return;
            
            const columnCount = document.querySelector('#workPlanTable thead tr').children.length;
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" class="form-control form-control-sm" placeholder="Add activity"></td>
                ${Array(columnCount - 1).fill('<td><input type="checkbox" class="form-check-input"></td>').join('')}
            `;
            tbody.appendChild(row);
        }

        // Update work plan when dates change
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const addWorkPlanRowBtn = document.getElementById('addWorkPlanRowBtn');
            
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', generateWorkPlanDates);
                endDateInput.addEventListener('change', generateWorkPlanDates);
            }
            
            if (addWorkPlanRowBtn) {
                addWorkPlanRowBtn.addEventListener('click', addWorkPlanRow);
            }
        });

        // Add row for Monitoring and Evaluation table
        function addMonitoringRow() {
            const tbody = document.querySelector('#page8 table tbody');
            if (tbody) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                    <td><textarea class="form-control" rows="2"></textarea></td>
                `;
                tbody.appendChild(row);
            }
        }

        // Personnel function to add a person to the list
        function addPersonnelToList(role, person) {
            // Check if person is already in the list
            if (selectedPersonnel[role].some(p => p.id === person.id)) {
                return;
            }

            // Add to selected personnel array
            selectedPersonnel[role].push(person);

            // Create a badge for the selected person
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary me-2 mb-2 personnel-badge';
            badge.dataset.id = person.id;
            
            // Determine if the badge should be read-only (PPAS data)
            // If the data is coming from PPAS fetch, make it read-only
            const isPpasData = document.getElementById('ppasId') && document.getElementById('ppasId').value;
            
            if (isPpasData) {
                // Read-only badge without remove button
                badge.innerHTML = `${person.name}`;
                badge.className = 'badge bg-secondary me-2 mb-2 personnel-badge';
                badge.title = "Personnel from PPAS data cannot be modified";
            } else {
                // Regular badge with remove button
                badge.innerHTML = `${person.name} <i class="fas fa-times ms-1 remove-personnel"></i>`;
                
                // Add click handler to remove badge
                badge.querySelector('.remove-personnel').addEventListener('click', function() {
                    badge.remove();
                    selectedPersonnel[role] = selectedPersonnel[role].filter(p => p.id !== person.id);
                    updateHiddenField(role);
                });
            }

            // Add badge to the list container
            const listContainer = document.getElementById(`${role}List`);
            if (listContainer) {
                listContainer.appendChild(badge);
            }

            // Update the hidden field with the selected personnel
            updateHiddenField(role);
        }

        // Function to update the hidden field with selected personnel
        function updateHiddenField(role) {
            // Create a hidden input if it doesn't exist
            let hiddenField = document.getElementById(`${role}Hidden`);
            if (!hiddenField) {
                hiddenField = document.createElement('input');
                hiddenField.type = 'hidden';
                hiddenField.id = `${role}Hidden`;
                hiddenField.name = `${role}Hidden`;
                const inputField = document.getElementById(role);
                if (inputField && inputField.parentNode) {
                    inputField.parentNode.appendChild(hiddenField);
                }
            }

            // Update the hidden field value with the selected personnel IDs
            const personnelIds = selectedPersonnel[role].map(p => p.id).join(',');
            hiddenField.value = personnelIds;

            // Update the original input with the personnel names for display purposes
            const personnelNames = selectedPersonnel[role].map(p => p.name).join(', ');
            const inputField = document.getElementById(role);
            if (inputField) {
                inputField.value = personnelNames;
            }
        }

        // Load all personnel on page load
        function loadAllPersonnel() {
            // If jQuery is available
            if (typeof $ !== 'undefined') {
                $.ajax({
                    url: '../personnel_list/get_all_personnel.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Loaded personnel:', data);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading personnel:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);
                    }
                });
            } else {
                console.warn('jQuery not available for personnel loading');
                // Fallback to fetch API if needed
            }
        }

        // Function to load available years
        function loadAvailableYears() {
            fetch('get_available_data.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        const yearSelect = document.getElementById('year');
                        if (yearSelect) {
                            yearSelect.innerHTML = '<option value="">Select Year</option>';
                            
                            if (data.success) {
                                if (data.years && data.years.length > 0) {
                                    data.years.forEach(year => {
                                        const option = document.createElement('option');
                                        option.value = year;
                                        option.textContent = year;
                                        yearSelect.appendChild(option);
                                    });
                                }
                                
                                if (data.message) {
                                    Swal.fire({
                                        title: 'Information',
                                        text: data.message,
                                        icon: 'info',
                                        confirmButtonColor: '#6a1b9a'
                                    });
                                }
                            } else {
                                throw new Error(data.message || 'Failed to load years');
                            }
                        }
                    } catch (e) {
                        console.error('Server response:', text);
                        throw new Error('Invalid response from server');
                    }
                })
                .catch(error => {
                    console.error('Error loading years:', error);
                    console.error('Error details:', error.stack);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load available years. Please try refreshing the page.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Load quarters for selected year
        function loadQuarters(year) {
            const quarterSelect = document.getElementById('quarter');
            if (!quarterSelect) return;
            
            quarterSelect.disabled = true;
            quarterSelect.innerHTML = '<option value="">Select Quarter</option>';

            if (year) {
                fetch(`get_available_data.php?year=${year}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            if (data.quarters && data.quarters.length > 0) {
                                data.quarters.forEach(quarter => {
                                    const option = document.createElement('option');
                                    option.value = quarter;
                                    option.textContent = quarter;
                                    quarterSelect.appendChild(option);
                                });
                                quarterSelect.disabled = false;
                            } else {
                                Swal.fire({
                                    title: 'Information',
                                    text: 'No quarters available for the selected year',
                                    icon: 'info',
                                    confirmButtonColor: '#6a1b9a'
                                });
                            }
                        } else {
                            throw new Error(data.message || 'Failed to load quarters');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading quarters:', error);
                        Swal.fire({
                            title: 'Error',
                            text: error.message || 'Failed to load quarters',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    });
            }
        }

        // Project team fetching function
        function fetchProjectTeam() {
            const year = document.getElementById('year')?.value;
            const quarter = document.getElementById('quarter')?.value;

            if (year && quarter) {
                // Clear existing personnel
                selectedPersonnel = {
                    projectLeaders: [],
                    assistantProjectLeaders: [],
                    projectStaff: []
                };
                
                const projectLeadersList = document.getElementById('projectLeadersList');
                const assistantProjectLeadersList = document.getElementById('assistantProjectLeadersList');
                const projectStaffList = document.getElementById('projectStaffList');
                
                if (projectLeadersList) projectLeadersList.innerHTML = '';
                if (assistantProjectLeadersList) assistantProjectLeadersList.innerHTML = '';
                if (projectStaffList) projectStaffList.innerHTML = '';
                
                const projectLeaders = document.getElementById('projectLeaders');
                const assistantProjectLeaders = document.getElementById('assistantProjectLeaders');
                const projectStaff = document.getElementById('projectStaff');
                
                if (projectLeaders) projectLeaders.value = '';
                if (assistantProjectLeaders) assistantProjectLeaders.value = '';
                if (projectStaff) projectStaff.value = '';
            }
        }

        // Print function
        function printProposal() {
            const proposalId = document.getElementById('currentProposalId')?.value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please save the proposal first before printing',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Ask which version to print
            Swal.fire({
                title: 'Choose Print Format',
                text: 'Select the format you want to use for printing',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'PDF Format',
                cancelButtonText: 'HTML Format',
                showCloseButton: true,
                confirmButtonColor: '#6a1b9a',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    // PDF option selected
                    window.open(`print_proposal.php?id=${proposalId}`, '_blank');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    // HTML option selected
                    window.open(`print_html.php?id=${proposalId}`, '_blank');
                }
            });
        }

        // Function to fetch PPAS data
        function fetchPPASData(year, quarter) {
            fetch(`get_ppas_data.php?year=${year}&quarter=${quarter}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Store PPAS ID in hidden field
                        const ppasIdInput = document.getElementById('ppasId') || document.createElement('input');
                        ppasIdInput.type = 'hidden';
                        ppasIdInput.id = 'ppasId';
                        ppasIdInput.name = 'ppasId';
                        ppasIdInput.value = data.data.ppas.id;
                        
                        // Add to form if it doesn't exist
                        const form = document.getElementById('gadProposalForm');
                        if (!document.getElementById('ppasId') && form) {
                            form.appendChild(ppasIdInput);
                        }
                        
                        // Populate title from PPAS data
                        document.getElementById('activityTitle').value = data.data.ppas.title || '';
                        
                        // Populate start and end dates if available 
                        if (data.data.ppas.start_date) {
                            document.getElementById('startDate').value = data.data.ppas.start_date;
                        }
                        
                        if (data.data.ppas.end_date) {
                            document.getElementById('endDate').value = data.data.ppas.end_date;
                        } else if (data.data.ppas.start_date) {
                            // If no end date is available, use start date as fallback
                            document.getElementById('endDate').value = data.data.ppas.start_date;
                        }
                        
                        // Generate work plan dates based on the new date values
                        generateWorkPlanDates();
                        
                        // Populate venue/location if available
                        if (data.data.ppas.location) {
                            document.getElementById('venue').value = data.data.ppas.location;
                        }

                        // Clear existing personnel
                        selectedPersonnel = {
                            projectLeaders: [],
                            assistantProjectLeaders: [],
                            projectStaff: []
                        };

                        // Clear personnel lists
                        document.getElementById('projectLeadersList').innerHTML = '';
                        document.getElementById('assistantProjectLeadersList').innerHTML = '';
                        document.getElementById('projectStaffList').innerHTML = '';
                        
                        // Add note about personnel being read-only
                        const personnelNotes = document.querySelectorAll('.personnel-note');
                        personnelNotes.forEach(note => {
                            note.style.display = 'block';
                        });

                        // Populate personnel data
                        if (data.data.personnel && Array.isArray(data.data.personnel)) {
                            data.data.personnel.forEach(person => {
                                // Map the role from database to our frontend role names
                                let role;
                                switch (person.role) {
                                    case 'project_leader':
                                    case 'projectLeader':
                                        role = 'projectLeaders';
                                        break;
                                    case 'assistant_project_leader':
                                    case 'assistantProjectLeader':
                                    case 'asst_project_leader':
                                        role = 'assistantProjectLeaders';
                                        break;
                                    case 'project_staff':
                                    case 'projectStaff':
                                        role = 'projectStaff';
                                        break;
                                }

                                if (role) {
                                    addPersonnelToList(role, {
                                        id: person.personnel_id,
                                        name: person.personnel_name,
                                        gender: '', // Not provided in this data structure
                                        academic_rank: '' // Not provided in this data structure
                                    });
                                }
                            });
                        }
                        
                        // Show success message
                        Swal.fire({
                            title: 'Data Loaded',
                            text: 'PPAS data has been loaded successfully',
                            icon: 'success',
                            confirmButtonColor: '#6a1b9a',
                            timer: 2000,
                            timerProgressBar: true
                        });
                    } else if (data.error) {
                        console.error('Server error:', data.message);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to fetch PPAS data: ' + data.message,
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    } else {
                        Swal.fire({
                            title: 'No Data Found',
                            text: data.message || 'No PPAS data found for the selected year and quarter',
                            icon: 'info',
                            confirmButtonColor: '#6a1b9a'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching PPAS data:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch PPAS data. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Add event listener for quarter selection
        document.getElementById('quarter').addEventListener('change', function() {
            const year = document.getElementById('year').value;
            const quarter = this.value;
            
            if (year && quarter) {
                fetchTitlesForYearQuarter(year, quarter);
            }
        });

        // Function to fetch titles for a specific year and quarter
        function fetchTitlesForYearQuarter(year, quarter) {
            const titleSelector = document.getElementById('titleSelector');
            titleSelector.disabled = true;
            titleSelector.innerHTML = '<option value="">Select Activity</option>';
            
            fetch(`get_titles.php?year=${year}&quarter=${quarter}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.titles && data.titles.length > 0) {
                        data.titles.forEach(title => {
                            const option = document.createElement('option');
                            option.value = title.id; // Assuming each title has an ID
                            option.textContent = title.title;
                            titleSelector.appendChild(option);
                        });
                        titleSelector.disabled = false;
                        
                        // Show message if multiple titles available
                        if (data.titles.length > 1) {
                            Swal.fire({
                                title: 'Multiple Activities Available',
                                text: `${data.titles.length} activities found for ${quarter} ${year}. Please select one.`,
                                icon: 'info',
                                confirmButtonColor: '#6a1b9a',
                                timer: 3000,
                                timerProgressBar: true
                            });
                        }
                    } else {
                        Swal.fire({
                            title: 'No Activities Found',
                            text: data.message || 'No activities found for the selected year and quarter',
                            icon: 'info',
                            confirmButtonColor: '#6a1b9a'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching titles:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch activities. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Add event listener for title selection
        document.getElementById('titleSelector').addEventListener('change', function() {
            const ppasId = this.value;
            
            if (ppasId) {
                // Fetch PPAS data for the selected title
                fetch(`get_ppas_data.php?id=${ppasId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            // Store PPAS ID in hidden field
                            const ppasIdInput = document.getElementById('ppasId') || document.createElement('input');
                            ppasIdInput.type = 'hidden';
                            ppasIdInput.id = 'ppasId';
                            ppasIdInput.name = 'ppasId';
                            ppasIdInput.value = data.data.ppas.id;
                            
                            // Add to form if it doesn't exist
                            const form = document.getElementById('gadProposalForm');
                            if (!document.getElementById('ppasId') && form) {
                                form.appendChild(ppasIdInput);
                            }
                            
                            // Populate title from PPAS data
                            document.getElementById('activityTitle').value = data.data.ppas.title || '';
                            
                            // Populate start and end dates if available 
                            if (data.data.ppas.start_date) {
                                document.getElementById('startDate').value = data.data.ppas.start_date;
                            }
                            
                            if (data.data.ppas.end_date) {
                                document.getElementById('endDate').value = data.data.ppas.end_date;
                            } else if (data.data.ppas.start_date) {
                                // If no end date is available, use start date as fallback
                                document.getElementById('endDate').value = data.data.ppas.start_date;
                            }
                            
                            // Generate work plan dates based on the new date values
                            generateWorkPlanDates();
                            
                            // Populate venue/location if available
                            if (data.data.ppas.location) {
                                document.getElementById('venue').value = data.data.ppas.location;
                            }

                            // Clear existing personnel
                            selectedPersonnel = {
                                projectLeaders: [],
                                assistantProjectLeaders: [],
                                projectStaff: []
                            };

                            // Clear personnel lists
                            document.getElementById('projectLeadersList').innerHTML = '';
                            document.getElementById('assistantProjectLeadersList').innerHTML = '';
                            document.getElementById('projectStaffList').innerHTML = '';
                            
                            // Add note about personnel being read-only
                            const personnelNotes = document.querySelectorAll('.personnel-note');
                            personnelNotes.forEach(note => {
                                note.style.display = 'block';
                            });

                            // Populate personnel data
                            if (data.data.personnel && Array.isArray(data.data.personnel)) {
                                data.data.personnel.forEach(person => {
                                    // Map the role from database to our frontend role names
                                    let role;
                                    switch (person.role) {
                                        case 'project_leader':
                                        case 'projectLeader':
                                            role = 'projectLeaders';
                                            break;
                                        case 'assistant_project_leader':
                                        case 'assistantProjectLeader':
                                        case 'asst_project_leader':
                                            role = 'assistantProjectLeaders';
                                            break;
                                        case 'project_staff':
                                        case 'projectStaff':
                                            role = 'projectStaff';
                                            break;
                                    }

                                    if (role) {
                                        addPersonnelToList(role, {
                                            id: person.personnel_id,
                                            name: person.personnel_name,
                                            gender: '', // Not provided in this data structure
                                            academic_rank: '' // Not provided in this data structure
                                        });
                                    }
                                });
                            }
                        } else if (data.error) {
                            console.error('Server error:', data.message);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to fetch PPAS data: ' + data.message,
                                icon: 'error',
                                confirmButtonColor: '#6a1b9a'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching PPAS data:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to fetch PPAS data. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    });
            }
        });

        // Document ready function
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Initialize page
                showPage(1);
                updateDateTime();
                setInterval(updateDateTime, 1000);
                
                // Load theme
                const savedTheme = localStorage.getItem('theme') || 'light';
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
                updateThemeIcon(savedTheme);
                
                // Add event listeners for form fields
                const maleBeneficiaries = document.getElementById('maleBeneficiaries');
                const femaleBeneficiaries = document.getElementById('femaleBeneficiaries');
                if (maleBeneficiaries) maleBeneficiaries.addEventListener('input', updateTotalBeneficiaries);
                if (femaleBeneficiaries) femaleBeneficiaries.addEventListener('input', updateTotalBeneficiaries);
                
                // Form submission
                const gadProposalForm = document.getElementById('gadProposalForm');
                if (gadProposalForm) {
                    gadProposalForm.addEventListener('submit', handleFormSubmit);
                    
                    // Reset form
                    gadProposalForm.reset();
                }
                
                // Year selection
                const yearSelect = document.getElementById('year');
                if (yearSelect) {
                    yearSelect.addEventListener('change', function() {
                        const year = this.value;
                        const quarterSelect = document.getElementById('quarter');
                        const titleSelector = document.getElementById('titleSelector');
                        
                        if (quarterSelect) {
                            quarterSelect.value = '';
                            loadQuarters(year);
                        }
                        
                        // Reset the title selector
                        if (titleSelector) {
                            titleSelector.innerHTML = '<option value="">Select Activity</option>';
                            titleSelector.disabled = true;
                        }
                        
                        // Clear project team fields
                        const projectLeaders = document.getElementById('projectLeaders');
                        const assistantProjectLeaders = document.getElementById('assistantProjectLeaders');
                        const projectStaff = document.getElementById('projectStaff');
                        
                        if (projectLeaders) projectLeaders.value = '';
                        if (assistantProjectLeaders) assistantProjectLeaders.value = '';
                        if (projectStaff) projectStaff.value = '';
                    });
                }
                
                // Quarter selection
                const quarterSelect = document.getElementById('quarter');
                if (quarterSelect) {
                    quarterSelect.addEventListener('change', fetchProjectTeam);
                }
                
                // Generate Work Plan dates if start and end dates are already set
                const startDate = document.getElementById('startDate');
                const endDate = document.getElementById('endDate');
                if (startDate && endDate && startDate.value && endDate.value) {
                    generateWorkPlanDates();
                }
                
                // Add event listeners for dates
                if (startDate) startDate.addEventListener('change', generateWorkPlanDates);
                if (endDate) endDate.addEventListener('change', generateWorkPlanDates);
                
                // Work plan and monitoring row buttons
                const addWorkPlanRowBtn = document.getElementById('addWorkPlanRowBtn');
                const addMonitoringRowBtn = document.getElementById('addMonitoringRowBtn');
                if (addWorkPlanRowBtn) addWorkPlanRowBtn.addEventListener('click', addWorkPlanRow);
                if (addMonitoringRowBtn) addMonitoringRowBtn.addEventListener('click', addMonitoringRow);
                
                // Initialize autocomplete for personnel fields if jQuery and jQuery UI are available
                if (typeof $ !== 'undefined' && $.fn.autocomplete) {
                    // We no longer need autocomplete for these fields as they're read-only
                    // The personnel comes from PPAS data and cannot be edited
                    
                    // Add CSS for personnel badges
                    $("<style>")
                        .prop("type", "text/css")
                        .html(`
                            .personnel-badge {
                                font-size: 0.9rem;
                                padding: 0.5rem 0.75rem;
                            }
                            .personnel-badge .remove-personnel {
                                cursor: pointer;
                            }
                        `)
                        .appendTo("head");
                }
                
                // Load available years
                loadAvailableYears();
                
                // Clear textareas
                document.querySelectorAll('textarea').forEach(textarea => {
                    textarea.value = '';
                });
                
                console.log("GAD Proposal form initialization complete");
            } catch (error) {
                console.error("Error initializing GAD Proposal form:", error);
                alert("There was an error initializing the form. Please check the console for details or refresh the page.");
            }
        });

        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileToggle = document.querySelector('.mobile-nav-toggle');
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            
            if (mobileToggle && sidebar && backdrop) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    backdrop.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open');
                });
                
                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    backdrop.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });
            }
        });

        function loadProposal(id) {
            showSpinner();
            fetch(`get_gad_proposal.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fillForm(data.proposal);
                        if (data.activities) {
                            clearExistingActivities(); // Clear any existing activities
                            data.activities.forEach(activity => {
                                addActivity(activity);
                            });
                        }
                        loadPersonnel(id);
                        document.getElementById('currentProposalId').value = id;
                        
                        // Show the print and navigation buttons
                        document.getElementById('printBtn').style.display = 'inline-block';
                        document.getElementById('printHtmlBtn').style.display = 'inline-block';
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Failed to load proposal: ' + data.message,
                            icon: 'error',
                            confirmButtonColor: '#6a1b9a'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading proposal:', error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to load proposal. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#6a1b9a'
                    });
                });
        }

        // Function to print HTML version
        function printHtmlVersion() {
            const proposalId = document.getElementById('currentProposalId')?.value;
            
            if (!proposalId) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please save the proposal first before printing',
                    icon: 'warning',
                    confirmButtonColor: '#6a1b9a'
                });
                return;
            }
            
            // Open HTML print view in new window
            window.open(`print_html.php?id=${proposalId}`, '_blank');
        }
    </script>
</body>
</html>