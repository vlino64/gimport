<?php
    $hosting   = 1;
    error_reporting(0);
    $url_origin = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'unknown';

    if ($hosting){
        $servidor = "https://".$_SERVER['SERVER_NAME'];
    }
    else {
        $servidor = "https://".$_SERVER['SERVER_NAME']."/tutoria";
    }

    
    if (isset($_REQUEST['hr'])) {
    }
    else if (isset($_SESSION['usuari']) && ($url_origin=='unknown')){
        $url = $servidor."/index.php";
        header("Location:".$url);
    }
    else if (! isset($_SESSION['usuari']) ){
        $url = $servidor."/index.php";
        header("Location:".$url);
    }

?>