<?php 
    require "../config/session.php";
    
    // sécurité session admin
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }

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
?>

<!DOCTYPE html>
<html lang="fr">
<?php include("partials/head.php"); ?>
<body>
    <?php include("partials/nav.php"); ?>
    <div class="container">
        <h2>Ajouter une image au produit : <?= $product['name'] ?></h2>
        <form action="treatmentAddImg.php?id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">

            <!-- faille CSRF -->
            <?php 
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <!-- retirer le commentaire -->
            <div class="form-group my-2">
                <label for="image">Image: </label>
                <input type="file" name="image" id="image" class="form-control">
            </div>
            <div class="form-group my-2">
                <input type="submit" value="Ajouter" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>
</html>