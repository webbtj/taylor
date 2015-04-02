<?php

function wp_smarty(){
    global $wp_smarty;
    if($wp_smarty)
        return $wp_smarty;

    $wp_smarty = smarty_get_instance();

    /* ALWAYS REMEMEBER TO RESET QUERY!!! */

    //Load Sidebar and Menu Data

    //Load Extra Footer Links (menu and/or social)

    //Load social meta

    //Load rich snippet meta

    //Load title

    return $wp_smarty;
}