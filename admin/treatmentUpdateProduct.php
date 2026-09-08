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

    // récupération du produit à modifier (via son identifiant)
    // sécu de l'id
    if(!isset($_GET['id']) || !filter_var($_GET['id'],FILTER_VALIDATE_INT)){
        header("Location: ../404.php");
        exit();
    }
    // connexion bdd + functions
    require "../config/connexion.php";
    require "functions.php";

    // vérification de l'existance du produit dans la bdd
    $product = fetchOne($bdd,"SELECT * FROM products WHERE id=?",[$_GET['id']]);
    if(!$product){
        header("Location: ../404.php");
        exit();
    }
    /************************/

    // gestion des erreurs (hors fichier)
    // init de la var err à 0
    $err = 0;

    // nettoyage des données (hors fichier) 
    $name = trim($_POST['name'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $prix = trim($_POST['prix'] ?? "");
    $categorie = trim($_POST['categorie'] ?? "");

    // vérification des données en conformité avec ce que l'on veut récupérer
    if (empty($name)){
        $err = 1;
    }elseif (empty($description)){
        $err = 2;
    }elseif (empty($prix)){
        $err = 3;
    }elseif(!filter_var($prix, FILTER_VALIDATE_FLOAT)){
        $err = 4;
    }elseif(!filter_var($categorie, FILTER_VALIDATE_INT)){
        $err = 5;
    }

    // vérification de la var err si 0 ok sinon redirection vers formulaire
    if($err===0){

        // récup l'image (savoir s'il y a une image ou pas)
       $newImage = isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE;
        
        // s'il y a une image
        if($newImage){
            // vérification si l'image envoyée est ok
            if ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
                header("Location: updateProduct.php?id=" . $product['id'] . "&error=6");
                exit();
            }
            /************************/ 

            // récup le fichier
            $tmpPath = $_FILES['cover']['tmp_name'];
            $tailleMax = 2 * 1024 * 1024;
            /************************/ 

            // vérification de taille
            if($_FILES['cover']['size'] > $tailleMax)
            {
                header("Location: updateProduct.php?id=".$product['id']."&error=7");
                exit();
            }
            /************************/ 

            // vérification de l'extensions
            $extension = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
            $extensionAutorisees = ['jpg','jpeg','png','svg',"webp"];
            if(!in_array($extension,$extensionAutorisees, true)){
                header("Location: updateProduct.php?id=".$product['id']."&error=8");
                exit();
            }
            /************************/ 

            // vérification du Mime Type 
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeReel = $finfo->file($tmpPath);
    
            $mimesAutorises = [
                "jpg" => "image/jpeg",
                "jpeg" => "image/jpeg",
                "png" => "image/png",
                "svg" => "image/svg+xml",
                "webp" => "image/webp"
            ];
            if(!in_array($mimeReel, $mimesAutorises, true) || $mimesAutorises[$extension] !== $mimeReel){
                header("Location:  updateProduct.php?id=".$product['id']."&error=9");
                exit();
            }
            /************************/ 

            // changement du nom du fichier (sanitize)
            $nomImage =  basename($_FILES['cover']['name']);
            $nomImageLisible = strtr($nomImage, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ','AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
            $nomImageSafe = preg_replace('/([^.a-z0-9]+)/i', '-', $nomImageLisible);
            $uniqnomSafe = uniqid().'-'.$nomImageSafe;
             /************************/ 

             // dossier de destination
             $dossierDestination = "../images/";

            // déplacement du fichier
            if(move_uploaded_file($tmpPath, $dossierDestination.$uniqnomSafe)){
                // mise à jour de la bdd
                try{
                    // gestion de la modification 
                    execute($bdd, "UPDATE products SET name = :name, description = :description, prix = :prix, id_category = :categorie, cover = :cover WHERE id= :id",[
                        "name" => $name,
                        "description" => $description,
                        "prix" => $prix,
                        "categorie" => $categorie,
                        "cover" => $uniqnomSafe,
                        "id" => $product['id']
                    ]);
                   
                    // faille CSRF token
                    unset($_SESSION['csrf_token']);

                    // supprimer ancienne image
                     if(file_exists($dossierDestination.$product['cover'])){
                        unlink($dossierDestination.$product['cover']);
                    }

                    // redirection vers la page products.php avec l'id du produit modifié
                    header("Location: products.php?update=success&upid=".$product['id']);
                    exit();
                }catch(PDOException $e){
                    // en cas d'erreur
                    // supprimer le fichier déplacé (pour rien vu qu'il y a une erreur)
                    if(file_exists($dossierDestination.$uniqnomSafe)){
                        unlink($dossierDestination.$uniqnomSafe);
                    }
                    // redirection indicant une erreur internet (erreur 500)
                    header("Location: products.php?error=500");
                    exit();
                }

            }else{
                // déplacement de l'image impossible
                header("Location: updateProduct.php?id=".$product['id']."&error=10");
                exit();
            }

        }else{
            // il n'y a pas d'image
            // update sans gestion de l'image
            execute($bdd, "UPDATE products SET name = :name, description = :description, prix = :prix, id_category = :categorie WHERE id= :id",[
                    "name" => $name,
                    "description" => $description,
                    "prix" => $prix,
                    "categorie" => $categorie,
                    "id" => $product['id']
            ]);

            // faille CSRF token
            unset($_SESSION['csrf_token']);

            // redirection vers la page products.php avec l'id du produit modifié
            header("Location: products.php?update=success&upid=".$product['id']);
            exit();
        }     

    }else{
        // erreur dans le formulaire (hors fichier)
        header("Location: updateProduct.php?id=".$product['id']."&error=".$err);
        exit();
    }
