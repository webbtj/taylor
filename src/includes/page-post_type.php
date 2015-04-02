<?php
/**
* Template Name: [[index_name]]
**/

$smarty = wp_smarty();

$smarty->assign('title', $post->post_title);
$smarty->assign('content', apply_filters('the_content', $post->post_content));

$args = array(
    'post_type' => '[[type]]',
    'posts_per_page' => 10
);

$[[type]] = array();
$[[type]]_query = new WP_Query($args);
if($[[type]]_query->have_posts()){
    while($[[type]]_query->have_posts()){
        $[[type]]_query->the_post();
        $[[type]]_query->post->permalink = get_permalink($[[type]]_query->post->ID);
        $[[type]][] = $[[type]]_query->post;
    }
}
wp_reset_query();

$smarty->assign('[[type]]', $[[type]]);

get_header();
$smarty->display('pages/page-[[type]].tpl');
get_footer();