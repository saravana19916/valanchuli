<?php get_header();
    $pageMyCreation = false;
    if ( isset($_GET['from']) && $_GET['from'] === 'mycreation' ) {
        $pageMyCreation = true;
    }
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-12">

            <?php
                $description = get_post_meta(get_the_ID(), 'description', true);
                $cardClass = 'card ' . ($description ? 'border-0' : 'border-0 rounded');
            ?>

            <h4 class="text-primary-color fw-bold text-center"><?php the_title(); ?></h4>

            <?php
                $post_id     = get_the_ID();
                $author_id   = get_post_field('post_author', $post_id);
                $author_name = get_the_author_meta('display_name', $author_id);
                $posted_date = get_the_date('d M Y', $post_id);

                $competition = get_post_meta($post_id, 'competition', true);

                $competitionParam = '';
                if (!empty($competition)) {
                    $competitionParam = '&from=competition';
                }
            ?>

            <p class="text-muted fs-16px text-center">
                <a href="<?php echo esc_url(site_url('/user-profile/?uid=' . $author_id)); ?>" 
                class="text-primary-color text-decoration-underline">
                    <?php echo esc_html($author_name); ?>
                </a>
                | <?php echo esc_html($posted_date); ?>
                <?php 
                $division_id = get_post_meta($post_id, 'division', true);

                if ($division_id) {
                    $division = get_term($division_id, 'division');
                    if (!is_wp_error($division) && $division) {
                        $division_link = site_url('/division/' . $division->slug . '/');
                        ?>
                        | Division: <a href="<?php echo esc_url($division_link); ?>"><?php echo esc_html($division->name); ?></a>
                    <?php } } ?>
            </p>

            <div class="<?= esc_attr($cardClass); ?>">
                <div class="card-body p-0 fs-16px">
                    <div class="card-text">
                        <?php

                            $division = get_post_meta($post_id, 'division', true);
                            if (!empty($description) || !empty($division)) {
                                $terms = get_the_terms(get_the_ID(), 'series');

                                if (!empty($terms) && !is_wp_error($terms)) {
                                    $series_term = $terms[0];

                                    $related_stories = new WP_Query([
                                        'post_type'      => 'post',
                                        'posts_per_page' => -1,
                                        'post_status'    => 'publish',
                                        'orderby'        => 'date',
                                        'order'          => 'ASC',
                                        'post__not_in'   => [get_the_ID()],
                                        'tax_query'      => [
                                            [
                                                'taxonomy' => 'series',
                                                'field'    => 'term_id',
                                                'terms'    => [$series_term->term_id],
                                            ],
                                        ],
                                    ]);
                                    ?>

                                    <div class="row col-12 p-2 mb-4 border border-2 border-primary rounded mx-auto">
                                        <!-- Image Section -->
                                        <div class="col-12 text-center mb-3">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('medium', [
                                                    'class' => 'img-fluid d-inline-block rounded post-image-size',
                                                ]); ?>
                                            <?php else : ?>
                                                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                                    class="img-fluid d-inline-block rounded post-image-size"
                                                    alt="Default Image">
                                            <?php endif; ?>
                                        </div>

                                        <!-- Description Section -->
                                        <div class="col-12">
                                            <div class="text-start">
                                                <?php
                                                    $word_limit = 200;
                                                    $words = explode(' ', wp_strip_all_tags($description)); // strip HTML tags
                                                    $first_part = implode(' ', array_slice($words, 0, $word_limit));
                                                    $remaining_part = implode(' ', array_slice($words, $word_limit));
                                                ?>

                                                <div class="description-content">
                                                    <p class="text-start">
                                                        <?php echo esc_html($first_part); ?>
                                                        <?php if (!empty($remaining_part)) : ?>
                                                            <span class="dots">... </span>
                                                            <span class="more-text d-none"><?php echo esc_html($remaining_part); ?></span>
                                                            <a href="javascript:void(0);" class="read-more-toggle fw-bold text-decoration-underline ms-2">Read more</a>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $series_id = get_the_ID(); ?>

                                    <h4 class="mt-5 fw-bold">பாகங்கள் (<?php echo $related_stories->found_posts; ?>)</h4>

                                    <?php if ($related_stories->have_posts()) { ?>
                                        <div class="row mt-2">
                                            <?php $count = 0; ?>
                                            <?php while ($related_stories->have_posts()) : $related_stories->the_post(); ?>
                                                <div class="col-12 col-md-6 col-xl-4 my-3">
                                                    <div class="w-100 p-4 shadow rounded">
                                                        <?php
                                                            $episode_id = get_the_ID();
                                                            $average_rating = get_custom_average_rating(get_the_ID());
                                                            $total_views = get_custom_post_views(get_the_ID());
                                                        ?>
                                                        <div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0 fw-bold">
                                                                    <?php echo sprintf("%2d", $count + 1); ?>.&nbsp;
                                                                    <?php
                                                                        $lock_status = get_episode_lock_status($series_id, $episode_id, $count + 1);
                                                                        $locked = $lock_status['locked'];
                                                                        $lock_type = $lock_status['type'];
                                                                        if ($locked):
                                                                    ?>
                                                                        <a href="javascript:void(0);" 
                                                                            class="locked-episode" 
                                                                            data-lock-type="<?php echo esc_attr($lock_type); ?>"
                                                                            onclick="showLockPopup('<?php echo esc_js($lock_type); ?>')">
                                                                            <?php echo esc_html(get_the_title()); ?>
                                                                            <i class="fa-solid fa-lock text-danger ms-2"></i>
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                                                    <?php endif; ?>
                                                                </h6>

                                                                <?php 
                                                                    $date = get_the_date('j F Y');
                                                                    $tamil_months = array(
                                                                        'January' => 'ஜனவரி',
                                                                        'February' => 'பிப்ரவரி',
                                                                        'March' => 'மார்ச்',
                                                                        'April' => 'ஏப்ரல்',
                                                                        'May' => 'மே',
                                                                        'June' => 'ஜூன்',
                                                                        'July' => 'ஜூலை',
                                                                        'August' => 'ஆகஸ்ட்',
                                                                        'September' => 'செப்டம்பர்',
                                                                        'October' => 'அக்டோபர்',
                                                                        'November' => 'நவம்பர்',
                                                                        'December' => 'டிசம்பர்'
                                                                    );

                                                                    $tamil_date = str_replace(array_keys($tamil_months), array_values($tamil_months), $date); 
                                                                ?>
                                                                <span class="text-muted fs-custom"><?php echo $tamil_date; ?></span>
                                                            </div>

                                                            <div class="ms-4 mt-3">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <span class="text-muted fs-custom">
                                                                            <i class="fa-solid fa-eye"></i>&nbsp;<?php echo format_view_count($total_views); ?>
                                                                        </span>
                                                                        <span class="mb-0 ms-4">
                                                                            <i class="fa-solid fa-star" style="color: gold;"></i>&nbsp;&nbsp;<?php echo $average_rating; ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php
                                                                    $post_id    = get_the_ID();
                                                                    $author_id  = get_post_field('post_author', $post_id);
                                                                    $current_id = get_current_user_id();

                                                                    if ($pageMyCreation && $current_id === (int) $author_id) :
                                                                    ?>
                                                                        <div>
                                                                            <a 
                                                                                href="<?php echo esc_url(home_url('/write?id=' . $post_id . $competitionParam)); ?>" 
                                                                                class="p-1" 
                                                                                title="Edit">
                                                                                <i class="fa-solid fa-pen-to-square fa-lg"></i>
                                                                            </a>

                                                                            <?php 
                                                                                $nonce = wp_create_nonce('frontend_delete_post_' . $post_id);
                                                                                $delete_url = add_query_arg([
                                                                                    'action'   => 'frontend_delete_post',
                                                                                    'post_id'  => $post_id,
                                                                                    'nonce'    => $nonce,
                                                                                ], admin_url('admin-post.php'));
                                                                            ?>

                                                                            <a href="<?php echo esc_url($delete_url); ?>"
                                                                                class="p-1" 
                                                                                title="Delete" 
                                                                                onclick="return confirm('தொடர்கதையில் இருந்து இந்த பாகத்தை நீக்க விரும்புகிறீர்களா?');">
                                                                                <i class="fa-solid fa-trash-can fa-lg"></i>
                                                                            </a>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>

                                                            <!-- <div class="d-flex mt-4">
                                                                <div class="d-flex align-items-center top-0 end-0 bg-primary-color px-2 py-1 me-1 fw-bold rounded text-highlight-color">
                                                                    <i class="fa-solid fa-eye me-1"></i>
                                                                    <?php echo format_view_count($total_views); ?>
                                                                </div>
                                                                <span class="mt-1 fs-12px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                                                            </div> -->
                                                    </div>
                                                </div>
                                            <?php $count++; endwhile; ?>
                                        </div>

                                        <?php
                                            if ( is_user_logged_in() ) {
                                                $post_id = get_the_ID();
                                                $current_user_id = get_current_user_id();
                                                $post_author_id = (int) get_post_field('post_author', $post_id);

                                                if ( $current_user_id === $post_author_id ) :
                                                    ?>
                                                    <div class="alert alert-warning text-center w-75 mx-auto mt-3 text-primary-color" role="alert">
                                                        <p class="mb-2">
                                                            அடுத்த பாகம் சேர்க்க கீழே உள்ள லிங்கை கிளிக் செய்யுங்கள்
                                                        </p>
                                                        <?php if ($competitionParam) { ?>
                                                            <a href="<?php echo esc_url( home_url('/write?id=' . $post_id . $competitionParam) ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                                படைப்பை சேர்க்க
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?php echo esc_url( site_url('/write') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                                படைப்பை சேர்க்க
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                    <?php
                                                endif;
                                            }
                                        ?>
                                        <?php wp_reset_postdata(); ?>
                                    <?php } else { ?>
                                        <?php
                                            if ( is_user_logged_in() ) {
                                                $post_id = get_the_ID();
                                                $current_user_id = get_current_user_id();
                                                $post_author_id = (int) get_post_field('post_author', $post_id);

                                                if ( $current_user_id === $post_author_id ) :
                                        ?>
                                            <div class="col-12 text-center mt-4">
                                                <div class="alert alert-warning text-center w-75 mx-auto mt-3 text-primary-color" role="alert">
                                                    <p class="mb-2">
                                                        இந்தப் படைப்பிற்கு இன்னும் தொடர்கதை உருவாக்கப் படவில்லை. தொடர்கதை உருவாக்க கீழே உள்ள  லிங்கை கிளிக் செய்யுங்கள்!
                                                    </p>
                                                    <?php if ($competitionParam) { ?>
                                                        <a href="<?php echo esc_url( home_url('/write?id=' . $post_id . $competitionParam) ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                            படைப்பை சேர்க்க
                                                        </a>
                                                    <?php } else { ?>
                                                        <a href="<?php echo esc_url( site_url('/write') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                            படைப்பை சேர்க்க
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <div class="col-12 text-center mt-4">
                                                <div class="alert alert-warning text-center w-75 mx-auto mt-3 text-primary-color" role="alert">
                                                    <p class="mb-2">
                                                        இந்தப் படைப்பிற்கு இன்னும் தொடர்கதை உருவாக்கப் படவில்லை.
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; } ?>
                                    <?php } ?>
                                    <?php 
                                }
                            } else { ?>
                                <?php increase_story_view_count(); ?>

                                <div class="row col-12 mb-3 mx-auto">
                                    <!-- Image Section -->
                                    <div class="col-12 text-center mb-3">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('medium', [
                                                'class' => 'img-fluid d-inline-block rounded post-image-size',
                                            ]); ?>
                                        <?php else : ?>
                                            <!-- <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                                class="img-fluid d-inline-block rounded post-image-size"
                                                alt="Default Image"> -->
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="py-2 single-page-content">
                                    <?php
                                        the_content();
                                    ?>
                                </div>

                                <?php
                                    $series = get_the_terms(get_the_ID(), 'series');
                                    $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';
                                    $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                                    $is_parent = $series_name == 'தொடர்கதை அல்ல' ? false : true;
                                ?>

                                <?php
                                    if ($is_parent && $series_id) :
                                        // Get all posts from the same series ordered by date
                                        $episodes = get_posts([
                                            'post_type'      => 'post',
                                            'posts_per_page' => -1,
                                            'orderby'        => 'date',
                                            'order'          => 'ASC',
                                            'tax_query'      => [
                                                [
                                                    'taxonomy' => 'series',
                                                    'field'    => 'term_id',
                                                    'terms'    => $series_id,
                                                ],
                                            ],
                                        ]);

                                        $episode_ids = wp_list_pluck($episodes, 'ID');
                                        $current_index = array_search(get_the_ID(), $episode_ids);

                                        $prev_episode_id = ($current_index > 1) ? $episode_ids[$current_index - 1] : null;
                                        $next_episode_id = ($current_index < count($episode_ids) - 1) ? $episode_ids[$current_index + 1] : null;
                                        ?>

                                        <div class="episode-navigation row my-4">
                                            <div class="col-6 text-start">
                                                <?php if ($prev_episode_id): ?>
                                                    <button type="button"
                                                            class="btn btn-primary"
                                                            onclick="window.location.href='<?php echo esc_url(get_permalink($prev_episode_id)); ?>'">
                                                        ← Previous Episode
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <div class="col-6 text-end">
                                                <?php if ($next_episode_id): ?>
                                                    <button type="button"
                                                            class="btn btn-primary"
                                                            onclick="window.location.href='<?php echo esc_url(get_permalink($next_episode_id)); ?>'">
                                                        Next Episode →
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                <div
                                    class="star-rating sec-comment text-center d-flex flex-column align-items-center justify-content-center text-primary-color mt-4 mx-auto responsive-rating login-shadow"
                                    data-post-id="<?php the_ID(); ?>"
                                    data-series-id="<?php echo esc_attr($series_id); ?>"Add commentMore actions
                                    data-post-parent="<?php echo $is_parent; ?>">
                                        <p class="my-2 fw-bold fs-13px">இந்த படைப்பை மதிப்பிட விரும்புகிறீர்களா?</p>
                                        <p class="mb-2">Click on a star to rate it!</p>
                                        <div class="stars">
                                            <?php
                                                $user_id = get_current_user_id();
                                                $post_id = get_the_ID();
                                                $rating = get_user_rating_for_post($user_id, $post_id);
                                            ?>

                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="star <?php echo ($i <= $rating) ? 'rated' : ''; ?>" data-value="<?php echo $i; ?>">&#9733;</span>
                                            <?php endfor; ?>
                                        </div>
                                        <p>No votes so far! Be the first to rate this post.</p>
                                </div>

                                    <?php
    $message  = "திகிலும் ருசிக்கும் ஒரு தொடர்கதை....\n\n";
    $message .= "கதையை பிரதிலிபி செயலியில் வாசியுங்கள்\n\n";
    $message .= get_the_title() . "\n";
    $message .= get_permalink() . "\n\n";
    $message .= "வாசிக்க கதையின் மேல் க்ளிக் செய்யவும் 👆";

    $whatsapp_link = "https://wa.me/?text=" . rawurlencode($message);
    ?>

    <a href="<?php echo $whatsapp_link; ?>" 
    class="btn btn-success whatsapp-popup">
        <i class="fa-brands fa-whatsapp"></i> Share on WhatsApp1
    </a>


                                
                                <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="loginRequiredLabel">Login Required</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            You must be logged in to rate. <br>
                                            <a href="#" class="btn btn-primary mt-3 login-btn">Login</a>
                                        </div>
                                        </div>
                                    </div>
                                </div>


                                <?php if (comments_open() || get_comments_number()) : ?>
                                    <div class="text-center d-flex flex-column align-items-center justify-content-center text-primary-color mt-4 mx-auto">
                                    <div class="col-12 col-md-9 mx-auto border-0 my-3">
                                            <div class="card-body">
                                                <?php comments_template(); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php }
                        ?>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>

<script>
    function openSharePopup(url) {
    window.open(
        url,
        'whatsappShareWindow',
        'width=600,height=600,top=100,left=200'
    );
    return false; // prevent normal link navigation
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.read-more-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const container = btn.closest('.description-content');
            const dots = container.querySelector('.dots');
            const moreText = container.querySelector('.more-text');

            dots.classList.toggle('d-none');
            moreText.classList.toggle('d-none');

            btn.textContent = moreText.classList.contains('d-none') ? 'Read more' : 'Read less';
        });
    });
});

function showLockPopup(type) {
    let msg = '';
    switch (type) {
        case 'ads':
            msg = 'இந்த பாகம் Ads Lock ஆகும் — விளம்பரம் பார்க்க வேண்டும்.';
            break;
        case 'coin':
            msg = 'இந்த பாகம் Coin Lock ஆகும் — நாணயங்கள் தேவை.';
            break;
        case 'default':
            msg = 'இது இயல்புநிலை பூட்டு (Default Lock) ஆகும்.';
            break;
        default:
            msg = 'இந்த பாகம் பூட்டப்பட்டுள்ளது.';
    }

    // Simple popup (you can replace with Bootstrap modal if needed)
    const popup = document.createElement('div');
    popup.innerHTML = `
        <div style="
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;">
            <div style="background: white; padding: 20px 30px; border-radius: 8px; text-align:center; max-width:400px;">
                <p>${msg}</p>
                <button onclick="this.closest('div').parentNode.remove()" style="margin-top:10px;" class="btn btn-primary">சரி</button>
            </div>
        </div>
    `;
    document.body.appendChild(popup);
}
</script>
