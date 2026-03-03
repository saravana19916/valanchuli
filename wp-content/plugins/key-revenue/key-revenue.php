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

// Main dashboard page
function render_key_revenue_dashboard() {
    global $wpdb;

    // Handle save for payment status/unpaid reason
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['writer_payment_update'])) {
        check_admin_referer('save_writer_payment');
        $writer_id = intval($_POST['writer_id']);
        $status = sanitize_text_field($_POST['payment_status']);
        $reason = sanitize_text_field($_POST['unpaid_reason']);
        update_user_meta($writer_id, 'key_writer_payment_status', $status);
        update_user_meta($writer_id, 'key_writer_unpaid_reason', $reason);
        echo '<div class="updated"><p>Payment status updated.</p></div>';
    }

    // Date filter
    $from = isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-01');
    $to = isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-t');

    // Key value from option (default 0.5)
    $key_value = floatval(get_option('common_coin_unlock', 0.5));
    $writerPer = floatval(get_option('writer_revenue_percentage', 30));
    $platformPer = floatval(get_option('platform_revenue_percentage', 70));
    $writer_share_per_key = $key_value * ($writerPer / 100);
    $platform_share_per_key = $key_value * ($platformPer / 100);

    // Total keys purchased (lock_type='key')
    $total_keys = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}user_episode_unlocks WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));
    $total_keys = $total_keys ?: 0;

    // Total payment
    $total_payment = $total_keys * $key_value;

    // Writer and platform revenue
    $writers_revenue = $total_keys * $writer_share_per_key;
    $platform_revenue = $total_keys * $platform_share_per_key;

    // Writerwise revenue
    $writerwise = $wpdb->get_results($wpdb->prepare(
        "SELECT author_id, COUNT(DISTINCT episode_id) as episodes, COUNT(*) as total_keys
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY author_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    $writerwise_data = [];
    foreach ($writerwise as $row) {
        $user_info = get_userdata($row->author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $payment_status = get_user_meta($row->author_id, 'key_writer_payment_status', true) ?: 'Unpaid';
        $unpaid_reason = get_user_meta($row->author_id, 'key_writer_unpaid_reason', true) ?: '';
        $payment = $row->total_keys * $key_value;
        $revenue_share = $row->total_keys * $writer_share_per_key;
        $writerwise_data[] = [
            'id' => $row->author_id,
            'name' => $name,
            'episodes' => $row->episodes,
            'keys' => $row->total_keys,
            'payment' => $payment,
            'revenue_share' => $revenue_share,
            'status' => $payment_status,
            'reason' => $unpaid_reason,
        ];
    }

    // Storywise revenue
    $storywise = $wpdb->get_results($wpdb->prepare(
        "SELECT series_id, author_id, COUNT(DISTINCT episode_id) as episodes, COUNT(*) as total_keys
         FROM {$wpdb->prefix}user_episode_unlocks
         WHERE lock_type='key' AND unlocked_at BETWEEN %s AND %s
         GROUP BY series_id",
        $from . ' 00:00:00', $to . ' 23:59:59'
    ));

    $storywise_data = [];
    foreach ($storywise as $row) {
        $post = get_post($row->series_id);
        $user_info = get_userdata($row->author_id);
        $name = $user_info ? $user_info->display_name : 'Unknown';
        $payment = $row->total_keys * $key_value;
        $revenue_share = $row->total_keys * $writer_share_per_key;
        $storywise_data[] = [
            'id' => $row->author_id,
            'title' => $post ? $post->post_title : 'Unknown',
            'writer' => $name,
            'keys' => $row->total_keys,
            'payment' => $payment,
            'episodes' => $row->episodes,
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
                <div style="font-size:1.2rem;color:#005d67;">Key Value</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;">₹<?php echo number_format($key_value, 2); ?></div>
            </div>
            <div style="flex:1;background:#fff0f0;padding:18px 24px;border-radius:10px;">
                <div style="font-size:1.2rem;color:#b71c1c;">Total Keys Purchase</div>
                <div style="font-size:2rem;font-weight:bold;margin-top: 15px;"><?php echo number_format($total_keys); ?></div>
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
        <input type="text" id="writerwise-search" placeholder="Search Writerwise..." style="margin-bottom:10px;width:250px;">
        <button id="writerwise-csv" class="button" style="margin-left:10px;">Download CSV</button>
        <table id="writerwise-table" class="widefat striped" style="margin-bottom:30px;">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Writer Name</th>
                    <th>Number of Episodes Unlocked</th>
                    <th>Total Keys</th>
                    <th>Total Payment</th>
                    <th>Revenue Share</th>
                    <th>Payment Status</th>
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
                    <td>₹<?php echo number_format($wr['payment'], 2); ?></td>
                    <td>₹<?php echo number_format($wr['revenue_share'], 2); ?></td>
                    <td>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('save_writer_payment'); ?>
                            <input type="hidden" name="writer_id" value="<?php echo esc_attr($wr['id']); ?>">
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

        <h2>Storywise Revenue Share</h2>
        <input type="text" id="storywise-search" placeholder="Search Storywise..." style="margin-bottom:10px;width:250px;">
        <button id="storywise-csv" class="button" style="margin-left:10px;">Download CSV</button>
        <table id="storywise-table" class="widefat striped">
            <thead>
                <tr>
                    <th>Writer ID</th>
                    <th>Story Name</th>
                    <th>Writer Name</th>
                    <th>Unlocked Episodes</th>
                    <th>Total Keys</th>
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
    // Filter tables
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