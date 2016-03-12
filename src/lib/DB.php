<?php

class DB{
    public static function install($config){
        global $WP_PATH;

        $required_params = array('host', 'username', 'password', 'database');
        Validate($required_params, $config);

        extract($config);

        if(isset($admin_user) && isset($admin_pass)){
            $connection = new mysqli($host, $admin_user, $admin_pass);
            if($connection->connect_error)
                throw new Exception("Error connecting to the DB as admin user: " . $connection->connect_error, 1);

            $connection->query("CREATE DATABASE IF NOT EXISTS $database");
            if($connection->error)
                throw new Exception("MySQL Error: " . $connection->connect_error, 1);

            $connection->query("GRANT ALL PRIVILEGES ON $database.* TO '$username' IDENTIFIED BY '$password' ");
            if($connection->error)
                throw new Exception("MySQL Error: " . $connection->connect_error, 1);

            $connection->close();
        }else{
            $connection = new mysqli($host, $username, $password);
            if($connection->connect_error)
                throw new Exception("Error connecting to the DB as WP user: " . $connection->connect_error, 1);

            $connection->query("CREATE DATABASE IF NOT EXISTS $database");
            if($connection->error)
                throw new Exception("MySQL Error: " . $connection->connect_error, 1);

            $connection->close();
        }

        if(WordPress::root()){
            WordPress::update_config_file($config);
        }
    }

    public static function initialize($config){
        $required_params = array('site_title', 'domain', 'admin_username', 'admin_email', 'admin_password');
        $do_wp_install = false;
        foreach($required_params as $param)
            if(array_key_exists($param, $config))
                $do_wp_install = true;

        if($do_wp_install){
            Validate($required_params, $config);
            extract($config);

            if(!isset($public))
                $public = true;

            ob_start();
            define('WP_SITEURL', $domain);
            require_once( WordPress::root('/wp-admin/install.php') );
            $install = wp_install($site_title, $admin_username, $admin_email, $public, '', $admin_password);
            ob_end_clean();
        }
    }
}