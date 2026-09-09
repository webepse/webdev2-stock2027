<?php
    require "../config/session.php";

    // Vérifier le type de méthode HTTP
    if($_SERVER['REQUEST_METHOD'] !== "POST"){
        http_response_code(405); // 405 Méthode non autorisée
        header("Allow: POST"); // indiquer la méthode autorisée
        exit("Méthode non autorisée, Utilisez POST");
    }
    /************************/

    // Gestion faille CSRF (voir dans le formulaire + après la gestion de la base de données)
    if(!isset($_SESSION['csrf_token'], $_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){
        http_response_code(403);
        exit("Jeton de sécurité invalide");
    }
    /************************/

    // vérification de l'id de la catégorie à modifier
    if(!isset($_GET['id']) || !filter_var($_GET['id'],FILTER_VALIDATE_INT)){
        header("Location: ../404.php");
        exit();
    }

    // vérification si la catégorie existe bien
    require "../config/connexion.php";
    require "functions.php";
    $category = fetchOne($bdd,"SELECT * FROM categories WHERE id=?",[$_GET['id']]);
    if(!$category){
        header("Location: ../404.php");
        exit();
    }


    // gestion des erreurs (hors fichier)
    // init de la var err à 0
    $err = 0;

    // nettoyage des données (hors fichier) 
    $name = trim($_POST['name'] ?? "");
    $description = trim($_POST['description'] ?? "");
   
    // vérification des données en conformité avec ce que l'on veut récupérer
    if (empty($name)){
        $err = 1;
    }elseif (empty($description)){
        $err = 2;
    }

    // vérification de la var err si 0 ok sinon redirection vers formulaire
    if($err===0){
           
        // insertion dans la base de données
            // SQL : UPDATE categories SET name=?, description=? WHERE id=?
            // $params = [$name,$description,$category['id]];
        
            execute($bdd, "UPDATE categories SET name=:name, description=:description WHERE id=:id",[
                "name" => $name,
                "description" => $description,
                "id" => $category['id']
            ]);
            // faille CSRF token
            unset($_SESSION['csrf_token']);
            // redirection vers la page categories avec les info ok
            header("Location: categories.php?update=success&upid=".$category['id']);
            exit();

    }else{
        // erreur dans le formulaire (hors fichier)
        header("Location: updateCategory.php?id=".$category['id']."&error=".$err);
        exit();
    }
