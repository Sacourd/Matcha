<?php

session_start();

require_once('../Class/Libft.php');
require_once('../Class/Front.php');
require_once('../Class/Users.php');

include('../config/db.php');
include('../config/log.php');

$Libft = new Matcha\Libft($DB);
$Front = new Matcha\Front($DB);
$Users = new Matcha\Users($DB);

$userid = -1;
if (isset($_SESSION['id'])) {
	$userid = $_SESSION['id'];
	$Libft->updateLastActivity($userid);
}

if ($userid == -1)
    header('Location: /');

if (!isset($_GET['dest']) OR !is_numeric($_GET['dest']))
    header('Location: /');

if ($Libft->countOcc('users', 'id', 'id = '.$_GET['dest']) == 0)
    header('Location: /');

$dest = $_GET['dest'];

if ($Users->inLove($userid, $dest) == 0)
	header('Location: /');

$profilPictureDest = $Libft->selectAndFetch('users', 'photos', "id = ?", array($dest))['photos'];
$profilpicture = explode('/', $profilPictureDest)[0];
echo $Front->getAllMessages($dest, $profilpicture);

$Libft->updateCol("messages", array("opened"), "(dest = $userid AND author = $dest)", array(1))

?>