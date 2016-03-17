<?php

function wp_smarty(){
    global $wp_smarty;
    if($wp_smarty)
        return $wp_smarty;

    $wp_smarty = smarty_get_instance();

    /* ALWAYS REMEMEBER TO RESET QUERY!!! */

    //Load Menus

    //Load social meta

    //Load rich snippet meta

    //Load title

    return $wp_smarty;
}

function get_sized_image_src($image, $size='full'){
    if(is_array($image)){
        if(isset($image['id']) && is_numeric($image['id']))
            $image_id = $image['id'];
        if(isset($image['sizes']) && is_array($image['sizes'])){
            if(isset($image['sizes'][$size]))
                return $image['sizes'][$size];
        }
        if($image_id)
            $image = $image_id;
        else
            return '';
    }
    if(is_string($image))
        return $image;
    if($image){
        $image = wp_get_attachment_image_src($image, $size);
        if(!empty($image))
            $image = $image[0];
        else
            $image = false;
    }
    else
        $image = false;

    return $image;
}

//requires posts to posts plugin
function get_related_posts($relationship, $post_id = null, $additional_args = array(), $custom_fields = array()){
    global $post;
    
    if(!$post_id)
        $post_id = $post->ID;

    $old_post = $post;

    $args = array(
      'connected_type' => $relationship,
      'connected_items' => $post_id,
      'nopaging' => true
    );

    $args = array_merge($args, $additional_args);

    $connected = new WP_Query($args);

    $posts = array();
    while($connected->have_posts()){
        $connected->the_post();
        $connected->post->url = get_permalink($connected->post->ID);
        if(!empty($custom_fields)){
            foreach($custom_fields as $field){
                $connected->post->$field = get_field($field, $connected->post->ID);
            }
        }
        $posts[] = $connected->post;
    }

    wp_reset_query();
    $post = $old_post;

    return $posts;
}

function get_child_posts($post_type, $post_id = null, $additional_args = array(), $custom_fields = array()){
    global $post;

    if(!$post_id)
        $post_id = $post->ID;

    $old_post = $post;

    $args = array(
        'post_parent' => $post_id,
        'post_type' => $post_type,
        'nopaging' => true
    );

    $args = array_merge($args, $additional_args);

    $children = get_children($args);

    foreach($children as &$child){
        $child->url = get_permalink($child->ID);
        if(!empty($custom_fields)){
            foreach($custom_fields as $field){
                $child->$field = get_field($field, $child->ID);
            }
        }
    }

    wp_reset_query();
    $post = $old_post;

    return $children;
}

function get_attachment_url($attachment){
    if(is_array($attachment) && isset($attachment['url']))
        return $attachment['url'];
    if(is_string($attachment))
        return $attachment;
    if(is_numeric($attachment))
        return wp_get_attachment_url($attachment);
    return null;
}

function get_post_objects($object, $custom_fields = array()){
    if(!$object)
        return null;
    $posts = array();
    $object_is_array = is_array($object);
    if($object_is_array)
        $posts = $object;
    else
        $posts[] = $object;

    foreach($posts as &$p){
        if(is_numeric($p))
            $p = get_post($p);
        $p->url = get_permalink($p);
        if(!empty($custom_fields)){
            foreach($custom_fields as $field){
                $p->$field = get_field($field, $p->ID);
            }
        }
    }

    if($object_is_array)
        return $posts;
    if(!empty($posts))
        return $posts[0];
    return null;
}