<?php

require_once('config/config.php');
require_once('Class/Users.php');

if ($userid > 0)
    header('Location: /');

if (isset($_POST['submit']) AND isset($_POST['username']) AND isset($_POST['password'])) {

    $Users = new Matcha\Users($DB);

    $username   = htmlentities($_POST['username']);
    $password   = htmlentities($_POST['password']);
    $log = $Users->login($username, $password);
    if ($log == 1)
        header('Location: /');
    else if ($log == -1)
        $alert = '<div class="alert alert-danger" role="alert"><span>Le compte attends d\'être validé par mail !</span></div>';
    else if ($log == 0)
        $alert = '<div class="alert alert-danger" role="alert"><span>Les identifiants sont incorrects</span></div>';
    else
        $alert = '<div class="alert alert-danger" role="alert"><span>Cet utilisateur est banni</span></div>';

}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Matcha</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row mh-100vh">
            <div class="col-10 col-sm-8 col-md-6 col-lg-6 offset-1 offset-sm-2 offset-md-3 offset-lg-0 align-self-center d-lg-flex align-items-lg-center align-self-lg-stretch bg-white p-5 rounded rounded-lg-0 my-5 my-lg-0" id="login-block">
                <div class="m-auto w-lg-75 w-xl-50">
                    <h2 class="text-info font-weight-light mb-5"><i class="fas fa-fire"></i> Matcha</h2>

                    <form method="post" action="">

                        <div class="form-group">
                            <label class="text-secondary">Nom d'utilisateur</label>
                            <input name="username" class="form-control" type="text" required>
                        </div>

                        <div class="form-group">
                            <label class="text-secondary">Mot de passe</label>
                            <input name="password" class="form-control" type="password" required>
                        </div>

                        <div class="form-group text-center">
                            <button class="btn btn-info text-center mt-2" type="submit" name="submit">Se connecter</button>
                        </div>

                    </form>

                    <p class="text-center mt-3 mb-0">
                        <a class="text-info small" href="/forgot_password.php">Mot de passe oublié ?</a><br>
                        <a class="text-info small" href="/register.php">Pas encore de compte ?</a><br>
                    </p>

                    <p class="text-center mt-3 mb-0">
                        <a class="text-info small" href="/">Retour à l'accueil</a><br>
                    </p>

                    <?= $alert ?>

                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-end" id="bg-block" style="background-image: url('assets/img/Matcha_bg.jpg');background-size: cover;background-position: center center;filter: brightness(5%) sepia(100%);"></div>
        </div>
    </div>
</body>

</html>