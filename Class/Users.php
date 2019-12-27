<?php
// PrqP8u}-8n'4Y:3Q
namespace Matcha;

use \PDO;

class Users extends Libft {

	public 	$db;
	private $session = -1;

	public function __construct($db) {
		$this->db = $db;
        if (isset($_SESSION['id']))
            $this->session = $_SESSION['id'];
	}


///////////////////////////////////////////////////////////////////////////////
	public function getIp() {
		$parseIP = json_decode(file_get_contents("https://api.ipify.org?format=json"));
		$ip = $parseIP->ip;
		return ($ip);
	}


///////////////////////////////////////////////////////////////////////////////
	public function getLocalisationWithIP($ip, $localisation) {
		 $location = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
		 $loc = explode(',', $location->loc);
		 $localisation['latitude'] 	= $loc[0];
		 $localisation['longitude'] = $loc[1];
		 return ($localisation);
	}


///////////////////////////////////////////////////////////////////////////////
	public function register($post, $latitude, $longitude) {
		$checkPass = $this->checkpassword($post['password'], $post['password-confirm']);
		if ($checkPass == -1)
			return '<div class="alert alert-danger" role="alert"><span>Le mot de passe doit contenir 8 caractères au moins, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial.</span></div>';
		elseif ($checkPass == 0)
			return '<div class="alert alert-danger" role="alert"><span>Les mots de passes saisis sont différents</span></div>';
		$email = htmlentities($post['email']);
		$password = htmlentities($post['password']);
		$username = htmlentities($post['username']);
		if ($this->validEmail($email) == 0)
			return '<div class="alert alert-danger" role="alert"><span>L\'email saisi est invalide</span></div>';
		if ($this->countOcc("users", "email", "email = '$email'") > 0)
			return '<div class="alert alert-danger" role="alert"><span>L\'email saisi est déjà utilisé</span></div>';
		if ($this->countOcc("users", "username", "username = '$username'") > 0)
			return '<div class="alert alert-danger" role="alert"><span>Le nom d\'utilisateur saisi est déjà utilisé</span></div>';
		if (strlen($username) > 20)
			return '<div class="alert alert-danger" role="alert"><span>Le nom d\'utilisateur saisi est trop long</span></div>';
		$registrationKey = uniqid();
		$password = htmlentities($_POST['password']);
		$password = password_hash($password, PASSWORD_DEFAULT);
		$value = array($username, $email, $password, $latitude, $longitude, $registrationKey);
		$this->insertSQL("users", "username, email, password, latitude, longitude, registrationKey", $value);

		$subject = "Bienvenue sur Matcha $username !";
		$message = 'Bienvenue chez nous ! Vous êtes bientôt un des nôtres ! Pour finaliser votre inscription, cliquez sur le lien suivant:<br>
		<a href="http://localhost?key='.$registrationKey.'">Validez votre inscription</a><br><br>

		Cordialement,<br>
		L\'équipe Matcha<br><br>

		Note: Ceci est un mail automatique, merci de ne pas y répondre.';
		$senderEmail = 'matcha@42.fr';
		$senderName = 'Matcha';

		$mail = $this->ft_sendMail($email, $subject, $message, $senderEmail, $senderName);
		if ($mail == 0)
			return '<div class="alert alert-danger" role="alert"><span>Une erreur s\'est produite dans l\'envoi du mail.</span></div>';
		return '<div class="alert alert-success" role="alert"><span>Un mail de confirmation a été envoyé pour valider votre compte !</span></div>';
	}


///////////////////////////////////////////////////////////////////////////////
	public function login($username, $password) {
		$checklog = $this->db->prepare("SELECT id, password, registrationKey FROM users WHERE username = ?");
		$checklog->execute(array($username));
		if ($checklog->rowCount() == 0)
			return (0);
		$result = $checklog->fetch(PDO::FETCH_ASSOC);
		if (password_verify($password, $result['password']) == false)
			return (0);
		if ($result['registrationKey'] != 0)
			return (-1);
		$_SESSION['id'] = $result['id'];
		$this->session = $_SESSION['id'];
		$this->updateCol("users", array("logged"), "id = ".$this->session, array(1));
		$this->updateLastActivity($this->session);
		return (1);
	}


///////////////////////////////////////////////////////////////////////////////
	public function editProfile($post, $pagename) {
		$userinfos = $this->selectAndFetch('users', '*', 'id = ?', array($this->session));
		$change = 0;

		///////////////////////////
		// Paramètres du compte

		if ($pagename == 'parameters') {
			// MODIFICATION DU MOT DE PASSE //
			if (!empty($post['oldpass']) OR !empty($post['newpass'])) {
				$oldpass = htmlentities($post['oldpass']);
				if (password_verify($oldpass, $userinfos['password']) == false)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>L\'ancien mot de passe ne correspond pas à l\'actuel</span></div>');
				if (empty($post['newpass']) OR empty($post['newpass-c']))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Merci d\'entrer un nouveau mot de passe et de le confirmer</span></div>');
				if ($this->checkpassword($post['newpass'], $post['newpass-c'])) {
					$newpass = password_hash(htmlentities($post['newpass']), PASSWORD_DEFAULT);
					$this->updateCol("users", array("password"), "id = ".$this->session, array($newpass));
					$change++;
				}
			}

