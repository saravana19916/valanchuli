<?php
    get_header();
?>

<div class="container my-4">
<?php
$series = get_queried_object();
echo "<h1>{$series->name}</h1>";

$stories = new WP_Query([
    'post_type' => 'story',
    'posts_per_page' => -1,
    'tax_query' => [
        [
            'taxonomy' => 'series',
            'field' => 'term_id',
            'terms' => $series->term_id,
        ],
    ],
]);

if ($stories->have_posts()) {
    echo "<ul>";
    while ($stories->have_posts()) {
        $stories->the_post();
        echo "<li><a href='" . get_permalink() . "'>" . get_the_title() . "</a></li>";
    }
    echo "</ul>";
    wp_reset_postdata();
}
?>

</div>

<?php get_footer(); ?>