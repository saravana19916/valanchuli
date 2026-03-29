<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Active Keys',
        'Active Keys',
        'manage_options',
        'active-keys',
        'render_active_keys_page',
        'dashicons-admin-network',
        86
    );
});

function render_active_keys_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'coin_purchases';

    // Handle filters
    $status = isset($_GET['status']) ? $_GET['status'] : 'success';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $month  = isset($_GET['month']) ? intval($_GET['month']) : '';
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;

    // Build WHERE clause
    $where = "WHERE 1=1";
    if ($status == 'success') {
        $where .= " AND payment_status = 'success'";
    } elseif ($status == 'failed') {
        $where .= " AND payment_status != 'success'";
    }
    if ($search) {
        $where .= $wpdb->prepare(
            " AND (
                u.display_name LIKE %s
                OR u.user_email LIKE %s
                OR c.payment_id LIKE %s
            )",
            "%$search%", "%$search%", "%$search%"
        );
    }
    if ($month) {
        $where .= " AND MONTH(c.created_at) = " . intval($month);
    }

    // Count for tabs
    $total_active = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE payment_status = 'success'");
    $total_failed = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE payment_status != 'success'");

    // Count total for pagination
    $total = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table c
        LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
        $where
    ");
    $total_pages = ceil($total / $per_page);

    // Fetch results
    $results = $wpdb->get_results("
        SELECT c.*, u.display_name, u.user_email
        FROM $table c
        LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
        $where
        ORDER BY c.created_at DESC
        LIMIT $per_page OFFSET $offset
    ");

    $total_key = 0;
    $total_amount = 0;
    foreach ($results as $row) {
        $total_key += intval($row->coin);
        $total_amount += floatval($row->price);
    }

    $base_url = admin_url('admin.php?page=active-keys');
    ?>
    <div class="wrap">
        <h1>Active Keys</h1>
        <div style="display:flex;gap:18px;margin-bottom:20px;">
            <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Total Key</div>
                <div style="font-size:2rem;font-weight:bold; margin-top: 15px;"><?php echo number_format($total_key); ?></div>
            </div>
            <div style="flex:1;background:#fff0f0;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#b71c1c;">Total Amount</div>
                <div style="font-size:2rem;font-weight:bold; margin-top: 15px;">₹<?php echo number_format($total_amount); ?></div>
            </div>
        </div>

        <div class="sub-tabs" style="display:flex;gap:12px;margin-bottom:18px;">
            <a href="<?php echo $base_url; ?>&status=success" class="tab<?php if($status=='success') echo ' active'; ?>">
                Active <span class="tab-count"><?php echo $total_active; ?></span>
            </a>
            <a href="<?php echo $base_url; ?>&status=failed" class="tab<?php if($status=='failed') echo ' active'; ?>">
                Failed <span class="tab-count"><?php echo $total_failed; ?></span>
            </a>
        </div>
        <style>
        .tab {padding:8px 18px;border-radius:8px;background:#f8f9fa;color:#23282d;text-decoration:none;font-weight:500;display:inline-block;}
        .tab.active {background:#007cba;color:#fff;}
        .tab-count {background:#e9ecef;color:#007cba;border-radius:12px;padding:2px 10px;margin-left:6px;font-weight:bold;}
        </style>
        <form method="get" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;">
            <input type="hidden" name="page" value="active-keys" />
            <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>" />
            <label>Select Month:
                <select name="month">
                    <option value="">--</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </label>
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search user, email, payment id..." style="min-width:180px;">
            <button type="submit" class="button" style="margin-left:10px;">Filter</button>
            <button type="button" id="active-keys-csv" class="button" style="margin-left:10px;">Download CSV</button>
        </form>
        <table id="active-keys-table" class="widefat striped">
            <thead>
                <tr>
                    <th>User (ID)</th>
                    <th>Email</th>
                    <th>Mobile Number</th>
                    <th>Keys</th>
                    <th>Amount</th>
                    <th>Payment ID</th>
                    <th>Payment Method</th>
                    <th>Purchase Date</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->display_name . " ({$row->user_id})"); ?></td>
                        <td><?php echo esc_html($row->user_email); ?></td>
                        <td><?php echo esc_html($row->phone_number); ?></td>
                        <td><?php echo esc_html($row->coin); ?></td>
                        <td><?php echo esc_html($row->price); ?></td>
                        <td><?php echo esc_html($row->payment_id); ?></td>
                        <td><?php echo esc_html($row->payment_method); ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($row->created_at))); ?></td>
                        <td>
                            <?php
                            $status_label = $row->payment_status == 'success' ? 'Success' : 'Failed';
                            $status_color = $row->payment_status == 'success' ? '#28a745' : '#dc3545';
                            ?>
                            <span style="background:<?php echo $status_color; ?>;color:#fff;padding:3px 10px;border-radius:12px;font-size:90%;">
                                <?php echo $status_label; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="8">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="custom-pagination">
                <?php
                // Build base URL for pagination
                $base_url = admin_url('admin.php?page=active-keys');
                if ($search) {
                    $base_url .= '&s=' . urlencode($search);
                }
                if ($status) {
                    $base_url .= '&status=' . urlencode($status);
                }
                if ($month) {
                    $base_url .= '&month=' . intval($month);
                }
                echo paginate_links(array(
                    'base'      => $base_url . '&paged=%#%',
                    'format'    => '',
                    'total'     => $total_pages,
                    'current'   => $page,
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'type'      => 'list',
                ));
                ?>
            </div>
        </div>
    </div>
    <style>
    .wp-admin .custom-pagination ul {
        display: flex;
        gap: 6px;
        margin: 18px 0;
        padding: 0;
        list-style: none;
        flex-wrap: wrap;
        float: right;
    }
    .wp-admin .custom-pagination li {
        display: inline-block;
    }
    .wp-admin .custom-pagination a,
    .wp-admin .custom-pagination span {
        display: inline-block;
        padding: 6px 14px;
        border: 1px solid #ccd0d4;
        border-radius: 6px;
        background: #f8f9fa;
        color: #23282d;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }
    .wp-admin .custom-pagination a:hover {
        background: #007cba;
        color: #fff;
        border-color: #007cba;
    }
    .wp-admin .custom-pagination .current {
        background: #007cba;
        color: #fff;
        border-color: #007cba;
        font-weight: bold;
    }
    </style>
    <script>
    function downloadTableAsCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        let csv = [];
        // Get headers
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => `"${th.innerText.trim()}"`);
        csv.push(headers.join(','));
        // Get visible rows
        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.style.display === 'none') return;
            const cols = Array.from(row.children).map(td => `"${td.innerText.trim()}"`);
            csv.push(cols.join(','));
        });
        // Download
        const csvString = csv.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    document.getElementById('active-keys-csv').onclick = function() {
        downloadTableAsCSV('active-keys-table', 'active-keys.csv');
    };
    </script>
    <?php
}