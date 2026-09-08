<?php 
    require "../config/session.php";
    
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }

?>

<!DOCTYPE html>
<html lang="fr">
<?php include("partials/head.php"); ?>
<body>
    <?php include("partials/nav.php"); ?>
    <div class="container">
        <h2>Ajouter un produit</h2>
        <form action="treatmentAddProduct.php" method="POST" enctype="multipart/form-data">
            <?php 
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
               <label for="nom">Nom: </label>
               <input type="text" name="name" id="nom" class="form-control">
            </div>
            <div class="form-group my-2">
                <label for="description">Déscription: </label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>
            <div class="form-group my-2">
                <label for="prix">Prix: </label>
                <input type="number" name="prix" id="prix" step="0.01" class="form-control">
            </div>
            <div class="form-group my-2">
                <label for="categorie">Catégorie: </label>
                <select name="categorie" id="categorie" class="form-control">
                    <?php
                        require "../config/connexion.php";
                        require "functions.php";
                        $categories = fetchAll($bdd,"SELECT * FROM categories ORDER BY id");
                    ?>
                    <?php foreach($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group my-2">
                <label for="cover">Image de couverture: </label>
                <input type="file" name="cover" id="cover" class="form-control">
            </div>
            <div class="form-group my-2">
                <input type="submit" value="Ajouter" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>
</html>