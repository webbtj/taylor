<?php

class File{
    public static function read($path, $absolute=false){
        global $phar;

        if($phar && !$absolute)
            return file_get_contents('phar://taylor.phar/' . $path);

        return file_get_contents($path);
    }

    public static function write($filename, $content){
        file_put_contents($filename, $content);
    }

    public static function append($filename, $content){
        file_put_contents($filename, $content, FILE_APPEND);
    }

    public static function exists($filename){
        return file_exists($filename);
    }

    public static function copy($from, $to, $params = null){
        $output = File::read($from);
        if(!empty($params)){
            foreach($params as $param => $value){
                if(is_string($value))
                    $output = str_replace("[[$param]]", $value, $output);
            }
        }
        File::write(WordPress::path($to), $output);
    }

    public static function init($file){
        if(!File::exists($file))
            File::write($file, File::read('includes/php-header.php'));
    }
}