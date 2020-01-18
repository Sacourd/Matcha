<?php

require_once('config/config.php');
require_once('Class/Front.php');
require_once('Class/Users.php');

if ($userid == -1)
    header('Location: /');

$Users = new Matcha\Users($DB);
$Front = new Matcha\Front($DB);

$id = 0;

if (isset($_GET['messages']) AND is_numeric($_GET['messages'])) {
    $id = $_GET['messages'];
    $Libft->updateCol("messages", array("opened"), "author = $id AND dest = $userid", array(1));
}

if (isset($_GET['clear']) AND $userid != -1)
    $Libft->updateCol("messages", array("opened"), "dest = $userid", array(1));

if (isset($_POST['msg']) AND !empty($_POST['msg']))
    $Users->newMessage($userid, $id, $_POST['msg']);

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
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/scrollbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
</head>

<body style="background-image: url('assets/img/Matcha_bg_2.jpg');background-repeat: no-repeat; background-size: cover;">

    <?= $Front->navbar(); ?>

    <div class="row no-gutters text-center justify-content-center align-items-center" style="width: 100vw;margin-bottom: 42px;">
        <div class="col-auto text-center">
            <h1 class="text-center" style="color: rgb(222,72,62);">Messagerie</h1>
        </div>
    </div>

    <div class="row">

        <?= $Front->messageList($id); ?>


            

              
                <?php 
                    if ($id != 0 AND $Libft->countOcc('messages', 'id', 'author = '.$id.' AND dest = '.$userid) > 0)
                        echo $Front->messagerie($id);
                ?>

                <?php if ($id != 0 AND $Users->inLove($id, $userid)) { ?>
                <form method="post" action="" id="chatbox">

                <div class="row no-gutters" style="background-color: #dadada;">
                    <div class="col-12 col-sm-12 col-md-10">
                        <textarea class="form-control form-control-sm" style="font-size: 14px;" minlength="1" name="msg" required></textarea>
                    </div>
                    <div class="col offset-0">
                        <button class="btn btn-danger btn-block btn-lg" type="submit" name="submit" style="height: 100%;">
                            <i class="far fa-paper-plane"></i>
                        </button>
                    </div>
                </div>

                </form>
                <?php } ?>
        
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script src="assets/js/navbar.js"></script>
    <script type="text/javascript">

        if (document.getElementsByClassName('sc-overflow')[0]) {
            var scrolldownMsg = document.getElementsByClassName('sc-overflow')[0];
            scrolldownMsg.scrollTop = scrolldownMsg.scrollHeight;
        }

        function getParameterByName(name, url) {
            if (!url) url = window.location.href;
                name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }

        if (scrolldownMsg != undefined) {
        setInterval(function() {
                let id = getParameterByName('messages');
                let url = 'inc/chatbox_reader.php?dest=' + id;
                let changed = false;
                var self = this;
                        $.ajax({
                            url         :   url,
                            dataType    :   "HTML",
                            type        :   "GET",
                            success     :   function( response )
                            {
                                let a = response.trim().length;
                                let b = document.getElementById('allMsg').innerHTML.trim().length;
                                let diff = Math.abs( a - b );
                                if ( diff > 100 ) {
                                    $("#allMsg").html(response);
                                    document.getElementsByClassName('sc-overflow')[0].scrollTop = document.getElementsByClassName('sc-overflow')[0].scrollHeight;
                                }
                            }
                        })
        }, 1000);
    }

        $(document).ready(function() {
            $("[data-link]").click(function() {
                window.location.href = $(this).attr("data-link");
                return false;
            });
        });

    </script>
</body>

</html>