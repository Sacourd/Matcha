<?php

namespace Matcha;

use \PDO;

class Front extends Libft {

	public $db;
    private $session = 0;
    private $pagename;

	public function __construct($db, $pagename = null) {
		$this->db = $db;
        $this->pagename = $pagename;
        if (isset($_SESSION['id']))
            $this->session = $_SESSION['id'];
	}


    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                           navbar                             ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    private function linkNavbar($pagename, $link) {
        return ("<li class=\"nav-item\" role=\"presentation\"><a class=\"nav-link\" href=\"$link\">$pagename</a></li>");
    }

    private function dropDown() {
        $alert          = null;
        $alert_msg      = null;
        $alert_notif    = null;

        $session = $this->session;

        $nbAlerts     = $this->countOcc('notifications', 'id', 'dest = '.$session.' AND opened = 0');
        $nbMsgs   = $this->countOcc('messages', 'id', 'dest = ".$session." AND opened = 0');

        if ($nbAlerts > 0) {
            $alert = 'text-danger';
            $alert_notif = 'text-danger';
            $nbAlerts = '('.$nbAlerts.')';
        }
        else
            $nbAlerts = null;

        if ($nbMsgs > 0) {
            $alert = 'text-danger';
            $alert_msg = 'text-danger';
            $nbMsgs = '('.$nbMsgs.')';
        }
        else
            $nbMsgs = null;

        $Content = '<li class="dropdown nav-item">
        <a class="dropdown-toggle nav-link '.$alert.'" data-toggle="dropdown" aria-expanded="false" href="/notifs.php">Notifications</a>
        <div class="dropdown-menu dropdown-menu-right" role="menu">
        <a class="dropdown-item '.$alert_notif.'" role="presentation" href="notifications.php">Du neuf ? '.$nbAlerts.'</a>
        <a class="dropdown-item '.$alert_msg.'" role="presentation" href="messages.php">Messagerie '.$nbMsgs.'</a></div>
        </li>';
        return ($Content);
    }

    public function navbar() {

        if ($this->pagename == 'index')
            $attribute = 'fixed-top';
        else
            $attribute = 'sticky-top';

        $Content = '<nav class="navbar navbar-light navbar-expand-lg '.$attribute.' bg-white transparency border-bottom border-light" id="transmenu">
        <div class="container">
        <a class="navbar-brand text-success" href="/">
        <i class="fas fa-fire fa-2x"></i> Z\'INA
        </a>
        <button data-toggle="collapse" class="navbar-toggler collapsed" data-target="#navcol-1" style="color: rgba(255,0,0,0.5);"><span class="text-secondary" style="background-color: rgb(18,159,6);"></span><span style="background-color: rgb(18,159,6);"></span><span style="background-color: rgb(18,159,6);"></span></button>';

        $Content .= '<div class="collapse navbar-collapse" id="navcol-1"><ul class="nav navbar-nav ml-auto">';
        $Content .= $this->linkNavbar('Accueil', '/');
        if ($this->session == 0) {
            $Content .= $this->linkNavbar('Nous rejoindre', '/register.php');
            $Content .= $this->linkNavbar('Connexion', '/login.php');
        }
        else {
            $Content .= $this->dropDown();
            $Content .= $this->linkNavbar('Mon profil', '/profile.php');
            $Content .= $this->linkNavbar('Déconnexion', '/?logout');
        }
        $Content .= '</ul></div></div></nav>';
        return ($Content);
    }


    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                         sideform                             ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    private function tHead($title) {
        $Content = "<thead><tr><th>$title</th></tr></thead>";
        return ($Content);
    }

    private function trLine($title, $page) {
        if ($page == $this->pagename)
            $Content = '<tr style="background-color: #2c2c2c"><td class="text-secondary">'.$title.'</td></tr>';
        else
            $Content = '<tr><td><a href="?page='.$page.'" class="link">'.$title.'</a></td></tr>';
        return ($Content);
    }

