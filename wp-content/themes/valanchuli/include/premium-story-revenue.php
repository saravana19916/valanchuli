<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Premium Stories Revenue',
        'Premium Stories Revenue',
        'manage_options',
        'premium-stories-revenue',
        'render_premium_stories_revenue_page',
        'dashicons-money-alt',
        87
    );
});

function render_premium_stories_revenue_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'premium_story_unlocks';
    $key_value = floatval(get_option('common_single_key_amount', 0.5));

    // Handle filters
    $status = isset($_GET['status']) ? $_GET['status'] : 'active';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;
    $now = current_time('mysql');

    // Build WHERE clause
    $where = "WHERE 1=1";
    if ($status == 'active') {
        $where .= " AND unlock_until >= '$now'";
    } elseif ($status == 'expired') {
        $where .= " AND unlock_until < '$now'";
    }
    if ($search) {
        $where .= $wpdb->prepare(
            " AND (
                u.display_name LIKE %s
                OR u.user_email LIKE %s
                OR p.post_title LIKE %s
            )",
            "%$search%", "%$search%", "%$search%"
        );
    }

    // Count for tabs
    $total_active = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE unlock_until >= '$now'");
    $total_expired = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE unlock_until < '$now'");

    // Count total for pagination
    $total = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table t
        LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
        LEFT JOIN {$wpdb->posts} p ON t.series_id = p.ID
        $where
    ");
    $total_pages = ceil($total / $per_page);

    // Fetch results
    $results = $wpdb->get_results("
        SELECT t.*, u.display_name, u.user_email, p.post_title, p.ID as post_id, t.author_id
        FROM $table t
        LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID
        LEFT JOIN {$wpdb->posts} p ON t.series_id = p.ID
        $where
        ORDER BY t.unlocked_at DESC
        LIMIT $per_page OFFSET $offset
    ");

    // Calculate total_key and total_amount for the current filter/page
    $total_key = 0;
    $total_amount = 0;
    foreach ($results as $row) {
        $total_key += intval($row->key_count);
        $total_amount += floatval($row->key_count) * $key_value;
    }

    $base_url = admin_url('admin.php?page=premium-stories-revenue');
    ?>
    <div class="wrap">
        <h1>Premium Stories Revenue</h1>
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
            <a href="<?php echo $base_url; ?>&status=active" class="tab<?php if($status=='active') echo ' active'; ?>">
                Active <span class="tab-count"><?php echo $total_active; ?></span>
            </a>
            <a href="<?php echo $base_url; ?>&status=expired" class="tab<?php if($status=='expired') echo ' active'; ?>">
                Expired <span class="tab-count"><?php echo $total_expired; ?></span>
            </a>
        </div>
        <style>
        .tab {padding:8px 18px;border-radius:8px;background:#f8f9fa;color:#23282d;text-decoration:none;font-weight:500;display:inline-block;}
        .tab.active {background:#007cba;color:#fff;}
        .tab-count {background:#e9ecef;color:#007cba;border-radius:12px;padding:2px 10px;margin-left:6px;font-weight:bold;}
        </style>
        <form method="get" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;">
            <input type="hidden" name="page" value="premium-stories-revenue" />
            <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>" />
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search user, email, story..." style="min-width:180px;">
            <button type="submit" class="button" style="margin-left:10px;">Filter</button>
            <button type="button" id="premium-stories-csv" class="button" style="margin-left:10px;">Download CSV</button>
        </form>
        <table id="premium-stories-table" class="widefat striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Story Name</th>
                    <th>Genre</th>
                    <th>No. of Episodes Locked</th>
                    <th>Writer</th>
                    <th>Keys</th>
                    <th>Amount</th>
                    <th>Validity Period</th>
                    <th>Purchase Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                    <?php
                    // Genre (division)
                    $division_id = get_post_meta($row->post_id, 'division', true);
                    $division_name = '';
                    if ($division_id) {
                        $division = get_term($division_id, 'division');
                        if (!is_wp_error($division) && $division) {
                            $division_name = $division->name;
                        }
                    }
                    // Writer info
                    $writer_info = get_userdata($row->author_id);
                    $writer_name = $writer_info ? $writer_info->display_name : '';
                    // Amount
                    $amount = $key_value * intval($row->key_count);
                    // Status
                    $is_active = strtotime($row->unlock_until) >= strtotime($now);
                    $status_label = $is_active ? 'Active' : 'Expired';
                    $status_color = $is_active ? '#28a745' : '#dc3545';
                    ?>
                    <tr>
                        <td><?php echo esc_html($row->display_name . " ({$row->user_id})"); ?></td>
                        <td><?php echo esc_html($row->user_email); ?></td>
                        <td><?php echo esc_html($row->post_title . ' (VLN' . $row->post_id . ')'); ?></td>
                        <td><?php echo esc_html($division_name); ?></td>
                        <td><?php echo esc_html($row->episodes_locked_count); ?></td>
                        <td><?php echo esc_html($writer_name . " ({$row->author_id})"); ?></td>
                        <td><?php echo esc_html($row->key_count); ?></td>
                        <td><?php echo esc_html(number_format($amount, 2)); ?></td>
                        <td><?php echo esc_html($row->validity_period); ?></td>
                        <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($row->unlocked_at))); ?></td>
                        <td>
                            <span style="background:<?php echo $status_color; ?>;color:#fff;padding:3px 10px;border-radius:12px;font-size:90%;">
                                <?php echo $status_label; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="custom-pagination">
                <?php
                // Build base URL for pagination
                $base_url = admin_url('admin.php?page=premium-stories-revenue');
                if ($search) {
                    $base_url .= '&s=' . urlencode($search);
                }
                if ($status) {
                    $base_url .= '&status=' . urlencode($status);
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

    document.getElementById('premium-stories-csv').onclick = function() {
        downloadTableAsCSV('premium-stories-table', 'premium-stories-revenue.csv');
    };
    </script>
    <?php
}