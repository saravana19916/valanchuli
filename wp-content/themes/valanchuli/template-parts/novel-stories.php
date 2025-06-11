<?php 
    $novel_query = new WP_Query([
        'post_type' => ['story', 'competition_post'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ]);
    
    $novel_stories = [];
    
    if ($novel_query->have_posts()) {
        while ($novel_query->have_posts()) {
            $novel_query->the_post();
            $post_id = get_the_ID();
            $description = get_post_meta($post_id, 'description', true);
            if (!empty($description)) {
                $series = get_the_terms($post_id, 'series');
                $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                $series_name = ($series && !is_wp_error($series)) ? $series[0]->name : '';

                $views = get_average_series_views($post_id, $series_id);
        
                $novel_stories[] = [
                    'post' => get_post(),
                    'views' => $views,
                ];
            }
        }
        wp_reset_postdata();
    }

    usort($novel_stories, function ($a, $b) {
        return $b['views'] <=> $a['views'];
    });

    $total_novel_count = count($novel_stories);
?>

<div class="row mt-4" style="gap: 25px;">
    <h5 class="py-2 text-highlight-color fw-bold bg-primary-color">🔥 நாவல்கள்</h5>
    <?php foreach ($novel_stories as $item) :
        $post = $item['post'];
        setup_postdata($post);
        $post_id = $post->ID;
        $total_views = $item['views'];
        $average_rating = get_custom_average_rating($post_id, 0);
    ?>
        <div class="col-12 col-sm-4 col-md-3 col-xl-2 col-xxl-2 px-5 px-sm-2 p-md-0 d-flex align-items-center justify-content-center text-primary-color novel-card">
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
    <?php endforeach; ?>
    <?php wp_reset_postdata(); ?>

    <?php if ($total_novel_count == 0) { ?>
        <div class="text-center fs-14px text-primary-color" role="alert">
            No stories found.
        </div>
    <?php } ?>
</div>

<!-- Read More Button -->
<div class="text-center mt-4">
    <button id="novel-toggle-read-btn" class="btn btn-primary text-highlight-color d-none">Read More</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const novelCards = document.querySelectorAll('.novel-card');
    const readMoreBtn = document.getElementById('novel-toggle-read-btn');
    const totalTrendingCount = <?php echo $total_novel_count; ?>;
    let expanded = false;

    function isSmallScreen() {
        return window.innerWidth < 1199;
    }

    function limitCards() {
        const visibleLimit = isSmallScreen() ? 3 : 5;

        novelCards.forEach((card, index) => {
            if (index < visibleLimit) {
                card.classList.remove('d-none', 'extra-novel-story');
            } else {
                card.classList.add('d-none', 'extra-novel-story');
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
            novelCards.forEach(card => card.classList.remove('d-none'));
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