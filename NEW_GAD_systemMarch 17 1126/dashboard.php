<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        /* Prevent theme flash */
        html[data-theme="dark"] {
            background-color: #1a1a1a !important;
        }

        /* Fade transition styles */
        body {
            opacity: 1;
            transition: opacity 0.05s ease-in-out;
        }

        .fade-out {
            opacity: 0;
        }
    </style>
    <script>
        // Immediate theme loading
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        })();
    </script>
    <link rel="icon" type="image/x-icon" href="/images/Batangas_State_Logo.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/common.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --accent-color: #6a1b9a;
            --accent-hover: #4a148c;
            --scrollbar-thumb: #cccccc;
            --scrollbar-thumb-hover: #aaaaaa;
        }
        
        /* Light Theme Variables */
        [data-theme="light"] {
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
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --sidebar-bg: #2d2d2d;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --hover-color: #8a4ebd;
            --card-bg: #2d2d2d;
            --border-color: #404040;
            --horizontal-bar: rgba(255, 255, 255, 0.1);
            --scrollbar-thumb: #6a1b9a;
            --scrollbar-thumb-hover: #9c27b0;
            --accent-color: #9c27b0;
            --accent-hover: #7b1fa2;
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
            z-index: 10;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: var(--sidebar-width);
            margin-top: -32px;
            background: var(--bg-primary);
            border-radius: 20px;
            position: relative;
            overflow-y: auto;
            max-height: 100vh;
            transition: margin-left 0.3s ease;
            display: flex;
            flex-direction: column;
            z-index: 5;
            scrollbar-width: none;  /* Firefox */
            -ms-overflow-style: none;  /* IE and Edge */
        }

        .main-content::-webkit-scrollbar {
            width: 8px;
            background: transparent;
        }
        
        .main-content::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background-color: transparent;
            border-radius: 20px;
            transition: background-color 0.3s ease;
        }
        
        .main-content:hover::-webkit-scrollbar-thumb,
        .main-content:active::-webkit-scrollbar-thumb {
            background-color: var(--scrollbar-thumb);
        }
        
        .main-content:hover::-webkit-scrollbar-thumb:hover {
            background-color: var(--scrollbar-thumb-hover);
        }

        body::-webkit-scrollbar {
            width: 0;
            display: none;
        }
        
        body {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;     /* Firefox */
            overflow: hidden;
        }

        @media (max-width: 991px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
                max-height: calc(100vh - 60px);
            }
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
        [data-theme="light"] .nav-link:hover {
            color: var(--accent-color);
        }

        [data-theme="light"] .nav-item .dropdown-menu .dropdown-item:hover {
            color: var(--accent-color);
        }

        [data-theme="light"] .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: var(--accent-color) !important;
        }

        .nav-link.active {
            color: var(--accent-color);
            position: relative;
        }

        /* Add hover state for active nav links in dark mode */
        [data-theme="dark"] .nav-link.active:hover {
            color: white;
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

        .nav-item .dropdown-toggle[aria-expanded="true"] {
            color: white !important;
            background: var(--hover-color);
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
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--card-accent), var(--card-accent-secondary));
        }

        /* Card color variations - First set */
        .analytics-card:nth-child(1) {
            --card-accent: #4361ee;
            --card-accent-secondary: #3a0ca3;
        }

        .analytics-card:nth-child(2) {
            --card-accent: #f72585;
            --card-accent-secondary: #7209b7;
        }

        .analytics-card:nth-child(3) {
            --card-accent: #06d6a0;
            --card-accent-secondary: #118ab2;
        }

        /* Second set for the second row */
        .analytics-row:nth-child(2) .analytics-card:nth-child(1) {
            --card-accent: #ff9e00;
            --card-accent-secondary: #ff0054;
        }

        .analytics-row:nth-child(2) .analytics-card:nth-child(2) {
            --card-accent: #8338ec;
            --card-accent-secondary: #3a86ff;
        }

        .analytics-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .nav-content {
            flex-grow: 1;
            overflow-y: auto;
            max-height: calc(100vh - 470px);
            margin-bottom: 20px;
            padding-right: 5px;
            scrollbar-width: thin;
            scrollbar-color: rgba(106, 27, 154, 0.3) transparent;
            overflow-x: hidden; 
        }

        .nav-content::-webkit-scrollbar {
            width: 2px;
        }

        .nav-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-content::-webkit-scrollbar-thumb {
            background-color: rgba(106, 27, 154, 0.3);
            border-radius: 1px;
        }

        .nav-content::-webkit-scrollbar-thumb:hover {
            background-color: rgba(106, 27, 154, 0.5);
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

        .logout-button {
            background: var(--bg-primary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 12px;
            border-radius: 10px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logout-button:hover {
            background: #6a1b9a;
            color: white !important;
            border-color: #6a1b9a;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--accent-color);
        }

        .theme-switch {
            position: static;
            margin: 0 0 15px 0;
        }

        .theme-switch-button {
            width: 100%;
            height: auto;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }

        .theme-switch-button .icon-container {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: var(--accent-color);
            color: white;
        }

        .theme-switch-button .theme-text {
            flex: 1;
            text-align: left;
            font-weight: 500;
        }

        .theme-switch-button:hover {
            transform: translateY(-2px);
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .theme-switch-button:hover .icon-container {
            background: white;
            color: var(--accent-color);
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
                top: 20px;
                right: 30px;
            }

            .analytics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-top: 10px;
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
            
            .welcome-card {
                height: 100px;
                margin-bottom: 1rem;
            }
            
            .welcome-content h1 {
                font-size: 1.5rem;
            }
            
            .welcome-subtitle {
                font-size: 1rem;
            }
        }
        
        @media (min-width: 768px) and (max-width: 1024px) {
            .main-content {
                padding: 1.5rem;
            }

            .analytics-grid {
                gap: 1rem;
            }

            .analytics-row {
                grid-template-columns: repeat(2, 1fr);
            }

            .analytics-row:nth-child(2) {
                grid-template-columns: repeat(2, 1fr);
            }

            .welcome-card {
                height: 110px;
            }
            
            .welcome-content h1 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 767px) {
            .analytics-row, 
            .analytics-row:nth-child(2) {
                grid-template-columns: 1fr;
            }
            
            .analytics-card {
                height: 300px;
            }
        }

        /* Theme-specific card styles */
        [data-theme="light"] .analytics-card {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        [data-theme="dark"] .analytics-card {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        [data-theme="light"] .analytics-card::before {
            opacity: 0.9;
        }

        [data-theme="dark"] .analytics-card::before {
            opacity: 0.7;
        }

        /* Percentage indicators theme-specific styles */
        [data-theme="light"] .percentage.up {
            background-color: rgba(40, 199, 111, 0.15);
        }

        [data-theme="dark"] .percentage.up {
            background-color: rgba(40, 199, 111, 0.25);
        }

        [data-theme="light"] .percentage.down {
            background-color: rgba(234, 84, 85, 0.15);
        }

        [data-theme="dark"] .percentage.down {
            background-color: rgba(234, 84, 85, 0.25);
        }
        
        /* Modal styles for card popup */
        .card-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .card-modal {
            background: var(--card-bg);
            border-radius: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2rem;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transform: scale(0.9);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border-top: 5px solid var(--modal-accent, var(--accent-color));
        }

        .card-modal.active {
            transform: scale(1);
            opacity: 1;
        }

        .card-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .card-modal-header h2 {
            margin: 0;
            color: var(--text-primary);
            font-size: 1.5rem;
        }

        .card-modal-close {
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .card-modal-close:hover {
            background: var(--hover-color);
        }

        .card-modal-last-updated {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px dashed var(--border-color);
        }

        .card-modal-body {
            margin-bottom: 1.5rem;
        }

        .card-modal-chart {
            height: 400px;
            margin-bottom: 1.5rem;
            background: var(--bg-primary);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .card-modal-chart canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .card-modal-footer {
            display: flex;
            justify-content: space-between;
            color: var(--text-secondary);
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        .card-modal::-webkit-scrollbar {
            width: 8px;
        }

        .card-modal::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 10px;
        }

        .card-modal::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 10px;
        }

        .card-modal::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }

        .card-modal-details {
            background: var(--bg-secondary);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .detail-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-secondary);
        }

        .detail-value {
            color: var(--text-primary);
            font-weight: 500;
        }

        #modal-change.positive {
            color: #28c76f;
        }

        #modal-change.negative {
            color: #ea5455;
        }

        .card-modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        /* Add these styles to increase blur for modals */
        .modal-backdrop {
            backdrop-filter: blur(8px);
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-backdrop.show {
            opacity: 1;
        }

        /* Container for both buttons - reduced gap */
        .bottom-controls {
            position: absolute;
            bottom: 20px;
            width: calc(var(--sidebar-width) - 40px);
            display: flex;
            gap: 5px;  /* Reduced from 10px */
            align-items: center;
        }

        /* Theme switch button - matched height */
        .theme-switch-button {
            width: 60px;
            height: 50px;  /* Changed from fixed height to match logout button */
            padding: 12px 0;  /* Added vertical padding to match logout button */
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

        /* Hover effects */
        .logout-button:hover,
        .theme-switch-button:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }

        /* Updated blur effect for SweetAlert */
        .swal-blur-container {
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
        }

        /* Dropdown submenu styles */
        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -8px;
            margin-left: 1px;
            border-radius: 0 6px 6px 6px;
            display: none;
        }

        .dropdown-submenu:hover > .dropdown-menu {
            display: block;
        }

        .dropdown-submenu > a:after {
            display: block;
            content: " ";
            float: right;
            width: 0;
            height: 0;
            border-color: transparent;
            border-style: solid;
            border-width: 5px 0 5px 5px;
            border-left-color: var(--text-primary);
            margin-top: 5px;
            margin-right: -10px;
        }

        .dropdown-submenu:hover > a:after {
            border-left-color: var(--accent-color);
        }

        /* Mobile styles for dropdown submenu */
        @media (max-width: 991px) {
            .dropdown-submenu .dropdown-menu {
                position: static !important;
                left: 0;
                margin-left: 20px;
                margin-top: 0;
                border-radius: 0;
                border-left: 2px solid var(--accent-color);
            }
            
            .dropdown-submenu > a:after {
                transform: rotate(90deg);
                margin-top: 8px;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <img src="images/Batangas_State_Logo.png" alt="Batangas State Logo">
            </div>
        </div>
        <div class="datetime-container">
            <div class="date" id="current-date"></div>
            <div class="time" id="current-time"></div>
        </div>
        <div class="nav-content">
            <nav class="nav flex-column">
                <a href="" class="nav-link active">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="staffDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-users me-2"></i> Staff
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="academic_rank/academic.php">Academic Rank</a></li>
                        <li><a class="dropdown-item" href="personnel_list/personnel_list.php">Personnel List</a></li>
                    </ul>
                </div>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="formsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-file-alt me-2"></i> Forms
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="target_forms/target.php">Target Form</a></li>
                        <li><a class="dropdown-item" href="gbp_forms/gpb.php">GPB Form</a></li>
                        <li><a class="dropdown-item" href="ps_atrib/ps.php">Ps Attributions</a></li>
                        <li class="dropdown-submenu">
                            <a class="dropdown-item dropdown-toggle" href="#" id="ppasDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                PPAs Form
                            </a>
                            <ul class="dropdown-menu dropdown-submenu" aria-labelledby="ppasDropdown">
                                <li><a class="dropdown-item" href="ppas_forms/ppas.php">Main PPAs Form</a></li>
                                <li><a class="dropdown-item" href="ppas_proposal/gad_proposal.php">Proposal</a></li>
                                <li><a class="dropdown-item" href="narrative/narrative.php">Narrative Report</a></li>
                            </ul>
                        </li>
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
            <a href="index.php" class="logout-button" onclick="handleLogout(event)">
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
        <!-- Welcome Card -->
        <div class="welcome-card">
            <div class="welcome-overlay"></div>
            <div class="welcome-content">
                <h1>Welcome, <span class="username"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; ?></span>.</h1>
                <div class="welcome-subtitle">Gender and Development Information System</div>
            </div>
        </div>

        <!-- Analytics Grid -->
        <div class="analytics-grid">
            <!-- First Row of Cards -->
            <div class="analytics-row">
                <!-- OPCR Target Card -->
                <div class="analytics-card" onclick="openCardModal('opcrChart', 'OPCR Progress')">
                    <div class="card-header">
                        <h3>Quarterly Report vs OPCR Target</h3>
                        <span class="percentage up">+24%</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="opcrChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="trend-label">Trend: Positive</span>
                        <span class="date-label">Last Updated: Feb 2025</span>
                    </div>
                </div>

                <!-- GPB Quarterly Card -->
                <div class="analytics-card" onclick="openCardModal('gpbQuarterlyChart', 'GPB Quarterly')">
                    <div class="card-header">
                        <h3>Quarterly Report vs GPB</h3>
                        <span class="percentage down">-8%</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gpbQuarterlyChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="trend-label">Trend: Needs Attention</span>
                        <span class="date-label">Last Updated: Feb 2025</span>
                    </div>
                </div>

                <!-- Annual GPB Card -->
                <div class="analytics-card" onclick="openCardModal('gpbAnnualChart', 'GPB Annual')">
                    <div class="card-header">
                        <h3>Annual Report vs GPB</h3>
                        <span class="percentage up">+32%</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gpbAnnualChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="trend-label">Trend: Strong Growth</span>
                        <span class="date-label">Last Updated: Feb 2025</span>
                    </div>
                </div>
            </div>

            <!-- Second Row of Cards -->
            <div class="analytics-row">
                <!-- GAD Fund Quarterly Card -->
                <div class="analytics-card" onclick="openCardModal('gadQuarterlyChart', 'GAD Fund Quarterly')">
                    <div class="card-header">
                        <h3>Quarterly Report vs GAD Fund</h3>
                        <span class="percentage up">+15%</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gadQuarterlyChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="trend-label">Trend: Stable</span>
                        <span class="date-label">Last Updated: Feb 2025</span>
                    </div>
                </div>

                <!-- Annual GAD Fund Card -->
                <div class="analytics-card" onclick="openCardModal('gadAnnualChart', 'GAD Fund Annual')">
                    <div class="card-header">
                        <h3>Annual Report vs GAD Fund</h3>
                        <span class="percentage up">+18%</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="gadAnnualChart"></canvas>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="trend-label">Trend: Positive</span>
                        <span class="date-label">Last Updated: Feb 2025</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Modal Backdrop -->
    <div class="card-modal-backdrop" id="card-modal-backdrop">
        <!-- Card Modal -->
        <div class="card-modal" id="card-modal" onclick="event.stopPropagation()">
            <div class="card-modal-header">
                <h2 id="card-modal-title"></h2>
                <button class="card-modal-close" onclick="closeCardModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="card-modal-last-updated">
                <span id="card-modal-footer-text"></span>
            </div>
            <div class="card-modal-body">
                <div class="card-modal-chart" id="card-modal-chart"></div>
                <div class="card-modal-details">
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value" id="modal-status">Positive Growth</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Change:</div>
                        <div class="detail-value" id="modal-change">+24%</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Period:</div>
                        <div class="detail-value" id="modal-period">Q1 2025</div>
                    </div>
                </div>
            </div>
            <div class="card-modal-footer">
            </div>
        </div>
    </div>

    <style>
        /* Welcome Card Styles */
        .welcome-card {
            position: relative;
            height: 120px;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 1rem;
            width: 100%;
            min-height: 120px;
            display: block;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -310px;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('images/campus3.jpg') center center/cover no-repeat;
            z-index: 1;
            opacity: 0.9;
        }

        .welcome-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(106, 27, 154, 0.85), rgba(74, 20, 140, 0.7));
            z-index: 2;
        }

        .welcome-content {
            position: relative;
            z-index: 3;
            padding: 1.5rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .welcome-content h1 {
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            line-height: 1.2;
        }

        .welcome-subtitle {
            color: white;
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        .username {
    color: #ffff00; /* Bright yellow */
    font-weight: 600;
}

        /* Analytics Grid Styles */
        .analytics-grid {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            width: 100%;
        }

        .analytics-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            width: 100%;
        }

        .analytics-row:nth-child(2) {
            grid-template-columns: repeat(2, 1fr);
        }

        .analytics-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 289px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--card-accent), var(--card-accent-secondary));
        }

        /* Card color variations - First set */
        .analytics-card:nth-child(1) {
            --card-accent: #4361ee;
            --card-accent-secondary: #3a0ca3;
        }

        .analytics-card:nth-child(2) {
            --card-accent: #f72585;
            --card-accent-secondary: #7209b7;
        }

        .analytics-card:nth-child(3) {
            --card-accent: #06d6a0;
            --card-accent-secondary: #118ab2;
        }

        /* Second set for the second row */
        .analytics-row:nth-child(2) .analytics-card:nth-child(1) {
            --card-accent: #ff9e00;
            --card-accent-secondary: #ff0054;
        }

        .analytics-row:nth-child(2) .analytics-card:nth-child(2) {
            --card-accent: #8338ec;
            --card-accent-secondary: #3a86ff;
        }

        .analytics-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-header h3 {
    font-size: 1rem; /* Reduced from 1.1rem */
    color: var(--text-primary);
    margin: 0;
    flex: 1;
    padding-right: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

        .percentage {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        .percentage.up {
            background-color: rgba(40, 199, 111, 0.2);
            color: #28c76f;
        }

        .percentage.down {
            background-color: rgba(234, 84, 85, 0.2);
            color: #ea5455;
        }

        .chart-container {
            flex: 1;
            min-height: 180px; /* Increased from default */
            position: relative;
            margin-bottom: 1rem;
        }

        .card-footer {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem; /* Smaller font size */
    color: var(--text-secondary);
    margin-top: auto;
}

.trend-label, .date-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

        @media (max-width: 1400px) {
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

        @media (max-width: 1200px) {
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }

        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .welcome-card {
                height: 100px;
            }

            .welcome-content h1 {
                font-size: 1.5rem;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .analytics-card {
                height: 280px;
            }

            .card-header h3 {
                font-size: 1rem;
            }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Theme Switch and DateTime Script -->
    <script>
        function updateThemeIcon(theme) {
            const themeIcon = document.getElementById('theme-icon');
            themeIcon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            // Update theme on both document and body
            html.setAttribute('data-bs-theme', newTheme);
            document.body.setAttribute('data-bs-theme', newTheme);
            
            // Store theme preference
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
            
            // Update charts if they exist
            if (typeof updateChartsTheme === 'function') {
                updateChartsTheme(newTheme);
            }
        }

        // Apply saved theme on page load and initialize dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            // Theme initialization
            const savedTheme = localStorage.getItem('theme') || 'light';
            const html = document.documentElement;
            
            // Apply theme to both document and body
            html.setAttribute('data-bs-theme', savedTheme);
            document.body.setAttribute('data-bs-theme', savedTheme);
            
            // Update icon
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
            
            // Initialize all dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
            
            // Handle submenu dropdowns
            const dropdownSubmenus = document.querySelectorAll('.dropdown-submenu > a');
            dropdownSubmenus.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Prevent the default action (which would be to follow the link)
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Toggle the submenu
                    const parent = this.parentElement;
                    const submenu = parent.querySelector('.dropdown-menu');
                    
                    // Close all other submenus
                    const allSubmenus = document.querySelectorAll('.dropdown-submenu .dropdown-menu');
                    allSubmenus.forEach(menu => {
                        if (menu !== submenu) {
                            menu.classList.remove('show');
                        }
                    });
                    
                    // Toggle this submenu
                    submenu.classList.toggle('show');
                });
            });
            
            // Close submenus when clicking outside
            document.addEventListener('click', function(e) {
                const allSubmenus = document.querySelectorAll('.dropdown-submenu .dropdown-menu');
                allSubmenus.forEach(menu => {
                    if (!menu.contains(e.target) && !e.target.classList.contains('dropdown-toggle')) {
                        menu.classList.remove('show');
                    }
                });
            });
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

    <!-- Mobile Navigation Toggle Script -->
    <script>
        // Mobile Navigation Toggle
        const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        const body = document.body;

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        }

        mobileNavToggle.addEventListener('click', toggleSidebar);
        backdrop.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking a link on mobile
        const mobileNavLinks = document.querySelectorAll('.sidebar .nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    toggleSidebar();
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Function to get theme-aware colors
        function getThemeColors() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                text: isDark ? '#ffffff' : '#333333',
                grid: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                primary: '#dc3545',
                secondary: isDark ? 'rgba(220, 53, 69, 0.5)' : 'rgba(220, 53, 69, 0.2)',
                background: isDark ? '#2d2d2d' : '#ffffff'
            };
        }

        // Function to create charts
        function createChart(canvasId, label) {
            const colors = getThemeColors();
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            // Get the parent card element
            const cardElement = document.getElementById(canvasId).closest('.analytics-card');
            
            // Get computed styles to access the CSS variables
            const cardStyles = getComputedStyle(cardElement);
            const accentColor = cardStyles.getPropertyValue('--card-accent').trim();
            const accentSecondary = cardStyles.getPropertyValue('--card-accent-secondary').trim();
            
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: label,
                        data: [65, 59, 80, 81, 56, 85],
                        borderColor: accentColor || colors.primary,
                        backgroundColor: accentSecondary || colors.secondary,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: colors.background,
                            titleColor: colors.text,
                            bodyColor: colors.text,
                            borderColor: colors.grid,
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                color: colors.text
                            }
                        },
                        x: {
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                color: colors.text
                            }
                        }
                    }
                }
            });
        }

        // Function to create modal chart with more details
        function createModalChart(canvasId, title, originalChartId) {
            const colors = getThemeColors();
            const ctx = document.getElementById(canvasId).getContext('2d');
            
            // Get the parent card element
            const cardElement = document.getElementById(originalChartId).closest('.analytics-card');
            
            // Get computed styles to access the CSS variables
            const cardStyles = getComputedStyle(cardElement);
            const accentColor = cardStyles.getPropertyValue('--card-accent').trim();
            const accentSecondary = cardStyles.getPropertyValue('--card-accent-secondary').trim();
            
            // Generate more detailed data for the modal chart
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const data = [65, 59, 80, 81, 56, 85, 70, 75, 60, 90, 80, 75];
            const targetData = [60, 65, 70, 75, 80, 80, 80, 80, 80, 85, 85, 85];
            
            return new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [
                        {
                            label: 'Actual',
                            data: data,
                            borderColor: accentColor || colors.primary,
                            backgroundColor: accentSecondary || colors.secondary,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3
                        },
                        {
                            label: 'Target',
                            data: targetData,
                            borderColor: '#888888',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: colors.text,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: colors.background,
                            titleColor: colors.text,
                            bodyColor: colors.text,
                            borderColor: colors.grid,
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + '%';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                color: colors.text,
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return value + '%';
                                }
                            },
                            title: {
                                display: true,
                                text: 'Percentage',
                                color: colors.text,
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: colors.grid
                            },
                            ticks: {
                                color: colors.text,
                                font: {
                                    size: 12
                                }
                            },
                            title: {
                                display: true,
                                text: 'Month',
                                color: colors.text,
                                font: {
                                    size: 14,
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: {
                        duration: 1000
                    }
                }
            });
        }

        // Create all charts when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            let charts = {
                opcrChart: createChart('opcrChart', 'OPCR Progress'),
                gpbQuarterlyChart: createChart('gpbQuarterlyChart', 'GPB Quarterly'),
                gadQuarterlyChart: createChart('gadQuarterlyChart', 'GAD Fund Quarterly'),
                gpbAnnualChart: createChart('gpbAnnualChart', 'GPB Annual'),
                gadAnnualChart: createChart('gadAnnualChart', 'GAD Fund Annual')
            };
            
            // Update charts when theme changes
            const themeSwitch = document.querySelector('.theme-switch-button');
            themeSwitch.addEventListener('click', () => {
                // Give time for the theme to update
                setTimeout(() => {
                    // Destroy existing charts
                    Object.values(charts).forEach(chart => chart.destroy());
                    
                    // Recreate charts with new theme colors
                    charts = {
                        opcrChart: createChart('opcrChart', 'OPCR Progress'),
                        gpbQuarterlyChart: createChart('gpbQuarterlyChart', 'GPB Quarterly'),
                        gadQuarterlyChart: createChart('gadQuarterlyChart', 'GAD Fund Quarterly'),
                        gpbAnnualChart: createChart('gpbAnnualChart', 'GPB Annual'),
                        gadAnnualChart: createChart('gadAnnualChart', 'GAD Fund Annual')
                    };
                }, 100);
            });
        });
    </script>

    <script>
        // Function to open card modal
        function openCardModal(chartId, title) {
            const cardModal = document.getElementById('card-modal');
            const cardModalBackdrop = document.getElementById('card-modal-backdrop');
            const cardModalTitle = document.getElementById('card-modal-title');
            const cardModalChart = document.getElementById('card-modal-chart');
            const cardModalFooterText = document.getElementById('card-modal-footer-text');
            const modalStatus = document.getElementById('modal-status');
            const modalChange = document.getElementById('modal-change');
            const modalPeriod = document.getElementById('modal-period');
            
            // Get the parent card element that was clicked
            const cardElement = document.getElementById(chartId).closest('.analytics-card');
            
            // Get the percentage and trend from the card
            const percentageElement = cardElement.querySelector('.percentage');
            const trendElement = cardElement.querySelector('.trend-label');
            const dateElement = cardElement.querySelector('.date-label');
            
            // Get computed styles to access the CSS variables
            const cardStyles = getComputedStyle(cardElement);
            const accentColor = cardStyles.getPropertyValue('--card-accent').trim();
            
            // Set modal accent color
            cardModal.style.setProperty('--modal-accent', accentColor);
            
            // Update modal title and chart
            cardModalTitle.textContent = title;
            cardModalChart.innerHTML = `<canvas id="modal-${chartId}"></canvas>`;
            
            // Update modal details
            modalStatus.textContent = trendElement.textContent.replace('Trend: ', '');
            modalChange.textContent = percentageElement.textContent;
            modalChange.className = 'detail-value ' + (percentageElement.classList.contains('up') ? 'positive' : 'negative');
            modalPeriod.textContent = 'Q1 2025';

            // Show modal and backdrop
            cardModal.classList.add('active');
            cardModalBackdrop.style.display = 'flex';

            // Create chart in modal
            const modalChart = createModalChart(`modal-${chartId}`, title, chartId);

            // Update modal footer text
            cardModalFooterText.textContent = dateElement.textContent;
            
            // Add click event to backdrop
            cardModalBackdrop.onclick = closeCardModal;
        }

        // Function to close card modal
        function closeCardModal() {
            const cardModal = document.getElementById('card-modal');
            const cardModalBackdrop = document.getElementById('card-modal-backdrop');

            // Hide modal and backdrop
            cardModal.classList.remove('active');
            cardModalBackdrop.style.display = 'none';
        }
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
                        window.location.href = 'loading_screen.php?redirect=index.php';
                    }, 50);
                }
            });
        }
    </script>
</body>
</html>