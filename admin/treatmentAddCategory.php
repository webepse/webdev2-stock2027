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
        require "../config/connexion.php";
        require "functions.php";
        
            insert($bdd, "INSERT INTO categories(name,description) VALUES(:name,:description)",[
                "name" => $name,
                "description" => $description
            ]);
            // faille CSRF token
            unset($_SESSION['csrf_token']);
            header("Location: categories.php?add=success");
            exit();

    }else{
        // erreur dans le formulaire (hors fichier)
        header("Location: addCategory.php?error=".$err);
        exit();
    }
