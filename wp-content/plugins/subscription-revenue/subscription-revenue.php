<?php
/*
Plugin Name: Subscription Revenue
Description: Admin dashboard for subscription revenue and writer revenue share.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Subscription Revenue',
        'Subscription Revenue',
        'manage_options',
        'subscription-revenue',
        'render_subscription_revenue_dashboard',
        'dashicons-chart-bar',
        25
    );
});

// Main dashboard page
function render_subscription_revenue_dashboard() {
    global $wpdb;

    // Handle save for payment status/unpaid reason (AJAX or POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['writer_payment_update'])) {
        check_admin_referer('save_writer_payment');
        global $wpdb;
        $writer_id = intval($_POST['writer_id']);
        $revenue_payment = floatval($_POST['revenue_payment']);
        $status = sanitize_text_field($_POST['payment_status']);
        $reason = sanitize_text_field($_POST['unpaid_reason']);
        $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-01');
        $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-t');

        // Store in user meta (for backward compatibility)
        update_user_meta($writer_id, 'writer_payment_status', $status);
        update_user_meta($writer_id, 'writer_unpaid_reason', $reason);
        update_user_meta($writer_id, 'writer_revenue_payment', $revenue_payment);

        // Store in new table for reporting
        $table = $wpdb->prefix . 'writer_payment_history';
        // Check if already exists for this user and period
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND from_date = %s AND to_date = %s",
            $writer_id, $from, $to
        ));
        $data = [
            'user_id'        => $writer_id,
            'from_date'      => $from,
            'to_date'        => $to,
            'payment_status' => $status,
            'unpaid_reason'  => $reason,
            'revenue_payment'=> $revenue_payment,
            'updated_at'     => current_time('mysql'),
        ];
        if ($exists) {
            $wpdb->update($table, $data, ['id' => $exists]);
        } else {
            $wpdb->insert($table, $data);
        }

        echo '<div class="updated"><p>Payment status updated.</p></div>';
    }

    // Date filter
    $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-01');
    $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-t');

    // Get all successful subscriptions in the period
    $subscriptions = $wpdb->get_results($wpdb->prepare(
        "SELECT user_id, plan_amount 
         FROM {$wpdb->prefix}user_subscriptions 
         WHERE status = 1 
           AND payment_status = 'success'
           AND start_date <= %s AND end_date >= %s",
        $to . ' 23:59:59', $from . ' 00:00:00'
    ));

    // Unique users
    $user_ids = [];
    $total_subscription_amount = 0;
    foreach ($subscriptions as $sub) {
        $user_ids[$sub->user_id] = true;
        $total_subscription_amount += floatval($sub->plan_amount);
    }
    $subscription_users = count($user_ids);
    $subscription_users = $subscription_users ?: 0;

    // Gateway charges and refunds (dummy values, replace with real if available)
    $gateway_charges = 25000;
    $refunds = 10000;

    // Revenue split
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $platformPer = floatval(get_option('platform_revenue_percentage', 70));
    $writers_pool = $total_subscription_amount * ($writerPer / 100);
    $platform_revenue = $total_subscription_amount * ($platformPer / 100);
    // $site_net_revenue = $platform_revenue - $gateway_charges - $refunds;
    $site_net_revenue = $platform_revenue;

    // Writerwise reads
    $writer_reads = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(view_count) as total_reads
         FROM {$wpdb->prefix}daily_story_views
         WHERE view_date BETWEEN %s AND %s
         GROUP BY author_id",
        $from, $to
    ));

    $total_reads = 0;
    foreach ($writer_reads as $wr) $total_reads += $wr->total_reads;

    if ($total_reads > 0) {
        $writers_pool = $total_subscription_amount * ($writerPer / 100);
        $platform_revenue = $total_subscription_amount * ($platformPer / 100);
    } else {
        $writers_pool = 0;
        $platform_revenue = $total_subscription_amount;
    }
    $site_net_revenue = $platform_revenue;

    // Writerwise revenue
    $writer_revenue = [];
    foreach ($writer_reads as $wr) {
        $user_info = get_userdata($wr->author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $payment_status = get_user_meta($wr->author_id, 'writer_payment_status', true) ?: 'Unpaid';
        $unpaid_reason = get_user_meta($wr->author_id, 'writer_unpaid_reason', true) ?: '';
        $revenue = $total_reads > 0 ? round(($wr->total_reads / $total_reads) * $writers_pool) : 0;

        $locked_stories = get_locked_stories_count_by_author($wr->author_id);

        $writer_revenue[] = [
            'id' => $wr->author_id,
            'name' => $name,
            'reads' => $wr->total_reads,
            'locked_stories' => $locked_stories,
            'revenue' => $revenue,
            'status' => $payment_status,
            'reason' => $unpaid_reason,
        ];
    }

    // Storywise revenue
    $storywise = $wpdb->get_results($wpdb->prepare(
        "SELECT series_id, author_id, SUM(view_count) as total_reads
         FROM {$wpdb->prefix}daily_story_views
         WHERE view_date BETWEEN %s AND %s
         GROUP BY series_id",
        $from, $to
    ));

    // For each story, calculate revenue
    $story_revenue = [];
    foreach ($storywise as $row) {
        $post = get_post($row->series_id);
        $user_info = get_userdata($row->author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $division_id = get_post_meta($post->ID, 'division', true);
        $division_name = '';
        if ($division_id) {
            $division = get_term($division_id, 'division');
            if (!is_wp_error($division) && $division) {
                $division_name = $division->name;
            }
        }
        $post_name = $post ? $post->post_title : 'Unknown';

        //get last updated date
        $terms = get_the_terms($post->ID, 'series');

        $series_term = $terms[0];

        $query = new WP_Query([
            'post_type'      => 'post',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'ASC',
            'post__not_in'   => [$post->ID],
            'tax_query'      => [
                [
                    'taxonomy' => 'series',
                    'field'    => 'term_id',
                    'terms'    => [$series_term->term_id],
                ],
            ],
        ]);
        $episodes = $query->posts;
        $episodeCount = count($episodes);

        $last_updated = '';
        if (!empty($episodes)) {
            $last_updated_raw = max(array_map(function($ep) {
                return $ep->post_modified;
            }, $episodes));
            // Format date as 01-feb-2026
            $last_updated = date('d-M-Y', strtotime($last_updated_raw));
            $last_updated = strtolower($last_updated); // To get 'feb' instead of 'Feb'
        }

        $story_revenue[] = [
            'id' => $row->author_id,
            'title' => $post_name,
            'writer' => $name,
            'genre' => $division_name,
            'reads' => $row->total_reads,
            'revenue' => $total_reads > 0 ? round(($row->total_reads / $total_reads) * $writers_pool) : 0,
            'last_updated' => $last_updated,
            'episode_count' => $episodeCount,
        ];
    }

    // UI Output
    ?>
    <div class="wrap">
        <h1>Subscription Revenue</h1>
        <form method="get" style="margin-bottom:20px;">
            <input type="hidden" name="page" value="subscription-revenue" />
            <label>Custom Period: </label>
            <input type="date" name="from" value="<?php echo esc_attr($from); ?>" required>
            <input type="date" name="to" value="<?php echo esc_attr($to); ?>" required>
            <button class="button button-primary">Apply</button>
        </form>
        <div style="display:flex;gap:18px;margin-bottom:20px;">
            <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Subscription Users</div>
                <div style="font-size:2rem;font-weight:bold; margin-top: 15px;"><?php echo number_format($subscription_users); ?></div>
            </div>
            <div style="flex:1;background:#fff0f0;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#b71c1c;">Total Subscription Amount</div>
                <div style="font-size:2rem;font-weight:bold; margin-top: 15px;">₹<?php echo number_format($total_subscription_amount); ?></div>
            </div>
            <div style="flex:1;background:#f0f7ff;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#1976d2;">Writers Revenue Share</div>
                <div style="font-size:1.3rem;font-weight:bold; margin-top: 15px;">₹<?php echo number_format($writers_pool); ?></div>
            </div>
            <div style="flex:1;background:#fffbe7;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#bfa100;">Site Net Revenue</div>
                <div style="font-size:1.3rem;font-weight:bold; margin-top: 15px;">₹<?php echo number_format($site_net_revenue); ?></div>
            </div>
            <!-- <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Writers Revenue Share</div>
                <div style="font-size:1.3rem;font-weight:bold;">₹<?php echo number_format($writers_pool); ?></div>
                <div style="font-size:1.2rem;color:#005d67;">Site Net Revenue</div>
                <div style="font-size:1.3rem;font-weight:bold;">₹<?php echo number_format($site_net_revenue); ?></div>
            </div> -->
        </div>

        <!-- Writerwise Revenue Distribution -->
        <h2 style="margin-top:30px;">Writerwise Revenue Distribution</h2>
        <input type="text" id="writerwise-search" placeholder="Search Writerwise..." style="margin-bottom:10px;width:250px;">
        <button id="writerwise-csv" class="button" style="margin-left:10px;">Download CSV</button>
        <table id="writerwise-table" class="widefat striped" style="margin-bottom:30px;">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Writer Name</th>
                    <th>Locked Stories</th>
                    <th>Total Views</th>
                    <th>Revenue Payment</th>
                    <th>Payment Status</th>
                    <th>Unpaid Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($writer_revenue as $wr): ?>
                <tr>
                    <td><?php echo esc_html($wr['id']); ?></td>
                    <td><?php echo esc_html($wr['name']); ?></td>
                    <td><?php echo esc_html($wr['locked_stories']); ?></td>
                    <td><?php echo esc_html($wr['reads']); ?></td>
                    <td>₹<?php echo number_format($wr['revenue']); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('save_writer_payment'); ?>
                            <input type="hidden" name="writer_id" value="<?php echo esc_attr($wr['id']); ?>">
                            <input type="hidden" name="revenue_payment" value="<?php echo esc_attr($wr['revenue']); ?>">
                            <select name="payment_status">
                                <option value="Paid" <?php selected($wr['status'], 'Paid'); ?>>Paid</option>
                                <option value="Unpaid" <?php selected($wr['status'], 'Unpaid'); ?>>Unpaid</option>
                            </select>
                    </td>
                    <td>
                            <input type="text" name="unpaid_reason" value="<?php echo esc_attr($wr['reason']); ?>" placeholder="Reason">
                    </td>
                    <td>
                            <button type="submit" name="writer_payment_update" class="button">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Storywise Revenue Details -->
        <h2>Storywise Revenue Details</h2>
        <input type="text" id="storywise-search" placeholder="Search Storywise..." style="margin-bottom:10px;width:250px;">
        <button id="storywise-csv" class="button" style="margin-left:10px;">Download CSV</button>
        <table id="storywise-table" class="widefat striped">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Story Name</th>
                    <th>Writer Name</th>
                    <th>Story Genere</th>
                    <th>Number of Episodes</th>
                    <th>Total Views</th>
                    <th>Revenue Payment</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($story_revenue as $row): ?>
                <tr>
                    <td><?php echo esc_html($row['id']); ?></td>
                    <td><?php echo esc_html($row['title']); ?></td>
                    <td><?php echo esc_html($row['writer']); ?></td>
                    <td><?php echo esc_html($row['genre']); ?></td>
                    <td><?php echo esc_html($row['episode_count']); ?></td>
                    <td><?php echo esc_html($row['reads']); ?></td>
                    <td>₹<?php echo number_format($row['revenue']); ?></td>
                    <td><?php echo esc_html($row['last_updated']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <style>
        .widefat th, .widefat td { text-align:center; }
        .widefat select, .widefat input[type="text"] { width: 100px; }
    </style>
    <script>
        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            input.addEventListener('keyup', function() {
                const filter = input.value.toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }
        filterTable('writerwise-search', 'writerwise-table');
        filterTable('storywise-search', 'storywise-table');

        // CSV Export Function
        function downloadTableAsCSV(tableId, filename, excludeLastColumn = false) {
            const table = document.getElementById(tableId);
            let csv = [];
            let cols = [];
            // Get headers, skip last column
            const headers = excludeLastColumn ? Array.from(table.querySelectorAll('thead th')).slice(0, -1).map(th => `"${th.innerText.trim()}"`) : Array.from(table.querySelectorAll('thead th')).map(th => `"${th.innerText.trim()}"`);
            csv.push(headers.join(','));
            // Get visible rows
            table.querySelectorAll('tbody tr').forEach(row => {
                if (row.style.display === 'none') return;
                // For each cell except last
                if (excludeLastColumn) {
                    cols = Array.from(row.children).slice(0, -1).map((td, i) => {
                        // For payment status column (dropdown), get selected value
                        if (td.querySelector('select')) {
                            return `"${td.querySelector('select').value}"`;
                        }
                        return `"${td.innerText.trim()}"`;
                    });
                } else {
                    cols = Array.from(row.children).map(td => `"${td.innerText.trim()}"`);
                }
                
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

        // Attach to buttons
        document.getElementById('writerwise-csv').onclick = function() {
            downloadTableAsCSV('writerwise-table', 'writerwise-revenue.csv', true);
        };
        document.getElementById('storywise-csv').onclick = function() {
            downloadTableAsCSV('storywise-table', 'storywise-revenue.csv');
        };
    </script>
    <?php
}