// Sidebar JavaScript Functionality

// DOM elements
const sidebar = document.getElementById('sidebar');
const mobileToggle = document.getElementById('mobileToggle');
const overlay = document.getElementById('overlay');

// Mobile menu handling
function initMobileMenu() {
    if (window.innerWidth <= 768) {
        mobileToggle.style.display = 'block';
    } else {
        mobileToggle.style.display = 'none';
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    }
}

// Toggle mobile menu
mobileToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
});

// Close menu when clicking overlay
overlay.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
});

// Handle window resize
window.addEventListener('resize', initMobileMenu);

// Initialize mobile menu on page load
initMobileMenu();

document.querySelectorAll('.menu-link').forEach(link => {
    link.addEventListener('click', function(e) {
        // Don't prevent default for links with actual href values
        if (this.getAttribute('href') === '#') {
            e.preventDefault();
        }
        
        // Remove active class from all menu links
        document.querySelectorAll('.menu-link').forEach(l => l.classList.remove('active'));
        
        // Add active class to clicked link
        this.classList.add('active');
        
        // Close mobile menu if open
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        }
        
        // Handle different menu actions
        const linkText = this.textContent.trim();
        const href = this.getAttribute('href');
        
        if (href && href !== '#') {
            // If it's a real link, navigate there
            window.location.href = href;
        } else {
            // Handle special cases like logout
            handleMenuAction(linkText);
        }
    });
});

// Set active menu item based on current page
function setActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.menu-link').forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref && linkHref.includes(currentPage)) {
            link.classList.add('active');
            // Remove active from others
            document.querySelectorAll('.menu-link').forEach(l => {
                if (l !== link) l.classList.remove('active');
            });
        }
    });
}

// Call this on page load
document.addEventListener('DOMContentLoaded', setActiveMenuItem);

// Handle menu actions
function handleMenuAction(action) {
    switch (action) {
        case 'Logout':
            // Handle logout
            console.log('Logout');
            handleLogout();
            break;
        default:
            console.log('Unknown menu action:', action);
    }
}

// Handle logout
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear any saved data
        try {
            localStorage.removeItem('hematologyResults');
        } catch (error) {
            console.log('Could not clear localStorage');
        }
        
        // Redirect or reload page
        alert('Logged out successfully!');
        window.location.href = '../components/logout.php'; // Uncomment if you have a login page
    }
}