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
        <button class="button">Search</button>
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
        echo '</tr>';
    }
    echo '</tbody></table>';

    // --- Pagination links ---
    $total_pages = ceil($total / $per_page);
    if ($total_pages > 1) {
        echo '<div style="margin:20px 0;">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $url = add_query_arg([
                'page' => 'user-bank-details-report',
                'paged' => $i,
                'bank_search' => $search
            ], admin_url('admin.php'));
            if ($i == $paged) {
                echo '<span style="margin-right:8px;font-weight:bold;">' . $i . '</span>';
            } else {
                echo '<a href="' . esc_url($url) . '" style="margin-right:8px;">' . $i . '</a>';
            }
        }
        echo '</div>';
    }

    echo '</div>';
}