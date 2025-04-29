/**
 * Tax Assessment Management System - Sidebar JavaScript (Vanilla JS)
 * 
 * This file contains the code for handling the sidebar functionality without jQuery
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ensure sidebar is hidden on page load for mobile/tablet
    document.body.classList.remove('sidebar-open');
    const sidebar = document.getElementById('sidebar');
    
    // Only continue if we're on a page with a sidebar
    if (sidebar) {
        sidebar.classList.remove('show');
        
        // Toggle sidebar for all screen sizes
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.toggle('sidebar-open');
                sidebar.classList.toggle('show');
            });
        }
        
        // Close sidebar when close button is clicked
        const sidebarClose = document.getElementById('sidebarClose');
        if (sidebarClose) {
            sidebarClose.addEventListener('click', function(e) {
                e.preventDefault();
                document.body.classList.remove('sidebar-open');
                sidebar.classList.remove('show');
            });
        }
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            // If the click is not on the sidebar and not on the sidebar toggle button
            if (!e.target.closest('#sidebar') && 
                !e.target.closest('#sidebarToggle') && 
                sidebar.classList.contains('show')) {
                document.body.classList.remove('sidebar-open');
                sidebar.classList.remove('show');
            }
        });
        
        // Client search functionality
        const sidebarSearchInput = document.querySelector('.sidebar-search input');
        if (sidebarSearchInput) {
            sidebarSearchInput.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                
                // When implemented, this would filter clients in a dropdown
                // For now, we'll just log it
                console.log('Searching for client: ' + searchTerm);
                
                // Show a small indicator that the search is active
                if (searchTerm.length > 0) {
                    this.classList.add('active-search');
                } else {
                    this.classList.remove('active-search');
                }
            });
        }
        
        // Search button click
        const sidebarSearchButton = document.querySelector('.sidebar-search button');
        if (sidebarSearchButton) {
            sidebarSearchButton.addEventListener('click', function() {
                const searchTerm = document.querySelector('.sidebar-search input').value.toLowerCase();
                if (searchTerm.length > 0) {
                    // Would trigger the search
                    console.log('Search button clicked for: ' + searchTerm);
                }
            });
        }
    }
    
    // Add active class to current page's link
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(function(link) {
        const linkPath = link.getAttribute('href');
        if (currentPath === linkPath || 
            (currentPath.indexOf(linkPath) !== -1 && linkPath !== '/' && linkPath !== '/index.php')) {
            link.classList.add('active');
        }
    });

    // Initialize dropdowns (for user dropdown menu)
    if (typeof bootstrap !== 'undefined') {
        const dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    }
});