			// MODIFICATION DE L'EMAIL //
			if (!empty($post['email']) AND $post['email'] != $userinfos['email']) {
				$email = htmlentities($post['email']);
				if ($this->validEmail($email)) {
					if ($this->countOcc("users", "email", "email = '$email'") > 0)
						return ('<div class="alert alert-danger alertForm" role="alert"><span>Cet adresse e-mail est déjà utilisé par un autre utilisateur</span></div>');
					$this->updateCol("users", array("email"), "id = ".$this->session, array($email));
					$change++;
				}
				else
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Le format du mail saisi est incorrect</span></div>');

			}

			// MODIFICATION DE L'USERNAME
			if (!empty($post['username']) AND $post['username'] != $userinfos['username']) {
				$username = htmlentities($post['username']);
				if (strlen($username) > 20)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Le nom d\'utilisateur est trop long</span></div>');
				if ($this->countOcc("users", "username", "username = '$username'") > 0)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Un utilisateur a déjà ce nom</span></div>');
				$this->updateCol("users", array("username"), "id = ".$this->session, array($username));
				$change++;
			}
		}

		///////////////////////////
		// Informations personnelles

		if ($pagename == 'personal') {

			// MODIFICATION DU PRENOM
			if (!empty($post['firstname']) AND $post['firstname'] != $userinfos['firstname']) {
				$firstname = htmlentities($post['firstname']);
				if (strlen($firstname) > 20)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Désolé, le prénom est trop long</span></div>');
				if (!ctype_alpha($firstname))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Le prénom saisi est invalide</span></div>');
				$this->updateCol("users", array("firstname"), "id = ".$this->session, array($firstname));
				$change++;
			}

			// MODIFICATION DU NOM
			if (!empty($post['lastname']) AND $post['lastname'] != $userinfos['lastname']) {
				$lastname = htmlentities($post['lastname']);
				if (strlen($lastname) > 15)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Désolé, le nom est trop long</span></div>');
				if (!ctype_alpha($lastname))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Le nom saisi est invalide</span></div>');
				$this->updateCol("users", array("lastname"), "id = ".$this->session, array($lastname));
				$change++;
			}

			// MODIFICATION DE LA DATE DE NAISSANCE
			if (!empty($post['birthday']) AND $userinfos['birthday'] != $post['birthday']) {
				$splitBirthday = explode('-', $post['birthday']);
				if (count($splitBirthday) != 3)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>La date saisie est invalide</span></div>');
				$year 	= $splitBirthday[0];
				$month 	= $splitBirthday[1];
				$day 	= $splitBirthday[2];
				if (!checkdate($month, $day, $year))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>La date saisie est invalide</span></div>');
				$this->updateCol("users", array("birthday"), "id = ".$this->session, array($post['birthday']));
				$change++;
			}

			// MODIFICATION DU SEXE
			if (!empty($post["sex"]) AND $post["sex"] != $userinfos["gender"]) {
				$sex = htmlentities($post["sex"]);
				$availableSex = array("M", "F", "O");
				if (!in_array($sex, $availableSex))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Merci de ne pas toucher au DOM</span></div>');
				$this->updateCol("users", array("gender"), "id = ".$this->session, array($sex));
				$change++;
			}

			// MODIFICATION DU KINK
			if (isset($post['kink']) AND $post['kink'] != $userinfos['kink']) {
				$kink = htmlentities($post['kink']);
				$availableKink = array(0, 1, 2);
				if (!in_array($kink, $availableKink))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Merci de ne pas toucher au DOM</span></div>');
				$this->updateCol("users", array("kink"), "id = ".$this->session, array($kink));
				$change++;
			}

			// MODIFICATION DE LA LONGITUDE
			if (!empty($post['longitude']) AND $post['longitude'] != $userinfos['longitude']) {
				$longitude = $post['longitude'];
				if (!is_numeric($longitude))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Une erreur est survenue dans la géolocalisation</span></div>');
				$this->updateCol("users", array("longitude"), "id = ".$this->session, array($longitude));
				$change++;
			}

			// MODIFICATION DE LA LATITUDE
			if (!empty($post['latitude']) AND $post['latitude'] != $userinfos['latitude']) {
				$latitude = $post['latitude'];
				if (!is_numeric($latitude))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Une erreur est survenue dans la géolocalisation</span></div>');
				$this->updateCol("users", array("latitude"), "id = ".$this->session, array($latitude));
				$change++;
			}
		}

		///////////////////////////
		// Gestion du profil

		if ($pagename == 'gestion') {


			if (!empty($post['tags'])) {
				$tagsEpur 	= trim($post['tags']);
				if (substr_count($tagsEpur, '#') != (substr_count($tagsEpur, ' ') + 1))
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Vous avez mal saisis vos centres d\'intérêts</span></div>');
				if (substr_count($tagsEpur, '#') > 10)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Vous n\'avez le droit qu\'à 10 centres d\'intérêts maximum</span></div>');
				$tagsEpur 	= preg_replace('/\s+/', '', $tagsEpur);
				$splitTag 	= array_filter(explode('#', $tagsEpur));
				$doublon 	= array_unique(array_filter(explode('#', $tagsEpur)));
				if ($doublon != $splitTag)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Vous avez saisi plusieurs fois un même centre d\'intérêt</span></div>');
				$this->deleteSQL("tags_users", "userid = $this->session");
				$finalTag = array();
				$i = 0;
				while ($i <= count($splitTag)) {
					if (isset($splitTag[$i]))
						array_push($finalTag, $splitTag[$i]);
					$i++;
				}
				$i = 0;
				while (isset($finalTag[$i])){
					$this->cifnexist("tags", "name", "name = '".$finalTag[$i]."'", array($finalTag[$i]));
					$i++;
				}
				$i = 0;
				while ($i < count($finalTag)) {
					$change++;
					$tagID = $this->selectSQL("tags", "id", array("name"), array($finalTag[$i]))['id'];
					$this->insertSQL("tags_users", "userid, tag", array($this->session, $tagID));
					$i++;
				}
			}

			if (!empty($post['bio']) AND nl2br($post['bio']) != $userinfos['bio']) {
				$bio = htmlentities($post['bio']);
				if (strlen($bio) > 350) 
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Votre bio doit contenir au maximum 350 caractères</span></div>');
				$change++;
				$this->updateCol("users", array("bio"), "id = ".$this->session, array(nl2br($bio)));
			}

		}


		///////////////////////////
		// Gestion des photos


		if ($pagename == 'photos') {

			$listPhotos 		= explode('/', $userinfos['photos']);
			$profilPicture 		= $_FILES['profilpic'];

			$photos 			= array($_FILES['photo1'],
								$_FILES['photo2'],
								$_FILES['photo3'],
								$_FILES['photo4']);

			$newPhotos 			= array("", "", "", "", "");
			$legalExtensions 	= array("jpg", "png", "jpeg", "JPEG", "PNG", "JPG");
			$i = 0;

			if ($profilPicture['error'] == 0) {
				$newPhotos[$i] = uniqid();
				$return = $this->uploadFile($profilPicture, $newPhotos[$i], 'assets/img/profilpicture', $legalExtensions, 800000, 'jpg');
				if ($return != 1)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>'.$return.'</span></div>');
				if ($listPhotos[$i] != 'default.jpg' AND !empty($listPhotos[$i])) {
					unlink('assets/img/profilpicture/'.$listPhotos[$i]);
					$newPhotos[$i] = $newPhotos[$i].'.jpg';
				}
			}
			else if ($listPhotos[$i] == 'default.jpg')
				$newPhotos[$i] = 'default.jpg';
			else
				$newPhotos[$i] = $listPhotos[$i];
			$i++;
			$j = 0;
			while (isset($photos[$j])) { 
				if ($photos[$j]['error'] == 0) {
					$newPhotos[$i] = uniqid();
					$return = $this->uploadFile($photos[$j], $newPhotos[$i], 'assets/img/photos', $legalExtensions, 800000, 'jpg');
					if ($return != 1)
						return ('<div class="alert alert-danger alertForm" role="alert"><span>'.$return.'</span></div>');
					if (file_exists('assets/img/photos/'.$listPhotos[$i]) AND !empty($listPhotos[$i]))
						unlink('assets/img/photos/'.$listPhotos[$i]);
					$newPhotos[$i] = $newPhotos[$i].'.jpg';
				}
				else
					$newPhotos[$i] = $listPhotos[$i];
				$i++;
				$j++;
			}

			$dataPhotos = implode($newPhotos, '/');
			$this->updateCol("users", array("photos"), "id = ".$this->session, array($dataPhotos));
			if ($dataPhotos != $listPhotos)
				$change++;
		}


		///////////////////////////
		// Gestion des notifications

		if ($pagename == 'notif') {

			$view = 1;
			$like = 1;
			$dlike = 1;
			$unlike = 1;
			$msg = 1;

			$col = array("mail_view", "mail_like", "mail_dlike", "mail_unlike", "mail_msg");

			if (!isset($post['view']))
				$view = 0;
			if (!isset($post['like']))
				$like = 0;
			if (!isset($post['dlike']))
				$dlike = 0;
			if (!isset($post['unlike']))
				$unlike = 0;
			if (!isset($post['msg']))
				$msg = 0;

			$this->updateCol("users", $col, "id = ".$this->session, array($view, $like, $dlike, $unlike, $msg));
			$change++;

		}

	if ($change > 0)
		return ('<div class="alert alert-success alertForm" role="alert"><span>Les changements ont été opérés avec succès !</span></div>');

	}


