<?php
global $post;
setup_postdata($post);

$smarty = wp_smarty();
$smarty->assign('title', $post->post_title);
$smarty->assign('content', apply_filters('the_content', $post->post_content));

get_header();
$smarty->display('pages/single-[[type]].tpl');
get_footer();