    public function sideForm() {
        $Content = '<table class="table table-striped table-bordered table-hover table-dark">';
        $Content .= $this->tHead('Compte');
        $Content .= '<tbody>';
        $Content .= $this->trLine('Paramètres du compte', 'parameters');
        $Content .= $this->trLine('Informations personnelles', 'personal');
        $Content .= $this->trLine('Gestion du compte', 'gestion');
        $Content .= $this->trLine('Photos', 'photos');
        $Content .= $this->tHead('Divers');
        $Content .= $this->trLine('Gestion des notifications', 'notif');
        $Content .= $this->trLine('Utilisateurs bloqués', 'blocked');
        $Content .= '</tbody></table>';
        return ($Content);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                         editform                             ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    private function formGroup($type, $name, $title, $value = null, $placeholder = null) {
        $Content = '<div class="form-group"><label>'.$title.'</label><input class="form-control inputForm" type="'.$type.'" name="'.$name.'" placeholder="'.$placeholder.'" value="'.$value.'"></div>';
        return ($Content);
    }

    private function selectGroup($title, $name, $values, $selectedValue) {
        $Content = '<div class="form-group"><label>'.$title.'</label><select class="form-control" style="width: 80%;" name="'.$name.'">';
        foreach ($values as $key => $value) {
            $selected = null;
            if ($selectedValue == $value)
                $selected = 'selected';
            $Content .= '<option value="'.$value.'" '.$selected.'>'.$key.'</option>';
        }
        $Content .= '</select></div>';
        return ($Content);
    }

    private function checkbox($label, $name, $value) {
        $checked = null;
        if ($value == 1)
            $checked = 'checked';
        $Content = '<div class="form-check"><input class="form-check-input" type="checkbox" id="'.$name.'" name="'.$name.'" '.$checked.'><label class="form-check-label" for="'.$name.'">'.$label.'</label></div>';
        return ($Content);
    }

    public function profileForm() {
        $value = array($this->session);
        $userInfos = $this->selectAndFetch("users", "*", "id = ?", $value);

        if ($this->pagename == 'parameters') {
            $Content = $this->formGroup("text", "username", "Nom d'utilisateur", $userInfos['username']);
            $Content .= $this->formGroup("email", "email", "Adresse email", $userInfos['email']);
            $Content .= $this->formGroup("password", "oldpass", "Ancien mot de passe");
            $Content .= $this->formGroup("password", "newpass", "Nouveau mot de passe");
            $Content .= $this->formGroup("password", "newpass-c", "Confirmez mot de passe");
        }

        if ($this->pagename == 'personal') {
            $sex     = array(
                "Homme"  => "M",
                "Femme"  => "F",
                "Autre"  => "O");
            $kink    = array(
                "Hétéro" => 0,
                "Gay"    => 1,
                "Bi"     => 2);
            $Content = $this->formGroup("text", "firstname", "Prénom", $userInfos['firstname']);
            $Content .= $this->formGroup("text", "lastname", "Nom", $userInfos['lastname']);
            $Content .= $this->formGroup("date", "birthday", "Date de naissance", $userInfos['birthday']);
            $Content .= $this->selectGroup("Sexe", "sex", $sex, $userInfos['gender']);
            $Content .= $this->selectGroup("Orientation", "kink", $kink, $userInfos['kink']);
            $Content .= '<div class="form-group"><button class="btn btn-info" type="button" style="width: 80%;" onclick="geolocaliser()" id="button"><i class="fas fa-map-marker-alt"></i> Géolocaliser</button></div>';
            $Content .= '<input type="hidden" name="latitude" value="">   <input type="hidden" name="longitude" value="">';
        }

        if ($this->pagename == 'gestion') {
            $usedTag = $this->selectAndFetch("tags INNER JOIN tags_users ON userid = ?", "name", "tags.id = tag", $value, 1);
            $i = 0;
            $tagsList = null;
            while (isset($usedTag[$i]['name']))
                $tagsList .= '#'.$usedTag[$i++]['name'].' ';
            $Content = $this->formGroup("text", "tags", "Centres d'intérêts (commence par un #, séparés par des espaces, maximum 10)", $tagsList, "Exemple: #Animes, #Jojo, #CoconutBackBreaker, ...");
            $bio = $this->br2nl($userInfos['bio']);
            $Content .= '<div class="form-group"><label>Bio pour vous présenter</label><textarea class="form-control" placeholder="350 caractères maximum..." name="bio" rows=5 style="width: 80%" maxlength=350>'.$bio.'</textarea></div>';
        }

        if ($this->pagename == 'photos') {
            $listPhotos = explode('/', $userInfos['photos']);
            $photos = array();
            $photos[0] = $listPhotos[0];
            if ($photos[0] == 'default.jpg')
                $photos[0] = 'default';
            $i = 1;
            while (isset($listPhotos[$i])) {
                if ($listPhotos[$i] == null) 
                    $photos[$i] = 'empty.png';
                else
                    $photos[$i] = $listPhotos[$i];
                $i++;
            }
            $Content = '<div class="alert alert-info" role="alert" style="width: 80%;"><span><strong>Format autorisés</strong> : jpg, jpeg, png<br /> <strong>Taille max</strong> : 800ko</span></div><div class="form-row text-center"><div class="col">
                        <img class="rounded-circle" src="/assets/img/profilpicture/'.$photos[0].'.jpg" style="width: 109px;height:109px" id="profilpic"><div class="form-row"><div class="col" style="margin-top: -9px;"><label class="col-form-label">Photo de profil</label></div></div></div></div>';
            $Content .= '<div class="form-row text-center"><div class="col"><div style="margin-top: -44px;"><input type="file" id="user_group_logo" class="custom-file-input" accept="image/*" name="profilpic" onchange="loadFile(event, \'profilpic\')"><div class="text-center"><label id="user_group_label" for="user_group_logo"><i class="fas fa-upload"></i> Changer sa photo de profil</label></div></div></div></div><div style="margin-top: 14px;margin-bottom: 19px;"><div role="tablist" id="accordion-1" style="width: 80%;">';

            $j = 0;
            $i = 1;
            $tab = array("Première photo", "Deuxième photo", "Troisième photo", "Quatrième photo");
            while ($j <= 3) {
                $Content .= '<div class="card"><div class="card-header" role="tab"><h5 class="mb-0"><a data-toggle="collapse" aria-expanded="false" aria-controls="accordion-1 .item-'.$i.'" href="#accordion-1 .item-'.$i.'">'.$tab[$j].'</a></h5></div><div class="collapse item-'.$i.'" role="tabpanel" data-parent="#accordion-1"><div class="card-body"><img src="assets/img/photos/'.$photos[$i].'" style="max-width: 98%;" id="photo_'.$i.'"><input type="file" accept="image/*" onchange="loadFile(event, \'photo_'.$i.'\')" name="photo'.$i.'"></div></div></div>';
                $i++;
                $j++;
            }
            $Content .= '</div></div>';
        }

        if ($this->pagename == 'notif') {
            $Content = '<div style="margin-bottom:80px;margin-top:20px">';
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne visite mon profil", "view", $userInfos['mail_view']);
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne aime mon profil", "like", $userInfos['mail_like']);
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne aime mon profil en retour", "dlike", $userInfos['mail_dlike']);
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne cesse d'aimer mon profil", "dislike", $userInfos['mail_dislike']);
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne m'envoi un message", "msg", $userInfos['mail_msg']);
            $Content .= '</div>';
        }
        
        if ($this->pagename == 'blocked') {
            $Content = '<div class="table-responsive" style="width: 80%;"><table class="table table-striped table-hover"><thead><tr><th>Nom d\'utilisateur</th><th>Action</th></tr></thead>';
            if ($this->countOcc('blocked_users', 'id', 'user = '.$this->session) == 0)
                return ($Content.'<caption>Aucun utilisateur de bloqué</caption></table></div>');
            $value = array($this->session);
            $blockedUsers = $this->selectAndFetch("users INNER JOIN blocked_users", "username, users.id", "user = ? AND users.id = blocked_users.block", $value, 1);
            $i = 0;
            while (isset($blockedUsers[$i])) {
                $Content .= '<tr id="user_'.$blockedUsers[$i]['id'].'"><td>'.$blockedUsers[$i]['username'].'</td><td>
                <span style="color:inherit;text-decoration:none" onclick="removeLine(\'user_'.$blockedUsers[$i]['id'].'\')">
                <i class="fas fa-lock-open" style="color: rgb(255,39,39);"></i></span></td></tr>';
                $i++;
            }
            $Content .= '</tbody></table></div>';
        }

        return ($Content);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                     bouton de l'index                        ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////


    public function indexButton() {

        if ($this->session == 0)
            return ('<a href="register.php"><button class="button" type="button" data-hover="Nous rejoindre" style="background-color:#FF6E6E;color:red;opacity: 70%;border-color:red"> <span>Inscription</span></button></a>');

        $userInfos = $this->selectAndFetch("users", "photos", "id = ?", array($this->session))['photos'];
        $properPhotos = explode('/', $userInfos);
        if ($properPhotos[0] == 'default.jpg')
            return ('<a href="profile.php?page=photos"><button class="button" type="button" data-hover="Préparation" style="background-color:#FF6E6E;color:red;opacity: 70%;border-color:red"> <span>Photo</span></button></a>');
        else
            return ('<a href="search.php"><button class="button" type="button" data-hover="Retrouvez" style="background-color:#FF6E6E;color:red;opacity: 70%;border-color:red"> <span>Recherchez</span></button></a>');
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                    restriction des pages                     ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////


    public function restrictedPage() {
        if ($this->session == 0)
            header('Location: /');
        $userInfos = $this->selectAndFetch("users", "photos, bio, firstname, lastname, birthday", "id = ?", array($this->session));

        if ($this->countOcc('tags_users', "id", "userid = ".$this->session) == 0)
            return ('<div class="alert alert-danger" role="alert">Vous devez renseigner des centres d\'intérêts avant de continuer, vous pouvez le faire <a href="profile.php?page=gestion">ici</a></div>');

        $properPhotos = explode('/', $userInfos['photos']);
        if ($properPhotos[0] == 'default.jpg')
            return ('<div class="alert alert-danger" role="alert">Il vous faut une photo de profil pour continuer. Vous pouvez en choisir une <a href="profile.php?page=photos">ici</a></div>');

        if ($userInfos['bio'] == null)
            return ('<div class="alert alert-danger" role="alert">Vous devez renseigner une bio pour continuer. Vous pouvez en renseigner une <a href="profile.php?page=gestion">ici</a></div>');

        if ($userInfos['firstname'] == null OR $userInfos['lastname'] == null)
            return ('<div class="alert alert-danger" role="alert">Vouys devez renseigner votre nom complet pour continuer. Vous pouvez le renseigner <a href="profile.php?page=personal">ici</a></div>');

        if ($userInfos['birthday'] == null)
            return ('<div class="alert alert-danger" role="alert">Vous devez renseigner une date de naissance <a href="profile.php?page=personal">ici</a></div>');


    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                  affichage des resultats                     ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function printResults($query, $banner) {
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $i = 0;
        $Content = '<div class="container mt-5"><div class="row">';
        while (isset($results[$i])) {
            $profilPicture = explode('/', $results[$i]['photos'])[0].'.jpg';
            $userid        = $results[$i]['user_id'];
            $username      = $results[$i]['username'];
            $popularity    = $results[$i]['popularity'];
            $age           = $results[$i]['Age'];
            $distance      = $results[$i]['distance'];
            $interest      = $results[$i]['interest'];
            if ($banner == 'intérêts')
                $ribbon = $interest.' INTÉRÊTS';
            if ($banner == 'km')
                $ribbon = round($distance, 2).' KM';
            if ($banner == 'points')
                $ribbon = $popularity.' POINTS';
            if ($banner == 'ans')
                $ribbon = round($age, 0).' ANS';

            $Content .= '<div class="col-md-3  model-card"><a href="profile.php?view='.$userid.'" target="_blank"><div class="ribbon"><span>'.$ribbon.'</span></div><div class="bsblox-image-effect16"><img class="img-fluid" src="assets/img/profilpicture/'.$profilPicture.'" /><div class="bsblox-caption"><p>VOIR<strong> LE PROFIL</strong></p></div><div class="bsblox-links"><a class="profile-link" href="profile.php?view='.$userid.'" target="_blank"><i class="fa fa-search fa fa-search"></i></a></div><div class="text-center model-card-title">'.$username.'</div></div></a></div>';
            $i++;
        }
        $Content .= '</div></div>';
        return ($Content);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                  visionner un profil                         ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////


    private function getProfilPicture($photos) {
        return (explode('/', $photos)[0]);
    }

    private function likeButton($userid) {

        if ($this->session == $userid)
            return (null);

        if ($this->countOcc('love', "id", "user = ".$this->session." AND likes = $userid") == 0) {
            $func = 'onclick="like(\''.$userid.'\', \'like\')"';
            $outline = '-outline-';
        }
        else {
            $func = 'onclick="like(\''.$userid.'\', \'dislike\')"';
            $outline = '-';
        }
        $Content = '<button class="btn btn'.$outline.'danger border rounded-circle border-danger" type="button" '.$func.'><i class="fas fa-heart"></i></button>';
        return ($Content);
    }

    private function blockButton($userid) {
        if ($this->session == $userid)
            return (null);
        $Content = '<button class="btn btn-outline-danger border rounded-circle border-danger" type="button" onclick="block(\''.$userid.'\')"><i class="fas fa-lock"></i></button>';
        return ($Content);
    }

    private function alertButton($userid) {
        if ($this->session == $userid)
            return (null);

        if ($this->countOcc('alerts', "id", "user = ".$this->session." AND to_ban = $userid") == 0) {
            $func = 'onclick="alertUser(\''.$userid.'\', \'alert\')"';
            $outline = '-outline-';
        }
        else {
            $func = 'onclick="alertUser(\''.$userid.'\', \'unalert\')"';
            $outline = '-';
        }
        $Content = '<button class="btn btn'.$outline.'danger border rounded-circle border-danger" type="button" '.$func.' style="margin-top: 33px;"><i class="far fa-flag"></i></button>';
        return ($Content);
    }

    private function loggedIn($logged, $last_activity) {
        if ($logged == 1) {
            $Content = '<h6 class="text-success card-subtitle mb-2"><strong>En ligne</strong><br></h6>';
            return ($Content);
        }
        $Content = '<h6 class="text-danger card-subtitle mb-2"><strong>Hors ligne</strong></h6><h6 class="text-muted card-subtitle mb-2" style="font-size: 13px;">Dernière activité il y a '.$this->humanTiming($last_activity).'<br><br><br></h6>';
        return ($Content);
    }

    private function getSex($gender, $kink) {
        $fem = null;
        if ($gender == 'M')
            $sex = 'Homme';
        else if ($gender == 'F') {
            $sex = 'Femme';
            $fem = 'le';
        }
        else
            $sex = 'Autre';

        if ($kink == 0)
            $orientation = 'Hétérosexuel'.$fem;
        else if ($kink == 1)
            $orientation = 'Homosexuel'.$fem;
        else
            $orientation = 'Bi';

        return ('<strong>'.$sex.' '.$orientation.'</strong>');

    }

    private function getAge($birthday) {
        $today = date("Y-m-d");
        $diff = date_diff(date_create($birthday), date_create($today));
        return ('<br><strong>Âge:</strong> '.$diff->format('%y'));

    }

    private function printGallery($photos) {
        $i = 0;
        $Content = '<div class="row justify-content-center" style="margin-top: 19px;margin-bottom: 24px;">';
        while ($i <= count($photos)) {
            if (isset($photos[$i]))
                $Content .= '<div class="col-auto"><a href="assets/img/photos/'.$photos[$i].'" target="_blank"><img src="assets/img/photos/'.$photos[$i].'" style="width: 100px;"></a></div>';
            $i++;
        }
        $Content .= '</div>';
        return ($Content);
    }

    private function interestList($userid, $i = 0) {
        $usedTag = $this->selectAndFetch("tags INNER JOIN tags_users ON userid = ?", "name", "tags.id = tag", array($userid), 1);
        $userTag = $this->selectAndFetch("tags INNER JOIN tags_users ON userid = ?", "name", "tags.id = tag", array($this->session), 1);
        $tagsList = null;
        $j = 0;
        $tagsUsers = array();
        while (isset($userTag[$j]['name']))
            $tagUsers[$j] = $userTag[$j++]['name'];
        while (isset($usedTag[$i]['name'])) {
            $tag = '#'.$usedTag[$i]['name'].' ';
            if (in_array($usedTag[$i]['name'], $tagUsers) AND $userid != $this->session)
                $tagsList .= '<strong>'.$tag.'</strong> ';
            else
                $tagsList .= "<i>$tag</i>";
            $i++;
        }
        return ($tagsList);
    }

    private function interestedByYou($userid) {
        if ($this->countOcc('love', 'id', 'user = '.$userid.' AND likes = '.$this->session) AND $this->countOcc('love', 'id', 'user = '.$this->session.' AND likes = '.$userid) == 0)
            return ('<h6 class="text-danger card-subtitle mb-2"><strong>s\'intéresse à vous !</strong></h6>');
    }

    public function viewProfil($userinfos) {

        $Content = '<div class="row no-gutters justify-content-center" style="margin-top: 19px;"><div class="col-sm-12 col-md-10 col-lg-9 col-xl-7"><div class="profile-card" style="background-color: #150000; height: 100%; background-image: url(\'assets/img/bg.jpg\');"><div class="profile-back" style="background-image: url(\'assets/img/sky.jpg\');background-position: left;background-size: cover;background-repeat: repeat-y;"></div>';
        $profilPicture = $this->getProfilPicture($userinfos['photos']).'.jpg';
        $Content .= '<img class="rounded-circle profile-pic" src="assets/img/profilpicture/'.$profilPicture.'"><div class="row"><div class="col text-right">';
        $Content .= $this->likeButton($userinfos['id']);
        $Content .= '</div><div class="col-3"><h3 class="profile-name">'.$userinfos['username'].'</h3></div><div class="col text-left">';
        $Content .= $this->blockButton($userinfos['id']);
        $Content .= '</div></div><div class="card" style="margin-top: 17px;"><div class="card-body">';
        $Content .= $this->loggedIn($userinfos['logged'], $userinfos['last_activity']);
        $getPos  = $this->selectAndFetch("users", "latitude, longitude", "id = ?", array($this->session));

        $distance = round($this->haversine($getPos['latitude'], $getPos['longitude'], $userinfos['latitude'], $userinfos['longitude']), 2);

        $Content .= '<h6 class="text-muted card-subtitle mb-2"><strong>Distance</strong>: '.$distance.' km<br><strong>Score</strong>: <span id="score">'.$userinfos['popularity'].'</span> points<br><strong>Nom: </strong>'.$userinfos['firstname'].' '.$userinfos['lastname'].'<br>';

        $Content .= $this->getSex($userinfos['gender'], $userinfos['kink']);
        $Content .= $this->getAge($userinfos['birthday']);
        $Content .= $this->interestedByYou($userinfos['id']);
        $Content .= '</h6>';

        $photolist = explode('/', $userinfos['photos']);
        unset($photolist[0]);
        $photos = array_filter($photolist);

        if (count($photos) > 0)
            $Content .= $this->printGallery($photos);

        $Content .= '<p class="lead text-center card-text">'.$userinfos['bio'].'</p>';
        $Content .= '<label class="text-muted">Centres d\'intérêts : '.$this->interestList($userinfos['id']).'</label></div></div>';
        $Content .= $this->alertButton($userinfos['id']);
        $Content .= '</div></div></div>';

        return ($Content);

    }

    private function inLove($user_1, $user_2) {
        $user1_love = $this->countOcc('love', 'id', "user = $user_1 AND likes = $user_2");
        $user2_love = $this->countOcc('love', 'id', "user = $user_2 AND likes = $user_1");
        if ($user1_love + $user2_love == 2)
            return (1);
        return (0);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                     messagerie instantanée                   ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////


    public function messageList($id) {
        if ($this->countOcc('messages', 'id', 'author = '.$this->session.' OR dest = '.$this->session) == 0)
            header('Location: /');
        $statement = 'SELECT *, MAX(time) as maxtime FROM messages WHERE dest = '.$this->session.' GROUP BY author HAVING maxtime ORDER BY time DESC';
        $love = 0;
        $query = $this->db->query($statement);
        $Content = '<div class="col-10 col-sm-10 col-md-10 col-lg-4 col-xl-4 offset-1 order-sm-2 order-md-2 order-lg-1 order-xl-1 " >
            <div class="border rounded" style="background-color: #ebebeb;">';
        $addedUser = array();
        while ($authorList = $query->fetch(PDO::FETCH_ASSOC)) {
            if ($this->inLove($this->session, $authorList['author'])) {
                $love++;
                $userid = $authorList['author'];
                $userInfos = $this->selectSQL('users', "photos, username", array("id"), array($userid));
                $profilPicture = $this->getProfilPicture($userInfos['photos']);
                $username = $this->getProfilPicture($userInfos['username']);
                $msg = substr($authorList['message'], 0, 60);
                if (strlen($msg) < strlen($authorList['message']))
                    $msg = $msg.' ...';
                $selected = null;
                $label = null;
                if ($userid == $id) 
                    $selected = 'selected';

                if ($authorList['opened'] == 0) {
                    $msg = "<strong>$msg</strong>";
                    $label = '<span class="badge badge-danger text-monospace" style="margin-left: 9px;">'.$this->countOcc('messages', 'id', 'author = '.$userid.' AND opened = 0 AND dest = '.$this->session).'</span>';
                }

                $Content .= '
                <div class="messageslist">
                <div data-link="messages.php?messages='.$userid.'" class="row no-gutters '.$selected.'" style="margin-bottom: 12px;margin-top: 12px;">

                <div class="col-auto text-center d-xl-flex justify-content-xl-center align-items-xl-center" style="width: 108px;">
                    <img class="border rounded-circle shadow" src="assets/img/profilpicture/'.$profilPicture.'" style="height: 98px;max-height: 71px;width: 98px;max-width: 71px;">
                </div>
                
                <div class="col justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><p style="font-size: 13px;">
                <strong><a href="profile.php?view='.$userid.'" target="_blank" style="color:inherit;text-decoration:none">'.$username.'</a></strong>'.$label.'<br>'.$msg.'</p>
                </div>

                <div class="col-auto text-secondary" style="margin-right: 8px;margin-top: 2px;font-size: 10px;">
                    <p class="d-xl-flex justify-content-xl-end">'. strftime("%H:%M", strtotime($authorList['time'])).'</p>
                </div>

                </div></div>';
            }
        }
        if ($love == 0)
            $Content .= '<div class="row no-gutters">
                    <div class="col-form-label text-black-50 " style="margin-bottom: 8px;">Aucun message pour le moment...</div>
                </div>';
        else
            $Content .= '<div class="row no-gutters">
                    <div class="col text-center" style="margin-bottom: 12px;"><a class="text-center" href="?messages=<?= $id ?>&clear" style="font-size: 13px;">Marquer tout comme lu</a></div>
                </div>';
        return ($Content.'</div>
        </div>');

    }

    public function getAllMessages($dest, $profilPicture) {
        $i = 0;
        $author = $this->session;
        $value = array($author, $dest, $dest, $author);
        $query = $this->selectAndFetch("messages", "author, dest, message, time", "(author = ? AND dest = ?) OR (author = ? AND dest = ?) ORDER BY time ASC", $value, 1);
        
        $sessionInfos = $this->selectAndFetch("users", "photos", "id = ?", array($this->session));
        $S_ProfilPicture = $this->getProfilPicture($sessionInfos['photos']);
        $Content = '<div><section style="color: rgb(113,113,113);background-color: #ffffff;padding: 30px;"><div class="container scstyle-2 sc-overflow"><article style="height: 100%;">';
        while (isset($query[$i])) {
            $time = strftime("%H:%M", strtotime($query[$i]['time']));
            $query[$i]['message'] = str_replace(':dealwithkicausse:', '<img src="/assets/img/emoji/dealwithkicausse.png">', $query[$i]['message']);
            $query[$i]['message'] = str_replace(':joyjoy:', '<img src="/assets/img/emoji/joyjoy.png">', $query[$i]['message']);
            $query[$i]['message'] = str_replace(':)', '<img src="/assets/img/emoji/smile.png">', $query[$i]['message']);
            $query[$i]['message'] = str_replace('&lt;3', '<img src="/assets/img/emoji/heart.png">', $query[$i]['message']);
            if ($query[$i]['author'] == $dest)
                $Content .= '<div class="row no-gutters" style="margin-bottom: 38px;margin-top: 12px;"><div class="col-auto text-center d-xl-flex justify-content-xl-center align-items-xl-center" style="width: 108px;"><img class="border rounded-circle shadow" src="/assets/img/profilpicture/'.$profilPicture.'" style="height: 98px;max-height: 71px;width: 98px;max-width: 71px;"></div><div class="col-5 justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><div class="row"><div class="col-12" style="margin-bottom: -14px;"><div class="bg-info border rounded border-info"><p class="text-white-50" style="font-size: 15px;margin-top: 11px;margin-left: 23px;margin-right: 17px;margin-bottom: 31px;">'.$query[$i]['message'].'<br><br></p><p class="text-white-50" style="font-size: 10px;margin-top: -29px;margin-left: 10px;margin-bottom: 4px;">'.$time.'<br></p></div></div></div></div></div>';
            else
                $Content .= '<div class="row no-gutters d-xl-flex justify-content-xl-end" style="margin-bottom: 38px;margin-top: 12px;"><div class="col-5 justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><div class="row"><div class="col" style="margin-bottom: -14px;"><div class="bg-danger border rounded border-danger"><p class="text-right text-white-50" style="font-size: 15px;margin-top: 11px;margin-left: 17px;margin-bottom: 31px;margin-right: 23px;">'.$query[$i]['message'].'</p><p class="text-right text-white-50" style="font-size: 10px;margin-bottom: 4px;margin-top: -29px;margin-right: 10px;">'.$time.'<br></p></div></div></div></div><div class="col-auto text-center d-xl-flex justify-content-xl-center align-items-xl-center" style="width: 108px;"><img class="border rounded-circle shadow" src="/assets/img/profilpicture/'.$S_ProfilPicture.'" style="height: 98px;max-height: 71px;width: 98px;max-width: 71px;"></div></div>';
            $i++;
        }
        $Content .= '</article></div></section></div>';
        return ($Content);

    }

    public function messagerie($userid) {
        $user1_love = $this->countOcc('love', 'id', "user = $this->session AND likes = $userid");
        $user2_love = $this->countOcc('love', 'id', "user = $userid AND likes = $this->session");
        if ($user1_love + $user2_love != 2)
            return ;
        $Content = '<div class="col-10 col-sm-10 col-md-10 col-lg-6 col-xl-6 offset-1 offset-lg-0 offset-xl-0 order-1 order-sm-2 order-md-2 order-lg-1 order-xl-1">
            <div class="border rounded" style="background-color: #ffffff;">
            <div class="row no-gutters" style="margin-bottom: 12px;margin-top: 12px;background-color: #dadada;height: 82px;">
            <div class="col-auto text-center d-xl-flex justify-content-xl-center align-items-xl-center" style="width: 108px;">';
        $value = array($userid);
        $userInfos = $this->selectAndFetch("users", "username, bio, photos", "id = ?", $value);
        $username = $userInfos['username'];
        $profilPicture = $this->getProfilPicture($userInfos['photos']);
        $bio = $userInfos['bio'];

        $Content .= '<a href="profile.php?view='.$userid.'"><img class="border rounded-circle shadow" src="assets/img/profilpicture/'.$profilPicture.'" style="height: 98px;max-height: 71px;width: 98px;max-width: 71px;"></a></div> <div class="col justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><p class="text-secondary" style="font-size: 13px;"><strong>'.$username.'</strong><br>'.$bio.'</p></div></div><div id ="allMsg">';

        $Content .= $this->getAllMessages($userid, $profilPicture);

        $Content .= '</div>';

        return ($Content);

    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////
    ///                     notifications list                       ///
    ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////

    public function notificationList() {
        if ($this->session == -1)
            header('Location: /');
        $i = 0;
        $notifsAll = $this->selectAndFetch("notifications", "id, author, type, time", "dest = ? ORDER BY time DESC", array($this->session), 1);
        $Content = null;
        while ($i < count($notifsAll)) {
            $bg = null;
            $userid = $notifsAll[$i]['author'];
            $type = $notifsAll[$i]['type'];
            $notifId = $notifsAll[$i]['id'];
            if ($i % 2)
                $bg = 'background-color: #d9d9d9;';
            $userinfos = $this->selectAndFetch("users", "username, id, photos", "id = ?", array($userid));
            $profilPicture = $this->getProfilPicture($userinfos['photos']).'.jpg';
            $userLink = '<a href="profile.php?view='.$userid.'" style="text-decoration:none;color:inherit"><strong>'.$userinfos['username'].'</strong></a>';

            if ($type == 'like')
                $msg = '<strong>On vous apprécie !</strong><br>'.$userLink.' a aimé votre profil !<br>';
            else if ($type == 'dlike')
                $msg = '<strong>C\'est le coup de foudre !</strong><br>'.$userLink.' a aimé votre profil en retour !<br>';
            else if ($type == 'unlike')
                $msg = '<strong>Changement d\'avis...</strong><br>'.$userLink.'  n\'aime plus votre profil...<br>';
            else if ($type == 'view')
                $msg = '<strong>Visite du profil</strong><br>'.$userLink.' a visité votre profil !<br>';
            else if ($type == 'break')
                $msg = '<strong>La fin d\'une histoire.</strong><br>Désolé... Mais '.$userLink.' ne s\'intéresse plus à vous.<br>';

            $time = 'IL Y A '.strtoupper($this->humanTiming($notifsAll[$i]['time']));

            $Content .= '<div class="row no-gutters '.$notifId.'" style="margin-bottom: 12px;margin-top: 12px;'.$bg.'"><div class="col-auto text-center d-xl-flex justify-content-xl-center align-items-xl-center" style="width: 26px;margin-left: 18px;"><img class="border rounded-circle shadow" src="assets/img/profilpicture/'.$profilPicture.'" style="height: 40px;width: 40px;"></div><div class="col justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><p style="font-size: 13px;margin-bottom: 2px;">'.$msg.'</p><p class="text-black-50" style="font-size: 10px;">'.$time.'</p></div><div class="col-auto d-flex align-items-center" style="margin-right: 18px;" onclick="removeNotif('.$notifId.')"><i class="far fa-times-circle" style="color: rgb(232,0,0);"></i></div></div>';

            $i++;
        }

        if ($i == 0)
            $Content .= '<div class="row no-gutters" style="margin-bottom: 12px;margin-top: 12px;"><div class="col justify-content-xl-start align-items-xl-center" style="margin-left: 20px;"><p class="text-center text-black-50" style="font-size: 10px;"><em>Aucune notification ...</em></p></div></div>';

        return ($Content);

    }


}

?>