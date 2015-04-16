
add_action('init', 'taylor_taxonomy_register_[[type]]_[[taxonomy]]');
function taylor_taxonomy_register_[[type]]_[[taxonomy]]() {

    register_taxonomy('[[taxonomy]]',
        '[[type]]',
        array(
            'hierarchical' => [[hierarchical]],
            'labels' => array(
                'name'              => '[[plural]]',
                'singular_name'     => '[[singular]]',
                'search_items'      => 'Search [[plural]]',
                'all_items'         => 'All [[plural]]',
                'parent_item'       => 'Parent [[singular]]',
                'parent_item_colon' => 'Parent [[singular]]',
                'edit_item'         => 'Edit [[singular]]',
                'update_item'       => 'Update [[singular]]',
                'add_new_item'      => 'Add New [[singular]]',
                'new_item_name'     => 'New [[singular]] Name',
                'menu_name'         => '[[singular]]'
            ),
            'show_in_nav_menus' => false
        )
    );
}
