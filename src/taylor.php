<?php

global $phar;
$phar = false;

require_once('lib/File.php');
require_once('lib/Validate.php');
require_once('lib/WordPress.php');
require_once('lib/DB.php');
require_once('lib/Plugins.php');
$t = new Taylor();

class Taylor{
    function __construct($args = null){
        $this->manifest();
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
        if(file_exists('./taylor.json')){
            $manifest = object_to_array(json_decode(file_get_contents('./taylor.json')));
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

    function init($args){
        if($args['installations']){
            $this->installation($args['installations']);
        }

        $required_params = array('theme_dir', 'project_name');
        Validate($required_params, $args);
        
        WordPress::theme_dir($args['theme_dir']);

        if(!File::exists(WordPress::path())){
            if(!mkdir(WordPress::path()))
                throw new Exception("Could not create theme directory", 1);
        }

        $directories = array(
            'custom_fields', 'post_types', 'templates', 'templates/blocks',
            'templates/includes', 'templates/pages', 'templates/panels');
        foreach($directories as $directory)
            mkdir(WordPress::path($directory), 0755);

        mkdir(WordPress::path('templates_c'), 0777);
        chmod(WordPress::path('templates_c'), 0777);

        File::write(WordPress::path('templates_c/.gitignore'), "*\n!.gitignore");

        File::copy('includes/init/footer.php', 'footer.php');
        File::copy('includes/init/functions.php', 'functions.php');
        File::copy('includes/init/header.php', 'header.php', $args);
        File::copy('includes/init/home.php', 'home.php');
        File::copy('includes/init/index.php', 'index.php');
        File::copy('includes/init/page.php', 'page.php');
        File::copy('includes/init/single.php', 'single.php');
        File::copy('includes/init/page-home.php', 'page-home.php');
        File::copy('includes/init/404.php', '404.php');
        File::copy('includes/init/style.css', 'style.css', $args);

        File::copy('includes/init/header.tpl', 'templates/includes/header.tpl');
        File::copy('includes/init/footer.tpl', 'templates/includes/footer.tpl');
        File::copy('includes/init/page-home.tpl', 'templates/pages/page-home.tpl');
        File::copy('includes/init/page.tpl', 'templates/pages/page.tpl');
        File::copy('includes/init/404.tpl', 'templates/pages/404.tpl');
        File::copy('includes/init/single.tpl', 'templates/pages/single.tpl');

        if($args['styles'])
            $this->add_assets($args['styles'], 'css');

        if($args['javascripts'])
            $this->add_assets($args['javascripts'], 'js');

        if($args['menus'])
            $this->add_menus($args['menus'], 'js');

        require_once( WordPress::root('/wp-load.php') );
        require_once( WordPress::root('/wp-admin/includes/admin.php') );
        require_once( WordPress::root('/wp-admin/includes/upgrade.php') );
        switch_theme(WordPress::theme_dir());
    }

    function installation($software){
        if(array_key_exists('wordpress', $software))
            WordPress::install($software['wordpress']);
        if(array_key_exists('database', $software))
            DB::install($software['database']);
        if(array_key_exists('wordpress', $software) && array_key_exists('database', $software))
            DB::initialize($software['wordpress']);
        if(array_key_exists('plugins', $software))
            Plugins::install($software['plugins']);
    }

    

    function create_post_type($args){
        $required_params = array('type', 'plural', 'singular', 'slug');
        Validate($required_params, $args);

        extract($args);

        $output = File::read('includes/post-type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('post_types/' . $type . '.php');
        File::init($filename);

        File::append($filename, $output);

        File::append(WordPress::path('functions.php'), "\nrequire_once('post_types/$type.php');\n");

        if($templates)
            $this->create_templates($args);
    }

    function post_to_post($args){
        $required_params = array('from', 'to');
        Validate($required_params, $args);

        extract($args);

        if(!File::exists(WordPress::path('includes/')))
            mkdir(WordPress::path('includes/'), 0755, true);

        $filename = WordPress::path('includes/posts-to-posts.php');
        if(!File::exists($filename)){
            $output = File::read('includes/posts-to-posts.php');
            File::init($filename);
            File::append($filename, $output);
            File::append(WordPress::path('functions.php'), "\nrequire_once('includes/posts-to-posts.php');\n");
        }

        $output = File::read('includes/post-to-post.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }
        $p2p_file = File::read($filename, true);
        $p2p_file = str_replace('//P2P Connections', $output, $p2p_file);
        File::write($filename, $p2p_file);

        //
        $output = File::read('includes/related-post.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }
        $from_output = str_replace('[[opposite]]', $to, $output);
        $to_output = str_replace('[[opposite]]', $from, $output);

        $single_from = 'single-' . $from;
        $single_to = 'single-' . $to;
        if($from == 'post')
            $single_from = 'single';
        if($to == 'post')
            $single_to = 'single';

        $from_posttype = File::read(WordPress::path($single_from . '.php'), true);
        $from_output = str_replace('get_header();', "get_header();\n" . $from_output, $from_posttype);
        File::write(WordPress::path($single_from . '.php'), $from_output);

        $to_posttype = File::read(WordPress::path($single_to . '.php'), true);
        $to_output = str_replace('get_header();', "get_header();\n" . $to_output, $to_posttype);
        File::write(WordPress::path($single_to . '.php'), $to_output);

        //
        $output = File::read('includes/related-post.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }
        $from_output = str_replace('[[opposite]]', $to, $output);
        $to_output = str_replace('[[opposite]]', $from, $output);

        $single_from = 'single-' . $from;
        $single_to = 'single-' . $to;
        if($from == 'post')
            $single_from = 'single';
        if($to == 'post')
            $single_to = 'single';

        $from_template = File::read(WordPress::path('templates/pages/' . $single_from . '.tpl'), true);
        $from_output = str_replace('{$content}', "{\$content}\n" . $from_output, $from_template);
        File::write(WordPress::path('templates/pages/' . $single_from . '.tpl'), $from_output);

        $to_template = File::read(WordPress::path('templates/pages/' . $single_to . '.tpl'), true);
        $to_output = str_replace('{$content}', "{\$content}\n" . $to_output, $to_template);
        File::write(WordPress::path('templates/pages/' . $single_to . '.tpl'), $to_output);
    }

    function create_taxonomy($args){
        $required_params = array('type', 'plural', 'singular', 'taxonomy', 'hierarchical');
        Validate($required_params, $args);

        extract($args);

        $output = File::read('includes/taxonomy.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('post_types/' . $type . '.php');
        File::init($filename);

        File::append($filename, $output);
    }

    function create_templates($args){
        $required_params = array('type', 'index_name');

        if(!isset($args['index_name']) && $args['plural'])
            $args['index_name'] = $args['plural'];

        Validate($required_params, $args);
        extract($args);
        $output = File::read('includes/page-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('page-' . $type . '.php');
        File::append($filename, $output);

        $output = File::read('includes/single-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('single-' . $type . '.php');
        File::append($filename, $output);

        $output = File::read('includes/page-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('templates/pages/page-' . $type . '.tpl');
        File::append($filename, $output);

        $output = File::read('includes/single-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = WordPress::path('templates/pages/single-' . $type . '.tpl');
        File::append($filename, $output);
    }

    function add_assets($assets, $type='js'){
        $includes = array();
        foreach($assets as &$asset){
            if(filter_var($asset['path'], FILTER_VALIDATE_URL))
                $is_url = true;
            else
                $is_url = false;

            $media = null;
            if(isset($asset['media']))
                $media = $asset['media'];

            $is_copy = true;
            if(isset($asset['copy']) && $asset['copy'] == false)
                $is_copy = false;

            //if it is a local file it MUST be copied, we're not going to reference local files outside the theme
            if(!$is_url && strpos($asset['path'], '//') !== 0 )
                $is_copy = true;

            if($is_copy && $is_url){
                //copy from remote url
                $context=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );

                $contents = file_get_contents($asset['path'], false, stream_context_create($context));
                $path = WordPress::path();
                if($asset['asset_path'])
                    $path .= '/' . $asset['asset_path'];
                $path .= parse_url($asset['path'], PHP_URL_PATH);
                if(!File::exists(dirname($path)))
                    mkdir(dirname($path), 0755, true);
                File::write($path, $contents);

                $includes[] = array(
                    'file' => str_replace(WordPress::path(), '', $path),
                    'footer' => (bool) isset($asset['footer']),
                    'handle' => $this->asset_handle($path),
                    'reset_jquery' => $this->asset_handle($path) == 'jquery',
                    'local' => true,
                    'media' => $media
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
                        for($i = 0; $i < (int)$asset['drop_root']; $i++)
                            array_shift($parts);
                        $destination = implode('/', $parts);
                    }
                    if($asset['asset_path'])
                        $destination = $asset['asset_path'] . '/' . $destination;
                    $destination = WordPress::path($destination);

                    if(!File::exists(dirname($destination)))
                        mkdir(dirname($destination), 0755, true);

                    File::write($destination, file_get_contents($source));

                    $includes[] = array(
                        'file' => str_replace(WordPress::path(), '', $destination),
                        'footer' => (bool) $asset['footer'],
                        'handle' => $this->asset_handle($destination),
                        'reset_jquery' => $this->asset_handle($destination) == 'jquery',
                        'local' => true,
                        'media' => $media
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
                    'media' => $media
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
                $output .= "\twp_enqueue_style('$handle', $file, array(), ASSET_VERSION $media);\n";
        }

        if($output){
            $output = "\n$output";
            if($type == 'js'){
                $file_output = File::read('includes/init/enqueue_js.php');
                $file_output = str_replace('[[js_output]]', $output, $file_output);
            }
            elseif($type == 'css'){
                $file_output = File::read('includes/init/enqueue_css.php');
                $file_output = str_replace('[[css_output]]', $output, $file_output);
            }

            $filename = WordPress::path('functions.php');
            File::append($filename, $file_output);
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
                    
                    $content = File::read('includes/init/load_menus.php');
                    $content = str_replace('[[menu]]', $location, $content);
                    $content = str_replace('[[class]]', $class, $content);
                    $content = str_replace('[[container]]', $container, $content);

                    $menu_output .= "$content\n\n";

                    $content = File::read('includes/init/register_menus.php');
                    $content = str_replace('[[menu]]', $location, $content);
                    $content = str_replace('[[name]]', $name, $content);

                    $register_output .= "$content\n";
                }
            }
        }

        $filename = WordPress::path('functions.php');
        File::append($filename, $register_output);

        $functions = file_get_contents($filename);
        $functions = str_replace('//Load Menus', $menu_output, $functions);
        File::write($filename, $functions);
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
        if(!isset($this->find_file_path) || !$this->find_file_path)
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