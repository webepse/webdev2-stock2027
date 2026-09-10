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

    // vérification de l'id du produit à modifier
    if(!isset($_GET['id']) || !filter_var($_GET['id'],FILTER_VALIDATE_INT)){
        header("Location: ../404.php");
        exit();
    }

    // vérification si le produit existe bien
    require "../config/connexion.php";
    require "functions.php";
    $product = fetchOne($bdd,"SELECT * FROM products WHERE id=?",[$_GET['id']]);
    if(!$product){
        header("Location: ../404.php");
        exit();
    }
        
        // vérification si l'image est envoyée et dans une bonne condition
        if(!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK){
            header("Location: addImg.php?id=".$_GET['id']."&error=1");
            exit();
        }

        // récup le fichier
        $tmpPath = $_FILES['image']['tmp_name'];
        $tailleMax = 2 * 1024 * 1024; 
     
        // vérification de taille
        if($_FILES['image']['size'] > $tailleMax)
        {
            header("Location: addImg.php?id=".$_GET['id']."&error=2");
            exit();
        }

        // vérification de l'extensions
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $extensionAutorisees = ['jpg','jpeg','png','svg',"webp"];

        if(!in_array($extension,$extensionAutorisees, true)){
             header("Location: addImg.php?id=".$_GET['id']."&error=3");
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
            header("Location: addImg.php?id=".$_GET['id']."&error=4");
            exit();
        }
        /************************/ 

        // changement du nom du fichier (sanitize)
        $nomImage =  basename($_FILES['image']['name']);
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
            try{
                insert($bdd, "INSERT INTO images(file,id_product) VALUES(:img,:id)",[
                    "img" => $uniqnomSafe,
                    "id" => $product['id']
                ]);
                /*
                    $req = $bdd->prepare("INSERT INTO images(file,id_product) VALUES(?,?)");
                    $req->execute([$uniqnomSafe,$_GET['id']]);

                     $req = $bdd->prepare("INSERT INTO images(file,id_product) VALUES(:img,:myid)");
                    $req->execute([
                        "myid" => $_GET['id'],
                        "img" => $uniqnomSafe
                    ]);
                */


                // faille CSRF token
                unset($_SESSION['csrf_token']);
                //header("Location: products.php?add=success");
                // test test
                // test+test
                header("Location: updateProduct.php?id=".$product['id']."&add=success");
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

     

   
