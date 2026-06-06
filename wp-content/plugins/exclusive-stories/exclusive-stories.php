<?php
/**
 * Plugin Name: Exclusive Stories
 * Description: Mark stories as Exclusive and manage them from admin. Adds Exclusive tag + category for frontend.
 * Version: 1.0.0
 * Author: Saravanakumar
 */

if (!defined('ABSPATH')) exit;

define('ES_EXCLUSIVE_META_KEY', '_exclusive_story');
define('ES_TAX_ADDED_META_KEY', '_es_tax_added');

add_action('admin_menu', function () {
    add_menu_page(
        'Exclusive Stories',
        'Exclusive Stories',
        'manage_options',
        'exclusive-stories',
        'es_admin_page',
        'dashicons-star-filled',
        26
    );
});

function es_admin_page() {
    if (!current_user_can('manage_options')) return;

    global $wpdb;
    $table = $wpdb->prefix . 'exclusive_stories';

    // Existing exclusive post IDs
    $existing_ids = array_map('intval', $wpdb->get_col("SELECT post_id FROM $table"));

    // Handle delete (single row)
    if (isset($_GET['delete'])) {
        $delete_id = (int) $_GET['delete'];
        check_admin_referer('es_delete_' . $delete_id);

        $post_id = (int) $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $table WHERE id=%d", $delete_id));
        $wpdb->delete($table, ['id' => $delete_id], ['%d']);

        echo '<div class="updated notice"><p>Exclusive story deleted.</p></div>';
        echo '<script>location.href="' . esc_url(admin_url('admin.php?page=exclusive-stories')) . '";</script>';
        return;
    }

    // Handle save (bulk update)
    if (isset($_POST['es_save'])) {
        check_admin_referer('es_save_exclusive');

        $selected = isset($_POST['story_ids']) && is_array($_POST['story_ids'])
            ? array_values(array_unique(array_map('intval', $_POST['story_ids'])))
            : [];

        $to_insert = array_values(array_diff($selected, $existing_ids));
        $to_remove = array_values(array_diff($existing_ids, $selected));

        foreach ($to_insert as $post_id) {
            $created_at = current_time('mysql');

            $wpdb->query($wpdb->prepare(
                "INSERT INTO $table (post_id, created_at)
                 VALUES (%d, %s)
                 ON DUPLICATE KEY UPDATE post_id = VALUES(post_id)",
                $post_id,
                $created_at
            ));
        }

        if (!empty($to_remove)) {
            foreach ($to_remove as $post_id) {
                $wpdb->delete($table, ['post_id' => $post_id], ['%d']);
            }
        }

        // Refresh existing ids after save
        $existing_ids = array_map('intval', $wpdb->get_col("SELECT post_id FROM $table"));

        echo '<div class="updated notice"><p>Exclusive stories updated successfully.</p></div>';
    }

    $highlight_post = isset($_GET['highlight']) ? (int) $_GET['highlight'] : 0;

    // Stories list (same filter style as your premium plugin)
    $stories = get_posts([
        'post_type'   => 'post',
        'numberposts' => -1,
        'meta_query'  => [[
            'key'     => 'division',
            'compare' => 'EXISTS'
        ]]
    ]);

    // Pagination for existing exclusive list
    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
    $offset = ($paged - 1) * $per_page;

    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table ORDER BY id DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));
    ?>
    <div class="wrap">
        <h1>Exclusive Stories</h1>

        <form method="post">
            <?php wp_nonce_field('es_save_exclusive'); ?>

            <h2>Select Stories</h2>

            <div style="margin-bottom:10px;">
                <input type="text" id="es-story-search" placeholder="Search stories..." style="width:220px;padding:4px;border-radius:6px;border:1px solid #ccd0d4;">
                <button id="es-story-search-btn" class="button" type="button" style="margin-left:8px;">
                    Search
                </button>
                <label style="margin-left:10px;">
                    <input type="checkbox" id="es-select-all-stories"> Select All
                </label>
            </div>

            <div id="es-story-list-box" style="max-height:260px;overflow-y:auto;border:1px solid #ccd0d4;padding:10px;background:#fff;">
                <?php foreach ($stories as $story): ?>
                    <?php
                        $checked = in_array((int) $story->ID, $existing_ids, true);
                        $hl = ($highlight_post === (int) $story->ID);
                    ?>
                    <label style="display:block;margin-bottom:6px;<?= $hl ? 'background:#fff6d5;padding:6px;border-radius:6px;' : '' ?>">
                        <input type="checkbox"
                               class="es-story-checkbox"
                               name="story_ids[]"
                               value="<?= esc_attr($story->ID); ?>"
                               <?= $checked ? 'checked' : ''; ?>>
                        <?= esc_html($story->post_title . ' (VLN' . $story->ID . ')'); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:12px;">
                <button type="submit" name="es_save" class="button button-primary">
                    Save Exclusive Stories
                </button>
            </p>
        </form>

        <hr>

        <h2>Exclusive Stories List</h2>

        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Story</th>
                    <th>Created</th>
                    <th width="140">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rows): foreach ($rows as $row): ?>
                    <tr>
                        <td><?= esc_html(get_the_title((int) $row->post_id) . ' (VLN' . (int) $row->post_id . ')'); ?></td>
                        <td><?= esc_html($row->created_at); ?></td>
                        <td>
                            <a href="<?= esc_url(admin_url('admin.php?page=exclusive-stories&highlight=' . (int) $row->post_id)); ?>">Edit</a>
                            |
                            <?php
                                $del_url = wp_nonce_url(
                                    admin_url('admin.php?page=exclusive-stories&delete=' . (int) $row->id),
                                    'es_delete_' . (int) $row->id
                                );
                            ?>
                            <a href="<?= esc_url($del_url); ?>" onclick="return confirm('Delete this exclusive story?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3">No exclusive stories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $total_pages = (int) ceil($total / $per_page);
        if ($total_pages > 1) {
            echo '<div style="margin:16px 0; display:flex; justify-content:flex-end; gap:6px;">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $url = add_query_arg(['page' => 'exclusive-stories', 'paged' => $i], admin_url('admin.php'));
                if ($i === $paged) {
                    echo '<span style="padding:6px 10px;background:#005d67;color:#fff;border-radius:6px;">' . $i . '</span>';
                } else {
                    echo '<a style="padding:6px 10px;background:#f5f5f5;color:#005d67;border-radius:6px;text-decoration:none;" href="' . esc_url($url) . '">' . $i . '</a>';
                }
            }
            echo '</div>';
        }
        ?>

    </div>

    <script>
    document.getElementById('es-story-search-btn')?.addEventListener('click', function() {
        var filter = (document.getElementById('es-story-search')?.value || '').toLowerCase();
        document.querySelectorAll('#es-story-list-box label').forEach(function(label) {
            var text = label.textContent.toLowerCase();
            label.style.display = text.includes(filter) ? 'block' : 'none';
        });
    });

    document.getElementById('es-select-all-stories')?.addEventListener('change', function() {
        var checked = this.checked;
        document.querySelectorAll('.es-story-checkbox').forEach(function(cb) {
            cb.checked = checked;
        });
    });
    </script>
    <?php
}

/**
 * Frontend helpers: add CSS class so theme can show badge like premium
 */
add_filter('post_class', function ($classes, $class, $post_id) {
    $is = (int) get_post_meta($post_id, ES_EXCLUSIVE_META_KEY, true);
    if ($is === 1) $classes[] = 'is-exclusive-story';
    return $classes;
}, 10, 3);