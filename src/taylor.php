<?php

parse_str(implode('&', array_slice($argv, 1)), $args);

$t = new Taylor($args);

class Taylor{
    function __construct($args){
        $function = key($args);
        array_shift($args);
        $this->do_function($function, $args);
    }

    function file_get_contents($path){
        $phar = false;
        if($phar)
            return file_get_contents('phar://taylor.phar/' . $path);

        return file_get_contents($path);

    }

    function do_function($function, $args){
        if(method_exists($this, $function))
            $this->$function($args);
        elseif($function)
            throw new Exception("`$function` is not a function", 1);
        else
            throw new Exception("No function provided", 1);
    }

    function manifest($args = null){
        if(file_exists('./taylor.manifest.json')){
            $manifest = object_to_array(json_decode(file_get_contents('./taylor.manifest.json')));
            if(empty($manifest) || empty($manifest['commands']))
                throw new Exception("Mainfest file empty or invalid", 1);

            foreach($manifest['commands'] as $command){
                if(empty($command))
                    throw new Exception("Mainfest file empty or invalid", 1);
                foreach($command as $function => $args){
                    $this->do_function($function, $args);
                }
            }
        }
        else
            throw new Exception("Mainfest file not found", 1);
    }

    function init_file($filename, $header_filename){
        if(!file_exists($filename))
            file_put_contents($filename, $this->file_get_contents($header_filename));
    }

    function set_constants($args){
        $constants = array('theme_dir', 'project_name');
        foreach($constants as $constant){
            if(array_key_exists($constant, $args))
                $this->$constant = $args[$constant];
        }
    }

    function set_path(){
        if($this->path)
            $this->path = dirname($this->path);
        else
            $this->path = Phar::running(false) . dirname(__FILE__);

        if($this->path == '/')
            throw new Exception("Could not find WordPress theme directory", 1);
        
        if(!file_exists($this->path . '/wp-content'))
            $this->set_path();
        else{
            $this->path .= '/wp-content/themes/' . $this->theme_dir;
        }
    }

    function get_path(){
        if(!$this->path)
            $this->set_path();
        return $this->path;
    }

    function file_path($file){
        return $this->get_path() . '/' . $file;
    }

    function check_requirements($required_params, $args){
        $diff = array_diff($required_params, array_keys($args));
        if(!empty($diff))
            throw new Exception("The following parameters are missing: " . implode(", ", $diff), 1);
    }

    function copy_file($from, $to, $params = null){
        $output = $this->file_get_contents($from);
        if(!empty($params)){
            foreach($params as $param => $value){
                $output = str_replace("[[$param]]", $value, $output);
            }
        }
        $filename = $this->file_path($to);
        file_put_contents($filename, $output);
    }

    function init($args){
        if($args['installations']){
            $this->installation($args['installations']);
        }
        $required_params = array('theme_dir', 'project_name');
        $this->check_requirements($required_params, $args);
        $this->set_constants($args);

        if(!file_exists($this->get_path())){
            if(!mkdir($this->get_path()))
                throw new Exception("Could not create theme directory", 1);
        }

        $directories = array(
            'custom_fields', 'post_types', 'templates', 'templates/blocks',
            'templates/includes', 'templates/pages', 'templates/panels');
        foreach($directories as $directory)
            mkdir($this->file_path($directory, 0755));

        mkdir($this->file_path('templates_c', 0777));

        file_put_contents($this->file_path('templates_c/.gitignore'), "*\n!.gitignore");

        $this->copy_file('includes/init/footer.php', 'footer.php');
        $this->copy_file('includes/init/functions.php', 'functions.php');
        $this->copy_file('includes/init/header.php', 'header.php', $args);
        $this->copy_file('includes/init/home.php', 'home.php');
        $this->copy_file('includes/init/index.php', 'index.php');
        $this->copy_file('includes/init/page-home.php', 'page-home.php');
        $this->copy_file('includes/init/style.css', 'style.css', $args);

        $this->copy_file('includes/init/header.tpl', 'templates/includes/header.tpl');
        $this->copy_file('includes/init/footer.tpl', 'templates/includes/footer.tpl');
        $this->copy_file('includes/init/page-home.tpl', 'templates/pages/page-home.tpl');

        if($args['styles'])
            $this->add_assets($args['styles'], 'css');

        if($args['javascripts'])
            $this->add_assets($args['javascripts'], 'js');

        if($args['menus'])
            $this->add_menus($args['menus'], 'js');
    }

