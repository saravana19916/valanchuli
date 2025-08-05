<?php get_header(); ?>

<?php
$banners = get_theme_mod('banner_slides');
if (!empty($banners)) :
    $carousel_id = 'bannerCarousel';
?>
<div id="<?php echo $carousel_id; ?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($banners as $index => $banner) : ?>
            <div class="carousel-item <?php echo ($index === 0) ? 'active' : ''; ?>">
                <div class="banner-slide" style="background-image: url('<?php echo esc_url($banner['image']); ?>'); background-size: cover; background-position: center;">
                    <div class="carousel-caption">
                        <h2><?php echo esc_html($banner['title']); ?></h2>
                        <p><?php echo esc_html($banner['description']); ?></p>
                        <?php
                        if (!empty($banner['button_text']) && !empty($banner['button_url'])) :
                            $button_url = (strpos($banner['button_url'], 'http') === 0)
                                ? esc_url($banner['button_url'])
                                : get_permalink(get_page_by_path($banner['button_url']));
                        ?>
                            <a href="<?php echo esc_url($button_url); ?>" class="btn btn-primary">
                                <?php echo esc_html($banner['button_text']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Carousel Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carousel_id; ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
<?php endif; ?>

<div class="container my-4">
    <!-- Trending stories start -->
    <?php get_template_part('template-parts/trending-stories'); ?>
    <!-- Trending stories end -->

    <!-- Trending stories start -->
    <?php get_template_part('template-parts/latest-stories'); ?>
    <!-- Trending stories end -->

    <!-- நாவல்கள் stories start -->
    <?php get_template_part('template-parts/novel-stories'); ?>
    <!-- நாவல்கள் stories end -->

    <!-- competition stories start -->
    <?php get_template_part('template-parts/competition-stories'); ?>
    <!-- competition stories end -->

    <!-- sirukathai stories start -->
    <?php get_template_part('template-parts/category-stories', null, ['categoryKey' => 'sirukathai', 'categoryValue' => 'சிறுகதை']); ?>
    <!-- sirukathai stories end -->

    <!-- kavithai stories start -->
     <?php get_template_part('template-parts/category-stories', null, ['categoryKey' => 'kavithai', 'categoryValue' => 'கவிதை']); ?>
    <!-- kavithai stories end -->

    <!-- katturai stories start -->
    <?php get_template_part('template-parts/category-stories', null, ['categoryKey' => 'katturai', 'categoryValue' => 'கட்டுரை']); ?>
    <!-- katturai stories end -->
</div>

<?php get_footer(); ?>
