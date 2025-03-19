<?php
session_start();
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target Forms - GAD System</title>
    <link rel="icon" type="image/x-icon" href="../images/Batangas_State_Logo.ico">
    <script src="../js/common.js"></script>
    <style>
        /* Prevent theme flash */
        html[data-bs-theme="dark"] {
            background-color: #1a1a1a !important;
        }

        /* Remove the green check mark for valid campus selection */
        #campus.is-valid + .valid-feedback i {
            display: none; /* Hide the checkmark icon */
        }

        /* Ensure the valid-feedback icon is hidden by default */
        .valid-feedback i {
            display: none; /* Hide all checkmark icons by default */
        }
    </style>
    <script>
        // Immediate theme loading
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            --bg-secondary: #e5e5e5;
            --sidebar-bg: white;
            --text-primary: #444444;
            --text-secondary: #666666;
            --hover-color: #e1bee7;
            --card-bg: white;
            --border-color: #cccccc;
            --horizontal-bar: #cccccc;
            --input-bg: #ffffff;
            --table-bg: #ffffff;
            --table-border: #cccccc;
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: rgba(156, 39, 176, 0.1);
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --table-bg: #2d2d2d;
            --table-border: #404040;
            --table-row-bg: #1a1a1a;
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;  /* Brighter purple for sidebar text/icons */
            --accent-hover: #7b1fa2;  /* Brighter purple for hover */
        }

        /* Update these selectors to ensure they use the brighter purple */
        [data-bs-theme="dark"] .nav-link.active,
        [data-bs-theme="dark"] .datetime-container .time,
        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item::before,
        [data-bs-theme="dark"] .nav-link:hover {
            color: #9c27b0;  /* Force the brighter purple */
        }

        [data-bs-theme="dark"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: #9c27b0 !important;
        }

        /* Theme Switch Styles */
        .theme-switch {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .theme-switch-button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: none;
        }

        /* Light theme button styles */
        [data-bs-theme="light"] .theme-switch-button {
            background: #1a1a1a; 
            color: var(--text-primary) !important;  
        }

        /* Dark theme button styles */
        [data-bs-theme="dark"] .theme-switch-button {
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .theme-switch-button i {
    font-size: 1.0rem;
}

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
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
        }

        .main-content {
    margin-top: -50px;
    margin-left: calc(var(--sidebar-width) + 20px);
    margin-right: 20px;
    height: calc(110vh);  /* Remove the -0px and simplify */
    background: transparent;
    display: flex;
    flex-direction: column;
    overflow-y: scroll !important;  /* Change from auto to scroll */
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 20px;
    position: relative;
}

        /* Hide webkit scrollbar */
.main-content::-webkit-scrollbar {
    display: none;  /* Chrome, Safari, Opera */
}

/* Ensure body doesn't show scrollbar either */
body {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;     /* Firefox */
    overflow: hidden;
}