    function installation($software){
        if(array_key_exists('wordpress', $software)){

            $version = 'latest';

            if(array_key_exists('version', $software['wordpress']))
                $version = $software['wordpress']['version'];

            $directory = './';

            if(array_key_exists('directory', $software['wordpress']))
                $directory = $software['wordpress']['directory'];

            if($version != 'latest' && strpos($version, 'wordpress-') !== 0)
                $version = 'wordpress-' . $version;
            exec('wget http://wordpress.org/' . $version . '.tar.gz');
            exec('tar xfz ' . $version . '.tar.gz');
            exec('mkdir -p ' . $directory);
            exec('mv wordpress/* ' . $directory);
            exec('rm -rf ./wordpress/');
            exec('rm -rf ' . $version . '.tar.gz');
        }
        exit;
    }

    function create_post_type($args){
        $required_params = array('type', 'plural', 'singular', 'slug');
        $this->check_requirements($required_params, $args);
        $this->set_constants($args);

        extract($args);

        $output = $this->file_get_contents('includes/post-type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('post_types/' . $type . '.php');
        $this->init_file($filename, 'includes/php-header.php');

        file_put_contents($filename, $output, FILE_APPEND);

        file_put_contents($this->file_path('functions.php'), "\nrequire_once('post_types/$type.php');\n", FILE_APPEND);

        if($templates)
            $this->create_templates($args);
    }

    function create_taxonomy($args){
        $required_params = array('type', 'plural', 'singular', 'taxonomy', 'hierarchical');
        $this->check_requirements($required_params, $args);
        $this->set_constants($args);

        extract($args);

        $output = $this->file_get_contents('includes/taxonomy.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('post_types/' . $type . '.php');
        $this->init_file($filename, 'includes/php-header.php');

        file_put_contents($filename, $output, FILE_APPEND);
    }

    function create_templates($args){
        $required_params = array('type', 'index_name');

        if(!$args['index_name'] && $args['plural'])
            $args['index_name'] = $args['plural'];

        $this->check_requirements($required_params, $args);
        extract($args);
        $output = $this->file_get_contents('includes/page-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('page-' . $type . '.php');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = $this->file_get_contents('includes/single-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('single-' . $type . '.php');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = $this->file_get_contents('includes/page-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('templates/pages/page-' . $type . '.tpl');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = $this->file_get_contents('includes/single-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('templates/pages/single-' . $type . '.tpl');
        file_put_contents($filename, $output, FILE_APPEND);
    }

    function add_assets($assets, $type='js'){
        $includes = array();
        foreach($assets as &$asset){
            if(filter_var($asset['path'], FILTER_VALIDATE_URL))
                $is_url = true;
            else
                $is_url = false;

            $is_copy = true;
            if(isset($asset['copy']) && $asset['copy'] == false)
                $is_copy = false;

            //if it is a local file it MUST be copied, we're not going to reference local files outside the theme
            if(!$is_url && strpos($asset['path'], '//') !== 0 )
                $is_copy = true;

            if($is_copy && $is_url){
                //copy from remote url
                $contents = file_get_contents($asset['path']);
                $path = $this->get_path();
                if($asset['asset_path'])
                    $path .= '/' . $asset['asset_path'];
                $path .= parse_url($asset['path'], PHP_URL_PATH);
                if(!file_exists(dirname($path)))
                    mkdir(dirname($path), 0755, true);
                file_put_contents($path, $contents);

                $includes[] = array(
                    'file' => str_replace($this->get_path(), '', $path),
                    'footer' => (bool) $asset['footer'],
                    'handle' => $this->asset_handle($path),
                    'reset_jquery' => $this->asset_handle($path) == 'jquery',
                    'local' => true,
                    'media' => $asset['media']
                );
            }elseif($is_copy){
                //copy from local file system
                $source = $this->find_file($asset['path']);

                if(!$source){
                    print "\nWarning! Asset file `$source` not found!\n";
                }else{
                    $destination = $asset['path'];
                    if($asset['drop_root']){
                        $parts = explode('/', $destination);
                        array_shift($parts);
                        $destination = implode('/', $parts);
                    }
                    if($asset['asset_path'])
                        $destination = $asset['asset_path'] . '/' . $destination;
                    $destination = $this->get_path() . '/' . $destination;

                    if(!file_exists(dirname($destination)))
                        mkdir(dirname($destination), 0755, true);

                    file_put_contents($destination, file_get_contents($source));

                    $includes[] = array(
                        'file' => str_replace($this->get_path(), '', $destination),
                        'footer' => (bool) $asset['footer'],
                        'handle' => $this->asset_handle($destination),
                        'reset_jquery' => $this->asset_handle($destination) == 'jquery',
                        'local' => true,
                        'media' => $asset['media']
                    );
                }

            }elseif($is_url || strpos($asset['path'], '//') === 0){
                //use cdn
                $includes[] = array(
                    'file' => $asset['path'],
                    'footer' => (bool) $asset['footer'],
                    'handle' => $this->asset_handle($asset['path']),
                    'reset_jquery' => $this->asset_handle($asset['path']) == 'jquery',
                    'local' => false,
                    'media' => $asset['media']
                );
            }else{
                //technically shouldn't happen
            }

        }
        $output = "";
        
        foreach($includes as $include){
            $handle = $include['handle'];
            $file = "'" . $include['file'] . "'";
            if($include['local'])
                $file = "get_bloginfo('stylesheet_directory') . " . $file;
            $footer = (int) $include['footer'];
            $media = '';
            if($include['media'] && $type == 'css')
                $media = ", '$media'";

            if($include['reset_jquery'] && $type == 'js')
                $output .= "\twp_deregister_script('jquery');\n";

            if($type == 'js')
                $output .= "\twp_enqueue_script('$handle', $file, array(), ASSET_VERSION, $footer);\n";
            elseif($type == 'css')
                $output .= "\twp_enqueue_style('$handle', $file, array(), ASSET_VERSION $meida);\n";
        }

        if($output){
            $output = "\n$output";
            if($type == 'js'){
                $file_output = $this->file_get_contents('includes/init/enqueue_js.php');
                $file_output = str_replace('[[js_output]]', $output, $file_output);
            }
            elseif($type == 'css'){
                $file_output = $this->file_get_contents('includes/init/enqueue_css.php');
                $file_output = str_replace('[[css_output]]', $output, $file_output);
            }

            $filename = $this->file_path('functions.php');
            file_put_contents($filename, $file_output, FILE_APPEND);
        }
    }

    function add_menus($menus){
        $menu_output = "//Load Menus\n";
        $register_output = "\n\n//Register Menus\n";
        foreach($menus as $menu){
            if(!empty($menu)){
                foreach($menu as $location => $properties){

                    $class = isset($properties['class']) ? $properties['class'] : '';
                    $container = isset($properties['container']) ? $properties['container'] : 'false';
                    if(isset($properties['name']))
                        $name = $properties['name'];
                    else
                        $name = ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $location));
                    
                    $content = $this->file_get_contents('includes/init/load_menus.php');
                    $content = str_replace('[[menu]]', $location, $content);
                    $content = str_replace('[[class]]', $class, $content);
                    $content = str_replace('[[container]]', $container, $content);

                    $menu_output .= "$content\n\n";

                    $content = $this->file_get_contents('includes/init/register_menus.php');
                    $content = str_replace('[[menu]]', $location, $content);
                    $content = str_replace('[[name]]', $name, $content);

                    $register_output .= "$content\n";
                }
            }
        }

        $filename = $this->file_path('functions.php');
        file_put_contents($filename, $register_output, FILE_APPEND);

        $functions = file_get_contents($filename);
        $functions = str_replace('//Load Menus', $menu_output, $functions);
        file_put_contents($filename, $functions);
    }

    function asset_handle($file_name){
        $parts = explode('/', $file_name);
        $file = array_pop($parts);
        $parts = explode('.', $file);
        $slug = $parts[0];
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-zA-Z0-9]/', '', $slug);
        return $slug;
    }

    function find_file($path){
        if(!$this->find_file_path)
            $this->find_file_path = Phar::running(false) . dirname(__FILE__);

        if(!file_exists($this->find_file_path . '/' . $path)){

            if($this->find_file_path == '/'){
                $this->find_file_path = null;
                return false;
            }else{
                $this->find_file_path = dirname($this->find_file_path);
                return $this->find_file($path);
            }

        }else{
            return $this->find_file_path . '/' . $path;
        }
    }
}

function object_to_array($obj) {
    if(is_object($obj)) $obj = (array) $obj;
    if(is_array($obj)) {
        $new = array();
        foreach($obj as $key => $val) {
            $new[$key] = object_to_array($val);
        }
    }
    else $new = $obj;
    return $new;       
}