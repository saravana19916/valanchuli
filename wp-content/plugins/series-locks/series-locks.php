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
});

function render_series_locks_admin_page() {
    $series_list = get_posts([
        'post_type'  => 'post',
        'numberposts' => -1,
        'meta_query' => [[
            'key'     => 'division',
            'compare' => 'EXISTS'
        ]],
    ]);

    $selected_series = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

    echo '<div class="wrap">';
    echo '<h1>Manage Series Episode Locks</h1>';

    echo '<form method="get" action="">';
    echo '<input type="hidden" name="page" value="series-locks" />';
    echo '<label>Select Series: </label>';
    echo '<select name="post_id" onchange="this.form.submit()">';
    echo '<option value="">-- Select Series --</option>';
    foreach ($series_list as $s) {
        $sel = $selected_series == $s->ID ? 'selected' : '';
        echo "<option value='{$s->ID}' $sel>{$s->post_title}</option>";
    }
    echo '</select>';
    echo '</form>';

    if ($selected_series) {
        render_series_lock_form($selected_series);
    }

    echo '</div>';
}

function render_series_lock_form($post_id) {
    $no_lock = get_post_meta($post_id, '_no_lock', true);
    $default_lock_after = get_post_meta($post_id, '_default_lock_after', true);
    $locks = get_post_meta($post_id, '_episode_locks', true);
    if (!is_array($locks)) $locks = [];

    // Fetch episodes under this series
    $episodes = get_posts([
        'post_type' => 'episode',
        'meta_key' => 'post_id',
        'meta_value' => $post_id,
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
        'numberposts' => -1
    ]);

    echo '<form method="post" action="'.admin_url('admin-post.php').'">';
    wp_nonce_field('save_series_locks');
    echo '<input type="hidden" name="action" value="save_series_locks">';
    echo '<input type="hidden" name="post_id" value="'.$post_id.'">';

    echo '<h2>'.$post_id.' – '.get_the_title($post_id).'</h2>';

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
                        <option value="coin" '.selected($lock['type'],'coin',false).'>Coin Lock</option>
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

    echo '<h3>Episodes under this series:</h3><ul>';
    foreach ($episodes as $ep) {
        echo '<li>'.esc_html($ep->post_title).'</li>';
    }
    echo '</ul>';

    // JS for adding/removing rows
    echo <<<HTML
<script>
document.getElementById('add-lock').addEventListener('click', function() {
    var row = `<tr>
        <td>
            <select name="lock_type[]">
                <option value="ads">Ads Lock</option>
                <option value="coin">Coin Lock</option>
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

add_action('admin_post_save_series_locks', function() {
    if (!current_user_can('manage_options')) return;
    check_admin_referer('save_series_locks');

    $post_id = intval($_POST['post_id']);
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

    update_post_meta($post_id, '_no_lock', $no_lock);
    update_post_meta($post_id, '_default_lock_after', $default_lock_after);
    update_post_meta($post_id, '_episode_locks', $locks);

    wp_redirect(admin_url('admin.php?page=series-locks&post_id='.$post_id.'&saved=1'));
    exit;
});
