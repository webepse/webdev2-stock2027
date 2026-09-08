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
        
        // vérification si l'image est envoyée et dans une bonne condition
        if(!isset($_FILES['cover']) || $_FILES['cover']['error'] !== UPLOAD_ERR_OK){
            header("Location: addProduct.php?error=6");
            exit();
        }

        // récup le fichier
        $tmpPath = $_FILES['cover']['tmp_name'];
        $tailleMax = 2 * 1024 * 1024; 
     
        // vérification de taille
        if($_FILES['cover']['size'] > $tailleMax)
        {
            header("Location: addProduct.php?error=7");
            exit();
        }

        // vérification de l'extensions
        $extension = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $extensionAutorisees = ['jpg','jpeg','png','svg',"webp"];

        if(!in_array($extension,$extensionAutorisees, true)){
            header("Location: addProduct.php?error=8");
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
            header("Location: addProduct.php?error=9");
            exit();
        }
        /************************/ 

        // changement du nom du fichier (sanitize)
        $nomImage =  basename($_FILES['cover']['name']);
        $nomImageLisible = strtr($nomImage, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ','AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        $nomImageSafe = preg_replace('/([^.a-z0-9]+)/i', '-', $nomImageLisible);
        $uniqnomSafe = uniqid().'-'.$nomImageSafe;

        // autre solution : remplacement complet du nom
        // $uniqnomSafe = bin2hex(random_bytes(16)).'.'.$extension;

        // dossier de destination
        $dossierDestination = "../images/";
        
         // déplacement du fichier
        if(move_uploaded_file($tmpPath, $dossierDestination.$uniqnomSafe)){
            // insertion dans la base de données
            require "../config/connexion.php";
            require "functions.php";
            try{
                insert($bdd, "INSERT INTO products(name,description,prix,id_category,cover) VALUES(:name,:description,:prix,:categorie,:cover)",[
                    "name" => $name,
                    "description" => $description,
                    "prix" => $prix,
                    "categorie" => $categorie,
                    "cover" => $uniqnomSafe
                ]);
                // faille CSRF token
                unset($_SESSION['csrf_token']);
                header("Location: products.php?add=success");
                exit();
            }catch(PDOException $e){
                if(file_exists($dossierDestination.$uniqnomSafe)){
                    unlink($dossierDestination.$uniqnomSafe);
                }
                header("Location: products.php?error=500");
                exit();
            }

        }else{
            // déplacement de l'image impossible
            header("Location: addProduct.php?error=10");
            exit();
        }

     

    }else{
        // erreur dans le formulaire (hors fichier)
        header("Location: addProduct.php?error=".$err);
        exit();
    }
