<?php
/*
Plugin Name: Key Revenue
Description: Admin dashboard for key-based revenue and writer share.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

// Add admin menu
add_action('admin_menu', function() {
    add_menu_page(
        'Key Revenue',
        'Key Revenue',
        'manage_options',
        'key-revenue',
        'render_key_revenue_dashboard',
        'dashicons-admin-generic',
        26
    );
});

// Enqueue Font Awesome
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');
});

// Main dashboard page
function render_key_revenue_dashboard() {
    global $wpdb;

    // Handle save for payment status/unpaid reason (AJAX or POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['key_writer_payment_update'])) {
        check_admin_referer('save_key_writer_payment');
        global $wpdb;
        $writer_id = intval($_POST['writer_id']);
        $revenue_payment = floatval($_POST['revenue_payment']);
        $status = sanitize_text_field($_POST['payment_status']);
        $transaction_id = sanitize_text_field($_POST['transaction_id']);
        $reason = $status == 'Paid' ? '' : sanitize_text_field($_POST['unpaid_reason']);
        $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-01');
        $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-t');

        // Store in new table for reporting
        $table = $wpdb->prefix . 'writer_payment_history';
        // Check if already exists for this user and period
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND revenue_type = %s AND from_date = %s AND to_date = %s",
            $writer_id, 'key', $from, $to
        ));
        $data = [
            'user_id'        => $writer_id,
            'from_date'      => $from,
            'to_date'        => $to,
            'revenue_type'   => 'key',
            'payment_status' => $status,
            'transaction_id' => $transaction_id,
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

    $key_value = floatval(get_option('common_single_key_amount', 0.5));
    $keysToUnlockEpisode = floatval(get_option('common_coin_unlock', 0));
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $platformPer = floatval(get_option('platform_revenue_percentage', 70));
    $writer_share_per_key = $key_value * ($writerPer / 100);
    $platform_share_per_key = $key_value * ($platformPer / 100);

    // Get reward keys for each writer in the period
    $reward_keys_by_writer = [];
    $reward_keys_total = 0;
    $reward_keys_revenue_total = 0;
    $reward_table = $wpdb->prefix . 'writer_key_rewards';
    $reward_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(`key`) as reward_keys
         FROM $reward_table
         WHERE rewarded_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));
    foreach ($reward_rows as $row) {
        $reward_keys_by_writer[$row->author_id] = intval($row->reward_keys);
        $reward_keys_total += intval($row->reward_keys);
        $reward_keys_revenue_total += intval($row->reward_keys) * $writer_share_per_key;
    }

    // Total keys purchased (lock_type='key')
    $total_unlocked_episodes = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}user_episode_unlocks WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));
    $total_unlocked_episodes = $total_unlocked_episodes ?: 0;

    $total_keys = $total_unlocked_episodes * $keysToUnlockEpisode;

    // Total payment
    $total_keys_with_rewards = $total_keys + $reward_keys_total;
    $total_payment = $total_keys_with_rewards * $key_value;

    // Writer and platform revenue
    $writers_revenue = ($total_keys * $writer_share_per_key) + $reward_keys_revenue_total;
    $platform_revenue = $total_keys_with_rewards * $platform_share_per_key;

    // Writerwise revenue
    // 1. Get unlocks
    $writerwise = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, COUNT(episode_id) as episodes
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // 2. Get rewards
    $reward_table = $wpdb->prefix . 'writer_key_rewards';
    $reward_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(`key`) as reward_keys
         FROM $reward_table
         WHERE rewarded_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // 3. Merge author IDs
    $all_author_ids = [];
    foreach ($writerwise as $row) $all_author_ids[$row->author_id] = ['episodes' => $row->episodes];
    foreach ($reward_rows as $row) {
        if (!isset($all_author_ids[$row->author_id])) $all_author_ids[$row->author_id] = ['episodes' => 0];
        $all_author_ids[$row->author_id]['reward_keys'] = intval($row->reward_keys);
    }
    foreach ($all_author_ids as $author_id => $data) {
        if (!isset($data['reward_keys'])) $all_author_ids[$author_id]['reward_keys'] = 0;
    }

    // 4. Build writerwise_data
    $writerwise_data = [];
    foreach ($all_author_ids as $author_id => $data) {
        $user_info = get_userdata($author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $total_keys_Purchase = $data['episodes'] * $keysToUnlockEpisode;
        $reward_keys = $data['reward_keys'];
        $payment = ($total_keys_Purchase + $reward_keys) * $key_value;
        $revenue_share = ($total_keys_Purchase * $writer_share_per_key) + ($reward_keys * $writer_share_per_key);
        
        // Fetch payment info for this writer and period
        $payment_info = $wpdb->get_row($wpdb->prepare(
            "SELECT payment_status, transaction_id, unpaid_reason 
             FROM {$wpdb->prefix}writer_payment_history 
             WHERE user_id = %d AND revenue_type = %s AND from_date = %s AND to_date = %s",
            $author_id, 'key', $from, $to
        ));

        $status = $payment_info ? $payment_info->payment_status : 'Unpaid';
        $transaction_id = $payment_info ? $payment_info->transaction_id : '';
        $reason = $payment_info ? $payment_info->unpaid_reason : '';

        $writerwise_data[] = [
            'id' => $author_id,
            'name' => $name,    
            'episodes' => $data['episodes'],
            'keys' => $total_keys_Purchase,
            'reward_keys' => $reward_keys,
            'payment' => $payment,
            'revenue_share' => $revenue_share,
            'status' => $status,
            'transaction_id' => $transaction_id,
            'reason' => $reason,
        ];
    }

    // Storywise revenue
    // 1. Get unlocks per story
    $storywise = $wpdb->get_results($wpdb->prepare(
        "SELECT series_id, author_id, COUNT(episode_id) as episodes
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY series_id, author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // 2. Get rewards per story
    $reward_rows_story = $wpdb->get_results($wpdb->prepare(
        "SELECT parent_post_id as series_id, post_id, author_id, SUM(`key`) as reward_keys
         FROM $reward_table
         WHERE rewarded_at BETWEEN %s AND %s
         GROUP BY parent_post_id, author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // 3. Merge story-author pairs
    $all_story_authors = [];
    foreach ($storywise as $row) $all_story_authors[$row->series_id . '_' . $row->author_id] = ['episodes' => $row->episodes];
    foreach ($reward_rows_story as $row) {
        $key = $row->series_id . '_' . $row->author_id;
        if (!isset($all_story_authors[$key])) $all_story_authors[$key] = ['episodes' => 0];
        $all_story_authors[$key]['reward_keys'] = intval($row->reward_keys);
    }
    foreach ($all_story_authors as $key => $data) {
        if (!isset($data['reward_keys'])) $all_story_authors[$key]['reward_keys'] = 0;
    }

    // 4. Build storywise_data
    $storywise_data = [];
    foreach ($all_story_authors as $key => $data) {
        list($series_id, $author_id) = explode('_', $key);
        $post = get_post($series_id);
        $user_info = get_userdata($author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $total_keys_Purchase = $data['episodes'] * $keysToUnlockEpisode;
        $reward_keys = $data['reward_keys'];
        $payment = ($total_keys_Purchase + $reward_keys) * $key_value;
        $revenue_share = ($total_keys_Purchase * $writer_share_per_key) + ($reward_keys * $writer_share_per_key);
        $storywise_data[] = [
            'id' => $author_id,
            'title' => $post ? $post->post_title . ' (VLN' . $post->ID . ')' : 'Unknown',
            'writer' => $name,
            'keys' => $total_keys_Purchase,
            'reward_keys' => $reward_keys,
            'payment' => $payment,
            'episodes' => $data['episodes'],
            'revenue_share' => $revenue_share,
        ];
    }

    // UI Output
    ?>
    <div class="wrap">
        <h1>Revenue Overview</h1>
        <form method="get" style="margin-bottom:20px;">
            <input type="hidden" name="page" value="key-revenue" />
            <label>Custom Period: </label>
            <input type="date" name="from" value="<?php echo esc_attr($from); ?>" required>
            <input type="date" name="to" value="<?php echo esc_attr($to); ?>" required>
            <button class="button button-primary">Apply</button>
        </form>
        <div style="display:flex;gap:18px;margin-bottom:20px;">
            <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Single Key Amount</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;">₹<?php echo number_format($key_value, 2); ?></div>
            </div>
            <div style="flex:1;background:#fff0f0;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#b71c1c;">Total Episodes Unlocked</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;"><?php echo number_format($total_unlocked_episodes); ?></div>
            </div>
            <div style="flex:1;background:#fff0f0;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#b71c1c;">Total Keys Purchased</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;"><?php echo number_format($total_keys) + number_format($reward_keys_total); ?></div>
            </div>
            <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Total Payment</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;">₹<?php echo number_format($total_payment, 2); ?></div>
            </div>
            <div style="flex:1;background:#f0f7ff;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#1976d2;">Writers Revenue Share</div>
                <div style="font-size:1.3rem;font-weight:bold;margin-top: 15px;">₹<?php echo number_format($writers_revenue, 2); ?></div>
            </div>
            <div style="flex:1;background:#eaf7f2;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#005d67;">Site Net Revenue</div>
                <div style="font-size:1.3rem;font-weight:bold;margin-top: 15px;">₹<?php echo number_format($platform_revenue, 2); ?></div>
            </div>
        </div>

        <h2 style="margin-top:30px;">Writerwise Revenue Share</h2>
        <div style="display:flex;align-items:center;margin-bottom:10px;">
            <input type="text" id="writerwise-search" placeholder="Search Writerwise..." style="flex:1;max-width:250px;">
            <button id="writerwise-search-btn" class="button" style="margin-left:8px;">
                <i class="fas fa-search"></i> Search
            </button>
            <button id="writerwise-csv" class="button" style="margin-left:16px;">Download CSV</button>
        </div>
        <table id="writerwise-table" class="widefat striped" style="margin-bottom:30px;">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Writer Name</th>
                    <th>Number of Episodes Unlocked</th>
                    <th>Total Keys</th>
                    <th>Reward Keys</th>
                    <th>Total Payment</th>
                    <th>Revenue Share</th>
                    <th>Payment Status</th>
                    <th>Transaction ID</th>
                    <th>Unpaid Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($writerwise_data as $wr): ?>
                <tr>
                    <td><?php echo esc_html($wr['id']); ?></td>
                    <td><?php echo esc_html($wr['name']); ?></td>
                    <td><?php echo esc_html($wr['episodes']); ?></td>
                    <td><?php echo esc_html($wr['keys']); ?></td>
                    <td><?php echo esc_html($wr['reward_keys']); ?></td>
                    <td>₹<?php echo number_format($wr['payment'], 2); ?></td>
                    <td>₹<?php echo number_format($wr['revenue_share'], 2); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('save_key_writer_payment'); ?>
                            <input type="hidden" name="writer_id" value="<?php echo esc_attr($wr['id']); ?>">
                            <input type="hidden" name="revenue_payment" value="<?php echo esc_attr($wr['revenue_share']); ?>">
                            <select name="payment_status">
                                <option value="Paid" <?php selected($wr['status'], 'Paid'); ?>>Paid</option>
                                <option value="Unpaid" <?php selected($wr['status'], 'Unpaid'); ?>>Unpaid</option>
                                <option value="Processing" <?php selected($wr['status'], 'Processing'); ?>>Processing</option>
                            </select>
                    </td>
                    <td>
                            <input type="text" name="transaction_id" value="<?php echo esc_attr($wr['transaction_id']); ?>" placeholder="Transaction ID">
                    </td>
                    <td>
                            <input type="text" name="unpaid_reason" value="<?php echo esc_attr($wr['reason']); ?>" placeholder="Reason">
                    </td>
                    <td>
                            <button type="submit" name="key_writer_payment_update" class="button">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Storywise Revenue Share</h2>
        <div style="display:flex;align-items:center;margin-bottom:10px;">
            <input type="text" id="storywise-search" placeholder="Search Storywise..." style="flex:1;max-width:250px;">
            <button id="storywise-search-btn" class="button" style="margin-left:8px;">
                <i class="fas fa-search"></i> Search
            </button>
            <button id="storywise-csv" class="button" style="margin-left:16px;">Download CSV</button>
        </div>
        <table id="storywise-table" class="widefat striped">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Story Name</th>
                    <th>Writer Name</th>
                    <th>Number of Episodes Unlocked</th>
                    <th>Total Keys</th>
                    <th>Reward Keys</th>
                    <th>Total Payment</th>
                    <th>Revenue Share</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($storywise_data as $row): ?>
                <tr>
                    <td><?php echo esc_html($row['id']); ?></td>
                    <td><?php echo esc_html($row['title']); ?></td>
                    <td><?php echo esc_html($row['writer']); ?></td>
                    <td><?php echo esc_html($row['episodes']); ?></td>
                    <td><?php echo esc_html($row['keys']); ?></td>
                    <td><?php echo esc_html($row['reward_keys']); ?></td>
                    <td>₹<?php echo number_format($row['payment'], 2); ?></td>
                    <td>₹<?php echo number_format($row['revenue_share'], 2); ?></td>
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

    // Filter tables on button click
    function filterTableOnClick(inputId, tableId, btnId) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        const btn = document.getElementById(btnId);
        btn.addEventListener('click', function() {
            const filter = input.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
    filterTableOnClick('writerwise-search', 'writerwise-table', 'writerwise-search-btn');
    filterTableOnClick('storywise-search', 'storywise-table', 'storywise-search-btn');

    // CSV Export Function (skip Action column, use selected value for dropdown)
    function downloadTableAsCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        let csv = [];
        // Get headers, skip last column if Action
        const headers = Array.from(table.querySelectorAll('thead th'));
        const skipLast = headers[headers.length-1].innerText.toLowerCase().includes('action');
        const headerCells = skipLast ? headers.slice(0, -1) : headers;
        csv.push(headerCells.map(th => `"${th.innerText.trim()}"`).join(','));
        // Get visible rows
        table.querySelectorAll('tbody tr').forEach(row => {
            if (row.style.display === 'none') return;
            const cells = Array.from(row.children);
            const dataCells = skipLast ? cells.slice(0, -1) : cells;
            const cols = dataCells.map(td => {
                if (td.querySelector('select')) {
                    return `"${td.querySelector('select').value}"`;
                }
                return `"${td.innerText.trim()}"`;
            });
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
    document.getElementById('writerwise-csv').onclick = function() {
        downloadTableAsCSV('writerwise-table', 'writerwise-revenue.csv');
    };
    document.getElementById('storywise-csv').onclick = function() {
        downloadTableAsCSV('storywise-table', 'storywise-revenue.csv');
    };
    </script>
    <?php
}

/**
 * Register activation/deactivation hooks for the cron
 */
