<?php

    include_once(ROOT.'libs/template/twig/lib/Twig/Autoloader.php');
    Twig_Autoloader::register();
    
    $loader = new Twig_Loader_Filesystem(array(ROOT, ROOT.'src')); 
    $twig = new Twig_Environment($loader, array(
      'cache' => false
    ));