<?php

    include_once(ROOT.'core/template/twig/lib/Twig/Autoloader.php');
    Twig_Autoloader::register();
    
    $loader = new Twig_Loader_Filesystem(ROOT.'views/'); 
    $twig = new Twig_Environment($loader, array(
      'cache' => false
    ));

    echo $twig->render($contro.'/'.$filename.'.html.twig', $array);
