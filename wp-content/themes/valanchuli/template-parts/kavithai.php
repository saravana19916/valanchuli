<h5 class="row p-2 mt-4 text-highlight-color fw-bold bg-primary-color">🔥 கவிதை</h5>
<?php
    $categories = get_categories([
        'taxonomy' => 'category',
        'hide_empty' => false,
        'exclude' => [get_cat_ID('Uncategorized')],
    ]);

    $has_stories = false;

    foreach ($categories as $category) {
?>

    <?php
    if ($category->name !== 'கவிதை') {
        continue;
    }

    $stories = new WP_Query([
        'post_type' => ['story', 'competition_post'],
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => [$category->term_id],
                'operator' => 'IN',
            ],
            [
                'taxonomy' => 'series',
                'field'    => 'name',
                'terms'    => ['தொடர்கதை அல்ல'],
                'operator' => 'IN',
            ],
        ],
    ]);

    $kavithai_count = $stories->found_posts;

    if ($stories->have_posts()) {
        $has_stories = true;
    ?>
        <div class="row" style="gap: 25px;">
        <?php while ($stories->have_posts()) {
            $stories->the_post();
            $post_id = get_the_ID();
            $total_views = get_custom_post_views($post_id);
            $average_rating = get_custom_average_rating($post_id);
            ?>
                <div class="col-12 col-sm-4 col-md-3 col-xl-2 col-xxl-2 px-5 px-sm-2 p-md-0 d-flex align-items-center justify-content-center text-primary-color kavithai-card">
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
            <?php
        } ?>
        </div>
        <!-- Read More Button -->
        <div class="text-center mt-4">
            <button id="kavithai-toggle-read-btn" class="btn btn-primary text-highlight-color d-none">Read More</button>
        </div>
    <?php } else {
        // echo 'No stories found for ' . esc_html($category->name);
    }
    wp_reset_postdata();
    ?>
<?php } ?>

<?php if (!$has_stories) { ?>
    <div class="text-center mt-4 fs-14px text-primary-color" role="alert">
        No stories found.
    </div>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const kavithaiCards = document.querySelectorAll('.kavithai-card');
    const readMoreBtn = document.getElementById('kavithai-toggle-read-btn');
    const totalTrendingCount = <?php echo $kavithai_count; ?>;
    let expanded = false;

    function isSmallScreen() {
        return window.innerWidth < 1199;
    }

    function limitCards() {
        const visibleLimit = isSmallScreen() ? 3 : 5;

        kavithaiCards.forEach((card, index) => {
            if (index < visibleLimit) {
                card.classList.remove('d-none', 'extra-kavithai-story');
            } else {
                card.classList.add('d-none', 'extra-kavithai-story');
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
            kavithaiCards.forEach(card => card.classList.remove('d-none'));
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