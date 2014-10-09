<?php

    include_once(ROOT.'vendor/twig/twig/lib/Twig/Autoloader.php');
    Twig_Autoloader::register();
    
    $loader = new Twig_Loader_Filesystem(array(ROOT, ROOT.'src', ROOT.'libs')); 
    $twig = new Twig_Environment($loader, array(
      'cache' => false
    ));