<?php
/*
Plugin Name: User Bank Details Report
Description: Admin report for user bank details.
*/

// Add menu in admin
add_action('admin_menu', function() {
    add_menu_page(
        'User Bank Details',
        'User Bank Details',
        'manage_options',
        'user-bank-details-report',
        'render_user_bank_details_report',
        'dashicons-id-alt',
        26
    );
});

// Render the report page
function render_user_bank_details_report() {
    global $wpdb;

    $table = $wpdb->prefix . 'user_bank_details';

    // --- Pagination setup ---
    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // --- Search setup ---
    $search = isset($_GET['bank_search']) ? sanitize_text_field($_GET['bank_search']) : '';
    $where = '';
    if ($search) {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $where = $wpdb->prepare(
            "WHERE user_id LIKE %s OR bank_name LIKE %s OR holder_name LIKE %s OR account_number LIKE %s OR ifsc_code LIKE %s OR pan_number LIKE %s OR phone_number LIKE %s",
            $like, $like, $like, $like, $like, $like, $like
        );
    }

    // --- Get total count for pagination ---
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table $where");

    // --- Get paginated results ---
    $results = $wpdb->get_results(
        "SELECT * FROM $table $where ORDER BY id DESC LIMIT $per_page OFFSET $offset"
    );

    echo '<div class="wrap"><h1>User Bank Details</h1>';

    // --- Search form ---
    echo '<form method="get" style="margin-bottom:20px;">
        <input type="hidden" name="page" value="user-bank-details-report">
        <input type="text" name="bank_search" value="' . esc_attr($search) . '" placeholder="Search user, bank, IFSC, etc." style="min-width:220px;">
        <button class="button" style="margin-left: 10px;"><i class="fas fa-search"></i> Search</button>
        <button id="bank-details-csv" class="button" style="margin-left: 20px;">Download CSV</button>
    </form>';

    if (!$results) {
        echo '<p>No bank details found.</p></div>';
        return;
    }

    echo '<table class="widefat striped" style="max-width:100%;margin-top:20px;">';
    echo '<thead>
        <tr>
            <th>User</th>
            <th>User ID</th>
            <th>Bank Name</th>
            <th>Holder Name</th>
            <th>Account Number</th>
            <th>IFSC Code</th>
            <th>PAN Number</th>
            <th>Phone Number</th>
            <th>Last Updated</th>
            <th>Remark</th>
            <th>Action</th>
        </tr>
    </thead><tbody>';
    foreach ($results as $row) {
        $user = get_userdata($row->user_id);
        echo '<tr>';
        echo '<td>' . ($user ? esc_html($user->display_name) : 'User ID ' . $row->user_id) . '</td>';
        echo '<td>' . esc_html($row->user_id) . '</td>';
        echo '<td>' . esc_html($row->bank_name) . '</td>';
        echo '<td>' . esc_html($row->holder_name) . '</td>';
        echo '<td>' . esc_html($row->account_number) . '</td>';
        echo '<td>' . esc_html($row->ifsc_code) . '</td>';
        echo '<td>' . esc_html($row->pan_number) . '</td>';
        echo '<td>' . esc_html($row->phone_number) . '</td>';
        echo '<td>' . esc_html($row->updated_at ?? '') . '</td>';
        echo '<td>' . esc_html($row->remark ?? '') . '</td>';
        echo '<td><button class="button edit-bank-btn" data-id="' . esc_attr($row->id) . '" data-user="' . esc_attr($row->user_id) . '">Edit</button></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    // --- Pagination links ---
    $total_pages = ceil($total / $per_page);
    if ($total_pages > 1) {
        echo '<div class="custom-bank-pagination">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $url = add_query_arg([
                'page' => 'user-bank-details-report',
                'paged' => $i,
                'bank_search' => $search
            ], admin_url('admin.php'));
            if ($i == $paged) {
                echo '<span class="custom-page-num active">' . $i . '</span>';
            } else {
                echo '<a class="custom-page-num" href="' . esc_url($url) . '">' . $i . '</a>';
            }
        }
        echo '</div>';
    }

    echo '</div>';

    // Add this CSS to your plugin or admin head
    echo '<style>
.custom-bank-pagination {
    margin: 20px 0;
    display: flex;
    justify-content: flex-end;
    gap: 6px;
}
.custom-page-num {
    display: inline-block;
    min-width: 32px;
    padding: 6px 12px;
    margin: 0 2px;
    border-radius: 6px;
    background: #f5f5f5;
    color: #005d67;
    text-align: center;
    text-decoration: none;
    font-weight: 500;
    border: 1px solid #e0e0e0;
    transition: background 0.2s, color 0.2s;
}
.custom-page-num:hover {
    background: #005d67;
    color: #fff;
}
.custom-page-num.active {
    background: #005d67;
    color: #fff;
    font-weight: bold;
    border: 1px solid #005d67;
    cursor: default;
}
</style>';

    // --- Edit Bank Details Modal ---
    echo '
