<?php

require_once('config/config.php');
require_once('Class/Front.php');
require_once('Class/Users.php');

$Front = new Matcha\Front($DB);
$Users = new Matcha\Users($DB);

$alert = $Front->restrictedPage();

$allowedDistance = array('5', '10', '20', '50', '100', '200', '500');
$allowedOrder    = array("interest", "popularity", "distance", "age");

if (isset($_GET['distance']) AND isset($_GET['order']) AND isset($_GET['age']) AND (isset($_GET['tri']) AND ($_GET['tri'] == 'ASC' OR $_GET['tri'] == 'DESC'))) {
    $distance = $_GET['distance'];
    $tri = $_GET['tri'];
    $age = explode('_', $_GET['age']);
    if (count($age) != 2 OR (!is_numeric($age[0]) AND !is_numeric($age[1])))
        header('Location: /');
    $orderGET = htmlentities($_GET['order']);
    if (!in_array($distance, $allowedDistance) OR !in_array($orderGET, $allowedOrder))
        header('Location: search.php');
    $result = $Users->searchMatcha($distance, $tri, $orderGET, $age[0], $age[1]);
    $search = true;
    $fem = null;
    $nbFound = $result->rowCount();
    if ($orderGET == "interest")
        $banner = "intérêts";
    else if ($orderGET == "popularity")
        $banner = "points";
    else if ($orderGET == "distance")
        $banner = "km";
    else if ($orderGET == "age") 
        $banner = "ans";

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
    <link rel="stylesheet" href="assets/css/profilcards.css">
</head>

<body style="background-image: url('assets/img/Matcha_bg_2.jpg');background-repeat: no-repeat; background-size: cover;">

    <?= $Front->navbar(); ?>

    <?php   if (!$search)
                include('inc/searchForm.php');
            else
                include('inc/results.php');
            ?>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script src="assets/js/cardhover.js"></script>
</body>

</html>