register_activation_hook(__FILE__, 'key_revenue_activate_cron');
register_deactivation_hook(__FILE__, 'key_revenue_deactivate_cron');

function key_revenue_activate_cron() {
    if (!wp_next_scheduled('key_revenue_monthly_auto_save')) {
        // Schedule for 1st of every month at 00:05
        $timestamp = strtotime('first day of next month 00:05:00');
        wp_schedule_event($timestamp, 'monthly', 'key_revenue_monthly_auto_save');
    }
}

function key_revenue_deactivate_cron() {
    wp_clear_scheduled_hook('key_revenue_monthly_auto_save');
}

/**
 * Register monthly cron interval
 */
add_filter('cron_schedules', function ($schedules) {
    if (!isset($schedules['monthly'])) {
        $schedules['monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => __('Once Monthly'),
        ];
    }
    return $schedules;
});

/**
 * Auto-save previous month's writer payment data
 */
add_action('key_revenue_monthly_auto_save', 'key_revenue_auto_save_previous_month');

function key_revenue_auto_save_previous_month() {
    global $wpdb;

    // Previous month date range
    $from = date('Y-m-01', strtotime('first day of last month'));
    $to   = date('Y-m-t',  strtotime('last day of last month'));

    $key_value            = floatval(get_option('common_single_key_amount', 0.5));
    $keysToUnlockEpisode  = floatval(get_option('common_coin_unlock', 0));
    $writerPer            = floatval(get_option('writer_revenue_percentage', 30));
    $writer_share_per_key = $key_value * ($writerPer / 100);

    $reward_table = $wpdb->prefix . 'writer_key_rewards';
    $history_table = $wpdb->prefix . 'writer_payment_history';

    // Get unlocks per writer
    $writerwise = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, COUNT(DISTINCT episode_id) as episodes
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type = 'key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // Get reward keys per writer
    $reward_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, SUM(`key`) as reward_keys
         FROM $reward_table
         WHERE rewarded_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    // Merge
    $all_author_ids = [];
    foreach ($writerwise as $row) {
        $all_author_ids[$row->author_id] = ['episodes' => (int) $row->episodes, 'reward_keys' => 0];
    }
    foreach ($reward_rows as $row) {
        if (!isset($all_author_ids[$row->author_id])) {
            $all_author_ids[$row->author_id] = ['episodes' => 0, 'reward_keys' => 0];
        }
        $all_author_ids[$row->author_id]['reward_keys'] = (int) $row->reward_keys;
    }

    foreach ($all_author_ids as $author_id => $data) {
        $total_keys_purchase = $data['episodes'] * $keysToUnlockEpisode;
        $reward_keys         = $data['reward_keys'];
        $revenue_share       = ($total_keys_purchase * $writer_share_per_key) + ($reward_keys * $writer_share_per_key);

        if ($revenue_share > 0) {
            // Check if already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $history_table
                WHERE user_id = %d AND revenue_type = %s AND from_date = %s AND to_date = %s",
                $author_id, 'key', $from, $to
            ));

            $record = [
                'user_id'         => $author_id,
                'from_date'       => $from,
                'to_date'         => $to,
                'revenue_type'    => 'key',
                'payment_status'  => 'Processing',
                'transaction_id'  => '',
                'unpaid_reason'   => '',
                'revenue_payment' => $revenue_share,
                'updated_at'      => current_time('mysql'),
            ];

            if ($exists) {
                // Only update revenue_payment, do not overwrite payment_status
                $wpdb->update(
                    $history_table,
                    ['revenue_payment' => $revenue_share, 'updated_at' => current_time('mysql')],
                    ['id' => $exists]
                );
            } else {
                $wpdb->insert($history_table, $record);
            }
        }
    }
}