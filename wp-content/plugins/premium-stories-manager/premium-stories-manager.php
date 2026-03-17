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
        coin INT NOT NULL,
        offer_coin INT NOT NULL,
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

        if (!empty($_POST['rule_id'])) {

            $wpdb->update(
                $table,
                [
                    'episode_from' => $episode_from,
                    'coin'         => intval($_POST['coin']),
                    'offer_coin'   => ($_POST['offer_coin'] !== '') ? intval($_POST['offer_coin']) : null,
                ],
                ['id' => intval($_POST['rule_id'])],
                ['%d','%d','%d']
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
                        'coin'         => intval($_POST['coin']),
                        'offer_coin'   => ($_POST['offer_coin'] !== '') ? intval($_POST['offer_coin']) : null,
                    ],
                    ['%d','%d','%d','%d']
                );
            }

            echo '<div class="updated notice"><p>Premium rule saved successfully.</p></div>';
        }
    }

    // Handle delete action
    if (isset($_GET['delete'])) {
        $delete_id = intval($_GET['delete']);
        $wpdb->delete($table, ['id' => $delete_id]);
        echo '<div class="updated notice"><p>Premium rule deleted successfully.</p></div>';
        // Optionally, redirect to avoid resubmission on refresh
        echo '<script>location.href="' . admin_url('admin.php?page=premium-stories') . '";</script>';
        return;
    }

    $selected_series = isset($_GET['series']) ? sanitize_text_field($_GET['series']) : '';

    $series_list = $wpdb->get_results("
        SELECT DISTINCT
            p.ID,
            p.post_title
        FROM {$table} t
        INNER JOIN {$wpdb->posts} p 
            ON p.ID = t.post_id
        WHERE p.post_status = 'publish'
    ");

    $where = '';
    if ($selected_series) {
        $where = $wpdb->prepare(
            " AND post_id = %d",
            $selected_series
        );
    }

    // Set up pagination
    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // Get total count for pagination
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE 1=1 $where");

    $rules = $wpdb->get_results("
        SELECT * FROM $table
        WHERE 1=1 $where
        ORDER BY id DESC
        LIMIT $per_page OFFSET $offset
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

        <?php
        // Save unlock duration setting
        if (isset($_POST['psm_unlock_duration_save'])) {
            update_option('psm_unlock_duration_years', intval($_POST['unlock_duration_years']));
            echo '<div class="updated notice"><p>Unlock duration saved.</p></div>';
        }
        $unlock_duration_years = get_option('psm_unlock_duration_years', 1);
        ?>

        <div class="wrap" style="margin-bottom: 24px;">
            <h2>Premium Story Unlock Settings</h2>
            <form method="post" style="margin-bottom: 0; margin-top: 10px;">
                <label for="unlock_duration_years"><strong>Unlock Duration (years):</strong></label>
                <input type="number" name="unlock_duration_years" id="unlock_duration_years"
                    value="<?php echo esc_attr($unlock_duration_years); ?>" style="width: 60px;">
                <button type="submit" name="psm_unlock_duration_save" class="button button-secondary">Save</button>
            </form>
        </div>

        <hr/>

        <form method="post">
            <?php if ($edit_rule): ?>
                <input type="hidden" name="rule_id" value="<?= esc_attr($edit_rule->id); ?>">
            <?php endif; ?>

            <table class="form-table">

                <tr>
                    <th>Select Stories</th>
                    <td>
                        <div style="margin-bottom:10px;">
                            <input type="text" id="story-search" placeholder="Search stories..." style="width:220px;padding:4px;border-radius:6px;border:1px solid #ccd0d4;">
                            <button id="story-search-btn" class="button" type="button" style="margin-left:8px; margin-top: 5px;">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <label style="margin-left:10px;">
                                <input type="checkbox" id="select-all-stories"> Select All
                            </label>
                        </div>
                        <div id="story-list-box" style="max-height:200px;overflow-y:auto;border:1px solid #ccd0d4;padding:10px;background:#fff;">
                            <?php foreach ($stories as $story): ?>
                                <label style="display:block;margin-bottom:6px;">
                                    <input type="checkbox"
                                           class="story-checkbox"
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
                    <th>Episode Range</th>
                    <td>
                        From <input type="number" name="episode_from" style="width:80px;"
                            value="<?= $edit_rule ? esc_attr($edit_rule->episode_from) : ''; ?>">
                    </td>
                </tr>

                <tr>
                    <th>Key</th>
                    <td>
                        <input type="number" name="coin" required
                               value="<?= $edit_rule ? esc_attr($edit_rule->coin) : ''; ?>">
                    </td>
                </tr>

                <tr>
                    <th>Offer Key</th>
                    <td>
                        <input type="number" name="offer_coin"
                               value="<?= $edit_rule ? esc_attr($edit_rule->offer_coin) : ''; ?>">
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

        <form method="get" style="margin-bottom:15px; display: flex; align-items: center;">
            <input type="hidden" name="page" value="premium-stories">

            <input type="text" id="premium-search-input" placeholder="Search stories..." style="margin-right:8px; padding:4px; border-radius:6px; border:1px solid #ccd0d4;">
            <button id="premium-search-btn" class="button" type="button" style="margin-right: 15px;">
                <i class="fas fa-search"></i> Search
            </button>

            <select name="series" style="margin-right: 12px;">
                <option value="">All Series</option>
                <?php foreach ($series_list as $series): ?>
                    <option value="<?= esc_attr($series->ID); ?>"
                        <?= selected($selected_series, $series->ID, false); ?>>
                        <?= esc_html($series->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="button" type="submit" style="margin-right:8px;">Filter</button>
        </form>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Story</th>
                    <th>Episodes</th>
                    <th>Key</th>
                    <th>Offer Key</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rules): foreach ($rules as $rule): ?>
                    <tr>
                        <td><?= esc_html(get_the_title($rule->post_id)); ?></td>
                        <td><?= esc_html($rule->episode_from); ?></td>
                        <td><?= esc_html($rule->coin); ?></td>
                        <td><?= esc_html($rule->offer_coin); ?></td>
                        <td><?= esc_html($rule->created_at); ?></td>
                        <td>
                            <a href="<?= admin_url('admin.php?page=premium-stories&edit=' . $rule->id); ?>">
                                Edit
                            </a>
                            |
                            <a href="<?= admin_url('admin.php?page=premium-stories&delete=' . $rule->id); ?>"
                               onclick="return confirm('Are you sure you want to delete this premium rule?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6">No premium rules found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $total_pages = ceil($total / $per_page);
        if ($total_pages > 1) {
            echo '<div class="custom-premium-pagination">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $url = add_query_arg([
                    'page' => 'premium-stories',
                    'paged' => $i,
                    'series' => $selected_series,
                    'premium_search' => isset($_GET['premium_search']) ? $_GET['premium_search'] : ''
                ], admin_url('admin.php'));
                if ($i == $paged) {
                    echo '<span class="custom-page-num active">' . $i . '</span>';
                } else {
                    echo '<a class="custom-page-num" href="' . esc_url($url) . '">' . $i . '</a>';
                }
            }
            echo '</div>';
        }
        ?>

    </div>

    <style>
    .custom-premium-pagination {
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
    </style>

    <script>

    document.getElementById('story-search-btn').addEventListener('click', function() {
        var filter = document.getElementById('story-search').value.toLowerCase();
        document.querySelectorAll('#story-list-box label').forEach(function(label) {
            var text = label.textContent.toLowerCase();
            label.style.display = text.includes(filter) ? 'block' : 'none';
        });
    });

    document.getElementById('select-all-stories').addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.story-checkbox').forEach(function(cb) {
            if (!cb.disabled) cb.checked = checked;
        });
    });

    // After page load, set Select All if all checkboxes are checked
    document.addEventListener('DOMContentLoaded', function() {
        var allCheckboxes = document.querySelectorAll('.story-checkbox:not([disabled])');
        var selectAll = document.getElementById('select-all-stories');
        if (allCheckboxes.length > 0) {
            var allChecked = Array.from(allCheckboxes).every(function(cb) { return cb.checked; });
            selectAll.checked = allChecked;
        }
    });

    document.getElementById('premium-search-btn').addEventListener('click', function() {
        var searchValue = document.getElementById('premium-search-input').value.toLowerCase();
        document.querySelectorAll('.widefat tbody tr').forEach(function(row) {
            var story = row.querySelector('td:first-child');
            if (story && story.textContent.toLowerCase().includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = searchValue ? 'none' : '';
            }
        });
    });
    </script>
<?php } ?>
