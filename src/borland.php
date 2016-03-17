<?php

$source_root = './';
$build_root = '../build/';

$phar = new Phar(
    $build_root . 'taylor.phar',
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    'taylor.phar'
);

$taylor_php = file_get_contents($source_root . 'taylor.php');

$phar['taylor.php'] = str_replace('$phar = false;', '$phar = true;', $taylor_php);

$includes = new DirectoryIterator($source_root . 'includes/');
foreach($includes as $include){
    $filename = $include->getFilename();
    if($include->isFile())
        $phar['includes/' . $filename] = file_get_contents($source_root . 'includes/' . $filename);
}

$includes = new DirectoryIterator($source_root . 'includes/init/');
foreach($includes as $include){
    $filename = $include->getFilename();
    if($include->isFile())
        $phar['includes/init/' . $filename] = file_get_contents($source_root . 'includes/init/' . $filename);
}

$lib = new DirectoryIterator($source_root . 'lib/');
foreach($lib as $include){
    $filename = $include->getFilename();
    if($include->isFile())
        $phar['lib/' . $filename] = file_get_contents($source_root . 'lib/' . $filename);
}

$phar->setStub("<?php
    Phar::mount('./', __DIR__);
    Phar::mapPhar();
    include 'phar://taylor.phar/taylor.php';
    __HALT_COMPILER();
    ?>"
);