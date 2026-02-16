<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Active Subscriptions',
        'Active Subscriptions',
        'manage_options',
        'active-subscriptions',
        'render_active_subscriptions_page',
        'dashicons-groups',
        85
    );
});

function render_active_subscriptions_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'user_subscriptions';

    // Handle search
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $month = isset($_GET['month']) ? intval($_GET['month']) : '';
    $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : '';
    $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : '';

    // Build WHERE clause based on status
    $where = "WHERE 1=1";
    $now = current_time('mysql');
    switch ($status) {
        case 'active':
            $where .= " AND s.status=1 AND s.start_date <= '$now' AND s.end_date >= '$now'";
            break;
        case 'expired':
            $where .= " AND s.status=1 AND s.end_date < '$now'";
            break;
        case 'cancelled':
            $where .= " AND s.status=0";
            break;
        default: // all
            // No extra filter
            break;
    }

    if ($search) {
        $where .= $wpdb->prepare(
            " AND (
                plan_name LIKE %s
                OR plan_period LIKE %s
                OR plan_amount LIKE %s
                OR u.display_name LIKE %s
                OR u.user_email LIKE %s
            )",
            "%$search%", "%$search%", "%$search%", "%$search%", "%$search%"
        );
    }
    if ($month) {
        $where .= " AND MONTH(s.start_date) = " . intval($month);
    }
    if ($from) {
        $where .= " AND s.start_date >= '" . esc_sql($from) . "'";
    }
    if ($to) {
        $where .= " AND s.start_date <= '" . esc_sql($to) . "'";
    }

    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;

    // Count total results for pagination
    $total = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table s
        LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
        $where
    ");
    $total_pages = ceil($total / $per_page);

    $results = $wpdb->get_results("
        SELECT s.*, u.user_email, u.display_name
        FROM $table s
        LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
        $where
        ORDER BY s.start_date DESC
        LIMIT $per_page OFFSET $offset
    ");

    // Count for each tab
    $total_all = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $total_active = $wpdb->get_var("SELECT COUNT(*) FROM $table s WHERE s.status=1 AND s.start_date <= '$now' AND s.end_date >= '$now'");
    $total_expired = $wpdb->get_var("SELECT COUNT(*) FROM $table s WHERE s.status=1 AND s.end_date < '$now'");
    $total_cancelled = $wpdb->get_var("SELECT COUNT(*) FROM $table s WHERE s.status=0");

    $base_url = admin_url('admin.php?page=active-subscriptions');
    ?>
    <div class="wrap">
        <h1>Active Subscriptions</h1>
        <div class="sub-tabs" style="display:flex;gap:12px;margin-bottom:18px;">
            <a href="<?php echo $base_url; ?>" class="tab<?php if($status=='all') echo ' active'; ?>">All Subscriptions <span class="tab-count"><?php echo $total_all; ?></span></a>
            <a href="<?php echo $base_url; ?>&status=active" class="tab<?php if($status=='active') echo ' active'; ?>">Active <span class="tab-count"><?php echo $total_active; ?></span></a>
            <a href="<?php echo $base_url; ?>&status=expired" class="tab<?php if($status=='expired') echo ' active'; ?>">Expired <span class="tab-count"><?php echo $total_expired; ?></span></a>
            <a href="<?php echo $base_url; ?>&status=cancelled" class="tab<?php if($status=='cancelled') echo ' active'; ?>">Cancelled <span class="tab-count"><?php echo $total_cancelled; ?></span></a>
        </div>
        <style>
        .tab {padding:8px 18px;border-radius:8px;background:#f8f9fa;color:#23282d;text-decoration:none;font-weight:500;display:inline-block;}
        .tab.active {background:#007cba;color:#fff;}
        .tab-count {background:#e9ecef;color:#007cba;border-radius:12px;padding:2px 10px;margin-left:6px;font-weight:bold;}
        </style>
        <form method="get" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;">
            <input type="hidden" name="page" value="active-subscriptions" />
            <input type="hidden" name="status" value="<?php echo esc_attr($status); ?>" />
            <label>Select Month:
                <select name="month">
                    <option value="">--</option>
                    <?php for($m=1;$m<=12;$m++): ?>
                        <option value="<?php echo $m; ?>" <?php selected($month, $m); ?>>
                            <?php echo date('F', mktime(0,0,0,$m,1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </label>
            <label>From:
                <input type="date" name="from" value="<?php echo esc_attr($from); ?>">
            </label>
            <label>To:
                <input type="date" name="to" value="<?php echo esc_attr($to); ?>">
            </label>
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search user, email, plan..." style="min-width:180px;">
            <button class="button">Search</button>
            <a href="<?php echo $base_url; ?>&status=<?php echo esc_attr($status); ?>&download=csv<?php
                if($month) echo '&month=' . $month;
                if($from) echo '&from=' . $from;
                if($to) echo '&to=' . $to;
                if($search) echo '&s=' . urlencode($search);
            ?>" class="button button-secondary" style="margin-left:10px;">&#8681; Download CSV</a>
        </form>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan Name</th>
                    <th>Revenue</th>
                    <th>Active From</th>
                    <th>Active To</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->display_name); ?></td>
                        <td><?php echo esc_html($row->user_email); ?></td>
                        <td><?php echo esc_html($row->plan_name); ?></td>
                        <td><?php echo esc_html($row->plan_amount); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($row->start_date))); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($row->end_date))); ?></td>
                        <td>
                            <?php
                            $now = current_time('mysql');
                            $status_label = '';
                            $status_color = '';
                            if ($row->status == 0) {
                                // Cancelled
                                $status_label = 'Cancelled';
                                $status_color = '#6c757d'; // secondary/grey
                            } elseif ($row->status == 1) {
                                if ($row->end_date < $now) {
                                    // Expired
                                    $status_label = 'Expired';
                                    $status_color = '#ffc107'; // warning/yellow
                                } elseif ($row->start_date <= $now && $row->end_date >= $now) {
                                    // Active
                                    $status_label = 'Active';
                                    $status_color = '#28a745'; // success/green
                                } else {
                                    // Future subscription (not started yet)
                                    $status_label = 'Pending';
                                    $status_color = '#17a2b8'; // info/blue
                                }
                            }
                            ?>
                            <span style="background:<?php echo $status_color; ?>;color:#fff;padding:3px 10px;border-radius:12px;font-size:90%;">
                                <?php echo $status_label; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6">No active subscriptions found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="custom-pagination">
                <?php
                // Build base URL for pagination
                $base_url = admin_url('admin.php?page=active-subscriptions');
                if ($search) {
                    $base_url .= '&s=' . urlencode($search);
                }
                if ($status && $status != 'all') {
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
    <?php
    // CSV download handler - MUST be before any HTML output!
    if (isset($_GET['download']) && $_GET['download'] === 'csv') {
        $csv_results = $wpdb->get_results("
            SELECT s.*, u.user_email, u.display_name
            FROM $table s
            LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
            $where
            ORDER BY s.start_date DESC
        ");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=\"subscriptions.csv\"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['User', 'Email', 'Plan Name', 'Revenue', 'Active From', 'Active To', 'Status']);
        foreach ($csv_results as $row) {
            $now = current_time('mysql');
            $status_label = '';
            if ($row->status == 0) {
                $status_label = 'Cancelled';
            } elseif ($row->status == 1) {
                if ($row->end_date < $now) {
                    $status_label = 'Expired';
                } elseif ($row->start_date <= $now && $row->end_date >= $now) {
                    $status_label = 'Active';
                } else {
                    $status_label = 'Pending';
                }
            }
            fputcsv($out, [
                $row->display_name,
                $row->user_email,
                $row->plan_name,
                $row->plan_amount,
                date('Y-m-d', strtotime($row->start_date)),
                date('Y-m-d', strtotime($row->end_date)),
                $status_label
            ]);
        }
        fclose($out);
        exit;
    }
}