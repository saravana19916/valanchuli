<?php
    $stories = new WP_Query([
        'post_type' => ['competition_post'],
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);

    $shown_series = [];
    $main_stories = [];
    $other_stories = [];

    // First pass: select one story per series with description
    while ($stories->have_posts()) {
        $stories->the_post();
        $post_id = get_the_ID();
        $description = get_post_meta($post_id, 'description', true);
        $series_terms = wp_get_post_terms($post_id, 'series');
        $series_id = (!empty($series_terms) && !is_wp_error($series_terms)) ? $series_terms[0]->term_id : 0;

        if (!empty($description)) {
            if ($series_id && !isset($shown_series[$series_id])) {
                $shown_series[$series_id] = true;
                $main_stories[] = get_post();
            } elseif (!$series_id) {
                $main_stories[] = get_post(); // standalone story with description
            }
        }
    }
    wp_reset_postdata();

    // Second pass: collect remaining stories
    if ($stories->have_posts()) {
        while ($stories->have_posts()) {
            $stories->the_post();
            $series_terms = wp_get_post_terms(get_the_ID(), 'series');
            $series_id = (!empty($series_terms) && !is_wp_error($series_terms)) ? $series_terms[0]->term_id : 0;

            if ($series_id && isset($shown_series[$series_id])) {
                continue;
            }

            $other_stories[] = get_post();
        }
    }
    wp_reset_postdata();

    $all_stories = array_merge($main_stories, $other_stories);

    usort($all_stories, function ($a, $b) {
        $a_id = $a->ID;
        $b_id = $b->ID;
    
        $a_series = get_the_terms($a_id, 'series');
        $a_series_id = ($a_series && !is_wp_error($a_series)) ? $a_series[0]->term_id : 0;
        $a_desc = get_post_meta($a_id, 'description', true);
        $a_series_name = ($a_series && !is_wp_error($a_series)) ? $a_series[0]->name : '';
        $a_views = 0;
        if ($a_series_name === 'தொடர்கதை அல்ல') {
            $a_views = get_custom_post_views($a_id);
        } elseif (!empty($a_desc)) {
            $a_views = get_average_series_views($a_id, $a_series_id);
        }
    
        $b_series = get_the_terms($b_id, 'series');
        $b_series_id = ($b_series && !is_wp_error($b_series)) ? $b_series[0]->term_id : 0;
        $b_desc = get_post_meta($b_id, 'description', true);
        $b_series_name = ($b_series && !is_wp_error($b_series)) ? $b_series[0]->name : '';
        $b_views = 0;
        if ($b_series_name === 'தொடர்கதை அல்ல') {
            $b_views = get_custom_post_views($b_id);
        } elseif (!empty($b_desc)) {
            $b_views = get_average_series_views($b_id, $b_series_id);
        }
    
        return $b_views <=> $a_views;
    });

    $total_competition_count = count($all_stories);
    
?>

    <div class="row mt-4" style="gap: 25px;">
        <h5 class="py-2 text-highlight-color fw-bold bg-primary-color">🔥 போட்டிகள்</h5>
        <?php foreach ($all_stories as $post) :
            setup_postdata($post);
            $post_id = get_the_ID();
            $description = get_post_meta($post_id, 'description', true);
            
            $series = get_the_terms(get_the_ID(), 'series');
            $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
            $average_rating = get_custom_average_rating($post_id, $series_id);

            $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';

            $total_views = 0;
            if ($series_name == 'தொடர்கதை அல்ல') {
                $total_views = get_custom_post_views($post_id);
            }
            
            if (!empty($description)){
                $total_views = get_average_series_views($post_id, $series_id);
            }
        ?>
            <div class="col-12 col-sm-4 col-md-3 col-xl-2 col-xxl-2 px-5 px-sm-2 p-md-0 d-flex align-items-center justify-content-center text-primary-color competition-card">
                <div class="h-100 w-100 border rounded overflow-hidden">
                    <div class="position-relative">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium', [
                                    'class' => 'd-block rounded post-image-size',
                                ]); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                        class="d-block rounded post-image-size"
                                        alt="Default Image">
                            <?php endif; ?>
                        </a>
                        <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 me-2 mt-3 rounded">
                            <p class="mb-0 fw-bold" style="color: #FFEB00;">
                                <?php echo $average_rating; ?>
                                <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                            </p>
                        </div>
                    </div>

                    <div class="px-2 py-3">
                        <p class="card-title fw-bold mb-1 fs-13px mb-2">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <?php echo esc_html(mb_strimwidth(get_the_title(), 0, 30, '...')); ?>
                            </a>
                            </p>

                        <!-- <?php if (!empty($description)) : ?>
                            <p class="fs-12px mb-2 text-primary-color">
                                <?php echo esc_html(mb_strimwidth($description, 0, 200, '...')); ?>
                            </p>
                        <?php endif; ?> -->

                        <?php
                            $author_id = get_post_field('post_author', get_the_ID());
                            $author_name = get_the_author_meta('display_name', $author_id);
                        ?>

                        <p class="fs-12px text-primary-color text-decoration-underline">
                            <?php echo $author_name; ?>
                        </p>

                        <div class="d-flex mt-3">
                            <div class="d-flex align-items-center top-0 end-0 bg-primary-color px-2 py-1 me-1 fw-bold rounded text-highlight-color">
                                <i class="fa-solid fa-eye me-1"></i>
                                <?php echo format_view_count($total_views); ?>
                            </div>
                            <span class="mt-1 fs-13px fw-bold fw-medium text-center text-primary-color">வாசித்தவர்கள்</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        wp_reset_postdata(); ?>
        <?php if ($total_competition_count == 0) { ?>
            <div class="text-center fs-14px text-primary-color" role="alert">
                No stories found.
            </div>
        <?php } ?>
    </div>

<!-- Read More Button -->
<div class="text-center mt-4">
    <button id="competition-toggle-read-btn" class="btn btn-primary text-highlight-color d-none">Read More</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const competitionCards = document.querySelectorAll('.competition-card');
    const readMoreBtn = document.getElementById('competition-toggle-read-btn');
    const totalTrendingCount = <?php echo $total_competition_count; ?>;
    let expanded = false;

    function isSmallScreen() {
        return window.innerWidth < 1199;
    }

    function limitCards() {
        const visibleLimit = isSmallScreen() ? 3 : 5;

        competitionCards.forEach((card, index) => {
            if (index < visibleLimit) {
                card.classList.remove('d-none', 'extra-competition-story');
            } else {
                card.classList.add('d-none', 'extra-competition-story');
            }
        });

        if (totalTrendingCount > visibleLimit) {
            readMoreBtn.classList.remove('d-none');
        } else {
            readMoreBtn.classList.add('d-none');
        }
    }

    readMoreBtn.addEventListener('click', () => {
        if (!expanded) {
            competitionCards.forEach(card => card.classList.remove('d-none'));
            readMoreBtn.textContent = 'Read Less';
        } else {
            limitCards();
            readMoreBtn.textContent = 'Read More';
        }
        expanded = !expanded;
    });

    limitCards();

    window.addEventListener('resize', () => {
        if (!expanded) {
            limitCards();
        }
    });
});
</script>