<div id="editBankModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:99999;">
    <div class="edit-bank-modal-content">
        <span id="closeEditBankModal" class="edit-bank-modal-close">&times;</span>
        <h2 style="margin-bottom:18px;">Edit Bank Details</h2>
        <form id="editBankForm">
            <input type="hidden" name="user_id" id="edit_user_id">
            <table class="edit-bank-table">
                <tr>
                    <td><label for="edit_bank_name">Bank Name</label></td>
                    <td><input type="text" name="bank_name" id="edit_bank_name" required></td>
                </tr>
                <tr>
                    <td><label for="edit_holder_name">Holder Name</label></td>
                    <td><input type="text" name="holder_name" id="edit_holder_name" required></td>
                </tr>
                <tr>
                    <td><label for="edit_account_number">Account Number</label></td>
                    <td><input type="text" name="account_number" id="edit_account_number" required></td>
                </tr>
                <tr>
                    <td><label for="edit_ifsc_code">IFSC Code</label></td>
                    <td><input type="text" name="ifsc_code" id="edit_ifsc_code" required></td>
                </tr>
                <tr>
                    <td><label for="edit_pan_number">PAN Number</label></td>
                    <td><input type="text" name="pan_number" id="edit_pan_number" required></td>
                </tr>
                <tr>
                    <td><label for="edit_phone_number">Phone Number</label></td>
                    <td><input type="text" name="phone_number" id="edit_phone_number" required></td>
                </tr>
                <tr>
                    <td><label for="edit_remark">Remark</label></td>
                    <td><input type="text" name="remark" id="edit_remark"></td>
                </tr>
            </table>
            <div style="text-align:right;margin-top:18px;">
                <button type="submit" class="button button-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<style>
.edit-bank-modal-content {
    background: #fff;
    max-width: 440px;
    margin: 60px auto;
    padding: 32px 28px 18px 28px;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
    position: relative;
    font-family: inherit;
    max-height: 80vh;         /* Add this */
    overflow-y: auto;         /* Add this */
}
.edit-bank-modal-close {
    position: absolute;
    top: 14px;
    right: 18px;
    cursor: pointer;
    font-size: 26px;
    color: #888;
    transition: color 0.2s;
}
.edit-bank-modal-close:hover {
    color: #c00;
}
.edit-bank-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
}
.edit-bank-table td {
    padding: 8px 6px;
    vertical-align: middle;
}
.edit-bank-table label {
    font-weight: 500;
    color: #005d67;
}
.edit-bank-table input[type="text"] {
    width: 100%;
    padding: 7px 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    font-size: 1rem;
    background: #f9f9f9;
    transition: border 0.2s;
}
.edit-bank-table input[type="text"]:focus {
    border-color: #005d67;
    outline: none;
}
.button.button-primary {
    background: #005d67;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 8px 22px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
    transition: background 0.2s;
}
.button.button-primary:hover {
    background: #008a99;
}
@media (max-width: 600px) {
    .edit-bank-modal-content {
        max-width: 98vw;
        margin: 20px auto;
        padding: 18px 6vw 12px 6vw;
    }
}
</style>
';

    echo '<script>
document.querySelectorAll(".edit-bank-btn").forEach(function(btn) {
    btn.onclick = function() {
        var row = btn.closest("tr");
        document.getElementById("edit_user_id").value = btn.getAttribute("data-user");
        document.getElementById("edit_bank_name").value = row.children[2].textContent;
        document.getElementById("edit_holder_name").value = row.children[3].textContent;
        document.getElementById("edit_account_number").value = row.children[4].textContent;
        document.getElementById("edit_ifsc_code").value = row.children[5].textContent;
        document.getElementById("edit_pan_number").value = row.children[6].textContent;
        document.getElementById("edit_phone_number").value = row.children[7].textContent;
        document.getElementById("edit_remark").value = row.children[9].textContent;
        document.getElementById("editBankModal").style.display = "block";
    };
});
document.getElementById("closeEditBankModal").onclick = function() {
    document.getElementById("editBankModal").style.display = "none";
};
document.getElementById("editBankForm").onsubmit = function(e) {
    e.preventDefault();
    var form = e.target;
    var fd = new FormData(form);
    fd.append("action", "admin_edit_bank_details");
    fetch(ajaxurl, {
        method: "POST",
        body: fd
    }).then(res => res.json()).then(data => {
        if (data.success) {
            alert("Bank details updated!");
            location.reload();
        } else {
            alert("Error: " + (data.data || "Unknown"));
        }
    });
};

// --- CSV Download ---
document.getElementById("bank-details-csv").onclick = function() {
    var table = document.querySelector(".widefat");
    var rows = Array.from(table.querySelectorAll("tr"));
    var csv = [];
    rows.forEach(function(row, idx) {
        var cols = Array.from(row.querySelectorAll("th, td"));
        // Exclude last column (Action)
        cols = cols.slice(0, -1);
        var line = cols.map(function(col) {
            return "\"" + col.textContent.replace(/"/g, "\"\"") + "\"";
        }).join(",");
        csv.push(line);
    });
    var csvContent = csv.join("\r\n");
    var blob = new Blob([csvContent], {type: "text/csv"});
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "user-bank-details.csv";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>';
}