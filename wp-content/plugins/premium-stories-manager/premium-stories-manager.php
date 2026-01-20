<?php
/**
 * Plugin Name: Premium Stories Manager
 * Description: Manage premium stories with optional episode ranges and pricing.
 * Version: 1.1
 * Author: Your Name
 */

if (!defined('ABSPATH')) exit;

/**
 * Create DB table
 */
register_activation_hook(__FILE__, 'psm_create_table');
function psm_create_table() {
    global $wpdb;

    $table = $wpdb->prefix . 'premium_story_rules';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        post_id BIGINT NOT NULL,
        episode_from INT NULL,
        episode_to INT NULL,
        price DECIMAL(10,2) NOT NULL,
        offer_price DECIMAL(10,2) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

/**
 * Admin Menu
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Premium Stories',
        'Premium Stories',
        'manage_options',
        'premium-stories',
        'psm_admin_page',
        'dashicons-lock',
        25
    );
});

/**
 * Admin Page
 */
function psm_admin_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'premium_story_rules';

    $edit_rule = null;
    if (isset($_GET['edit'])) {
        $edit_rule = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", intval($_GET['edit']))
        );
    }

    if (isset($_POST['psm_save'])) {

        $episode_from = ($_POST['episode_from'] !== '') ? intval($_POST['episode_from']) : null;
        $episode_to   = ($_POST['episode_to'] !== '') ? intval($_POST['episode_to']) : null;

        if (!empty($_POST['rule_id'])) {

            $wpdb->update(
                $table,
                [
                    'episode_from' => $episode_from,
                    'episode_to'   => $episode_to,
                    'price'        => floatval($_POST['price']),
                    'offer_price'  => floatval($_POST['offer_price']),
                ],
                ['id' => intval($_POST['rule_id'])],
                ['%d','%d','%f','%f'],
                ['%d']
            );

            echo '<div class="updated notice"><p>Premium rule updated successfully.</p></div>';

            unset($edit_rule);
            unset($_GET['edit']);
        }
        else {

            if (empty($_POST['story_ids'])) {
                echo '<div class="error notice"><p>Please select at least one story.</p></div>';
                return;
            }

            foreach ($_POST['story_ids'] as $story_id) {
                $wpdb->insert(
                    $table,
                    [
                        'post_id'      => intval($story_id),
                        'episode_from' => $episode_from,
                        'episode_to'   => $episode_to,
                        'price'        => floatval($_POST['price']),
                        'offer_price'  => floatval($_POST['offer_price']),
                    ],
                    ['%d','%d','%d','%f','%f']
                );
            }

            echo '<div class="updated notice"><p>Premium rule saved successfully.</p></div>';
        }
    }

    $selected_series = isset($_GET['series']) ? sanitize_text_field($_GET['series']) : '';

    $series_list = $wpdb->get_col("
        SELECT DISTINCT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = 'division'
        AND meta_value != ''
    ");

    $where = '';
    if ($selected_series) {
        $where = $wpdb->prepare("
            AND post_id IN (
                SELECT post_id FROM {$wpdb->postmeta}
                WHERE meta_key = 'division'
                AND meta_value = %s
            )
        ", $selected_series);
    }

    $rules = $wpdb->get_results("
        SELECT * FROM $table
        WHERE 1=1 $where
        ORDER BY id DESC
    ");

    $stories = get_posts([
        'post_type'   => 'post',
        'numberposts' => -1,
        'meta_query'  => [[
            'key'     => 'division',
            'compare' => 'EXISTS'
        ]]
    ]);
    ?>

    <div class="wrap">
        <h1>Premium Stories</h1>

        <form method="post">
            <?php if ($edit_rule): ?>
                <input type="hidden" name="rule_id" value="<?= esc_attr($edit_rule->id); ?>">
            <?php endif; ?>

            <table class="form-table">

                <tr>
                    <th>Select Stories</th>
                    <td>
                        <div style="max-height:200px;overflow-y:auto;border:1px solid #ccd0d4;padding:10px;background:#fff;">
                            <?php foreach ($stories as $story): ?>
                                <label style="display:block;margin-bottom:6px;">
                                    <input type="checkbox"
                                           name="story_ids[]"
                                           value="<?= esc_attr($story->ID); ?>"
                                           <?= ($edit_rule && $edit_rule->post_id == $story->ID) ? 'checked disabled' : ''; ?>>
                                    <?= esc_html($story->post_title); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>Episode Range (Optional)</th>
                    <td>
                        From <input type="number" name="episode_from" style="width:80px;"
                            value="<?= $edit_rule ? esc_attr($edit_rule->episode_from) : ''; ?>">
                        To <input type="number" name="episode_to" style="width:80px;"
                            value="<?= $edit_rule ? esc_attr($edit_rule->episode_to) : ''; ?>">
                        <p class="description">Leave empty to make entire story premium</p>
                    </td>
                </tr>

                <tr>
                    <th>Price (₹)</th>
                    <td>
                        <input type="number" name="price" step="0.01" required
                               value="<?= $edit_rule ? esc_attr($edit_rule->price) : ''; ?>">
                    </td>
                </tr>

                <tr>
                    <th>Offer Price (₹)</th>
                    <td>
                        <input type="number" name="offer_price" step="0.01"
                               value="<?= $edit_rule ? esc_attr($edit_rule->offer_price) : ''; ?>">
                    </td>
                </tr>

            </table>

            <p>
                <button type="submit" name="psm_save" class="button button-primary">
                    <?= $edit_rule ? 'Update Premium Rule' : 'Save Premium Rule'; ?>
                </button>
            </p>
        </form>

        <hr>

        <form method="get" style="margin-bottom:15px;">
            <input type="hidden" name="page" value="premium-stories">
            <select name="series">
                <option value="">All Series</option>
                <?php foreach ($series_list as $series): ?>
                    <option value="<?= esc_attr($series); ?>"
                        <?= selected($selected_series, $series, false); ?>>
                        <?= esc_html($series); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="button">Filter</button>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Story</th>
                    <th>Episodes</th>
                    <th>Price</th>
                    <th>Offer Price</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rules): foreach ($rules as $rule): ?>
                    <tr>
                        <td><?= esc_html(get_the_title($rule->post_id)); ?></td>
                        <td><?= is_null($rule->episode_from) ? 'All Episodes' : esc_html($rule->episode_from . ' – ' . $rule->episode_to); ?></td>
                        <td>₹<?= esc_html($rule->price); ?></td>
                        <td><?= $rule->offer_price ? '₹' . esc_html($rule->offer_price) : '-'; ?></td>
                        <td><?= esc_html($rule->created_at); ?></td>
                        <td>
                            <a href="<?= admin_url('admin.php?page=premium-stories&edit=' . $rule->id); ?>">
                                Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6">No premium rules found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
<?php } ?>
