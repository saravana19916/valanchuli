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
    $where = "WHERE status=1";
    $now = current_time('mysql');
    $where .= " AND start_date <= '$now' AND end_date >= '$now'";
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

    ?>
    <div class="wrap">
        <h1>Active Subscriptions</h1>
        <form method="get" style="margin-bottom:20px;">
            <input type="hidden" name="page" value="active-subscriptions" />
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search..." />
            <button class="button">Search</button>
        </form>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan Name</th>
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
                        <td><?php echo esc_html(date('Y-m-d', strtotime($row->start_date))); ?></td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($row->end_date))); ?></td>
                        <td>
                            <?php if ($row->status == 1): ?>
                                <span style="background:#28a745;color:#fff;padding:3px 10px;border-radius:12px;font-size:90%;">Active</span>
                            <?php else: ?>
                                <span style="background:#dc3545;color:#fff;padding:3px 10px;border-radius:12px;font-size:90%;">Inactive</span>
                            <?php endif; ?>
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
}