<!DOCTYPE html>
<?php $smarty = wp_smarty(); ?>

<!--[if lt IE 7]>      <html class="no-js lt-ie10 lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie10 lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie10 lt-ie9"> <![endif]-->
<!--[if IE 9]>         <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 9]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    
    <meta charset="utf-8" />
  
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>[[project_name]]</title>

    <!-- Favicons / Site Tile -->
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="/favicon-196x196.png?v=1" sizes="196x196">
    <link rel="icon" type="image/png" href="/favicon-160x160.png?v=1" sizes="160x160">
    <link rel="icon" type="image/png" href="/favicon-96x96.png?v=1" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicon-16x16.png?v=1" sizes="16x16">
    <link rel="icon" type="image/png" href="/favicon-32x32.png?v=1" sizes="32x32">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/mstile-144x144.png">

  </head>
    <body <?php body_class(); ?>>

    <?php

        $smarty->display('includes/header.tpl');