body::-webkit-scrollbar {
    display: none;
}

        .forms-container {
            display: flex;
            flex-direction: column;  
            margin: 0;
            padding-bottom: 20px; 
        }

        .target-form-card {
    margin: 0;
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    background: var(--card-bg);
    border-color: var(--border-color);
    height: 300px;  /* Decreased from 250px to 200px */
}

        .target-form-card .card-body {
            flex: 1;
            padding: 1.25rem;
        }

        .year-overview-card {
            flex: 0 0 auto;
            background: var(--card-bg);
            border-color: var(--border-color);
            margin: 0;
            height: 400px; 
        }

        .year-overview-card .card-body {
            height: 100%;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
        }

        .year-overview-card .table-responsive {
            flex: 1;
            display: flex;
            align-items: center;
        }

        #yearOverviewTable {
            margin: 0;  
        }

        #yearOverviewTable th,
        #yearOverviewTable td {
            padding: 0.75rem;
            vertical-align: middle;
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

        .nav-link:hover {
            background: var(--hover-color);
            color: white;
        }

        /* Restore light mode hover color */
        [data-bs-theme="light"] .nav-link:hover {
            color: var(--accent-color);
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

        .nav-link.dropdown-toggle::after {
            transition: transform 0.3s ease;
        }

        .nav-link.dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
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

        .analytics-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            height: 100%;
        }

        .analytics-card:hover {
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
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

        .logout-container {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
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
            color: white !important;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
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
                margin: 0;
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

            .analytics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-top: 20px;
            }

            .analytics-card {
                margin-bottom: 0;
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
        
        .form-control.is-valid, .form-select.is-valid {
            border-color: inherit;
            box-shadow: none;
        }

        .form-control.is-valid:focus, .form-select.is-valid:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .input-group .form-control.is-valid {
            z-index: 3;
            background-size: 16px 16px;
            background-position: right 10px center;
        }

        .input-group .form-control.is-valid:focus {
            z-index: 3;
        }

        .form-select.is-valid {
            background-size: 16px 16px;
            background-position: right 10px center;
            padding-right: 40px;
        }

        .swal2-backdrop-show {
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .form-control:read-only {
            background-color: var(--bg-secondary);
            cursor: not-allowed;
        }

        /* Disabled form controls styling */
        .form-select:disabled,
        .form-control:disabled {
            background-color: var(--bg-secondary);
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.8;
        }

        /* Default disabled state with diagonal pattern */
        #year:disabled,
        #total_gaa:disabled,
        #total_gad_fund {
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(0, 0, 0, 0.05) 10px,
                rgba(0, 0, 0, 0.05) 20px
            );
        }

        /* Special handling for select element (year dropdown) */
        #year:disabled {
            background-image: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(0, 0, 0, 0.05) 10px,
                    rgba(0, 0, 0, 0.05) 20px
                ),
                url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-position: 0 0, right 1.25rem center;
            background-repeat: repeat, no-repeat;
            background-size: auto, 16px 12px;
        }

        /* Remove diagonal pattern from total_gad_fund when year is selected */
        #year:not(:disabled) ~ .col-md-6 #total_gad_fund {
            background-image: none;
        }

        /* Style disabled input group */
        .input-group .form-control:disabled {
            background-color: var(--bg-secondary);
        }

        /* Override the diagonal pattern for validated select elements */
        #year.is-valid:disabled {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right 0.75rem center !important;
            background-size: 12px 12px !important;
            padding-right: calc(1.5em + 0.75rem) !important;
        }

        /* Make validation icon smaller for all validated inputs */
        .form-control.is-valid,
        .form-select.is-valid {
            background-size: 12px 12px !important;
        }

        .input-group .form-control:disabled + .input-group-text,
        .input-group .input-group-text:has(+ .form-control:disabled) {
            background-color: var(--bg-secondary);
            border-color: #dee2e6;
            color: #6c757d;
        }

        .invalid-feedback {
            color: var(--accent-color);
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* Modern icon buttons */
        .btn-icon {
            width: 45px;
            height: 45px;
            padding: 0;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .btn-icon i {
            font-size: 1.2rem;
        }

        /* Add button */
        #addTargetBtn {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addTargetBtn:hover {
            background: #198754;
            color: white;
        }

        /* Edit button */
        #editTargetBtn {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        #editTargetBtn:hover {
            background: #ffc107;
            color: white;
        }

        /* Edit button in cancel mode */
        #editTargetBtn.editing {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
        }

        #editTargetBtn.editing:hover {
            background: #dc3545 !important;
            color: white !important;
        }

        /* Delete button */
        #deleteTargetBtn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        #deleteTargetBtn:hover {
            background: #dc3545;
            color: white;
        }

        /* Delete button disabled state */
        #deleteTargetBtn.disabled {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }

        /* Update button state (when form is in edit mode) */
        #addTargetBtn.btn-update {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        #addTargetBtn.btn-update:hover {
            background: #198754;
            color: white;
        }

        /* SweetAlert2 Custom Styles */
        .modern-popup {
            border-radius: 15px !important;
            padding: 2rem !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .modern-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: var(--text-primary) !important;
        }

        .modern-input {
            border-radius: 10px !important;
            border: 1px solid var(--border-color) !important;
            padding: 0.75rem 1rem !important;
            background-color: var(--bg-primary) !important;
            color: var(--text-primary) !important;
            font-size: 1rem !important;
        }

        .modern-input:focus {
            border-color: var(--border-color) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        }

        .modern-confirm-button,
        .modern-cancel-button {
            border-radius: 10px !important;
            padding: 0.75rem 1.5rem !important;
            font-weight: 500 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
        }

        .modern-confirm-button i,
        .modern-cancel-button i {
            font-size: 1rem !important;
        }

        .year-overview-card .card-body {
            height: 100%;
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
        }

        .overview-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .overview-header i,
        .form-header i {
            font-size: 1.1rem;
            color: var(--accent-color);
        }

        .overview-header h4,
        .form-header h4 {
            font-size: 1.1rem;
            margin: 0;
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        #campusFilter {
            max-width: 150px;
        }

        #campusFilter {
            width: 200px;
            min-width: 200px;
        }

        #yearOverviewTable {
            table-layout: fixed;
            width: 100%;
        }

        #yearOverviewTable th {
            width: 20%;
        }

        #yearOverviewTable td {
            width: 20%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fund-amount {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Current year styling */
        .current-year {
            background-color: var(--accent-color) !important;
            color: white !important;
        }

        /* Remove hover effect from non-current years */
        #yearOverviewTable th:not(.current-year):hover {
            background-color: inherit !important;
            color: inherit !important;
        }

        /* Keep current year styling on hover */
        .current-year:hover {
            background-color: var(--accent-hover) !important;
            color: white !important;
        }

        .form-control, .form-select {
            background-color: var(--input-bg, #ffffff);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg, #ffffff);
            color: var(--text-primary);
            border-color: var(--accent-color);
        }

        .table {
            color: var(--text-primary);
            background-color: var(--table-bg);
            border-color: var(--table-border);
        }

        .table th, .table td {
            border-color: var(--table-border);
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .table tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .card-title, .overview-header {
            color: var(--text-primary);
        }

        .form-header h4, .overview-header h4 {
            color: var(--text-primary);
        }

        .year-range {
            color: var(--text-primary);
        }

        .table {
            color: var(--text-primary);
            background-color: var(--table-bg);
            border-color: var(--table-border);
        }

        .table th, .table td {
            border-color: var(--table-border);
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        .table tbody tr {
            background-color: var(--table-bg);
        }

        .table tbody tr:nth-of-type(even) {
            background-color: var(--table-row-bg);
        }

        [data-bs-theme="dark"] .table tbody tr {
            background-color: var(--table-bg);
        }

        [data-bs-theme="dark"] .table tbody tr:nth-of-type(even) {
            background-color: var(--table-row-bg);
        }

        .year-pagination, .year-pagination span { 
            color: var(--text-primary) !important; 
        }

        #yearOverviewTable tbody tr:nth-child(2), #yearOverviewTable tbody tr:nth-child(3) { 
            background-color: var(--bg-primary) !important; 
        }

        /* Year navigation styles */
        .year-navigation {
            color: var(--text-primary) !important;
        }
        
        .year-navigation .current-range {
            color: var(--text-primary) !important;
        }

        .year-navigation button {
            color: var(--text-primary) !important;
            border-color: var(--text-primary) !important;
        }

        /* Target overview table styles */
        #yearOverviewTable tbody tr.target-status-row,
        #yearOverviewTable tbody tr.fund-row {
            background-color: var(--bg-primary) !important;
        }

        #yearOverviewTable tbody tr.target-status-row td,
        #yearOverviewTable tbody tr.fund-row td {
            color: var(--text-primary) !important;
            background-color: var(--bg-primary) !important;
        }

        /* Navigation hover styles */
        .nav-link:hover,
        .nav-link.active:hover,
        .nav-item .dropdown-menu .dropdown-item:hover,
        .nav-item .dropdown-toggle[aria-expanded="true"] {
            background: #8A4EBD !important;
            color: white !important;
        }

        /* Override theme-specific styles */
        [data-bs-theme="light"] .nav-link:hover,
        [data-bs-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover,
        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"],
        [data-bs-theme="dark"] .nav-link:hover,
        [data-bs-theme="dark"] .nav-link.active:hover {
            background: #8A4EBD !important;
            color: white !important;
        }

        /* Dark mode dropdown toggle styles */
        [data-bs-theme="dark"] .nav-link:hover,
        [data-bs-theme="dark"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            background: #8A4EBD !important;
            color: white !important;
        }

        /* Keep the dropdown menu and items unchanged */
        [data-bs-theme="dark"] .nav-item .dropdown-menu {
            background: var(--sidebar-bg);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item {
            color: var(--text-primary);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item:hover {
            background: #8A4EBD !important;
            color: white !important;
        }

        /* Light mode - update to match the lighter purple from Staff dropdown */
        .nav-link:hover,
        .nav-item .dropdown-menu .dropdown-item:hover,
        .nav-item .dropdown-toggle[aria-expanded="true"] {
            background: rgba(138, 78, 189, 0.2) !important;
            color: var(--accent-color) !important;
        }

        /* Keep our working dark mode styles unchanged */
        [data-bs-theme="dark"] .nav-link:hover,
        [data-bs-theme="dark"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            background: #8A4EBD !important;
            color: white !important;
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu {
            background: var(--sidebar-bg);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item {
            color: var(--text-primary);
        }

        [data-bs-theme="dark"] .nav-item .dropdown-menu .dropdown-item:hover {
            background: #8A4EBD !important;
            color: white !important;
        }
    </style>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
        }
        
        /* Light Theme Variables */
        [data-bs-theme="light"] {
            --bg-primary: #f0f0f0;
            --bg-secondary: #e5e5e5;
            --sidebar-bg: white;
            --text-primary: #444444;
            --text-secondary: #666666;
            --hover-color: #e1bee7;
            --card-bg: white;
            --border-color: #cccccc;
            --horizontal-bar: #cccccc;
        }

        /* Dark Theme Variables */
        [data-bs-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: rgba(156, 39, 176, 0.1);
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --input-placeholder: rgba(255, 255, 255, 0.7);
            --input-bg: #404040;
            --input-text: #ffffff;
            --card-title: #ffffff;
            --scrollbar-thumb: rgba(156, 39, 176, 0.4);
            --scrollbar-thumb-hover: rgba(156, 39, 176, 0.7);
            --accent-color: #9c27b0;  /* Brighter purple for sidebar text/icons */
            --accent-hover: #7b1fa2;  /* Brighter purple for hover */
            --button-accent: #6a1b9a;  /* Darker purple for buttons */
            --button-hover: #4a148c;  /* Darker purple for button hover */
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            padding: 20px;
            overflow-y: hidden;
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
            background: var(--hover-color) !important;
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
            background: var(--hover-color) !important;
            color: var(--accent-color) !important;
        }

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
        }

        [data-bs-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
            background: var(--hover-color) !important;
        }

        .nav-link.dropdown-toggle::after {
            transition: transform 0.3s ease;
        }

        .nav-link.dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
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
        
        .fade-out {
            opacity: 0;
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
                <a class="nav-link" href="../dashboard.php">
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
                        <li><a class="dropdown-item" href="target.php">Target Form</a></li>
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
        <div class="forms-container">
            <!-- Target Form Card -->
            <div class="card target-form-card">
                <div class="card-body">
                    <div class="form-header">
                        <i class="fas fa-file-alt"></i>
                        <h4>Target Form</h4>
                    </div>
                    <form id="targetForm" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="campus" class="form-label">Campus</label>
                                    <select class="form-select" id="campus" name="campus" required>
                                        <option value="">Select Campus</option>
                                        <option value="Lipa">Lipa</option>
                                        <option value="Pablo Borbon">Pablo Borbon</option>
                                        <option value="Alangilan">Alangilan</option>
                                        <option value="Nasugbu">Nasugbu</option>
                                        <option value="Malvar">Malvar</option>
                                        <option value="Rosario">Rosario</option>
                                        <option value="Balayan">Balayan</option>
                                        <option value="Lemery">Lemery</option>
                                        <option value="San Juan">San Juan</option>
                                        <option value="Lobo">Lobo</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a campus.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" id="year" name="year" required style="background-image: none;">
                                        <option value="">Select Year</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a year.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                            <div class="form-group">
    <label for="total_gaa" class="form-label">Total GAA</label>
    <div class="input-group">
        <span class="input-group-text">₱</span>
        <input type="text" class="form-control" id="total_gaa" name="total_gaa" pattern="[1-9][0-9]*" required 
               oninput="this.value = this.value.replace(/^0+|[^0-9]/g, '')" style="background-image: none;">
    </div>
    <div class="invalid-feedback">Please enter a valid total GAA amount (no leading zeros)</div>
</div>  
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_gad_fund" class="form-label">Total GAD Fund (5% of GAA)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="total_gad_fund" name="total_gad_fund" required readonly>
                                    </div>
                                    <div class="invalid-feedback">GAD Fund is required.</div>
                                </div>
                            </div>

                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-icon me-2" id="addTargetBtn">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button type="button" class="btn btn-icon me-2" id="editTargetBtn">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-icon" id="deleteTargetBtn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Year Overview Section -->
            <div class="card year-overview-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="overview-header">
                            <i class="fas fa-chart-line"></i>
                            <h4 class="mb-0">Target Overview</h4>
                        </div>
                        <div class="d-flex align-items-center year-overview-controls">
                            <select id="campusFilter" class="form-select me-2">
                                <option value="Lipa">Lipa</option>
                                <option value="Pablo Borbon">Pablo Borbon</option>
                                <option value="Alangilan">Alangilan</option>
                                <option value="Nasugbu">Nasugbu</option>
                                <option value="Malvar">Malvar</option>
                                <option value="Rosario">Rosario</option>
                                <option value="Balayan">Balayan</option>
                                <option value="Lemery">Lemery</option>
                                <option value="San Juan">San Juan</option>
                                <option value="Lobo">Lobo</option>
                            </select>
                            <div class="year-navigation">
                                <button class="btn btn-outline-primary" id="prevYearSet">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="current-range"></span>
                                <button class="btn btn-outline-primary" id="nextYearSet">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered text-center" id="yearOverviewTable">
                            <thead>
                                <tr class="year-row"></tr>
                            </thead>
                            <tbody>
                                <tr class="target-status-row"></tr>
                                <tr class="fund-row"></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Function to format number without leading zeros
        function formatNumber(value) {
            if (!value) return '';
            
            // Handle decimal numbers
            if (value.includes('.')) {
                const [whole, decimal] = value.split('.');
                // Remove leading zeros from whole number part unless it's just "0"
                const formattedWhole = whole.replace(/^0+(?=\d)/, '');
                return formattedWhole + '.' + decimal;
            }
            
            // Remove leading zeros for whole numbers
            return value.replace(/^0+(?=\d)/, '');
        }

        // Function to update available years based on selected campus
        function populateYears(existingYears) {
            const yearSelect = document.getElementById('year');
            const currentYear = new Date().getFullYear();
            
            console.log('Existing years:', existingYears);
            
            // Clear existing options
            yearSelect.innerHTML = '<option value="">Select Year</option>';
            
            // Get 5 years starting from current year
            const years = [];
            let availableYears = 0;
            let yearToAdd = currentYear;
            
            while (availableYears < 5) {
                if (!existingYears.includes(yearToAdd)) {
                    years.push(yearToAdd);
                    availableYears++;
                }
                yearToAdd++;
            }
            
            // Add year options
            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            });
            
            // Enable the year select if there are available years
            yearSelect.disabled = yearSelect.options.length <= 1;
            if (yearSelect.options.length <= 1) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Available Years',
                    text: 'All years have targets set for this campus.'
                });
            }
        }

        // Function to reset form
        function resetForm() {
            const form = document.getElementById('targetForm');
            const yearSelect = document.getElementById('year');
            const addTargetBtn = document.getElementById('addTargetBtn');
            const editTargetBtn = document.getElementById('editTargetBtn');
            const deleteTargetBtn = document.getElementById('deleteTargetBtn');
            const totalGaaInput = document.getElementById('total_gaa');
            
            form.reset();
            
            // Reset year select to "Select Year"
            yearSelect.innerHTML = '<option value="">Select Year</option>';
            yearSelect.value = '';
            
            // Reset button states
            addTargetBtn.classList.remove('btn-update');
            addTargetBtn.innerHTML = '<i class="fas fa-plus"></i>';
            editTargetBtn.classList.remove('editing');
            editTargetBtn.innerHTML = '<i class="fas fa-edit"></i>';
            deleteTargetBtn.classList.remove('disabled');
            
            // Remove all validation classes and icons
            form.classList.remove('was-validated');
            const inputs = [yearSelect, totalGaaInput, document.getElementById('campus')];
            inputs.forEach(input => {
                input.classList.remove('is-valid', 'is-invalid');
                const validFeedback = input.parentElement.querySelector('.valid-feedback');
                if (validFeedback) {
                    const icon = validFeedback.querySelector('i');
                    if (icon) icon.style.display = 'none';
                }
            });
            
            // Re-enable campus input but disable year and total_gaa
            document.getElementById('campus').disabled = false;
            yearSelect.disabled = true;
            totalGaaInput.disabled = true;
        }

        // Function to validate input and show valid state
        function validateInput(input) {
    const value = input.value.trim();
    
    // Remove validation classes if empty
    if (!value) {
        input.classList.remove('is-valid', 'is-invalid');
        const parent = input.parentElement;
        const icon = parent.querySelector('.valid-feedback i');
        if (icon) {
            icon.style.display = 'none';
        }
        return;
    }

    if (input.checkValidity()) {
        input.classList.remove('is-invalid'); // Remove invalid class
        // Do not add is-valid class for campus
        if (input.id !== 'campus') {
            input.classList.add('is-valid'); // Only add for other inputs
        }
    } else {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        const parent = input.parentElement;
        const icon = parent.querySelector('.valid-feedback i');
        if (icon) {
            icon.style.display = 'none';
        }
    }
}

        // Function to validate Total GAA input
        function validateTotalGAA(input) {
            const value = input.value.trim();
            const numValue = parseFloat(value);
            
            // Remove any leading zeros
            if (value.startsWith('0')) {
                input.value = value.replace(/^0+/, '');
            }
            
            if (!value) {
                input.classList.remove('is-valid', 'is-invalid');
                const icon = input.parentElement.querySelector('.valid-feedback i');
                if (icon) icon.style.display = 'none';
                return;
            }

            // Check if value is 0 or negative or has leading zeros
            if (numValue <= 0 || isNaN(numValue) || /^0+/.test(value)) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
                const icon = input.parentElement.querySelector('.valid-feedback i');
                if (icon) icon.style.display = 'none';
                input.setCustomValidity('Total GAA must be greater than 0 and cannot have leading zeros');
            } else {
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
                const icon = input.parentElement.querySelector('.valid-feedback i');
                if (icon) icon.style.display = 'inline-block'; // Show the checkmark
                input.setCustomValidity('');
            }
        }

        // Initialize form controls
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('targetForm');
            const campusSelect = document.getElementById('campus');
            const yearSelect = document.getElementById('year');
            const totalGaaInput = document.getElementById('total_gaa');
            
            // Initially disable year and total_gaa
            yearSelect.disabled = true;
            totalGaaInput.disabled = true;

            // Add validation listeners to all required inputs
            document.querySelectorAll('select[required], input[required]').forEach(input => {
                input.addEventListener('input', function() {
                    if (this.id === 'total_gaa') {
                        validateTotalGAA(this);
                    } else {
                        validateInput(this);
                    }
                });

                input.addEventListener('change', function() {
                    if (this.id === 'total_gaa') {
                        validateTotalGAA(this);
                    } else {
                        validateInput(this);
                    }
                });
            });

            // Campus selection handler
            campusSelect.addEventListener('change', function() {
                const selectedCampus = this.value;
                yearSelect.value = '';
                totalGaaInput.value = '';
                document.getElementById('total_gad_fund').value = '';
                
                if (selectedCampus) {
                    yearSelect.disabled = false;
                    fetch(`../target_forms/get_existing_years.php?campus=${encodeURIComponent(selectedCampus)}`)
                        .then(response => response.json())
                        .then(existingYears => {
                            console.log('API Response:', existingYears);
                            if (Array.isArray(existingYears)) {
                                // Convert strings to numbers if needed
                                const years = existingYears.map(year => parseInt(year, 10));
                                console.log('Converted years:', years);
                                populateYears(years);
                            } else {
                                console.error('Invalid years data:', existingYears);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to fetch years. Please try again.'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching years:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch years. Please try again.'
                            });
                            yearSelect.disabled = true;
                        });
                } else {
                    yearSelect.disabled = true;
                    yearSelect.value = '';
                    totalGaaInput.disabled = true;
                    totalGaaInput.value = '';
                    document.getElementById('total_gad_fund').value = '';
                }
                validateInput(this);
            });

            // Year selection handler
            yearSelect.addEventListener('change', function() {
    totalGaaInput.value = '';
    document.getElementById('total_gad_fund').value = '';
    totalGaaInput.disabled = !this.value;
    validateInput(this); // Validate the year input
});

            // Calculate GAD Fund when GAA changes
            totalGaaInput.addEventListener('input', function() {
    const totalGAA = parseFloat(this.value) || 0;
    document.getElementById('total_gad_fund').value = (totalGAA * 0.05).toFixed(2);
    validateTotalGAA(this); // Validate the total GAA input
});

            // Form submit handler - SINGLE EVENT LISTENER
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const isUpdate = document.getElementById('editTargetBtn').classList.contains('editing');
                const formData = {
                    campus: document.getElementById('campus').value,
                    year: document.getElementById('year').value,
                    total_gaa: document.getElementById('total_gaa').value,
                    total_gad_fund: document.getElementById('total_gad_fund').value
                };

                console.log(`${isUpdate ? 'Updating' : 'Adding'} target:`, formData);

                try {
                    const url = isUpdate ? '../target_forms/update_target.php' : '../target_forms/add_target.php';
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();
                    console.log('Server response:', data);

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: isUpdate ? 'Updated!' : 'Added!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        resetForm();
                        updateYearOverview(new Date().getFullYear());
                    } else {
                        throw new Error(data.message || 'Failed to process request');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to process request',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });

            // Edit Target Button Handler
            document.getElementById('editTargetBtn').addEventListener('click', function() {
                if (this.classList.contains('editing')) {
                    resetForm();
                    return;
                }

                Swal.fire({
                    title: 'Select Campus',
                    html: '<h6 class="mb-3">Step 1 of 2</h6>',
                    input: 'select',
                    inputOptions: {
                        'Lipa': 'Lipa',
                        'Pablo Borbon': 'Pablo Borbon',
                        'Alangilan': 'Alangilan',
                        'Nasugbu': 'Nasugbu',
                        'Malvar': 'Malvar',
                        'Rosario': 'Rosario',
                        'Balayan': 'Balayan',
                        'Lemery': 'Lemery',
                        'San Juan': 'San Juan',
                        'Lobo': 'Lobo'
                    },
                    inputPlaceholder: 'Select Campus',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: '<i class="fas fa-arrow-right"></i>',
                    cancelButtonText: '<i class="fas fa-times"></i>',
                    reverseButtons: true,
                    inputValidator: (value) => !value && 'Please select a campus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const selectedCampus = result.value;
                        console.log('Selected campus:', selectedCampus);
                        
                        fetch(`../target_forms/get_existing_years.php?campus=${encodeURIComponent(selectedCampus)}`)
                            .then(response => response.json())
                            .then(years => {
                                if (!years || years.length === 0) {
                                    Swal.fire({
                                        title: 'No Data',
                                        text: `No targets found for ${selectedCampus} campus`,
                                        icon: 'info',
                                        confirmButtonColor: '#dc3545'
                                    });
                                    return;
                                }

                                const yearOptions = {};
                                years.forEach(year => yearOptions[year] = year);

                                return Swal.fire({
                                    title: 'Select Year',
                                    html: `<h6 class="mb-3">Step 2 of 2</h6><p>Selected Campus: ${selectedCampus}</p>`,
                                    input: 'select',
                                    inputOptions: yearOptions,
                                    inputPlaceholder: 'Select Year',
                                    showCancelButton: true,
                                    confirmButtonColor: '#6c757d',
                                    cancelButtonColor: '#dc3545',
                                    confirmButtonText: '<i class="fas fa-check"></i>',
                                    cancelButtonText: '<i class="fas fa-times"></i>',
                                    reverseButtons: true,
                                    inputValidator: (value) => !value && 'Please select a year'
                                });
                            })
                            .then(yearResult => {
                                if (yearResult && yearResult.isConfirmed) {
                                    console.log('Fetching data for:', selectedCampus, yearResult.value);
                                    return fetch(`../target_forms/get_target_data.php?campus=${encodeURIComponent(selectedCampus)}&year=${yearResult.value}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('Received data:', data);
                                            if (data && !data.error) {
                                                const campusInput = document.getElementById('campus');
                                                const yearSelect = document.getElementById('year'); // Get the year select element
                                                const totalGaaInput = document.getElementById('total_gaa');
                                                const totalGadFundInput = document.getElementById('total_gad_fund');
                                                const addTargetBtn = document.getElementById('addTargetBtn');
                                                const deleteTargetBtn = document.getElementById('deleteTargetBtn');

                                                // Set form values
                                                campusInput.value = data.campus;
                                                
                                                // Clear and update year select options
                                                yearSelect.innerHTML = ''; // Clear existing options
                                                const yearOption = document.createElement('option');
                                                yearOption.value = data.year;
                                                yearOption.textContent = data.year;
                                                yearSelect.appendChild(yearOption);
                                                yearSelect.value = data.year;
                                                
                                                totalGaaInput.value = data.total_gaa;
                                                totalGadFundInput.value = data.total_gad_fund;

                                                // Enable edit mode
                                                addTargetBtn.classList.add('btn-update');
                                                addTargetBtn.innerHTML = '<i class="fas fa-save"></i>';
                                                this.classList.add('editing');
                                                this.innerHTML = '<i class="fas fa-times"></i>';
                                                deleteTargetBtn.classList.add('disabled');

                                                // Disable inputs appropriately
                                                campusInput.disabled = true;
                                                yearSelect.disabled = true;
                                                totalGaaInput.disabled = false;
                                                
                                                // Add validation classes
                                                campusInput.classList.add('is-valid');
                                                yearSelect.classList.add('is-valid');
                                                
                                                // Show validation icons
                                                const campusIcon = campusInput.parentElement.querySelector('.valid-feedback i');
                                                const yearIcon = yearSelect.parentElement.querySelector('.valid-feedback i');
                                                if (campusIcon) campusIcon.style.display = 'inline-block';
                                                if (yearIcon) yearIcon.style.display = 'inline-block';

                                                // Show success message
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Success!',
                                                    text: 'Target data loaded for editing',
                                                    showConfirmButton: false,
                                                    timer: 1500
                                                });
                                            } else {
                                                Swal.fire({
                                                    icon: 'error',
                                                    title: 'Error',
                                                    text: data.error || 'Failed to fetch target data',
                                                    confirmButtonColor: '#dc3545'
                                                });
                                            }
                                        });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Failed to fetch data',
                                    confirmButtonColor: '#dc3545'
                                });
                            });
                    }
                });
            });

            // Delete Target Button Handler
            document.getElementById('deleteTargetBtn').addEventListener('click', function() {
                Swal.fire({
                    title: 'Select Campus',
                    html: '<h6 class="mb-3">Step 1 of 2</h6>',
                    input: 'select',
                    inputOptions: {
                        'Lipa': 'Lipa',
                        'Pablo Borbon': 'Pablo Borbon',
                        'Alangilan': 'Alangilan',
                        'Nasugbu': 'Nasugbu',
                        'Malvar': 'Malvar',
                        'Rosario': 'Rosario',
                        'Balayan': 'Balayan',
                        'Lemery': 'Lemery',
                        'San Juan': 'San Juan',
                        'Lobo': 'Lobo'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#dc3545',
                    confirmButtonText: '<i class="fas fa-arrow-right"></i>',
                    cancelButtonText: '<i class="fas fa-times"></i>',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Please select a campus';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const selectedCampus = result.value;
                        fetch(`../target_forms/get_existing_years.php?campus=${encodeURIComponent(selectedCampus)}`)
                            .then(response => response.json())
                            .then(years => {
                                if (!years || years.length === 0) {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'No Targets Found',
                                        text: `No targets found for ${selectedCampus} campus.`,
                                        confirmButtonColor: '#dc3545'
                                    });
                                    return Promise.reject('no_targets');
                                }

                                return Swal.fire({
                                    title: 'Select Year',
                                    html: '<h6 class="mb-3">Step 2 of 2</h6>',
                                    input: 'select',
                                    inputOptions: years.reduce((obj, year) => {
                                        obj[year] = year;
                                        return obj;
                                    }, {}),
                                    inputPlaceholder: 'Select Year',
                                    showCancelButton: true,
                                    confirmButtonColor: '#6c757d',
                                    cancelButtonColor: '#dc3545',
                                    confirmButtonText: '<i class="fas fa-arrow-right"></i>',
                                    cancelButtonText: '<i class="fas fa-times"></i>',
                                    reverseButtons: true,
                                    inputValidator: (value) => {
                                        if (!value) {
                                            return 'Please select a year';
                                        }
                                    }
                                });
                            })
                            .then(yearResult => {
                                if (yearResult && yearResult.isConfirmed) {
                                    return Swal.fire({
                                        title: 'Are you sure?',
                                        text: `Delete target for ${selectedCampus} campus, year ${yearResult.value}?`,
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#dc3545',
                                        cancelButtonColor: '#6c757d',
                                        confirmButtonText: '<i class="fas fa-trash"></i>',
                                        cancelButtonText: '<i class="fas fa-times"></i>',
                                        reverseButtons: true
                                    }).then((confirmResult) => {
                                        if (confirmResult.isConfirmed) {
                                            return fetch('../target_forms/delete_target.php', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify({
                                                    campus: selectedCampus,
                                                    year: yearResult.value
                                                })
                                            });
                                        }
                                        return Promise.reject('cancelled');
                                    });
                                }
                                return Promise.reject('cancelled');
                            })
                            .then(response => response && response.json())
                            .then(result => {
                                if (result && result.success) {
                                    Swal.close();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: result.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        resetForm();
                                        updateYearOverview(new Date().getFullYear());
                                    });
                                } else {
                                    throw new Error(result.message || 'Failed to delete target');
                                }
                            })
                            .catch(error => {
                                if (error === 'no_targets' || error === 'cancelled') {
                                    return; // Don't show error for these cases
                                }
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Failed to delete target',
                                    confirmButtonColor: '#dc3545'
                                });
                            });
                    }
                });
            });
        });

        function formatCurrency(amount) {
            if (!amount) return '—';
            return new Intl.NumberFormat('en-PH', {
                style: 'currency',
                currency: 'PHP'
            }).format(amount);
        }

        function updateYearOverview(centerYear = new Date().getFullYear()) {
            const campus = document.getElementById('campusFilter').value;
            
            fetch(`../target_forms/get_multi_year_data.php?campus=${encodeURIComponent(campus)}&centerYear=${centerYear}`)
                .then(response => response.json())
                .then(response => {
                    if (!response.success) {
                        throw new Error(response.error || 'Failed to fetch data');
                    }

                    const yearRow = document.querySelector('.year-row');
                    const targetStatusRow = document.querySelector('.target-status-row');
                    const fundRow = document.querySelector('.fund-row');
                    const currentRange = document.querySelector('.current-range');

                    // Clear existing content
                    yearRow.innerHTML = '';
                    targetStatusRow.innerHTML = '';
                    fundRow.innerHTML = '';

                    // Update year range display
                    const firstYear = response.data[0].year;
                    const lastYear = response.data[response.data.length - 1].year;
                    currentRange.textContent = `${firstYear} - ${lastYear}`;

                    response.data.forEach(yearData => {
                        // Year header
                        const yearHeader = document.createElement('th');
                        yearHeader.textContent = yearData.year;
                        if (yearData.year == new Date().getFullYear()) {
                            yearHeader.classList.add('current-year');
                        }
                        yearRow.appendChild(yearHeader);

                        // Target status
                        const targetCell = document.createElement('td');
                        if (yearData.hasTarget) {
                            targetCell.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                            targetCell.classList.add('has-target');
                        } else {
                            targetCell.innerHTML = '—';
                            targetCell.classList.add('no-target');
                        }
                        targetStatusRow.appendChild(targetCell);

                        // Fund information
                        const fundCell = document.createElement('td');
                        if (yearData.hasTarget) {
                            fundCell.innerHTML = `
                                <span class="fund-label">GAA</span>
                                <div class="fund-amount">${formatCurrency(yearData.total_gaa)}</div>
                                <span class="fund-label">GAD</span>
                                <div class="fund-amount">${formatCurrency(yearData.total_gad_fund)}</div>
                            `;
                        } else {
                            fundCell.innerHTML = `
                                <span class="fund-label">GAA</span>
                                <div class="fund-amount">—</div>
                                <span class="fund-label">GAD</span>
                                <div class="fund-amount">—</div>
                            `;
                        }
                        fundRow.appendChild(fundCell);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load year overview data'
                    });
                });
        }

        // Initialize the year overview
        document.addEventListener('DOMContentLoaded', function() {
            let currentCenterYear = new Date().getFullYear();
            
            updateYearOverview(currentCenterYear);

            document.getElementById('campusFilter').addEventListener('change', function() {
                updateYearOverview(currentCenterYear);
            });

            document.getElementById('prevYearSet').addEventListener('click', function() {
                currentCenterYear -= 5;
                updateYearOverview(currentCenterYear);
            });

            document.getElementById('nextYearSet').addEventListener('click', function() {
                currentCenterYear += 5;
                updateYearOverview(currentCenterYear);
            });
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

        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        // Apply saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
            updateThemeIcon(savedTheme);
        });
    </script>

    <style>
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            background-color: var(--input-bg, #ffffff);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg, #ffffff);
            color: var(--text-primary);
            border-color: var(--accent-color);
        }

        .input-group-text {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-primary {
            background: var(--accent-color);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .form-control:read-only {
            background-color: var(--bg-secondary);
            cursor: not-allowed;
        }

        /* Disabled form controls styling */
        .form-select:disabled,
        .form-control:disabled {
            background-color: var(--bg-secondary);
            border-color: #dee2e6;
            color: #6c757d;
            cursor: not-allowed;
            opacity: 0.8;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Existing validation code...
        });

        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
            
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update time every second
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

<script>
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
                    container: 'swal-blur-container'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.body.classList.add('fade-out');
                    
                    setTimeout(() => {
                        window.location.href = '../loading_screen.php?redirect=index.php';
                    }, 50);
                }
            });
        }
    </script>
</body>
</html>