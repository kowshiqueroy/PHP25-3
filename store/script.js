$(document).ready(function() {
    
    // Initialize Select2 with Tagging (Create new if not exists)
    $('.select2-tags').select2({
        tags: true,
        width: '100%'
    });

    // Dynamic Type Search
    $('#p_type').select2({
        tags: true,
        ajax: {
            url: 'api.php?action=search_type',
            dataType: 'json',
            processResults: function(data) { return data; }
        }
    });

    // Dynamic Name Search (Dependent on Type)
    $('#p_type').on('change', function() {
        $('#p_name').val(null).trigger('change');
        $('#p_name').prop('disabled', false);
    });

    $('#p_name').select2({
        tags: true,
        ajax: {
            url: function() {
                return 'api.php?action=search_name&type=' + $('#p_type').val();
            },
            dataType: 'json',
            processResults: function(data) { return data; }
        }
    });

    // Auto-fetch Stock and Unit
    $('#p_name').on('change', function() {
        var name = $(this).val();
        var type = $('#p_type').val();
        if(name && type) {
            $.get('api.php?action=get_product_details', {type: type, name: name}, function(data) {
                var res = JSON.parse(data);
                $('#p_unit').val(res.unit);
                $('#current_stock').val(res.stock);
            });
        }
    });

    // Auto Calc Total
    $('#quantity, #unit_value').on('input', function() {
        var qty = parseFloat($('#quantity').val()) || 0;
        var val = parseFloat($('#unit_value').val()) || 0;
        $('#total_value').val((qty * val).toFixed(2));
    });

    // Offline Handling (Basic Redirect)
    window.addEventListener('offline', function() {
        window.location.href = "offline.html";
    });
});

// Check for offline data on load
document.addEventListener("DOMContentLoaded", function() {
    if(localStorage.getItem('offline_txn')) {
        $('#syncBtn').show().text("⚠️ You have unsynced data! Click to Sync.");
    }
});

$(document).ready(function() {
    // 1. Check if we have offline data waiting
    checkOfflineData();

    // ... (Keep your existing select2 logic here) ...
});

function checkOfflineData() {
    var rawData = localStorage.getItem('inventory_offline_data');
    if (rawData) {
        var data = JSON.parse(rawData);
        if (data.length > 0) {
            // Found data! Show the button
            $('#syncBtn').show();
            $('#syncCount').text(data.length);
        }
    }
}

function syncOfflineData() {
    var rawData = localStorage.getItem('inventory_offline_data');
    if (!rawData) return;

    var data = JSON.parse(rawData);
    if (!confirm("Found " + data.length + " offline entries. Sync them now?")) return;

    var successCount = 0;
    
    // Loop through data and send to server
    data.forEach(function(entry) {
        $.ajax({
            type: "POST",
            url: "transaction.php",
            data: entry,
            async: false, // Wait for each to finish
            success: function(response) {
                // You might want to check for "Success" text in response here
                successCount++;
            }
        });
    });

    alert("Synced " + successCount + " entries successfully!");
    
    // Clear storage and reload
    localStorage.removeItem('inventory_offline_data');
    location.reload();
}

// Add this to the bottom of script.js

// CACHE DICTIONARY FOR OFFLINE USE
$(document).ready(function() {
    // Only fetch if we are online
    if (navigator.onLine) {
        $.ajax({
            url: 'api.php?action=get_offline_dictionary',
            dataType: 'json',
            success: function(data) {
                // Save the massive list to Local Storage
                localStorage.setItem('offline_dictionary', JSON.stringify(data));
                console.log("Offline Dictionary Updated: " + data.names.length + " products cached.");
            }
        });
    }
});