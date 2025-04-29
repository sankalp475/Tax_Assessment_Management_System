/**
 * Tax Assessment Management System - Main JavaScript File
 * 
 * This file contains common JavaScript functions used throughout the application
 */

$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Format currency inputs
    $('.currency-input').on('input', function() {
        formatCurrencyInput(this);
    });

    // Format date inputs
    $('.date-input').on('input', function() {
        formatDateInput(this);
    });

    // PAN validation
    $('.pan-input').on('input', function() {
        validatePAN(this);
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        const tableId = $(this).data('table');
        
        $(`#${tableId} tbody tr`).each(function() {
            const rowText = $(this).text().toLowerCase();
            const isVisible = rowText.indexOf(searchTerm) > -1;
            $(this).toggle(isVisible);
        });
    });

    // Add active class to current navigation item
    const currentLocation = window.location.pathname;
    $('.navbar-nav .nav-link').each(function() {
        const navLink = $(this).attr('href');
        if (currentLocation.includes(navLink) && navLink !== 'index.php') {
            $(this).addClass('active');
        } else if (currentLocation.endsWith('index.php') && navLink === 'index.php') {
            $(this).addClass('active');
        }
    });

    // Handle form submissions
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
                
                if (response.status === 'success') {
                    showAlert('success', response.message);
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    }
                    if (response.reload) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    }
                    if (response.reset) {
                        form[0].reset();
                    }
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function(xhr, status, error) {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
                
                showAlert('danger', 'An error occurred: ' + error);
            }
        });
    });

    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const deleteUrl = $(this).attr('href');
        const itemName = $(this).data('item') || 'item';
        
        if (confirm(`Are you sure you want to delete this ${itemName}? This action cannot be undone.`)) {
            window.location.href = deleteUrl;
        }
    });

    // Print functionality
    $('.print-btn').on('click', function() {
        const printArea = $(this).data('print-area');
        printElement(document.getElementById(printArea));
    });

    // Export to CSV functionality
    $('.export-csv-btn').on('click', function() {
        const tableId = $(this).data('table');
        exportTableToCSV($(`#${tableId}`).get(0), 'export.csv');
    });

    // Client type change handler
    $('#clientType').on('change', function() {
        const isIndividual = $(this).val() === 'individual';
        $('.firm-fields').toggle(!isIndividual);
        $('.individual-fields').toggle(isIndividual);
    });
});

/**
 * Format currency input
 * @param {HTMLElement} input - The input element
 */
function formatCurrencyInput(input) {
    // Remove non-numeric characters
    let value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Format with commas
    const decimalPart = parts.length > 1 ? '.' + parts[1] : '';
    const wholePart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    input.value = wholePart + decimalPart;
}

/**
 * Format date input
 * @param {HTMLElement} input - The input element
 */
function formatDateInput(input) {
    // Allow only digits and slashes
    let value = input.value.replace(/[^\d/]/g, '');
    
    // Automatically add slashes (DD/MM/YYYY format)
    if (value.length > 2 && value.charAt(2) !== '/') {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length > 5 && value.charAt(5) !== '/') {
        value = value.substring(0, 5) + '/' + value.substring(5);
    }
    
    input.value = value;
}

/**
 * Validate PAN (Permanent Account Number)
 * @param {HTMLElement} input - The input element
 */
function validatePAN(input) {
    const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    const value = input.value.toUpperCase();
    input.value = value;
    
    const isValid = panRegex.test(value);
    if (value && !isValid) {
        input.classList.add('is-invalid');
    } else {
        input.classList.remove('is-invalid');
    }
}

/**
 * Show an alert message
 * @param {string} type - Alert type (success, danger, warning, info)
 * @param {string} message - The message to display
 */
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Find alert container or create one
    let alertContainer = $('#alert-container');
    if (alertContainer.length === 0) {
        $('.container-fluid').prepend('<div id="alert-container"></div>');
        alertContainer = $('#alert-container');
    }
    
    // Add alert to container
    alertContainer.html(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}

/**
 * Print an element
 * @param {HTMLElement} elem - The element to print
 */
function printElement(elem) {
    const originalContents = document.body.innerHTML;
    const printContents = elem.innerHTML;
    
    document.body.innerHTML = `
        <div class="print-container">${printContents}</div>
        <style>
            @media print {
                body {
                    padding: 15mm;
                    font-size: 14px;
                }
                .no-print {
                    display: none !important;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
            }
        </style>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
}

/**
 * Export table to CSV
 * @param {HTMLElement} table - The table to export
 * @param {string} filename - The filename for the exported CSV
 */
function exportTableToCSV(table, filename) {
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Get the text content
            let data = cols[j].textContent.trim();
            // Escape double-quotes
            data = data.replace(/"/g, '""');
            // Add data to the row array
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    downloadCSV(csv.join('\n'), filename);
}

/**
 * Download CSV data
 * @param {string} csv - The CSV data
 * @param {string} filename - The filename for the download
 */
function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], {type: 'text/csv'});
    const downloadLink = document.createElement('a');
    
    // Create a link to the file
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    
    // Add the link to the DOM
    document.body.appendChild(downloadLink);
    
    // Click the download link
    downloadLink.click();
    
    // Clean up
    document.body.removeChild(downloadLink);
}

/**
 * Format a currency value
 * @param {number} value - The value to format
 * @param {string} locale - The locale to use (default: 'en-IN')
 * @param {string} currency - The currency code (default: 'INR')
 * @returns {string} Formatted currency string
 */
function formatCurrency(value, locale = 'en-IN', currency = 'INR') {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(value);
}

/**
 * Format a date
 * @param {string} dateStr - The date string
 * @param {string} format - The format to use (default: 'DD/MM/YYYY')
 * @returns {string} Formatted date string
 */
function formatDate(dateStr, format = 'DD/MM/YYYY') {
    const date = new Date(dateStr);
    
    if (isNaN(date.getTime())) {
        return dateStr; // Return original if invalid
    }
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    switch (format) {
        case 'DD/MM/YYYY':
            return `${day}/${month}/${year}`;
        case 'MM/DD/YYYY':
            return `${month}/${day}/${year}`;
        case 'YYYY-MM-DD':
            return `${year}-${month}-${day}`;
        default:
            return `${day}/${month}/${year}`;
    }
}
