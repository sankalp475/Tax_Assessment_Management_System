/**
 * Tax Assessment Management System - Sidebar JavaScript
 * 
 * This file contains the code for handling the sidebar functionality
 */

$(document).ready(function() {
    // Ensure sidebar is hidden on page load
    $('body').removeClass('sidebar-open');
    $('#sidebar').removeClass('show');
    
    // Toggle sidebar for all screen sizes
    $('#sidebarToggle').click(function(e) {
        e.preventDefault();
        console.log("Sidebar toggle clicked");
        $('body').toggleClass('sidebar-open');
        $('#sidebar').toggleClass('show');
    });
    
    // Close sidebar when close button is clicked
    $('#sidebarClose').click(function(e) {
        e.preventDefault();
        $('body').removeClass('sidebar-open');
        $('#sidebar').removeClass('show');
    });
    
    // Close sidebar when clicking outside
    $(document).on('click touchstart', function(e) {
        // If the click is not on the sidebar and not on the sidebar toggle button
        if (!$(e.target).closest('#sidebar').length && 
            !$(e.target).closest('#sidebarToggle').length && 
            $('#sidebar').hasClass('show')) {
            $('body').removeClass('sidebar-open');
            $('#sidebar').removeClass('show');
        }
    });
    
    // Add active class to current page's link
    var currentPath = window.location.pathname;
    $('.nav-link').each(function() {
        var linkPath = $(this).attr('href');
        if (currentPath === linkPath || 
            (currentPath.indexOf(linkPath) !== -1 && linkPath !== '/' && linkPath !== '/index.php')) {
            $(this).addClass('active');
        }
    });

    // Client search functionality
    $('.sidebar-search input').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        // When implemented, this would filter clients in a dropdown
        // For now, we'll just log it
        console.log('Searching for client: ' + searchTerm);
        
        // Show a small indicator that the search is active
        if (searchTerm.length > 0) {
            $(this).addClass('active-search');
        } else {
            $(this).removeClass('active-search');
        }
    });
    
    // Search button click
    $('.sidebar-search button').on('click', function() {
        var searchTerm = $('.sidebar-search input').val().toLowerCase();
        if (searchTerm.length > 0) {
            // Would trigger the search
            console.log('Search button clicked for: ' + searchTerm);
        }
    });
});