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

    // gestion de suppression d'image
    if(isset($_GET['delete']) && filter_var($_GET['delete'],FILTER_VALIDATE_INT)){
        $delete = fetchOne($bdd,"SELECT * FROM images WHERE id=?",[$_GET['delete']]);
       if(!$delete){
        header("Location: ../404.php");
        exit();
       }else{
        if(file_exists("../images/".$delete['file'])){
            unlink("../images/".$delete['file']);
        }
       }
       $result = execute($bdd,"DELETE FROM images WHERE id=?",[$_GET['delete']]);
       var_dump($result);
        // redirection vers la page
        header("Location: updateProduct.php?id=".$_GET['id']);
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
                    <?php
                        $categories = fetchAll($bdd,"SELECT * FROM categories ORDER BY id");
                        var_dump($categories);
                        /* 
                            écriture ternaire
                            (condition) ? return si vrai : return si faux
                          
                           <?php echo ($category['id']===$product['id_category']) ? 'selected' : '' ?>
                           <?= ($category['id']===$product['id_category']) ? 'selected' : '' ?>
                        */
                    ?>
                    <?php foreach($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($category['id']===$product['id_category']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                    
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
        <h2>Gestion des images</h2>
        <a href="addImg.php?id=<?= $product['id'] ?>" class="btn btn-primary my-2">Ajouter une image</a>
        <table class="table table-striped">
            <thead>
                <tr class="text-center">
                    <th class="col">#</th>
                    <th class="col">image</th>
                    <th class="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    // SELECT * FROM images WHERE id_product=? => $product['id']
                    // SELECT * FROM images WHERE id_product=:id => "id" => $product['id']
                    $images = fetchAll($bdd,"SELECT * FROM images WHERE id_product=?",[$product['id']]);
                    // count(array_keys($images))
                    // sizeof($images)
                ?>
                <?php if(count(array_keys($images)) < 1 ) : ?>
                    <tr>
                        <td colspan='3' class='text-center'>Aucune image pour ce produit</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($images as $image) : ?>
                        <tr class="text-center">
                            <td><?= $image['id'] ?></td>
                            <td><img class='col-2 img-fluid' src="../images/<?= $image['file'] ?>"></td>
                            <td>
                                <a href="updateProduct.php?id=<?= $product['id'] ?>&delete=<?= $image['id'] ?>" class="btn btn-danger">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>