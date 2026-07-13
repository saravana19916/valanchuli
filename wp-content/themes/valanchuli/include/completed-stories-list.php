<?php
add_action('admin_menu', function () {
    add_menu_page(
        'Completed Stories',
        'Completed Stories',
        'manage_options',
        'completed-stories',
        'render_completed_stories_page',
        'dashicons-yes-alt',
        87
    );
});

function render_completed_stories_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'completed_stories';
    $posts = $wpdb->posts;
    $users = $wpdb->users;

    // Handle removal
    if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['user_id']) && isset($_GET['story_id'])) {
        if (check_admin_referer('remove_completed_story', 'nonce')) {
            $del_user_id = (int) $_GET['user_id'];
            $del_story_id = (int) $_GET['story_id'];
            $wpdb->delete($table, ['user_id' => $del_user_id, 'story_id' => $del_story_id], ['%d', '%d']);
            $redirect = admin_url('admin.php?page=completed-stories&removed=1');
            if (isset($_GET['paged'])) {
                $redirect .= '&paged=' . intval($_GET['paged']);
            }
            wp_redirect($redirect);
            exit;
        }
    }

    if (isset($_GET['removed']) && $_GET['removed'] == 1) {
        echo '<div class="notice notice-success is-dismissible"><p>Story removed from completed list.</p></div>';
    }

    // Filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $per_page = 20;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($page - 1) * $per_page;

    $where = "WHERE 1=1";
    if ($search) {
        $where .= $wpdb->prepare(
            " AND (
                p.ID = %d
                OR p.post_title LIKE %s
                OR a.display_name LIKE %s
                OR u.display_name LIKE %s
            )",
            intval($search),
            "%$search%",
            "%$search%",
            "%$search%"
        );
    }

    $total = $wpdb->get_var("
        SELECT COUNT(*)
        FROM $table c
        LEFT JOIN $posts p ON c.story_id = p.ID
        LEFT JOIN $users a ON p.post_author = a.ID
        LEFT JOIN $users u ON c.user_id = u.ID
        $where
    ");
    $total_pages = ceil($total / $per_page);

    $results = $wpdb->get_results("
        SELECT c.*, p.post_title, a.display_name AS author_name, a.ID AS author_id,
               u.display_name AS user_name, u.ID AS completed_by_id
        FROM $table c
        LEFT JOIN $posts p ON c.story_id = p.ID
        LEFT JOIN $users a ON p.post_author = a.ID
        LEFT JOIN $users u ON c.user_id = u.ID
        $where
        ORDER BY c.completed_on DESC
        LIMIT $per_page OFFSET $offset
    ");

    $base_url = admin_url('admin.php?page=completed-stories');
    ?>
    <div class="wrap">
        <h1>Completed Stories</h1>
        <form method="get" style="margin-bottom:20px;display:flex;gap:12px;align-items:center;">
            <input type="hidden" name="page" value="completed-stories" />
            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search ID, story name, author or user..." style="min-width:260px;">
            <button type="submit" class="button">Search</button>
            <a href="<?php echo $base_url; ?>" class="button">Reset</a>
        </form>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Completed By</th>
                    <th>Completed On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($results): foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->story_id); ?></td>
                        <td>
                            <?php if ($row->post_title): ?>
                                <?php echo esc_html($row->post_title); ?>
                            <?php else: ?>
                                <em><?php echo esc_html('Story #' . $row->story_id); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row->author_name): ?>
                                <?php echo esc_html($row->author_name); ?>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row->user_name): ?>
                                <?php echo esc_html($row->user_name); ?>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($row->completed_on ?: '—'); ?></td>
                        <td>
                            <a class="button button-small"
                               href="<?php echo wp_nonce_url(admin_url('admin.php?page=completed-stories&action=remove&user_id=' . intval($row->user_id) . '&story_id=' . intval($row->story_id) . '&paged=' . $page), 'remove_completed_story', 'nonce'); ?>"
                               onclick="return confirm('Remove this story from the completed list?');">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="6">No completed stories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="tablenav bottom">
            <div class="custom-pagination">
                <?php
                $pagination_url = $base_url;
                if ($search) {
                    $pagination_url .= '&s=' . urlencode($search);
                }
                echo paginate_links(array(
                    'base'      => $pagination_url . '&paged=%#%',
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
