<?php 
    require "../config/session.php";
    
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }

    if(isset($_GET['deco'])){
        session_destroy();
        unset($_SESSION['email']);
        unset($_SESSION['id']);
        header("Location: index.php");
        exit();
    }

    require "../config/connexion.php";
    require "../assets/functions.php";

?>

<!DOCTYPE html>
<html lang="fr">
<?php include("partials/head.php"); ?>
<body>
    <?php include("partials/nav.php"); ?>
    <h1>Tableau de bord</h1>
    <div class="row d-flex justify-content-between">
        <div class="col-6 bg-primary text-white text-center">
            <h2>Catégorie(s)</h2>
            <?php
                $nbCat = myCount($bdd,"SELECT * FROM categories");
            ?>
            <h3><?= $nbCat ?></h3>
        </div>
        <div class="col-6 bg-warning text-white text-center">
             <h2>Produit(s)</h2>
            <?php
                $nbProd = myCount($bdd,"SELECT * FROM products");
            ?>
            <h3><?= $nbProd ?></h3>
        </div>
    </div>
</body>
</html>