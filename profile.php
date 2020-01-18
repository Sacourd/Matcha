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
else
    $page = null;

$authorizedPage = array("parameters", "personal", "gestion", "photos", "notif", "blocked");

if (isset($page) AND !in_array($page, $authorizedPage)) {
    $sec = 'edit';
    header('Location: profile.php');
}

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
$Users = new Matcha\Users($DB);

$accessDenied = null;
$head = 'Éditez votre profil';

if (isset($_POST['submit'])) {
    $alert = $Users->editProfile($_POST, $page);
}

if (isset($_GET['unblock']) AND is_numeric($_GET['unblock']) AND $userid != -1) {
    $unblock = $_GET['unblock'];
    if ($Libft->countOcc('blocked_users', 'id', 'user = '.$userid.' AND block = '.$unblock) > 0)
        $Libft->deleteSQL('blocked_users', 'user = '.$userid.' AND block = '.$unblock);
}

if (isset($_GET['view'])) {
    $accessDenied = $Front->restrictedPage();
    $sec = 'view';
    $idView = $_GET['view'];
    $head = 'Voir un profil';
    if (    ($Libft->countOcc('users', 'id', 'banned = 0 AND id = '.$idView) == 0)
        OR  $Libft->countOcc('blocked_users', 'id', '((user = '.$userid.' AND block = '.$idView.') OR (user = '.$idView.' AND block = '.$userid.'))') > 0)
        $accessDenied = '    <div class="row justify-content-center"><div class="col-4"><div class="alert alert-danger text-center" role="alert"><span><strong>Ce profil est inaccessible ou n\'existe pas.</strong><br></span></div></div></div>';
    else {
        $dataProfile = $Libft->selectAndFetch('users', '*', "id = ?", array($idView));
        $Users->addNotif($userid, $idView, 'view');
    }
    if (isset($_GET['block']) AND is_numeric($_GET['block']) AND $_GET['block'] != $userid) {
        $blockID = $_GET['block'];
        if ($Users->checkUserExists($blockID) AND $Libft->countOcc('blocked_users', 'id', 'user = '.$userid.' AND block = '.$blockID) == 0)
            $Libft->insertSQL("blocked_users", "user, block", array($userid, $blockID));
    }
    if (isset($_GET['like']) AND is_numeric($_GET['like']) AND $_GET['like'] != $userid) {
        $likeID = $_GET['like'];
        if ($Libft->countOcc('love', 'id', 'user = '.$userid.' AND likes = '.$likeID) == 0 AND $Libft->countOcc('love', 'id', 'user = '.$likeID.' AND likes = '.$userid) == 0)
            $Users->addNotif($userid, $likeID, 'like');
        else if ($Libft->countOcc('love', 'id', 'user = '.$userid.' AND likes = '.$likeID) == 1 AND $Libft->countOcc('love', 'id', 'user = '.$likeID.' AND likes = '.$userid) == 1)
            return ;
        else if ($Libft->countOcc('love', 'id', 'user = '.$userid.' AND likes = '.$likeID) == 0 AND $Libft->countOcc('love', 'id', 'user = '.$likeID.' AND likes = '.$userid) == 1)
            $Users->addNotif($userid, $likeID, 'dlike');

    }
    if (isset($_GET['dislike']) AND is_numeric($_GET['dislike']) AND $_GET['dislike'] != $userid) {

        $Users->addNotif($userid, $_GET['dislike'], 'dislike');
    }

    if (($sec == 'view' AND $accessDenied == NULL) AND isset($_GET['alert']) AND ($_GET['alert'] == 'alert' OR $_GET['alert'] == 'unalert'))
        $Users->alertUser($idView, $_GET['alert']);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/uploadfile.css">
    <link rel="stylesheet" href="assets/css/profilcard.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>

<body style="background-image: url('assets/img/Matcha_bg_2.jpg');background-repeat: no-repeat; background-size: cover;">

    <?= $Front->navbar(); ?>

    <div class="row text-center justify-content-center align-items-center" style="width: 100vw;margin-bottom: 42px;">
        <div class="col-auto text-center">
            <h1 class="text-center" style="color: rgb(222,72,62);"><?= $head ?></h1>
        </div>
    </div>

    <?= $accessDenied ?>

    <?php if (isset($sec) AND $accessDenied == null) echo $Front->viewProfil($dataProfile); else if ($accessDenied == null) include('inc/editprofile.php'); ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/uploadfile.js"></script>
    <script src="assets/js/loadFile.js"></script>
    <script src="assets/js/locate.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/ajaxreq.js"></script>

</body>

</html>