<?php

require_once('config/config.php');
require_once('Class/Front.php');

$Front = new Matcha\Front($DB);

if ($userid == -1)
    header('Location: /');

$Libft->updateCol("notifications", array("opened"), "dest = $userid", array(1));

if (isset($_GET['clear']))
    $Libft->deleteSQL("notifications", "dest = $userid");

if (isset($_GET['delete']) AND is_numeric($_GET['delete'])) {
    $notifID = $_GET['delete'];
    if ($Libft->countOcc("notifications", "id", "dest = $userid AND id = $notifID") > 0)
        $Libft->deleteSQL("notifications", "id = $notifID");
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Matcha - Notifications</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Actor">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Titillium+Web:400,600,700">
    <link rel="stylesheet" href="assets/fonts/fontawesome-all.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/fontawesome5-overrides.min.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
</head>

<body style="background-image: url('assets/img/Matcha_bg_2.jpg');background-repeat: no-repeat; background-size: cover;">

    <?= $Front->navbar(); ?>

    <div class="row no-gutters text-center justify-content-center align-items-center" style="width: 100vw;margin-bottom: 42px;">
        <div class="col-auto text-center">
            <h1 class="text-center" style="color: rgb(222,72,62);">Notifications</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-10 offset-1 order-sm-2 order-md-2 order-lg-1 order-xl-1">
            <div class="border rounded" style="background-color: #ebebeb;">

                <?= $Front->notificationList(); ?>



                <div class="row no-gutters">
                    <div class="col text-center" style="margin-bottom: 12px;"><a class="text-danger" href="?clear" style="font-size: 13px;text-decoration: none">Tout supprimer</a></div>
                </div>


            </div>
        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script type="text/javascript">
        
        var xhr = new XMLHttpRequest();

        function request(url) {
            xhr.open('GET', url);
            xhr.send();
        }

        function removeNotif(id) {
            document.getElementsByClassName(id)[0].remove();
            let url = window.location.href + '?delete=' + id;
            request(url);
        }

    </script>
</body>

</html>