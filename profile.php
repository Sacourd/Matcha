<?php

require_once('config/config.php');
require_once('Class/Front.php');
require_once('Class/Users.php');

if ((isset($_GET['view']) AND isset($_GET['page'])) OR $userid == -1 OR (isset($_GET['view']) AND !is_numeric($_GET['view'])))
    header('Location: /');

if (!isset($_GET['page']) AND !isset($_GET['view']))
    $page = 'parameters';

else if (!isset($_GET['view']))
    $page = htmlentities($_GET['page']);

$authorizedPage = array("parameters", "personal", "gestion", "photos", "notif", "blocked");

if (isset($page) AND !in_array($page, $authorizedPage))
    header('Location: profile.php');

if ($page == 'parameters')
    $title = 'Paramètres du compte';
if ($page == 'personal')
    $title = 'Informations personnelles';
if ($page == 'gestion')
    $title = 'Gestion du compte';
if ($page == 'photos')
    $title = 'Photos';
if ($page == 'notif')
    $title = 'Gestion des notifications';
if ($page == 'blocked')
    $title = 'Utilisateurs bloqués';

$Front = new Matcha\Front($DB, $page);

if (isset($_POST['submit'])) {
    $Users = new Matcha\Users($DB);
    $alert = $Users->editProfile($_POST, $page);
}

if (isset($_GET['unblock']) AND is_numeric($_GET['unblock']) AND $userid != -1) {
    $unblock = $_GET['unblock'];
    if ($Libft->countOcc('blocked_users', 'id', 'user = '.$userid.' AND block = '.$unblock) > 0)
        $Libft->deleteSQL('blocked_users', 'user = '.$userid.' AND block = '.$unblock);
}

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
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/uploadfile.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>

<body style="background-image: url('assets/img/zina_bg_2.jpg');">

    <?= $Front->navbar(); ?>

    <div class="row text-center justify-content-center align-items-center" style="width: 100vw;margin-bottom: 42px;">
        <div class="col-auto text-center">
            <h1 class="text-center" style="color: rgb(222,72,62);">Éditez votre profil</h1>
        </div>
    </div>


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


    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/uploadfile.js"></script>
    <script src="assets/js/navbar.js"></script>

    <script type="text/javascript">
    
    var loadFile = function(event, id) {
        var output = document.getElementById(id);
        output.src = URL.createObjectURL(event.target.files[0]);
    };

    if (document.getElementsByName("longitude"))
        var longitude = document.getElementsByName("longitude")[0];
    if (document.getElementsByName("latitude"))
        var latitude  = document.getElementsByName("latitude")[0];

    function geolocaliser() {
        if (navigator.geolocation)
            navigator.geolocation.getCurrentPosition(getPos);
    }

    function getPos(position) {
        let button = document.getElementById("button");

        longitude.value = position.coords.longitude;
        latitude.value  = position.coords.latitude;
        button.innerHTML = 'Vous avez été correctement géolocalisé !';
        button.className = 'btn btn-success';
        button.setAttribute("disabled", "");
    }

    function removeLine(remove) {
        let tr = document.getElementById(remove);
        let xhr = new XMLHttpRequest();
        let url = window.location.href + '&unblock=' + remove.substr(5);

        tr.style = 'display:none';
        xhr.open('GET', url);
        xhr.send();
        console.log(xhr);
    }

    </script>

</body>

</html>