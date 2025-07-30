<?php
get_header(); ?>

<?php

    $context = $_GET['context'] ?? '';
    $user_id = $_GET['user_id'] ?? '';
?>

<?php get_template_part('template-parts/competition-related-stories', null, ['context' => $context, 'user_id' => $user_id]); ?>

<?php get_footer(); ?>