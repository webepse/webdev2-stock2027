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
        if(file_exists("../images/mini_".$delete['cover'])){
            unlink("../images/mini_".$delete['cover']);
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
            $products = fetchAll($bdd, "SELECT products.id as pid, products.name as pname, categories.name as cname, products.prix as pprix FROM products INNER JOIN categories ON products.id_category = categories.id ORDER BY products.id DESC");
        ?>
        <a href="addProduct.php" class="btn btn-primary my-3">Ajouter un produit</a>
        <table class="table table-hover">
            <thead>
                <tr class="text-center">
                    <th class="col">#</th>
                    <th class="col">Nom</th>
                    <th class="col">Prix</th>
                    <th class="col">Catégorie</th>
                    <th class="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $product) : ?>
                    <tr class="text-center">
                        <td><?= $product['pid'] ?></td>
                        <td><?= htmlspecialchars($product['pname']) ?></td>
                        <td><?= $product['pprix'] ?>€</td>
                        <td><?= $product['cname'] ?></td>
                        <td>
                            <a href="updateProduct.php?id=<?= $product['pid'] ?>" class="btn btn-warning mx-3">Modifier</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal<?= $product['pid'] ?>">
                            Supprimer
                            </button>
                        </td>

                        <!-- Button trigger modal -->


                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal<?= $product['pid'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $product['pid'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel<?= $product['pid'] ?>">Confirmation de suppression</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Voulez vous supprimer le produit <?= $product['pname'] ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                            <a href="products.php?delete=<?= $product['pid'] ?>" class="btn btn-danger">Supprimer</a>
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