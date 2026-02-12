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
                    <th scope="row">
                        Episode Locked From
                    </th>
                    <td>
                        <input type="text"
                               name="common_episode_lock"
                               value="<?php echo esc_attr(get_option('common_episode_lock')); ?>"
                               class="regular-text"
                               placeholder="Enter episode lock from">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        Key to Unlock
                    </th>
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
        // Check if all selected series have the same lock settings
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
                // If any value differs, show blank/defaults
                $no_lock = '';
                $default_lock_after = '';
                $locks = [];
                break;
            }
        }
    }

    echo '<form method="post" action="'.admin_url('admin-post.php').'">';
    wp_nonce_field('save_series_locks');
    // Pass all selected series IDs
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

    echo '<h3>Custom Lock Ranges</h3>';
    echo '<table class="widefat fixed" id="lock-rules-table">';
    echo '<thead><tr><th>Lock Type</th><th>From Episode</th><th>To Episode</th><th>Action</th></tr></thead>';
    echo '<tbody>';
    if (!empty($locks)) {
        foreach ($locks as $i => $lock) {
            echo '<tr>
                <td>
                    <select name="lock_type[]">
                        <option value="ads" '.selected($lock['type'],'ads',false).'>Ads Lock</option>
                        <option value="coin" '.selected($lock['type'],'coin',false).'>Key Lock</option>
                    </select>
                </td>
                <td><input type="number" name="lock_from[]" value="'.esc_attr($lock['from']).'" min="1"></td>
                <td><input type="number" name="lock_to[]" value="'.esc_attr($lock['to']).'" min="1"></td>
                <td><button type="button" class="button remove-lock">Remove</button></td>
            </tr>';
        }
    }
    echo '</tbody></table>';
    echo '<p><button type="button" class="button" id="add-lock">+ Add New Lock Range</button></p>';
    echo '<p><input type="submit" class="button-primary" value="Save Locks"></p>';
    echo '</form>';

    // JS for adding/removing rows (unchanged)
    echo <<<HTML
<script>
document.getElementById('add-lock').addEventListener('click', function() {
    var row = `<tr>
        <td>
            <select name="lock_type[]">
                <option value="ads">Ads Lock</option>
                <option value="coin">Key Lock</option>
            </select>
        </td>
        <td><input type="number" name="lock_from[]" min="1"></td>
        <td><input type="number" name="lock_to[]" min="1"></td>
        <td><button type="button" class="button remove-lock">Remove</button></td>
    </tr>`;
    document.querySelector('#lock-rules-table tbody').insertAdjacentHTML('beforeend', row);
});
document.addEventListener('click', function(e){
    if(e.target.classList.contains('remove-lock')){
        e.target.closest('tr').remove();
    }
});
</script>
HTML;
}

// Save handler: update all selected series
add_action('admin_post_save_series_locks', function() {
    if (!current_user_can('manage_options')) return;
    check_admin_referer('save_series_locks');

    $post_ids = isset($_POST['post_ids']) ? array_map('intval', (array)$_POST['post_ids']) : [];
    $no_lock = isset($_POST['no_lock']) ? 1 : 0;
    $enable_default = isset($_POST['enable_default']);
    $default_lock_after = $enable_default ? intval($_POST['default_lock_after']) : '';

    $locks = [];
    if (!empty($_POST['lock_type'])) {
        foreach ($_POST['lock_type'] as $i => $type) {
            $locks[] = [
                'type' => sanitize_text_field($type),
                'from' => intval($_POST['lock_from'][$i]),
                'to'   => intval($_POST['lock_to'][$i]),
            ];
        }
    }

    foreach ($post_ids as $post_id) {
        update_post_meta($post_id, '_no_lock', $no_lock);
        update_post_meta($post_id, '_default_lock_after', $default_lock_after);
        update_post_meta($post_id, '_episode_locks', $locks);
    }

    // Redirect to the first selected series
    $redirect_id = !empty($post_ids) ? $post_ids[0] : 0;
    // Build query string for all selected series
    $query = http_build_query([
        'page' => 'series-locks',
        'saved' => 1,
    ] + array('post_ids' => $post_ids));
    wp_redirect(admin_url('admin.php?' . $query));
    exit;
});
