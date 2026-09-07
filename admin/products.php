<?php 
    require "../config/session.php";
    
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }

    require_once "../config/connexion.php";
    require "functions.php";

     if(isset($_GET['delete']) && filter_var($_GET['delete'],FILTER_VALIDATE_INT)){
       $delete = fetchOne($bdd,"SELECT * FROM products WHERE id=?",[$_GET['delete']]);
       if(!$delete){
        header("Location: ../404.php");
        exit();
       }else{
        if(file_exists("../images/".$delete['cover'])){
            unlink("../images/".$delete['cover']);
        }
      
       }

       $result = execute($bdd,"DELETE FROM products WHERE id=?",[$_GET['delete']]);
       //var_dump($result);
    }

?>

<!DOCTYPE html>
<html lang="fr">
<?php include("partials/head.php"); ?>
<body>
    <?php include("partials/nav.php"); ?>
    <div class="container-fluid">
        <h1>Gestion des produits</h1>
        <?php
            $products = fetchAll($bdd, "SELECT * FROM products ORDER BY id DESC");
        ?>
        <a href="addProduct.php" class="btn btn-primary my-3">Ajouter un produit</a>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="col-3 text-center">id</th>
                    <th class="col-3 text-center">nom</th>
                    <th class="col-3 text-center">prix</th>
                    <th class="col-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $product) : ?>
                    <tr>
                        <td class="text-center"><?= $product['id'] ?></td>
                        <td class="text-center"><?= htmlspecialchars($product['name']) ?></td>
                        <td class="text-center"><?= $product['prix'] ?>€</td>
                        <td class="text-center">
                            <a href="updateProduct.php?id=<?= $product['id'] ?>" class="btn btn-warning mx-3">Modifier</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal<?= $product['id'] ?>">
                            Supprimer
                            </button>
                        </td>

                        <!-- Button trigger modal -->


                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal<?= $product['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $product['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel<?= $product['id'] ?>">Confirmation de suppression</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Voulez vous supprimer le produit <?= $product['name'] ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                            <a href="products.php?delete=<?= $product['id'] ?>" class="btn btn-danger">Supprimer</a>
                        </div>
                        </div>
                    </div>
                    </div>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>