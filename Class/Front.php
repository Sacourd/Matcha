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
        <a class="dropdown-item '.$alert_notif.'" role="presentation" href="#">Du neuf ? '.$nbAlerts.'</a>
        <a class="dropdown-item '.$alert_msg.'" role="presentation" href="#">Messagerie '.$nbMsgs.'</a></div>
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
            $i = 1;
            while (isset($listPhotos[$i])) {
                if ($listPhotos[$i] == null) 
                    $photos[$i] = 'empty.png';
                else
                    $photos[$i] = $listPhotos[$i];
                $i++;
            }
            $Content = '<div class="alert alert-info" role="alert" style="width: 80%;"><span><strong>Format autorisés</strong> : jpg, jpeg, png<br /> <strong>Taille max</strong> : 800ko</span></div><div class="form-row text-center"><div class="col">
                        <img class="rounded-circle" src="/assets/img/profilpicture/'.$photos[0].'" style="width: 109px;height:109px" id="profilpic"><div class="form-row"><div class="col" style="margin-top: -9px;"><label class="col-form-label">Photo de profil</label></div></div></div></div>';
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
            $Content .= $this->checkbox("Recevoir un mail lorsqu'une personne cesse d'aimer mon profil", "unlike", $userInfos['mail_unlike']);
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
}


?>