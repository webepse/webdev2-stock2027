<div class="container">
    <h2>Les Produits</h2>
    <?php
        echo "<div class='pagination'>";
            if($pg>1)
            {
                echo "<a href='index.php?action=products&page=".($pg-1)."'>&laquo;</a>";
            }

            for($p=1;$p<=$nbpage;$p++)
            {
                if($p==$pg)
                {
                    echo "<a class='active' href='index.php?action=products&page=".$p."'>".$p."</a>";
                }else{
                    echo "<a href='index.php?action=products&page=".$p."'>".$p."</a>";
                }
            }

            if($pg!=$nbpage)
            {
                echo "<a href='index.php?action=products&page=".($pg+1)."'>&raquo;</a>";
            }
        echo "</div>";
    ?>
    <div class="products row justify-content-center my-4">
        <?php
            $products = pagination($bdd,"SELECT * FROM products ORDER BY id DESC LIMIT :offset, :limit", $offset,$limit);
            foreach($products as $product) :
        ?>
            <div class="card col-md-3 m-1">
                <img src="images/mini_<?= $product['cover'] ?>" class="card-img-top" alt="image de couverture de <?= htmlspecialchars($product['name']) ?>">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(nl2br($product['description'])) ?></p>
                    <a href="product-<?= $product['id'] ?>" class="btn btn-primary">En voir plus</a>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

