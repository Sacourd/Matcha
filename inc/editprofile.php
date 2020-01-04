    <div class="row no-gutters justify-content-center" style="width: 100vw;">
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-4 text-uppercase" style="background-color: #343a40;">
            <div class="table-responsive table-borderless">
                <?= $Front->sideForm() ?>
            </div>
        </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6" style="background-color: #ffffff;">
            <h1 class="display-4" style="font-size: 37px;margin-top: 20px;margin-left: 28px;"><?= $title ?></h1>
            <form style="margin-left: 32px;" method="post" action="" enctype="multipart/form-data">

                <?= $Front->profileForm() ?>

                <div class="form-group">
                    <?php if ($page != 'blocked') { ?>
                    <button class="btn btn-danger inputForm" type="submit" name="submit">Appliquer les modifications</button>
                    <?php } ?>
                    <?= $alert ?>
                </div>
            </form>
        </div>
    </div>