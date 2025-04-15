function handleLogout(e) {
    e.preventDefault();
    window.location.href = getBasePath() + 'loading_screen.php?to=' + getBasePath() + 'index.php';
}

function getBasePath() {
    const path = window.location.pathname;
    if (path.includes('/print_proposal_module/')) {
        return '../';
    }
    return '';
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    // Save theme preference
    localStorage.setItem('theme', newTheme);
    
    // Apply new theme
    document.documentElement.setAttribute('data-bs-theme', newTheme);
    
    // Update theme icon
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
}

// Update date and time
function updateDateTime() {
    const now = new Date();
    
    // Format date
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateElement = document.getElementById('current-date');
    if (dateElement) {
        dateElement.textContent = now.toLocaleDateString('en-US', options);
    }
    
    // Format time
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    const timeElement = document.getElementById('current-time');
    if (timeElement) {
        timeElement.textContent = now.toLocaleTimeString('en-US', timeOptions);
    }
}

// Handle document ready state
document.addEventListener('DOMContentLoaded', function() {
    // Initial theme setting from localStorage
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);
    
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = savedTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
    }
    
    // Initialize date and time
    updateDateTime();
    
    // Update every second
    setInterval(updateDateTime, 1000);
}); 