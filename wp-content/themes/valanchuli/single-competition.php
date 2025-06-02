<?php get_header(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="text-primary-color fw-bold"><?php the_title(); ?></h4>
                    <p class="text-muted fs-13px">
                        <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_name = get_the_author_meta('display_name', $author_id);
                            $posted_date = get_the_date('d M Y');

                            $series_id = get_post_meta(get_the_ID(), '_competition_series', true);
                            $series_name = $series_id ? get_term($series_id)->name : 'தொடர்கதை அல்ல';

                            echo esc_html($author_name) . ' | ' . esc_html($posted_date) . ' | ' . esc_html($series_name);
                        ?>
                    </p>

                    <div class="card-text my-5">
                        <?php
                            $content = get_post_meta(get_the_ID(), '_competition_content', true);
                            echo wpautop(esc_html($content));
                        ?>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="mt-5 shadow-sm border-0">
                    <div class="card-body">
                        <?php comments_template(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
jQuery(document).ready(function($) {
    function loadCompetitionPosts(competition_id, page = 1) {
        $.ajax({
            type: 'POST',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            data: {
                action: 'fetch_competition_posts',
                competition_id: competition_id,
                paged: page
            },
            beforeSend: function() {
                $('#competition-table-body').html('<tr><td colspan="2">Loading...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    $('#competition-table-body').html(response.data.table_data);
                    $('#competition-pagination').html(response.data.pagination);
                }
            }
        });
    }

    let competition_id = $('#competition-id').val();
    loadCompetitionPosts(competition_id);

    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadCompetitionPosts(competition_id, page);
    });
});
</script>

