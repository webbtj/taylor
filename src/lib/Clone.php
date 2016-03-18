<?php

class Clone{
    public static function install(){
        $url = 'https://gitlab.com/norex/app_clone/repository/archive.zip?ref=dist';
        global $WP_CONFIG, $WP_PATH;
        $version = 'latest';

        $WP_CONFIG = $config;

        if(array_key_exists('version', $config))
            $version = $config['version'];

        $directory = './';

        if(array_key_exists('directory', $config))
            $directory = $config['directory'];

        if($version != 'latest' && strpos($version, 'wordpress-') !== 0)
            $version = 'wordpress-' . $version;

        exec("wget $url");
        exec('tar xfz ' . $version . '.tar.gz');
        exec('mkdir -p ' . $directory);
        exec('mv wordpress/* ' . $directory);
        exec('rm -rf ./wordpress/');
        exec('rm -rf ' . $version . '.tar.gz');
        exec('cp ' . $directory . '/wp-config-sample.php ' . $directory . '/wp-config.php');

        $WP_PATH = $directory;
    }
}