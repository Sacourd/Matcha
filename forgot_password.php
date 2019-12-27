<?php

require_once('config/config.php');
require_once('Class/Front.php');
require_once('Class/Users.php');

if ($userid > 0)
    header('Location: /');

$Users = new Matcha\Users($DB);

$Libft->deleteSQL('forgot_password', "CURRENT_TIMESTAMP - time > 86400");

if (isset($_GET['recover'])) {
    $recoverKey = htmlentities($_GET['recover']);
    if ($Libft->countOcc('forgot_password', 'id', "keylock = '$recoverKey'") == 0)
        header('Location: /');
}

if (isset($_POST['recover']) AND isset($_POST['email']))
    $alert = $Users->forgotPassword(htmlentities($_POST['email']));

if (isset($_POST['newpass']) AND isset($_POST['pass']) AND isset($_POST['pass-c']))
    $alert = $Users->newPassword(array_map('htmlentities', $_POST), $recoverKey);

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Zina</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Actor">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Titillium+Web:400,600,700">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row mh-100vh">

            <div class="col-lg-6 d-flex align-items-end" id="bg-block" style="background-image: url('assets/img/zina_bg.jpg');background-size: cover;background-position: center center;filter: brightness(5%) sepia(100%);">
            </div>

            <div class="col-10 col-sm-8 col-md-6 col-lg-6 offset-1 offset-sm-2 offset-md-3 offset-lg-0 align-self-center d-lg-flex align-items-lg-center align-self-lg-stretch bg-white p-5 rounded rounded-lg-0 my-5 my-lg-0" id="login-block">
                <div class="m-auto w-lg-75 w-xl-50">
                    <h2 class="text-info font-weight-light mb-5"><i class="fas fa-fire"></i> Z'INA</h2>

                    <?= $alert ?>
                    
                    <form method="post" action="">

                        <?php if (isset($_GET['recover']))  { ?>

                        <div class="form-group">
                            <label class="text-secondary">Nouveau mot de passe</label>
                            <input class="form-control" type="password" data-toggle="tooltip" data-bs-tooltip="" data-placement="right" required name="pass" title="8 caractères minimum, une majuscule, une minuscule, un symbole et un chiffre" minlength=8>
                        </div>

                        <div class="form-group">
                            <label class="text-secondary">Confirmer nouveau mot de passe</label>
                            <input class="form-control" type="password" name="pass-c" minlength=8 required>
                        </div>

                        <div class="form-group text-center">
                            <button class="btn btn-info text-center mt-2" type="submit" name="newpass">Nouveau mot de passe</button>
                        </div>

                        <?php } else { ?>

                        <div class="form-group">
                            <label class="text-secondary">E-mail</label>
                            <input class="form-control" type="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,15}$" name="email">
                        </div>

                        <div class="form-group text-center">
                            <button class="btn btn-info text-center mt-2" type="submit" name="recover">Récupérer son mot de passe</button>
                        </div>

                        <?php } ?>


                    </form>

                    

                    <p class="text-center mt-3 mb-0">
                        <a class="text-info small" href="login.php">Se connecter<br></a>
                        <a class="text-info small" href="register.php">Pas encore de compte ?</a>
                    </p>

                    <p class="text-center mt-3 mb-0">
                        <a class="text-info small" href="/">Retour à l'accueil</a>
                    </p>

                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
</body>

</html>