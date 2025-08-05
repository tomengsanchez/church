// Church Management System JavaScript

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

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Confirm delete actions
    $('.btn-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Status change functionality
    $('.status-change').on('change', function() {
        var memberId = $(this).data('member-id');
        var newStatus = $(this).val();
        var row = $(this).closest('tr');

        $.ajax({
            url: '/member/status/' + memberId,
            method: 'POST',
            data: {
                status: newStatus
            },
            success: function(response) {
                // Update the status badge
                var badge = row.find('.status-badge');
                badge.removeClass().addClass('badge status-badge');
                
                switch(newStatus) {
                    case 'active':
                        badge.addClass('bg-success').text('Active');
                        break;
                    case 'inactive':
                        badge.addClass('bg-warning').text('Inactive');
                        break;
                    case 'pending':
                        badge.addClass('bg-secondary').text('Pending');
                        break;
                    case 'suspended':
                        badge.addClass('bg-danger').text('Suspended');
                        break;
                }

                // Show success message
                showAlert('Status updated successfully', 'success');
            },
            error: function() {
                showAlert('Failed to update status', 'error');
                // Revert the select
                $(this).val($(this).data('original-status'));
            }
        });
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('.table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Form validation
    $('form').on('submit', function() {
        var isValid = true;
        var requiredFields = $(this).find('[required]');

        requiredFields.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            showAlert('Please fill in all required fields', 'error');
            return false;
        }

        // Show loading state
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Processing...'
        );
    });

    // Password strength indicator
    $('#password').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        var feedback = '';

        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;

        switch(strength) {
            case 0:
            case 1:
                feedback = '<span class="text-danger">Very Weak</span>';
                break;
            case 2:
                feedback = '<span class="text-warning">Weak</span>';
                break;
            case 3:
                feedback = '<span class="text-info">Medium</span>';
                break;
            case 4:
                feedback = '<span class="text-primary">Strong</span>';
                break;
            case 5:
                feedback = '<span class="text-success">Very Strong</span>';
                break;
        }

        $('#passwordStrength').html(feedback);
    });

    // DataTables initialization (if available)
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }

    // Chart.js integration (if available)
    if (typeof Chart !== 'undefined') {
        // Dashboard charts can be initialized here
        initializeCharts();
    }
});

// Utility functions
function showAlert(message, type) {
    var alertClass = type === 'error' ? 'alert-danger' : 'alert-' + type;
    var alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container').first().prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

function initializeCharts() {
    // Member statistics chart
    var ctx = document.getElementById('memberStatsChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Inactive', 'Pending', 'Suspended'],
                datasets: [{
                    data: [12, 19, 3, 5],
                    backgroundColor: [
                        '#198754',
                        '#ffc107',
                        '#6c757d',
                        '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

// Export functionality
function exportToCSV(tableId, filename) {
    var table = document.getElementById(tableId);
    var csv = [];
    var rows = table.querySelectorAll('tr');

    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (var j = 0; j < cols.length; j++) {
            var text = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + text + '"');
        }
        
        csv.push(row.join(','));
    }

    var csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
    var encodedUri = encodeURI(csvContent);
    var link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', filename + '.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print functionality
function printTable(tableId) {
    var printWindow = window.open('', '_blank');
    var table = document.getElementById(tableId);
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Print</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                ${table.outerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
} 