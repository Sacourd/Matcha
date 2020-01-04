    <div class="row text-center justify-content-center align-items-center" style="width: 100vw;">
        <div class="col-auto text-center">
            <h1 class="text-center" style="color: rgb(222,72,62);"><?= $nbFound ?> candidat<?= $fem ?>s</h1>
            <h3><a href="search.php" style="text-decoration: none; color: rgb(222,92,62);">Nouvelle recherche</a></h3>
        </div>
    </div>

          <?= $Front->printResults($result, $banner); ?>