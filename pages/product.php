<div class="container">
    <div class="col-6">
        <img src="images/<?= $product['cover'] ?>" alt="image de <?= $product['pname'] ?>" class="img-fluid">
    </div>
    <div class="row">
        <div class="col-md-4">
            <h6><?= htmlspecialchars($product['cname']) ?></h6>
            <h3><?= htmlspecialchars($product['pname']) ?></h3>
            <h4><?= $product['prix'] ?>€</h4>
        </div>
        <div class="col-md-8">
            <div class="text p-2">
                <?= htmlspecialchars(nl2br($product['pdescri'])) ?>
            </div>
        </div>
    </div>
</div>