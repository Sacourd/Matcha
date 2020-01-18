<?php

require_once('config/config.php');
require_once('Class/Front.php');

$Front = new Matcha\Front($DB, 'index');

if (isset($_GET['key'])) {
    $key = htmlentities($_GET['key']);
    if ($Libft->countOcc("users", "registrationkey", "registrationkey = '$key'") == 1)
        $Libft->updateCol("users", array("registrationkey"), "registrationkey = '$key'", array(0));
    
}

if (isset($_GET['logout'])) {
    $Libft->updateCol("users", array("logged"), "id = $userid", array(0));
    session_destroy();
    header('Location: /');
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
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/superbutton.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
</head>

<body>
            <?= $Front->navbar(); ?>
    <div>
        <header>


    <div class="bg-success d-flex align-items-center" style="height: 100vh;background-image: url('assets/img/Matcha_bg.jpg');background-repeat: no-repeat; background-size: cover; filter: grayscale(50%) saturate(50%);">

        <div data-aos="fade" data-aos-duration="600" class="text-center w-100 text-white">

            <h1 class="text-uppercase">Bienvenue sur Matcha</h1>

            <h2 class="font-weight-normal">
                <em>Trouve ta voie ici !</em>
            </h2>

            <?= $Front->indexButton(); ?>

        </div>

    </div>
    </header>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/navbar.js"></script>

</body>

</html>