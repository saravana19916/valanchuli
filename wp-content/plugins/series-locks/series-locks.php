<?php
/**
 * Plugin Name: Series Episode Locks
 * Description: Manage episode lock rules per series.
 * Author: Saravanakumar
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

// Register Admin Page
add_action('admin_menu', function() {
    add_menu_page(
        'Series Lock Control',
        'Series Locks',
        'manage_options',
        'series-locks',
        'render_series_locks_admin_page',
        'dashicons-lock',
        30
    );

    add_menu_page(
        'Common Episode Lock',
        'Common Episode Lock',
        'manage_options',
        'common-episode-lock',
        'render_common_episode_lock_page',
        'dashicons-lock',
        30
    );
});

add_action('admin_init', function () {
    register_setting(
        'common_episode_lock_group',
        'common_episode_lock'
    );
    register_setting(
        'common_episode_lock_group',
        'common_coin_unlock'
    );
});

add_action('admin_enqueue_scripts', 'series_locks_enqueue_admin_assets');

function series_locks_enqueue_admin_assets($hook)
{
    // LOAD ONLY on your plugin page
    if ($hook !== 'toplevel_page_series-locks') {
        return;
    }

    wp_enqueue_script(
        'series-locks-js',
        plugin_dir_url(__FILE__) . 'assets/js/series-locks.js',
        [],
        '1.0',
        true
    );

    wp_localize_script('series-locks-js', 'seriesLocks', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('series_lock_nonce')
    ]);
}

function render_common_episode_lock_page() {
    ?>
    <div class="wrap">
        <h1>Common Episode Lock</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('common_episode_lock_group');
                do_settings_sections('common_episode_lock_group');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Episode Locked From</th>
                    <td>
                        <input type="text"
                               name="common_episode_lock"
                               value="<?php echo esc_attr(get_option('common_episode_lock')); ?>"
                               class="regular-text"
                               placeholder="Enter episode lock from">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Key to Unlock</th>
                    <td>
                        <input type="text"
                               name="common_coin_unlock"
                               value="<?php echo esc_attr(get_option('common_coin_unlock')); ?>"
                               class="regular-text"
                               placeholder="Enter key to unlock">
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function render_series_locks_admin_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'series';
    echo '<div class="wrap">';
    echo '<h1>Episode Lock Management</h1>';
    echo '<h2 class="nav-tab-wrapper">';
    echo '<a href="?page=series-locks&tab=series" class="nav-tab '.($active_tab==='series'?'nav-tab-active':'').'">Series Episode Locks</a>';
    echo '<a href="?page=series-locks&tab=stories" class="nav-tab '.($active_tab==='stories'?'nav-tab-active':'').'">Story Lock Details</a>';
    echo '</h2>';
    if ($active_tab === 'series') {
        render_series_lock_tab();
    } else {
        render_story_lock_details_tab();
    }
    echo '</div>';
}

function render_series_lock_tab() {
    $series_list = get_posts([
        'post_type'  => 'post',
        'numberposts' => -1,
        'meta_query' => [[
            'key'     => 'division',
            'compare' => 'EXISTS'
        ]],
    ]);
    $selected_series = isset($_GET['post_ids']) ? array_map('intval', (array)$_GET['post_ids']) : [];
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="series-locks">';
    echo '<input type="hidden" name="tab" value="series">';
    echo '<label><strong>Select Series:</strong></label>';
    echo '<div style="max-height:250px;overflow:auto;border:1px solid #ccc;padding:10px;width:350px;">';
    foreach ($series_list as $s) {
        $checked = in_array($s->ID, $selected_series) ? 'checked' : '';
        echo "<label style='display:block'><input type='checkbox' name='post_ids[]' value='{$s->ID}' $checked> {$s->post_title}</label>";
    }
    echo '</div><br>';
    echo '<input type="submit" class="button button-primary" value="Edit Selected">';
    echo '</form>';
    if ($selected_series) {
        render_series_lock_form($selected_series);
    }
}

function render_story_lock_details_tab() {
    $search    = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $series_id = isset($_GET['series']) ? intval($_GET['series']) : '';
    $locked_posts = get_posts([
        'post_type'      => 'post',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => '_episode_locks',
                'compare' => 'EXISTS'
            ]
        ],
        'fields' => 'ids'
    ]);
    $locked_series = [];
    foreach ($locked_posts as $post_id) {
        $terms = get_the_terms($post_id, 'series');
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $locked_series[$term->term_id] = $term;
            }
        }
    }
    echo '<form method="get" style="margin:15px 0;display:flex;gap:10px;">';
    echo '<input type="hidden" name="page" value="series-locks">';
    echo '<input type="hidden" name="tab" value="stories">';
    echo '<input type="search" name="s" value="'.esc_attr($search).'" placeholder="Search story..." style="width:250px;">';
    echo '<select name="series">';
    echo '<option value="">All Series</option>';
    foreach ($locked_series as $term) {
        $selected = ($series_id == $term->term_id) ? 'selected' : '';
        echo "<option value='{$term->term_id}' {$selected}>{$term->name}</option>";
    }
    echo '</select>';
    echo '<button class="button">Filter</button>';
    echo '</form>';
    $args = [
        'post_type'      => 'post',
        'posts_per_page' => -1,
        's'              => $search,
        'meta_query'     => [
            [
                'key'     => '_episode_locks',
                'compare' => 'EXISTS'
            ]
        ]
    ];
    if ($series_id) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'series',
                'field'    => 'term_id',
                'terms'    => $series_id
            ]
        ];
    }
    $stories = get_posts($args);
    if (!$stories) {
        echo '<p>No stories with episode locks found.</p>';
        return;
    }
    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Series</th>
                <th>Episode Range</th>
                <th>Default Lock After</th>
                <th>No Lock</th>
            </tr>
          </thead><tbody>';
    foreach ($stories as $story) {
        $locks         = get_post_meta($story->ID, '_episode_locks', true);
        $default_after = get_post_meta($story->ID, '_default_lock_after', true);
        $no_lock       = get_post_meta($story->ID, '_no_lock', true);
        $series_terms = get_the_terms($story->ID, 'series');
        $series_name  = (!empty($series_terms) && !is_wp_error($series_terms))
                        ? $series_terms[0]->name
                        : '-';
        $episode_lock_text = '-';
        if (is_array($locks) && !empty($locks)) {
            $ranges = [];
            foreach ($locks as $lock) {
                $ranges[] = ucfirst($lock['type']) . ': ' . $lock['from'] . '→' . $lock['to'];
            }
            $episode_lock_text = implode('<br>', $ranges);
        }
        echo '<tr>
            <td>'.$series_name.'</td>
            <td>'.$episode_lock_text.'</td>
            <td>'.($default_after ?: '-').'</td>
            <td>'.($no_lock ? 'Yes' : '-').'</td>
        </tr>';
    }
    echo '</tbody></table>';
}

function render_series_lock_form($post_ids) {
    if (count($post_ids) === 1) {
        $post_id = $post_ids[0];
        $no_lock = get_post_meta($post_id, '_no_lock', true);
        $default_lock_after = get_post_meta($post_id, '_default_lock_after', true);
        $locks = get_post_meta($post_id, '_episode_locks', true);
        if (!is_array($locks)) $locks = [];
    } else {
        $first_id = $post_ids[0];
        $no_lock = get_post_meta($first_id, '_no_lock', true);
        $default_lock_after = get_post_meta($first_id, '_default_lock_after', true);
        $locks = get_post_meta($first_id, '_episode_locks', true);
        if (!is_array($locks)) $locks = [];
        foreach ($post_ids as $pid) {
            if (
                get_post_meta($pid, '_no_lock', true) !== $no_lock ||
                get_post_meta($pid, '_default_lock_after', true) !== $default_lock_after ||
                get_post_meta($pid, '_episode_locks', true) !== $locks
            ) {
                $no_lock = '';
                $default_lock_after = '';
                $locks = [];
                break;
            }
        }
    }

    echo '<form method="post" action="'.admin_url('admin-post.php').'">';
    wp_nonce_field('save_series_locks');
    foreach ($post_ids as $pid) {
        echo '<input type="hidden" name="post_ids[]" value="'.$pid.'">';
    }
    echo '<input type="hidden" name="action" value="save_series_locks">';
    echo '<h2>Selected Series: ';
    foreach ($post_ids as $pid) {
        echo get_the_title($pid) . ' (ID: ' . $pid . '), ';
    }
    echo '</h2>';
    echo '<p><label><input type="checkbox" name="no_lock" '.checked($no_lock, true, false).'> No Lock (disable all locks)</label></p>';
    echo '<p><label><input type="checkbox" name="enable_default" '.checked(!empty($default_lock_after), true, false).'> Default Lock after episode</label> ';
    echo '<input type="number" name="default_lock_after" value="'.esc_attr($default_lock_after ?: 10).'" min="1" style="width:80px;"></p>';
    echo '<p><input type="submit" class="button-primary" value="Save Locks"></p>';

    // Add New Lock Button and Modal (outside form)
    echo '</form>';
    echo <<<HTML
<p><button type="button" class="button button-primary" id="open-lock-modal">+ Add New Lock Range</button></p>
<div id="lock-modal" style="display:none;position:fixed;top:10%;left:50%;transform:translateX(-50%);background:#fff;z-index:9999;padding:20px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.2);min-width:350px;">
    <h3 id="lock-modal-title">Create Lock</h3>
    <form id="lockForm" autocomplete="off">
        <input type="hidden" id="edit_index" name="edit_index" value="">
        <table style="width:100%;border-collapse:separate;border-spacing:0 10px;">
            <tr>
                <td style="width:120px;"><label for="lock_type">Lock Type</label></td>
                <td>
                    <select id="lock_type">
                        <option value="ads">Ads Lock</option>
                        <option value="coin">Key Lock</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="lock_from">From Episode</label></td>
                <td>
                    <input type="number" id="lock_from" min="1" required>
                </td>
            </tr>
            <tr>
                <td><label for="lock_to">To Episode</label></td>
                <td>
                    <input type="number" id="lock_to" min="1" required>
                </td>
            </tr>
            <!-- Ads Lock extra fields -->
            <tr id="ads_fields" style="display:none;">
                <td><label>Time</label></td>
                <td>
                    <input type="number" id="ads_time_min" min="0" placeholder="Min" style="width:60px;"> :
                    <input type="number" id="ads_time_sec" min="0" max="59" placeholder="Sec" style="width:60px;">
                </td>
            </tr>
            <tr id="ads_content_row" style="display:none;">
                <td><label for="ads_content">Content</label></td>
                <td>
HTML;

            // Output the editor with PHP
            $content = '';
            $editor_id = 'ads_content';
            $editor_name = 'ads_content';
            wp_editor(
                $content,
                $editor_id,
                [
                    'textarea_name' => $editor_name,
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny'         => false,
                    'quicktags'     => true,
                ]
            );

            echo <<<HTML
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align:right;">
                    <button type="submit" class="button button-primary" id="lock_submit_btn">Add Lock</button>
                    <button type="button" class="button" id="lock_cancel_btn">Cancel</button>
                </td>
            </tr>
        </table>
    </form>
</div>
<div id="lock-modal-backdrop" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9998;"></div>
HTML;

    // Lock Table
    echo '<table class="widefat fixed" id="lock-rules-table">';
    echo '<thead>
<tr>
    <th>Lock Type</th>
    <th>From Episode</th>
    <th>To Episode</th>
    <th>Time</th>
    <th>Content</th>
    <th>Action</th>
</tr>
</thead><tbody id="lockTableBody">';
    if (!empty($locks)) {
        foreach ($locks as $i => $lock) {
            // Prepare time display (only for ads lock)
            $time = '';
            if ($lock['type'] === 'ads') {
                $min = isset($lock['ads_time_min']) ? $lock['ads_time_min'] : '';
                $sec = isset($lock['ads_time_sec']) ? $lock['ads_time_sec'] : '';
                $time = $min && $sec ? esc_html($min) . 'm ' . esc_html($sec) . 's' : '';
            }

            // Prepare content preview (only for ads lock)
            $content_preview = '';
            if ($lock['type'] === 'ads' && !empty($lock['ads_content'])) {
                $content = wp_strip_all_tags($lock['ads_content']);
                $content_preview = mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '');
                $content_preview = esc_html($content_preview);
            }

            echo '<tr>
                <td>'.ucfirst($lock['type']).'</td>
                <td>'.$lock['from'].'</td>
                <td>'.$lock['to'].'</td>
                <td>'.($lock['type'] === 'ads' ? $time : '').'</td>
                <td>'.($lock['type'] === 'ads' ? $content_preview : '').'</td>
                <td>
                    <button type="button" class="button edit-lock" data-index="'.$i.'">Edit</button>
                    <button type="button" class="button remove-lock" data-index="'.$i.'">Remove</button>
                    <input type="hidden" name="lock_type[]" value="'.esc_attr($lock['type']).'">
                    <input type="hidden" name="lock_from[]" value="'.esc_attr($lock['from']).'">
                    <input type="hidden" name="lock_to[]" value="'.esc_attr($lock['to']).'">
                    <input type="hidden" name="ads_time_min[]" value="'.esc_attr($lock['ads_time_min'] ?? '').'">
                    <input type="hidden" name="ads_time_sec[]" value="'.esc_attr($lock['ads_time_sec'] ?? '').'">
                    <input type="hidden" name="ads_content[]" value="'.esc_attr($lock['ads_content'] ?? '').'">
                </td>
            </tr>';
        }
    }
    echo '</tbody></table>';

    // Modal and Table JS
    echo <<<HTML
<script>
let editingIndex = null;
function showLockModal(edit = false) {
    document.getElementById('lock-modal').style.display = '';
    document.getElementById('lock-modal-backdrop').style.display = '';
    document.getElementById('lock-modal-title').textContent = edit ? 'Edit Lock' : 'Create Lock';
}
function hideLockModal() {
    document.getElementById('lock-modal').style.display = 'none';
    document.getElementById('lock-modal-backdrop').style.display = 'none';
    resetForm();
}
function resetForm() {
    document.getElementById('lockForm').reset();
    document.getElementById('edit_index').value = '';
    document.getElementById('lock_submit_btn').textContent = 'Add Lock';
    editingIndex = null;
}
document.getElementById('open-lock-modal').addEventListener('click', function() {
    resetForm();
    showLockModal(false);
});
document.getElementById('lock_cancel_btn').addEventListener('click', function(e) {
    e.preventDefault();
    hideLockModal();
});
document.getElementById('lock-modal-backdrop').addEventListener('click', hideLockModal);

document.getElementById('lockTableBody').addEventListener('click', function(e) {
    if (e.target.classList.contains('edit-lock')) {
        let tr = e.target.closest('tr');
        editingIndex = Array.from(tr.parentNode.children).indexOf(tr);
        document.getElementById('edit_index').value = editingIndex;
        document.getElementById('lock_type').value = tr.querySelector('input[name="lock_type[]"]').value;
        document.getElementById('lock_from').value = tr.querySelector('input[name="lock_from[]"]').value;
        document.getElementById('lock_to').value = tr.querySelector('input[name="lock_to[]"]').value;
        document.getElementById('ads_time_min').value = tr.querySelector('input[name="ads_time_min[]"]')?.value || '';
        document.getElementById('ads_time_sec').value = tr.querySelector('input[name="ads_time_sec[]"]')?.value || '';
        // For ads_content, handle TinyMCE if present
        let adsContent = tr.querySelector('input[name="ads_content[]"]')?.value || '';
        if (typeof tinymce !== 'undefined' && tinymce.get('ads_content')) {
            tinymce.get('ads_content').setContent(adsContent);
        } else {
            document.getElementById('ads_content').value = adsContent;
        }
        document.getElementById('lock_submit_btn').textContent = 'Update Lock';

        // *** ADD THIS LINE: ***
        document.getElementById('lock_type').dispatchEvent(new Event('change'));

        showLockModal(true);
    }
    if (e.target.classList.contains('remove-lock')) {
        e.target.closest('tr').remove();
        resetForm();
    }
});
document.getElementById('lock_type').addEventListener('change', function() {
    const isAds = this.value === 'ads';
    document.getElementById('ads_fields').style.display = isAds ? '' : 'none';
    document.getElementById('ads_content_row').style.display = isAds ? '' : 'none';
});
// Trigger on page load (in case of edit)
document.getElementById('lock_type').dispatchEvent(new Event('change'));
</script>
HTML;
}

if (!defined('DOING_AJAX') || !DOING_AJAX) {
    echo '<script>var ajaxurl="'.admin_url('admin-ajax.php').'";</script>';
}

// Save handler: update all selected series
add_action('admin_post_save_series_locks', function() {
    if (!current_user_can('manage_options')) return;
    check_admin_referer('save_series_locks');
    $post_ids = isset($_POST['post_ids']) ? array_map('intval', (array)$_POST['post_ids']) : [];
    $no_lock = isset($_POST['no_lock']) ? 1 : 0;
    $enable_default = isset($_POST['enable_default']);
    $default_lock_after = $enable_default ? intval($_POST['default_lock_after']) : '';
    // $locks = [];
    // if (!empty($_POST['lock_type'])) {
    //     foreach ($_POST['lock_type'] as $i => $type) {
    //         $lock = [
    //             'type' => sanitize_text_field($type),
    //             'from' => intval($_POST['lock_from'][$i]),
    //             'to'   => intval($_POST['lock_to'][$i]),
    //         ];
    //         if ($type === 'ads') {
    //             $lock['ads_time_min'] = sanitize_text_field($_POST['ads_time_min'][$i] ?? '');
    //             $lock['ads_time_sec'] = sanitize_text_field($_POST['ads_time_sec'][$i] ?? '');
    //             $lock['ads_content']  = urldecode($_POST['ads_content'][$i] ?? '');
    //         }
    //         $locks[] = $lock;
    //     }
    // }
    foreach ($post_ids as $post_id) {
        update_post_meta($post_id, '_no_lock', $no_lock);
        update_post_meta($post_id, '_default_lock_after', $default_lock_after);
        // update_post_meta($post_id, '_episode_locks', $locks);
    }
    $query = http_build_query([
        'page' => 'series-locks',
        'saved' => 1,
    ] + array('post_ids' => $post_ids));
    wp_redirect(admin_url('admin.php?' . $query));
    exit;
});


add_action('wp_ajax_add_series_lock', 'add_series_lock');

function add_series_lock()
{
    check_ajax_referer('series_lock_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $type = sanitize_text_field($_POST['type'] ?? '');
    $from = sanitize_text_field($_POST['from'] ?? '');
    $to   = sanitize_text_field($_POST['to'] ?? '');
    $edit_index = isset($_POST['edit_index']) && $_POST['edit_index'] !== '' ? intval($_POST['edit_index']) : null;

    $post_ids = array_map('intval', $_POST['post_ids'] ?? []);

    $ads_time_min = sanitize_text_field($_POST['ads_time_min'] ?? '');
    $ads_time_sec = sanitize_text_field($_POST['ads_time_sec'] ?? '');
    $ads_content  = $_POST['ads_content'] ?? '';

    $lock_data = [
        'type' => $type,
        'from' => $from,
        'to'   => $to
    ];

    if ($type === 'ads') {
        $lock_data['ads_time_min'] = $ads_time_min;
        $lock_data['ads_time_sec'] = $ads_time_sec;
        $lock_data['ads_content']  = $ads_content;
    }

    foreach ($post_ids as $post_id) {
        $locks = get_post_meta($post_id, '_episode_locks', true);
        if (!is_array($locks)) $locks = [];
        // If a single lock is stored as an associative array, wrap it
        if (isset($locks['type']) && isset($locks['from']) && isset($locks['to'])) {
            $locks = [$locks];
        }
        if ($edit_index !== null && isset($locks[$edit_index])) {
            // Update existing lock
            $locks[$edit_index] = $lock_data;
        } else {
            // Add new lock
            $locks[] = $lock_data;
        }
        update_post_meta($post_id, '_episode_locks', $locks);
    }

    wp_send_json_success();
}

add_action('admin_head', function() {
    ?>
    <style>
    #lock-modal {
        min-width: 50% !important;
        width: 50% !important;
        max-width: 100%;
    }
    .wp-editor-tools:after {
        display: none !important;
        content: none !important;
        clear: none !important;
    }
    #mceu_33 {
        display: none !important;
    }
    </style>
    <?php
});
