<?php
    require "config/session.php";

    // router 
    $pages = [
        "home" => "home.php",
        "product" => "product.php"
    ];

    if(isset($_GET['action'])){
        if(array_key_exists($_GET['action'],$pages)){
            $page = $pages[$_GET['action']];
        }else{
           $page = "404.php";
        }
    }else{
         $page = $pages['home'];
    }

    include("partials/head.php");
    include("partials/nav.php");

    include("pages/".$page);
    
    include("partials/foot.php");

