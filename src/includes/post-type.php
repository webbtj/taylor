
add_action('init', 'taylor_register_[[type]]');
function taylor_register_[[type]]() {
    register_post_type('[[type]]', array(
            'labels' => array(
                'name' => '[[plural]]',
                'singular_name' => '[[singular]]'
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'rewrite' => array('slug' => '[[slug]]'),
            'supports' => array('title', 'editor', 'revisions')
        )
    );
}
