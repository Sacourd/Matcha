<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Seed</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
</head>

<body>
    <div class="row justify-content-center align-items-center justify-content-xl-center" style="width: 50%;margin-right: auto;margin-left: auto;">
        <div class="col" style="margin-top: 50%;">
            <div class="progress">
                <div id="percent" class="progress-bar bg-success progress-bar-striped progress-bar-animated" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">100%</div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center align-items-center justify-content-xl-center" style="width: 50%;margin-right: auto;margin-left: auto;">
        <div class="col text-center text-muted">

            <div style="margin-bottom: -13px;">
                <label class="text-center" style="display:none" id="bdd">
                Création de la base de données...</label>
            </div>

            <div style="margin-bottom: -13px;">
                <label class="text-center" style="display:none" id="label_hobby">
                Création des tags : <span id="doneHobby"></span> / <span id="totalHobby">79</span></label>
            </div>

            <div>
                <label id="label_users" class="text-center" style="display: none">
                    Création des comptes : <span id="doneUsers"></span> / <span id="totalUsers">691</span></label>
            </div>

            <div>
                <label id="nbTags" class="text-center" style="display: none">
                    Tags distribués : <strong><span id="distributed"></span></strong></label>
            </div>

            <div>
                <label id="popTag" class="text-center" style="display: none">
                    Tags les plus populaires : <strong><span id="popularTags"></span></strong></label>
            </div>
            <div>
                <label id="pass" class="text-center" style="display: none">
                    Mot de passe universel : <strong>CKSu!R5}3tw.efdz</strong></label>
            </div>

        </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-init.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script type="text/javascript">
        

        var loop = true;
        var madeTable = 0;
        var xhr = new XMLHttpRequest();
        if (madeTable < 9)
            xhr.open('GET', 'http://localhost/setup/index.php?db');
        else if (loop)
            xhr.open('GET', 'http://localhost/setup/index.php');
        xhr.send();

        setInterval(function() {
                let url = 'setup/progress.php';
                var self = this;
                        $.ajax({
                            url         :   url,
                            dataType    :   "HTML",
                            type        :   "GET",
                            success     :   function( response )
                            {
                                if (loop) {
                                console.log(response);
                                let req = JSON.parse(response);
                                madeTable = req.dbRow;
                                let nbHobby = req.nbHobby;
                                let nbUsers = req.nbUsers;
                                let progHobby = req.progHobby;
                                let progUsers = req.progUsers;
                                let distributedTags = req.distributedTags;
                                let popularTags = req.popularTags;

                                let progressBar = parseInt((100 * (progHobby + progUsers)) / (nbHobby + nbUsers));
                                document.getElementById('percent').innerHTML = progressBar + '%';
                                document.getElementById('totalHobby').innerHTML = nbHobby;
                                document.getElementById('totalUsers').innerHTML = nbUsers;
                                document.getElementById('doneHobby').innerHTML = progHobby;
                                document.getElementById('doneUsers').innerHTML = progUsers;
                                document.getElementById('distributed').innerHTML = distributedTags;
                                document.getElementById('percent').setAttribute('aria-valuenow', progressBar);
                                document.getElementById('percent').setAttribute('style', 'width: ' + progressBar + '%');

                                if (madeTable == 9) {
                                        document.getElementById('label_hobby').setAttribute('style', 'font-size: 13px;');
                                        document.getElementById('bdd').setAttribute('class', 'text-center text-success');
                                        document.getElementById('bdd').setAttribute('style', 'font-size: 13px;font-weight: bold;');
                                        document.getElementById('bdd').innerHTML = 'Base de données crée !';
                                    if (nbHobby == progHobby) {
                                        document.getElementById('label_hobby').setAttribute('class', 'text-center text-success');
                                        document.getElementById('label_hobby').setAttribute('style', 'font-size: 13px;font-weight: bold;');
                                        document.getElementById('label_users').setAttribute('style', 'font-size: 13px;');
                                        document.getElementById('nbTags').setAttribute('style', 'font-size: 13px;');
                                    }
                                    if (nbUsers == progUsers) {
                                        document.getElementById('label_users').setAttribute('class', 'text-center text-success');
                                        document.getElementById('label_users').setAttribute('style', 'font-size: 13px;font-weight: bold;');
                                        document.getElementById('nbTags').setAttribute('class', 'text-center text-success');
                                        document.getElementById('nbTags').setAttribute('style', 'font-size: 13px;font-weight: bold;');
                                        document.getElementById('popTag').setAttribute('style', 'font-size: 13px;');
                                        document.getElementById('pass').setAttribute('style', 'font-size: 13px;');
                                        document.getElementById('popularTags').innerHTML = popularTags;
                                        loop = false;
                                    }
                                }
                            }
                            }
                        })
        }, 500);

    </script>
</body>

</html>