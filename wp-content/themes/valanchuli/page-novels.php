<?php
get_header(); ?>

<?php 

    $context = $_GET['context'] ?? '';
    $user_id = $_GET['user_id'] ?? '';

    $args = [
        'post_type'      => ['post'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];
    
    if (!empty($user_id)) {
        $args['author'] = (int) $user_id;
    }
    
    $novel_query = new WP_Query($args);
    
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
?>

<div class="container my-4">
	<div class="row">
        <h4 class="py-2 fw-bold m-0">🔥 நாவல்கள்</h4>
        <div class="row col-12 mt-4 d-lg-flex flex-wrap justify-content-center justify-content-sm-start" style="gap: 2rem;">
            <?php foreach ($novel_stories as $index => $item): ?>
                <?php
                    $post = $item['post'];
                    setup_postdata($post);
                    $post_id = $post->ID;
                    $total_views = $item['views'];
                    $series = get_the_terms($post_id, 'series');
                    $series_id = ($series && !is_wp_error($series)) ? $series[0]->term_id : 0;
                    $average_rating = get_custom_average_rating($post_id, $series_id);

                    $episode_count = 0;

                    if ($series_id) {
                        $related_stories = new WP_Query([
                            'post_type'      => 'post',
                            'posts_per_page' => -1,
                            'post_status'    => 'publish',
                            'orderby'        => 'date',
                            'order'          => 'ASC',
                            'post__not_in'   => [$post_id],
                            'tax_query'      => [
                                [
                                    'taxonomy' => 'series',
                                    'field'    => 'term_id',
                                    'terms'    => [$series_id],
                                ],
                            ],
                        ]);

                        $episode_count = $related_stories->found_posts;
                    }
                ?>
                <div class="page-post-image-size-div">
                        <div class="position-relative">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('medium', [
                                        'class' => 'd-block rounded page-post-image-size',
                                    ]); ?>
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/images/no-image.jpeg"
                                            class="d-block rounded page-post-image-size"
                                            alt="Default Image">
                                <?php endif; ?>
                            </a>
                            <div class="position-absolute top-0 end-0 bg-primary-color px-2 py-1 mt-3 rounded">
                                <p class="mb-0 fw-bold" style="color: #FFEB00;">
                                    <?php echo $average_rating; ?>
                                    <i class="fa-solid fa-star ms-2" style="color: gold;"></i>
                                </p>
                            </div>

                            <?php if ($context === 'my-creations') { ?>
                                <div class="position-absolute bottom-0 end-0 px-2 py-2 mb-4 d-flex gap-2">
                                    <a 
                                        href="<?php echo esc_url( home_url( "/write?id=" . get_the_ID()) ); ?>" 
                                        class="btn btn-warning btn-sm p-1" 
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <a 
                                        href="<?php echo get_delete_post_link(get_the_ID()); ?>" 
                                        class="btn btn-danger btn-sm p-1" 
                                        title="Delete" 
                                        onclick="return confirm('இந்த படைப்பை நீக்க விரும்புகிறீர்களா?');">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </a>
                                </div>
                            <?php } ?>

                            <div class="position-absolute bottom-0 start-0 w-100">
                                <div class="d-flex align-items-center text-white gap-2" style="background: rgba(0, 0, 0, 0.5); border-radius: 0.25rem; padding: 4px 8px;">
                                    <i class="fas fa-book"></i>
                                    <span><?php echo $episode_count; ?> பாகங்கள்</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <p class="card-title fw-bold mb-1 fs-16px text-truncate">
                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-truncate text-story-title">
                                    <?php echo esc_html(get_the_title()); ?>
                                </a>
                            </p>
                            <?php
                                $author_id = get_post_field('post_author', get_the_ID());
                                $author_name = get_the_author_meta('display_name', $author_id);
                            ?>
                            <p class="fs-12px text-primary-color text-decoration-underline mb-1">
                                <a href="<?php echo site_url('/user-profile/?uid=' . $author_id); ?>">
                                    <?php echo esc_html($author_name); ?>
                                </a>
                            </p>

                            <div class="d-flex mt-1">
                                <div class="d-flex align-items-center top-0 end-0 px-2 py-1 me-1 rounded text-story-title-next">
                                    <i class="fa-solid fa-eye me-1"></i>
                                    <?php echo format_view_count($total_views); ?>
                                </div>
                                <span class="mt-1 fs-13px text-center text-story-title-next">வாசித்தவர்கள்</span>
                            </div>
                        </div>
                    </div>
            <?php endforeach; ?>
        </div>
        
	</div>
</div>

<?php get_footer(); ?>