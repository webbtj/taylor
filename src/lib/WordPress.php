<?php

class WordPress{
    public static function path($file = null){
        global $PATH, $_PATH;
        if(!$PATH){
            WordPress::set_path();
            $PATH = $_PATH;
        }
        if(!$file)
            return $PATH;

        $path = $PATH . '/' . $file;
        $path = str_replace('//', '/', $path);
        return $path;
    }

    public static function root($file = null){
        global $WP_PATH;
        if(!$WP_PATH)
            WordPress::set_path();

        if(!$file)
            return $WP_PATH;

        $path = $WP_PATH . '/' . $file;
        $path = str_replace('//', '/', $path);
        return $path;
    }

    public static function set_path(){
        global $_PATH, $PATH, $WP_PATH;

        if(isset($_PATH)){
            $_PATH = dirname($_PATH);
        }
        else{
            $phar_running = Phar::running(false);
            if($phar_running)
                $_PATH = dirname($phar_running);
            else
                $_PATH = dirname(dirname(__FILE__));

            if(isset($WP_PATH)){
                $_PATH .= '/' . $WP_PATH;
            }
        }

        if($_PATH == '/')
            throw new Exception("Could not find WordPress theme directory", 1);
        
        if(!file_exists($_PATH . '/wp-content'))
            WordPress::set_path();
        else{
            $_PATH .= '/wp-content/themes/' . WordPress::theme_dir();
        }
    }

    public static function theme_dir($set = false){
        global $WP_THEME_DIR;
        if($set)
            $WP_THEME_DIR = $set;
        return $WP_THEME_DIR;
    }

    public static function install($config){
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

        exec('wget http://wordpress.org/' . $version . '.tar.gz');
        exec('tar xfz ' . $version . '.tar.gz');
        exec('mkdir -p ' . $directory);
        exec('mv wordpress/* ' . $directory);
        exec('rm -rf ./wordpress/');
        exec('rm -rf ' . $version . '.tar.gz');
        exec('cp ' . $directory . '/wp-config-sample.php ' . $directory . '/wp-config.php');

        $WP_PATH = $directory;
    }

    public static function update_config_file($config){
        global $WP_CONFIG, $WP_PATH;

        extract($config);

        if($WP_PATH){
            $wp_config = $WP_PATH . '/wp-config.php';

            $patterns = array(
                '/define\\(\'DB_NAME\', \'.*\'\\);/',
                '/define\\(\'DB_USER\', \'.*\'\\);/',
                '/define\\(\'DB_PASSWORD\', \'.*\'\\);/',
                '/define\\(\'DB_HOST\', \'.*\'\\);/',
            );

            $replacements = array(
                'define(\'DB_NAME\', \'' . $database . '\');',
                'define(\'DB_USER\', \'' . $username . '\');',
                'define(\'DB_PASSWORD\', \'' . $password . '\');',
                'define(\'DB_HOST\', \'' . $host . '\');',
            );

            if(isset($WP_CONFIG['salt']) && (bool) $WP_CONFIG['salt'] === false){

            }else{
                $salts = fopen('https://api.wordpress.org/secret-key/1.1/salt/', 'r');
                if($salts){
                    while (($line = fgets($salts)) !== false) {
                        $replacements[] = trim($line);
                    }
                    fclose($salts);
                }else{
                    throw new Exception("Could not generate salts for WP config file.", 1);
                }
                
                $patterns[] = '/define\\(\'AUTH_KEY\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'SECURE_AUTH_KEY\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'LOGGED_IN_KEY\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'NONCE_KEY\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'AUTH_SALT\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'SECURE_AUTH_SALT\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'LOGGED_IN_SALT\',\s+\'.*\'\\);/';
                $patterns[] = '/define\\(\'NONCE_SALT\',\s+\'.*\'\\);/';
            }

            $config = file_get_contents($wp_config);
            $config = preg_replace($patterns, $replacements, $config);

            File::write($wp_config, $config);
        }
    }
}