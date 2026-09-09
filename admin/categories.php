<?php
    // sécurité admin
    require "../config/session.php";
    
    if(!isset($_SESSION['email']) || !isset($_SESSION['id'])){
        header("Location: ../403.php");
        exit();
    }

    // base de données
    require_once "../config/connexion.php";
    require "functions.php";

    // fonctionnalité de suppression 
     if(isset($_GET['delete']) && filter_var($_GET['delete'],FILTER_VALIDATE_INT)){
        // vérifier que ce que je souhaite supp existe
       $delCategory = fetchOne($bdd,"SELECT * FROM categories WHERE id=?",[$_GET['delete']]);
       if(!$delCategory){
        header("Location: ../404.php");
        exit();
       }
      
       // retrouver les produits associés à ma catégorie
       $prods = fetchAll($bdd,"SELECT * FROM products WHERE id_category=?",[$_GET['delete']]);
       foreach($prods as $prod){
            if(file_exists("../images/".$prod['cover'])){
                unlink("../images/".$prod['cover']);
            }
       }

       // supprimer les données des produits assocé à la catégorie ciblée
       $resultProd = execute($bdd,"DELETE FROM products WHERE id_category=?",[$_GET['delete']]);
       
        // supprimer les données de la catégorie ciblée
       $resultCat = execute($bdd,"DELETE FROM categories WHERE id=?",[$_GET['delete']]);
       var_dump($resultProd);
       var_dump($resultCat);
    }
    /*****************************/

?>

<!DOCTYPE html>
<html lang="fr">
<?php include("partials/head.php"); ?>
<body>
    <?php include("partials/nav.php"); ?>
    <div class="container-fluid">
        <h1>Gestion des categories</h1>
        <?php
            // récup toutes les données de la table catégorie en une fois => fetchAll()
            $categories = fetchAll($bdd, "SELECT * FROM categories ORDER BY id ASC");
        ?>
        <a href="addCategory.php" class="btn btn-primary my-3">Ajouter une catégorie</a>
        <table class="table table-hover">
            <thead>
                <tr class="text-center">
                    <th class="col-2">#</th>
                    <th class="col-4">nom</th>
                    <th class="col-6">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($categories as $category) : ?>
                    <tr class="text-center">
                        <td><?= $category['id'] ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td class="text-center">
                            <a href="updateCategory.php?id=<?= $category['id'] ?>" class="btn btn-warning mx-3">Modifier</a>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#exampleModal<?= $category['id'] ?>">
                            Supprimer
                            </button>
                        </td>

                        <!-- Button trigger modal -->


                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal<?= $category['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel<?= $category['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel<?= $category['id'] ?>">Confirmation de suppression</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Voulez vous supprimer la catégorie <?= $category['name'] ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                            <a href="categories.php?delete=<?= $category['id'] ?>" class="btn btn-danger">Supprimer</a>
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