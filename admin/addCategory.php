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
        <h2>Ajouter une catégorie</h2>
        <form action="treatmentAddCategory.php" method="POST">
            <!-- GESTION CSRF -->
            <?php 
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
             <!-- FIN GESTION CSRF -->
            <div class="form-group">
               <label for="nom">Nom: </label>
               <input type="text" name="name" id="nom" class="form-control">
            </div>
            <div class="form-group my-2">
                <label for="description">Déscription: </label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>
            <div class="form-group my-2">
                <input type="submit" value="Ajouter" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>
</html>