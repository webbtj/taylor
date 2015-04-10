    $[[menu]] = wp_nav_menu(array(
        'theme_location' => '[[menu]]',
        'echo' => false,
        'menu_class' => '[[class]]',
        'menu_id' => '[[menu]]',
        'container' => [[container]]
    ));
    $wp_smarty->assign('menu_[[menu]]', $[[menu]]);