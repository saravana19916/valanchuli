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

                | <a href="javascript:void(0);" 
                    class="text-decoration-none text-muted"
                    data-bs-toggle="modal"
                    data-bs-target="#shareModal">
                        <i class="fa-solid fa-share-nodes me-1"></i> Share
                </a>
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

                                    <div class="row col-12 p-2 border border-2 border-primary rounded mx-auto">
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

                                    <?php
                                        global $wpdb;
                                        $premium_table = $wpdb->prefix . 'premium_story_rules';

                                        $premium_rule = $wpdb->get_row(
                                            $wpdb->prepare("SELECT * FROM $premium_table WHERE post_id = %d", $post_id)
                                        );

                                        // Check if unlocked for this user
                                        $is_unlocked = false;
                                        if ($premium_rule && is_user_logged_in()) {
                                            $user_id = get_current_user_id();
                                            $unlock_table = $wpdb->prefix . 'premium_story_unlocks';
                                            $unlock = $wpdb->get_row($wpdb->prepare(
                                                "SELECT * FROM $unlock_table WHERE user_id = %d AND series_id = %d AND unlock_until >= %s ORDER BY unlock_until DESC LIMIT 1",
                                                $user_id, $post_id, current_time('mysql')
                                            ));
                                            if ($unlock) {
                                                $is_unlocked = true;
                                            }
                                        }

                                        if ($premium_rule && !$is_unlocked) {
                                    ?>
                                        <div style="background:#004d4d;color:#fff;padding:10px;border-radius:0 0 10px 10px;margin-top:-4px;" class="text-center">
                                            <div style="font-size:1.1rem;">
                                                முழுகதையும் படிக்க இப்பொழுதே unlock செய்யுங்கள்
                                            </div>
                                            <div style="font-size:1.1rem;" class="mt-2">
                                                UnLock full story:
                                                <?php if (!empty($premium_rule->offer_coin) && $premium_rule->offer_coin > 0): ?>
                                                    <span style="text-decoration:line-through;color:#ffd600;">
                                                        <?php echo esc_html($premium_rule->coin); ?>
                                                    </span>
                                                    <span style="color:#ffd600;font-weight:bold;font-size:1.3rem;">
                                                        <?php echo esc_html($premium_rule->offer_coin); ?> Key
                                                    </span>
                                                    <div style="font-size:1.1rem;margin-top:6px;">
                                                        One Day Offer: <span style="color:#ffd600;font-weight:bold;"><?php echo esc_html($premium_rule->offer_coin); ?> Key</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:#ffd600;font-weight:bold;font-size:1.3rem;">
                                                        <?php echo esc_html($premium_rule->coin); ?> Key
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="my-2">
                                                <button
                                                    type="button" class="btn btn-warning fw-bold lock-episode"
                                                    data-lock-type="premium"
                                                    data-coin="<?php echo esc_attr($premium_rule->coin); ?>"
                                                    data-offer-coin="<?php echo esc_attr($premium_rule->offer_coin); ?>"
                                                >
                                                    Unlock Now
                                                </button>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <?php $series_id = get_the_ID(); ?>

                                    <h4 class="mt-5 fw-bold">பாகங்கள் (<?php echo $related_stories->found_posts; ?>)</h4>

                                    <?php if ($related_stories->have_posts()) { ?>
                                        <div class="row mt-2">
                                            <?php
                                                $count = 0;
                                                $episode_map_for_Key = [];
                                                $episode_map_for_All = [];
                                                $episodeIdToLockType = [];
                                            ?>
                                            <?php while ($related_stories->have_posts()) : $related_stories->the_post(); ?>
                                                <div class="col-12 col-md-6 col-xl-4 my-3">
                                                    <?php
                                                        $episode_id = get_the_ID();
                                                        $episodeNumber = get_post_meta($episode_id, 'episode_number', true);
                                                        $average_rating = get_custom_average_rating(get_the_ID());
                                                        $total_views = get_custom_post_views(get_the_ID());

                                                        $lock_status = get_episode_lock_status($series_id, $episode_id, $count + 1);
                                                        $locked = $lock_status['locked'];
                                                        $lock_type = $lock_status['type'];
                                                    ?>

                                                    <div class="w-100 p-4 shadow rounded <?php echo $locked ? 'episode-backdrop is-locked' : ''; ?>">
                                                        <div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <h6 class="mb-0 fw-bold">
                                                                    <?php echo sprintf("%2d", $count + 1); ?>.&nbsp;
                                                                    <?php
                                                                        if ($locked):
                                                                            if (
                                                                                (is_array($lock_type) && in_array('coin', $lock_type)) ||
                                                                                $lock_type === 'coin'
                                                                            ) {
                                                                                $episode_map_for_Key[$episodeNumber] = $episode_id;
                                                                            }
                                                                            $episode_map_for_All[$episodeNumber] = $episode_id;
                                                                            $episodeIdToLockType[$episode_id] = is_array($lock_type) ? $lock_type : [$lock_type];
                                                                    ?>
                                                                        <?php echo esc_html(get_the_title()); ?>

                                                                        <div class="lock-overlay lock-episode"
                                                                            data-episode-id="<?php echo $episode_id; ?>"
                                                                            data-parent-id="<?php echo $series_id; ?>"
                                                                            data-episode-number="<?php echo $episodeNumber; ?>"
                                                                            data-lock-type="<?php echo is_array($lock_type) ? implode(',', $lock_type) : $lock_type; ?>"
                                                                            <?php if ($lock_type === 'premium'): ?>
                                                                                data-coin="<?php echo esc_attr($lock_status['coin']); ?>"
                                                                                data-offer-coin="<?php echo esc_attr($lock_status['offer_coin']); ?>"
                                                                            <?php endif; ?>
                                                                            <?php if (
                                                                                (is_array($lock_type) && in_array('ads', $lock_type)) ||
                                                                                $lock_type === 'ads'
                                                                            ):
                                                                                // Get ads lock details for this episode
                                                                                $ads_lock = get_ads_lock_for_episode($series_id, $episodeNumber); // Implement this function to fetch lock details
                                                                                $ads_time_sec = isset($ads_lock['ads_time_sec']) ? $ads_lock['ads_time_sec'] : '';
                                                                                $ads_content = isset($ads_lock['ads_content']) ? $ads_lock['ads_content'] : '';
                                                                            ?>
                                                                                data-ads-time-sec="<?php echo esc_attr($ads_time_sec); ?>"
                                                                                data-ads-content="<?php echo esc_attr(mb_substr(wp_strip_all_tags($ads_content), 0, 100)); ?>"
                                                                            <?php endif; ?>
                                                                            >

                                                                            <div class="lock-image">
                                                                                <img 
                                                                                    src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/lock-coin.png'); ?>" 
                                                                                    alt="Locked Episode">
                                                                            </div>

                                                                            <!-- <div class="unlock-label">
                                                                                Unlock with 50 Coins
                                                                            </div> -->
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="unlocked" data-episode-number="<?php echo $episodeNumber; ?>">
                                                                            <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                                                        </div>
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
                                                            <a href="<?php echo esc_url( home_url('/write?postId=' . $post_id . $competitionParam . '&create=episode') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                                படைப்பை சேர்க்க
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?php echo esc_url( site_url('/write?postId=' . $post_id . '&create=episode') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
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
                                                        <a href="<?php echo esc_url( home_url('/write?postId=' . $post_id . $competitionParam . '&create=episode') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
                                                            படைப்பை சேர்க்க
                                                        </a>
                                                    <?php } else { ?>
                                                        <a href="<?php echo esc_url( site_url('/write?postId=' . $post_id . '&create=episode') ); ?>" class="text-decoration-underline fw-bold d-inline-block">
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
                                <?php
                                    global $wpdb;
                                    $post_id = get_the_ID();
                                    $user_id = get_current_user_id();

                                    // Get series info
                                    $parent_post_id = getParentPostId($post_id);

                                    // Get episode number (adjust if your meta key is different)
                                    $episode_number = get_post_meta($post_id, 'episode_number', true);

                                    // Check lock status
                                    $lock_status = get_episode_lock_status($parent_post_id, $post_id, $episode_number);
                                    if ($lock_status['locked']) {
                                        // Show lock message and stop further rendering
                                        ?>
                                        <div class="container my-5">
                                            <div class="row justify-content-center">
                                                <div class="col-12 text-center">
                                                    <div class="p-4 rounded shadow" style="background:#f8f9fa;max-width:400px;margin:auto;">
                                                        <img src="<?php echo get_template_directory_uri(); ?>/images/lock-coin.png" alt="Locked" style="width:64px;">
                                                        <h4 class="mt-3 mb-2 text-danger">This episode is locked</h4>
                                                        <p class="mb-3">Unlock to read this episode.</p>
                                                        <!-- Optionally, add unlock button/modal trigger here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                    } else {
                                ?>

                                    <?php
                                        $post_id = get_the_ID();
                                        $content = get_post_field('post_content', $post_id);
                                        $content = strip_shortcodes($content); // Remove shortcodes
                                        $content = strip_tags($content);       // Remove HTML tags
                                        $word_count = count(preg_split('/\s+/u', trim($content), -1, PREG_SPLIT_NO_EMPTY));

                                        if ($word_count <= 175) {
                                            $required_seconds = 30;
                                        } elseif ($word_count <= 350) {
                                            $required_seconds = 60;
                                        } elseif ($word_count <= 700) {
                                            $required_seconds = 120;
                                        } elseif ($word_count <= 1000) {
                                            $required_seconds = 168;
                                        } else {
                                            $required_seconds = ceil($word_count / 350) * 60;
                                        }
                                    ?>

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

                                            $series_posts = get_posts([
                                                'post_type'      => 'post',
                                                'posts_per_page' => 1,
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

                                            $series_parent_url = !empty($series_posts)
                                                ? get_permalink($series_posts[0]->ID)
                                                : home_url();
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
                                                    <button type="button"
                                                        class="btn btn-primary"
                                                        onclick="window.location.href='<?php echo esc_url($series_parent_url); ?>'">
                                                        ← Back
                                                    </button>

                                                    <?php if ($next_episode_id): ?>
                                                        <button type="button"
                                                            class="btn btn-primary ms-2"
                                                            onclick="window.location.href='<?php echo esc_url(get_permalink($next_episode_id)); ?>'">
                                                            Next Episode →
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                        <?php endif; ?>
                                    
                                    <?php
                                    $current_user_id = get_current_user_id();
                                    $post_author_id = (int) get_post_field('post_author', $post_id);

                                    if ($current_user_id !== $post_author_id) : ?>
                                        <div class="reward-key-box text-center mx-auto mb-4" style="max-width:420px;background:#fffbe8;border-radius:18px;box-shadow:0 2px 12px rgba(0,0,0,0.08);padding:24px 18px;">
                                            <div style="font-size:1.2rem;font-weight:bold;margin-bottom:8px;">இந்த episode பிடிச்சிருந்தா எழுத்தாளரை உற்சாகப்படுத்துங்க!!</div>
                                            <div class="reward-key-buttons" style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center;">
                                                <button class="reward-key-btn" data-key="2" style="background:#e6d3b3;border:none;border-radius:8px;padding:12px 18px;font-size:1rem;font-weight:bold;box-shadow:0 1px 4px #e6d3b3;">👍 Nice<br><span style="color:#7c5c2b;">2 Key</span></button>
                                                <button class="reward-key-btn" data-key="5" style="background:#cde7d8;border:none;border-radius:8px;padding:12px 18px;font-size:1rem;font-weight:bold;box-shadow:0 1px 4px #cde7d8;">👌 செம<br><span style="color:#2b7c5c;">5 Key</span></button>
                                                <button class="reward-key-btn" data-key="7" style="background:#d3e6f7;border:none;border-radius:8px;padding:12px 18px;font-size:1rem;font-weight:bold;box-shadow:0 1px 4px #d3e6f7;">😍 மனச தொட்டுடுச்சி!<br><span style="color:#2b5c7c;">7 Key</span></button>
                                                <button class="reward-key-btn" data-key="10" style="background:#f7d3e6;border:none;border-radius:8px;padding:12px 18px;font-size:1rem;font-weight:bold;box-shadow:0 1px 4px #f7d3e6;">🔥🔥 Fire Episode<br><span style="color:#7c2b5c;">10 Key</span></button>
                                                <button class="reward-key-btn" data-key="25" style="background:#e6c3b3;border:none;border-radius:8px;padding:12px 18px;font-size:1rem;font-weight:bold;box-shadow:0 1px 4px #e6c3b3;">⏰🚀⏩ next episode சீக்கிரம் வேணும்!!<br><span style="color:#7c3c2b;">25 Key</span></button>
                                            </div>
                                            <div style="font-size:1rem;font-weight:bold;margin-bottom:8px; margin-top:15px;">இப்போதே உற்சாகப்படுத்துங்கள்</div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="star-rating sec-comment text-center d-flex flex-column align-items-center justify-content-center text-primary-color mt-4 mx-auto responsive-rating login-shadow"
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
                                <?php } ?>
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

        window.episodeNumberToIdForKey = <?php echo json_encode($episode_map_for_Key); ?>;
        window.episodeNumberToIdForAll = <?php echo json_encode($episode_map_for_All); ?>;
        window.episodeIdToLockType = <?php echo json_encode($episodeIdToLockType); ?>;
    });

    document.addEventListener('DOMContentLoaded', function () {
        var requiredSeconds = <?php echo $required_seconds; ?>;
        var postId = <?php echo $post_id; ?>;
        var timer = null;
        var viewCountSent = false;
        var remainingSeconds = requiredSeconds;
        var lastHiddenTime = null;
        var timerPaused = false;

        function startTimer() {
            timer = setInterval(function () {
                console.log("remainingSeconds:", remainingSeconds);
                remainingSeconds--;
                if (remainingSeconds <= 0 && !viewCountSent) {
                    viewCountSent = true;
                    clearInterval(timer);
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=increase_story_view_count_ajax&post_id=' + postId
                    }).then(response => response.text())
                    .then(data => {
                        console.log(data);
                    });
                }
            }, 1000);
        }

        function stopTimer() {
            if (timer) clearInterval(timer);
        }

        // Start timer when page loads
        startTimer();

        // Pause timer when tab is hidden or window loses focus
        function pauseTimer() {
            if (!timerPaused) {
                lastHiddenTime = Date.now();
                stopTimer();
                timerPaused = true;
            }
        }

        // Resume timer when tab is visible or window regains focus
        function resumeTimer() {
            if (timerPaused) {
                lastHiddenTime = null;
                timerPaused = false;
                if (!viewCountSent && remainingSeconds > 0) {
                    startTimer();
                }
            }
        }

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                pauseTimer();
            } else {
                resumeTimer();
            }
        });

        window.addEventListener('blur', pauseTimer);
        window.addEventListener('focus', resumeTimer);

        window.addEventListener('beforeunload', function () {
            stopTimer();
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.reward-key-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var keyAmount = btn.getAttribute('data-key');
                var postId = <?php echo $post_id; ?>;
                var authorId = <?php echo $author_id; ?>;
                // Confirm action
                if (confirm(keyAmount + ' Key will be sent to the writer. Continue?')) {
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=reward_keys_to_writer&post_id=' + postId + '&author_id=' + authorId + '&key_amount=' + keyAmount
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Keys sent successfully!');
                        } else {
                            alert(data.data || 'Failed to send keys.');
                        }
                    });
                }
            });
        });
    });
</script>
