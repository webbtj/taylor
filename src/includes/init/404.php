<?php

$smarty = wp_smarty();
get_header();

$smarty->display('pages/404.tpl');

get_footer();
