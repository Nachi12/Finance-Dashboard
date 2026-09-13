/**
 * Main Application Script
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    
    function toggleSidebar() {
        if (!sidebar) return;
        sidebar.classList.toggle('open');
        
        // For mobile overlay logic
        if (window.innerWidth <= 767) {
            if (sidebar.classList.contains('open')) {
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', toggleSidebar);
    }
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', toggleSidebar);
    }
});

// Number formatter utility
window.formatINR = function(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        maximumFractionDigits: 0
    }).format(amount);
};

// Generic API fetch wrapper
window.apiFetch = async function(endpoint, options = {}) {
    try {
        const response = await fetch(`/api/${endpoint}`, options);
        if (!response.ok) {
            throw new Error(`API Error: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('API Fetch failed:', error);
        throw error;
    }
};
