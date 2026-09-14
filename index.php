<?php
    require "config/session.php";
    require "config/connexion.php";
    require "assets/functions.php";

    // router 
    $pages = [
        "home" => "home.php",
        "product" => "product.php",
        "products" => "products.php"
    ];

    if(isset($_GET['action'])){
        if(array_key_exists($_GET['action'],$pages)){

            if($_GET['action']==="product"){
                if(isset($_GET['id']) AND !empty($_GET['id']) AND filter_var($_GET['id'],FILTER_VALIDATE_INT)){
                    $product = fetchOne($bdd,"SELECT products.name as pname, products.description as pdescri, products.prix as prix, categories.name as cname, products.cover as cover FROM products INNER JOIN categories ON products.id_category = categories.id WHERE products.id=?",[$_GET['id']]);
                    if(!$product){
                        header("Location: 404.php");
                        exit();
                    }else{
                        $page = $pages["product"];
                    }
                }else{
                    header("Location: 404.php");
                    exit();
                }

            }elseif($_GET['action']==="products"){
                // construction de offset et de limit + partage de la page 
                // www.monsite.be/index.php?action=products&page=2
                // compter combien j'ai de produit dans ma table products
                $count = myCount($bdd,"SELECT id FROM products");
                $limit = 6;
                // 21 combien de page? 21 / 10 = 2,1 => besoin de 3 pages alors => besoin d'un système qui arrondi au supérieur
                // ceil(arrondi au supérieur)
                $nbpage = ceil($count/$limit);
                // vérifier si j'ai $_GET['page']
                // établir la variable pg
                if(isset($_GET['page'])){
                    if(is_numeric($_GET['page'])){
                        $pg = $_GET['page'];
                        if($pg> $nbpage){
                            $pg = $nbpage;
                        }elseif($pg <= 0){
                            $pg = 1;
                        }
                    }else{
                        header("Location: 404.php");
                        exit();
                    }
                }else{
                    $pg = 1;
                }

                // page 1 = 0
                // page 2 = 10
                // page 3 = 20
                // 1-1*10 = 0
                // 2-1*10 = 10
                // 3-1*10 = 20
                $offset = ($pg-1)*$limit;
                $page = $pages["products"];
            }else{
                $page = $pages[$_GET['action']];
            }
        }else{
           header("Location: 404.php");
           exit();
        }    
    }else{
         $page = $pages['home'];
    }

    include("partials/head.php");
    include("partials/nav.php");

    include("pages/".$page);
    
    include("partials/foot.php");

