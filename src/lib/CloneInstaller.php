<?php

class CloneInstaller{
    public static function install($path, &$args){
        $url = 'https://github.com/joshdrink/clone/archive/master.zip';

        exec("wget -O clone-master.zip $url");
        exec("unzip clone-master.zip");
        exec("cp -r clone-master/assets $path");

        CloneInstaller::add_assets($args);
        // exit;
        exec("rm -rf clone-master");
        exec("rm -rf clone-master.zip");

    }

    public static function add_assets(&$args){

        global $taylor_path;

        $clone_path = '';

        $paths = new DirectoryIterator($taylor_path . '/');
        foreach($paths as $path){
            $pathname = $path->getFilename();
            if($path->isDir() && strpos($pathname, 'clone-master') !== false)
                $clone_path = $pathname;
        }

        $index_html = file("$taylor_path/$clone_path/index.html");

        $clones_added = false;

        if(is_array($index_html) && !empty($index_html)){

            if(!isset($args['javascripts']))
                $args['javascripts'] = array();

            if(!isset($args['styles']))
                $args['styles'] = array();

            $in_head = false;

            foreach($index_html as $line){
                if(strpos($line, '<head') !== false)
                    $in_head = true;
                if(strpos($line, '</head') !== false)
                    $in_head = false;

                $matches = array();
                preg_match('/<script(.*?)src=[\'|"](.*?)\.js[\'|"]><\/script>/', $line, $matches);
                if(!empty($matches) && $matches[2]){
                    $script = $matches[2] . '.js';
                    $args['javascripts'][] = array(
                        'path' => $script,
                        'footer' => !$in_head,
                        '_is_clone' => true
                    );
                    $clones_added = true;
                }

                $matches = array();
                preg_match('/<link(.*?)rel=[\'|"]stylesheet[\'|"](.*?)href=[\'|"](.*?)\\.css[\'|"]/', $line, $matches);
                if(!empty($matches) && $matches[3]){
                    $css = $matches[3] . '.css';
                    $style = array(
                        'path' => $css,
                        '_is_clone' => true
                    );

                    $media_matches = array();
                    preg_match('/media=[\'|"](.*?)[\'|"]/', $line, $media_matches);
                    if(!empty($media_matches) && $media_matches[1]){
                        $style['media'] = $media_matches[1];
                    }

                    $args['styles'][] = $style;
                    $clones_added = true;
                }
            }
        }

        if($clones_added){
            print "\nClone was successfully added to your theme. Your should run `npm install`, followed by `npm run clone` from `assets` within your custom theme directory.\n";
        }
    }
}
