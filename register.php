<?php

require_once('config/config.php');
require_once('Class/Users.php');

if ($userid != -1)
    header('Location: /');

if (isset($_POST['submit'])) {
    if (empty($_POST['username']) OR empty($_POST['password']) OR empty($_POST['password-confirm']) OR empty($_POST['email']) OR empty($_POST['cgu']))
        $alert = '<div class="alert alert-danger" role="alert"><span>Tout les champs sont obligatoires</span></div>';
    else {
        $localisation = array(
            "latitude"  => null,
            "longitude" => null);
        $Users = new Matcha\Users($DB);
        if (empty($_POST['longitude']) OR empty($_POST['latitude']) OR !is_numeric($_POST['longitude']) OR !is_numeric($_POST['latitude']))
            $localisation = $Users->getLocalisationWithIP($localisation);
        else {
            $localisation['longitude']  = $_POST['longitude'];
            $localisation['latitude']   = $_POST['latitude'];
        }
        $alert = $Users->register($_POST, $localisation['latitude'], $localisation['longitude']);
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Matcha</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Actor">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Titillium+Web:400,600,700">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>

<body>
    <div class="register-photo" style="background-image: url('assets/img/Matcha_bg_2.jpg');">
        <div class="form-container">
            <div class="image-holder"></div>
            <form method="post" action="">
                <h2 class="text-center"><strong>Rejoignez</strong> nous !</h2>

                <div class="form-group">
                    <input class="form-control" type="text" name="username" placeholder="Nom d'utilisateur" maxlength=20 required>
                </div>

                <div class="form-group">
                    <input class="form-control" type="email" name="email" placeholder="Email" maxlength=70 required>
                </div>

                <div class="form-group">
                    <input class="form-control" type="password" name="password" placeholder="Mot de passe" required>
                </div>

                <div class="form-group">
                    <input class="form-control" type="password" name="password-confirm" placeholder="Confirmez le mot de passe" required="">
                </div>

                <input type="hidden" name="latitude"  value="">
                <input type="hidden" name="longitude" value="">

                <div class="form-group">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="cgu">J'accepte les conditions générales d'utilisations
                        </label>
                    </div>
                </div>

                <?= $alert ?>

                <div class="form-group">
                    <button class="btn btn-primary btn-block" type="submit" name="submit">Nous rejoindre</button>
                </div>

                <a class="already" href="login.php">Vous avez déjà un compte ?<br>
                <a class="already" href="/">Retour à l'accueil</a>
            </form>
        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="assets/js/getpos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
</body>

</html>