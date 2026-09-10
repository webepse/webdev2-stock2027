<?php
    // securisation session admin
    require "../config/session.php";
    
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }
    /**************************/

    // récupartation directe du nom du fichier
    $nomImage = basename($_GET['image']);
    $cheminSource = "../images/".$nomImage;
    $cheminDest = "../images/mini_".$nomImage;

    // récup l'extension du fichier
    $extension = strtolower(pathinfo($nomImage, PATHINFO_EXTENSION));

    // cas SVG (le vectoriel n'a pas besoin d'être redmin)
    if($extension === "svg") {
        copy($cheminSource, $cheminDest);
    }else{
        // traiter une redimension 
        // récup les dimension et le calcul (homothétique)
        $infos = getimagesize($cheminSource);
        $largeurSource = $infos[0];
        $hauteurSource = $infos[1];
        $mimeType = $infos['mime'];

        $nouvelleLargeur = 300;

        if($largeurSource > $nouvelleLargeur){
            $ratio = $nouvelleLargeur / $largeurSource;
            $nouvelleHauteur = (int) round($hauteurSource * $ratio);
        }else{
            $nouvelleLargeur = $largeurSource;
            $nouvelleHauteur = $hauteurSource;
        }
        /************************************/

        // chargement de la ressource image selon le format
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($cheminSource);
                break;
            case 'image/png':
                $source = imagecreatefrompng($cheminSource);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($cheminSource);
                break;
        }

        // caneva et verifier si je dois préserver la transparence
        $destination = imagecreatetruecolor($nouvelleLargeur,$nouvelleHauteur);

        if($mimeType === "image/png" || $mimeType === "image/webp") {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }

        // redimensionnement
        imagecopyresampled(
            $destination,
            $source,
            0,0,0,0,
            $nouvelleLargeur,
            $nouvelleHauteur,
            $largeurSource,
            $hauteurSource
        );

        // sauvegarde sous le format d'origine
        switch ($mimeType){
            case 'image/jpeg':
                imagejpeg($destination,$cheminDest,85);
                break;
            case 'image/png':
                imagepng($destination,$cheminDest, 6);
                break;
            case 'image/webp':
                imagewebp($destination, $cheminDest, 85);
                break;
        }
    }

    // redirection finale
    if(isset($_GET['update'])){
        header("Location: products.php?update=success&upid=".$_GET['update']);
    }else{
        header("Location: products.php?add=success");
    }
    exit();

