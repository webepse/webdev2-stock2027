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
        <h2>Modifier produit: <?= $product['name'] ?></h2>
        <form action="treatmentUpdateProduct.php?id=<?= $product['id'] ?>" method="POST" enctype="multipart/form-data">

            <!-- faille CSRF -->
            <?php 
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <!-- retirer le commentaire -->
             
            <div class="form-group">
               <label for="nom">Nom: </label>
               <input type="text" name="name" id="nom" class="form-control" value="<?= $product['name'] ?>">
            </div>
            <div class="form-group my-2">
                <label for="description">Déscription: </label>
                <textarea name="description" id="description" class="form-control"><?= $product['description'] ?></textarea>
            </div>
            <div class="form-group my-2">
                <label for="prix">Prix: </label>
                <input type="number" name="prix" id="prix" step="0.01" class="form-control" value="<?= $product['prix'] ?>">
            </div>
            <div class="form-group my-2">
                <label for="categorie">Catégorie: </label>
                <select name="categorie" id="categorie" class="form-control">
                    <option value="1">Catégorie 1</option>
                </select>
            </div>
            <div class="form-group my-2">
                <label for="cover">Image de couverture: </label>
                <div class="col-4">
                    <img src="../images/<?= $product['cover'] ?>" alt="image de <?= $product['name'] ?>" class="img-fluid">
                </div>
                <input type="file" name="cover" id="cover" class="form-control">
            </div>
            <div class="form-group my-2">
                <input type="submit" value="Modifier" class="btn btn-warning">
            </div>
        </form>
    </div>
</body>
</html>