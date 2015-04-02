<?php

parse_str(implode('&', array_slice($argv, 1)), $args);

$t = new Taylor($args);

class Taylor{
    function __construct($args){
        $function = key($args);
        array_shift($args);
        $this->do_function($function, $args);
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
            file_put_contents($filename, file_get_contents($header_filename));
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
            $this->path = dirname(__FILE__);

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
        $output = file_get_contents($from);
        if(!empty($params)){
            foreach($params as $param => $value){
                $output = str_replace("[[$param]]", $value, $output);
            }
        }
        $filename = $this->file_path($to);
        file_put_contents($filename, $output);
    }

    function init($args){
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
        $this->copy_file('includes/init/header.php', 'header.php');
        $this->copy_file('includes/init/home.php', 'home.php');
        $this->copy_file('includes/init/index.php', 'index.php');
        $this->copy_file('includes/init/page-home.php', 'page-home.php');
        $this->copy_file('includes/init/style.css', 'style.css', $args);

        $this->copy_file('includes/init/header.tpl', 'templates/includes/header.tpl');
        $this->copy_file('includes/init/footer.tpl', 'templates/includes/footer.tpl');
        $this->copy_file('includes/init/page-home.tpl', 'templates/pages/page-home.tpl');
    }

    function create_post_type($args){
        $required_params = array('type', 'plural', 'singular', 'slug');
        $this->check_requirements($required_params, $args);
        $this->set_constants($args);

        extract($args);

        $output = file_get_contents('includes/post-type.php');
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

        $output = file_get_contents('includes/taxonomy.php');
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
        $output = file_get_contents('includes/page-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('page-' . $type . '.php');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = file_get_contents('includes/single-post_type.php');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('single-' . $type . '.php');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = file_get_contents('includes/page-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('templates/pages/page-' . $type . '.tpl');
        file_put_contents($filename, $output, FILE_APPEND);

        $output = file_get_contents('includes/single-post_type.tpl');
        foreach($required_params as $param){
            $output = str_replace("[[$param]]", $$param, $output);
        }

        $filename = $this->file_path('templates/pages/single-' . $type . '.tpl');
        file_put_contents($filename, $output, FILE_APPEND);
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