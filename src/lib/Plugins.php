<?php

class Plugins{
    public static function install($plugins){
        foreach($plugins as $plugin){
            $name = $plugin['name'];

            $version = 'latest';
            if(isset($plugin['version']))
                $version = $plugin['version'];

            if($version == 'latest')
                $url = 'https://downloads.wordpress.org/plugin/' . $name . '.zip';
            else
                $url = 'https://downloads.wordpress.org/plugin/' . $name . '.' . $version . '.zip';

            if(isset($plugin['url']))
                $url = $plugin['url'];

            $plugin_dir = WordPress::root('/wp-content/plugins/');
            $tmp_file = $plugin_dir . $name . '.zip';

            $main_file = $name . '.php';
            if(isset($plugin['main_file']))
                $main_file = $plugin['main_file'];

            exec("wget -qO- -O $tmp_file $url && unzip $tmp_file -d $plugin_dir && rm -f $tmp_file");

            Plugins::activate($name . '/' . $main_file);

        }
    }

    public static function activate($plugin){
        require_once( WordPress::root('/wp-load.php') );
        require_once( WordPress::root('/wp-admin/includes/admin.php') );
        require_once( WordPress::root('/wp-admin/includes/upgrade.php') );

        $current = get_option( 'active_plugins' );
        $plugin = plugin_basename(trim($plugin));

        if (!in_array($plugin, $current)){
            $current[] = $plugin;
            sort($current);
            do_action('activate_plugin', trim($plugin));
            update_option('active_plugins',$current);
            do_action('activate_' . trim($plugin));
            do_action('activated_plugin', trim($plugin));
        }

        return null;
    }
}