///////////////////////////////////////////////////////////////////////////////
	public function forgotPassword($email) {

		if ($this->validEmail($email) == 0)
			return ('<div class="alert alert-danger" role="alert">Cette adresse e-mail est invalide</div>');
		if ($this->countOcc("users", "email", "email = '$email'") == 0)
			return ('<div class="alert alert-danger" role="alert">Cette adresse e-mail n\'est pas utilisée dans nos services</div>');

		$recoverKey = uniqid();

		$value = array($email, $recoverKey);
		$this->insertSQL("forgot_password", "email, keylock", $value);

		$subject = "Vous avez oublié votre mot de passe ?";
		$message = 'Coucou ! Vous avez oublié votre mot de passe on dirait ? Pas de soucis, récupérez le en cliquant sur le lien suivant:<br>
		<a href="http://localhost/forgot_password?recover='.$recoverKey.'">Changer de mot de passe</a><br>
		<strong>Attention, le lien expirera dans 24 heures !</strong><br>

		Cordialement,<br>
		L\'équipe Matcha<br><br>

		Note: Ceci est un mail automatique, merci de ne pas y répondre.';
		$senderEmail = 'matcha@42.fr';
		$senderName = 'Matcha';

		$mail = $this->ft_sendMail($email, $subject, $message, $senderEmail, $senderName);
		if (!$mail)
			return ('<div class="alert alert-danger" role="alert">Une erreur est survenue dans l\'envoi du mail</div>');
		return ('<div class="alert alert-success" role="alert">Un mail vous a été envoyé pour récupérer votre mot de passe !</div>');
	}


///////////////////////////////////////////////////////////////////////////////
	public function newPassword($post, $key) {
		$email = $this->selectAndFetch('forgot_password', 'email', 'keylock = ?', array($key))['email'];
		$checkPass = $this->checkpassword($post['pass'], $post['pass-c']);
		if ($checkPass == -1)
			return ('<div class="alert alert-danger" role="alert">Le mot de passe doit contenir 8 caractères au moins, 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial</div>');
		elseif ($checkPass == 0)
			return ('<div class="alert alert-danger" role="alert">Les mots de passes saisis sont différents</div>');
		$newpass = password_hash($post['pass'], PASSWORD_DEFAULT);
		$this->updateCol("users", array("password"), "email = '$email'", array($newpass));
		$this->deleteSQL('forgot_password', "email = '$email'");
		header('Location: login.php');
	}

}

?>