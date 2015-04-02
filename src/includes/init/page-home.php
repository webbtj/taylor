<?php
/*
 * Template Name: Homepage
 */

global $post;
$smarty = wp_smarty();

get_header();

$smarty->display('pages/page-home.tpl');

get_footer();
