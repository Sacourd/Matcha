<?php

/* CALCUL DU SCORE DE POPULARITE  ///////////////
											   //
Envoyer un message 					= 1 pt     //
Se faire liker 					    = 100 pt   //
Se faire deliker					= -100 pt  //
Liker back 							= 200 pt   //
Se faire deliker après un like back = -300 pt  //
											   //
///////////////////////////////////////////////*/

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
	public function getLocalisationWithIP($localisation) {
		 $ip = json_decode(file_get_contents("https://api.ipify.org?format=json"))->ip;
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
		<a href="http://localhost?key='.$registrationKey.'">Validez votre inscription</a><br><br>';

		$mail = $this->ft_sendMail($email, $subject, $message);
		if ($mail == 0)
			return '<div class="alert alert-danger" role="alert"><span>Une erreur s\'est produite dans l\'envoi du mail.</span></div>';
		return '<div class="alert alert-success" role="alert"><span>Un mail de confirmation a été envoyé pour valider votre compte !</span></div>';
	}


///////////////////////////////////////////////////////////////////////////////
	public function login($username, $password) {
		$checklog = $this->db->prepare("SELECT id, password, registrationKey, banned FROM users WHERE username = ?");
		$checklog->execute(array($username));
		if ($checklog->rowCount() == 0)
			return (0);
		$result = $checklog->fetch(PDO::FETCH_ASSOC);
		if (password_verify($password, $result['password']) == false)
			return (0);
		if ($result['registrationKey'] != 0)
			return (-1);
		if ($result['banned'] == 1)
			return (-2);
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

			// MODIFICATION DE DATE DE NAISSANCE
			if (!empty($post['birthday']) AND $userinfos['birthday'] != $post['birthday']) {
				$now = date('Y-m-d');
				$nowTime = strtotime($now);
				$dateTime = strtotime($post['birthday']);

				if ($nowTime < $dateTime)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Mirai Trunks ?</span></div>');
				else if ($nowTime - $dateTime < 567648000)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Vous êtes trop jeune pour bénéficier de nos services</span></div>');

				$splitBirthday = explode('-', $post['birthday']);
				if (count($splitBirthday) != 3)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>La date saisie est invalide</span></div>');
				$year 	= $splitBirthday[0];
				$month 	= $splitBirthday[1];
				$day 	= $splitBirthday[2];
				if ($year < 1930)
					return ('<div class="alert alert-danger alertForm" role="alert"><span>Désolé... Comprenez que vous ne pouvez pas vous inscrire ici...</span></div>');
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
			$dislike = 1;
			$msg = 1;

			$col = array("mail_view", "mail_like", "mail_dlike", "mail_dislike", "mail_msg");

			if (!isset($post['view']))
				$view = 0;
			if (!isset($post['like']))
				$like = 0;
			if (!isset($post['dlike']))
				$dlike = 0;
			if (!isset($post['dislike']))
				$dislike = 0;
			if (!isset($post['msg']))
				$msg = 0;

			$this->updateCol("users", $col, "id = ".$this->session, array($view, $like, $dlike, $dislike, $msg));
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
		<strong>Attention, le lien expirera dans 24 heures à partir de la réception de ce mail !</strong><br><br>';

		$mail = $this->ft_sendMail($email, $subject, $message);
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


///////////////////////////////////////////////////////////////////////////////
	public function searchMatcha($distance, $tri, $order, $agemin, $agemax) {
		$userinfos = $this->selectAndFetch('users', '*', 'id = ?', array($this->session));
		$taginfos  = $this->selectAndFetch('tags_users', 'tag', 'userid = ?', array($this->session), 1);

		$tags = array();
		$i = 0;
		while (isset($taginfos[$i])) {
			array_push($tags, $taginfos[$i]['tag']);
			$i++;
		}

		$tagsList = implode(', ', $tags);

		$latitude = $userinfos['latitude'];
		$longitude = $userinfos['longitude'];
		$userKink = $userinfos['kink'];
		$usergender = $userinfos['gender'];

		$order = $order.' '.$tri;

		$kink = "(kink = $userKink OR kink = 2)";

		$match = null;
		if (($usergender == 'M' AND $userKink == 0) OR ($usergender == 'F' AND $userKink == 1))
			$match = "AND gender = 'F' AND $kink";
		else if (($usergender == 'M' AND $userKink == 1) OR ($usergender == 'F' AND $userKink == 0))
			$match = "AND gender = 'M' AND $kink";
		else if ($usergender == 'O')
			$match = "AND gender = 'O' AND $kink";
		else if ($usergender == 'M' AND $userKink == 2)
			$match = "AND gender = 'M' AND kink IN ($userKink, 1) OR gender = 'F' AND kink IN ($userKink, 0)";
		else if ($usergender == 'F' AND $userKink == 2)
			$match = "AND ((gender = 'M' AND kink IN ($userKink, 0)) OR (gender = 'F' AND kink IN ($userKink, 1)))";

		$session = $this->session;

		$statement = "SELECT users.id AS user_id,
							photos,
							username,
							popularity,
							DATEDIFF(CURRENT_DATE, birthday) / 365.25 AS Age,
							((2 * ASIN(
								SQRT(
								POW( SIN( (RADIANS(latitude) - RADIANS($latitude)) / 2), 2)
								+ COS(RADIANS($latitude)) * COS(RADIANS(latitude))
								* POW( SIN( (RADIANS(longitude) - RADIANS($longitude)) / 2), 2)
								))) * 6371000) / 1000 AS distance,
							COUNT(tags_users.id) AS interest,
							COUNT(block) AS blocked

					FROM 	users

					INNER JOIN tags_users ON (userid = users.id AND tag IN ($tagsList))
					LEFT JOIN blocked_users ON ((user = $session OR user = users.id) AND (block = users.id OR block = $session))

					WHERE 	banned 					= 0 AND
							registrationkey 		= 0 AND
							firstname 				IS NOT NULL AND
							lastname  				IS NOT NULL AND
							SUBSTR(photos, 0, 7) 	!= 'default' AND
							users.id 				!= $session
							$match

					GROUP BY user_id

					HAVING 	COUNT(interest) >= 0
							AND Age BETWEEN $agemin AND $agemax
							AND distance <= $distance
							AND blocked = 0


					ORDER BY $order";
		$result = $this->db->query($statement);
		return ($result);

	}


	public function inLove($user_1, $user_2) {
		$user1_love = $this->countOcc('love', 'id', "user = $user_1 AND likes = $user_2");
		$user2_love = $this->countOcc('love', 'id', "user = $user_2 AND likes = $user_1");
		if ($user1_love + $user2_love == 2)
			return (1);
		return (0);
	}


///////////////////////////////////////////////////////////////////////////////
	public function checkUserExists($user) {
		if ($this->countOcc('users', 'id', "id = $user AND banned = 0") == 0)
			return (0);
		return (1);
	}


///////////////////////////////////////////////////////////////////////////////
	public function isBlocking($user, $dest) {
		$user1_b = $this->countOcc('blocked_users', 'id', "user = $user AND block = $dest");
		$user2_b = $this->countOcc('blocked_users', 'id', "user = $dest AND block = $user");
		return ($user1_b + $user2_b);
	}

///////////////////////////////////////////////////////////////////////////////
	public function addNotif($user, $dest, $type) {

		if ($this->session == -1)
			return ;

		if ($this->checkUserExists($dest) == 0 OR $this->isBlocking($user, $dest) > 0)
			return ;

		$mailCheck = 'mail_'.$type;
		$allowMail = $this->selectSQL('users', $mailCheck, array('id'), array($dest))[$mailCheck];
		$popularity = $this->selectSQL("users", "popularity", array("id"), array($dest))['popularity'];

		if ($user == $dest OR ($this->inLove($user, $dest) AND $type == 'view'))
			return ;

		if ($this->countOcc('notifications', 'id', "author = $user AND type = '$type' AND dest = $dest AND CURRENT_TIMESTAMP - time > 10 AND opened = 0") > 0
		OR  $this->countOcc('notifications', 'id', "author = $user AND type = '$type' AND dest = $dest AND opened = 0") == 0) {
			$this->deleteSQL('notifications', "author = $user AND dest = $dest AND type = '$type' AND opened = 0");
			$value = array($user, $dest, $type);
			$this->insertSQL("notifications", "author, dest, type", $value);
			$email = $this->selectSQL('users', 'email', array('id'), array($dest))['email'];
			$authorLink = 'http://localhost/profile.php?view='.$user;
			if ($type == 'view') {
				$subject = "Vous avez de la visite !";
				$message = 'Bonjour ! On dirait que quelqu\'un vient de visiter votre profil ! Vous pouvez consultez le sien en cliquant sur le lien suivant:<br>
					<a href="'.$authorLink.'">Qui s\'intéresse à moi?</a><br><br>';
			}
			elseif ($type == 'like' OR $type == 'dlike') {
				$value = array($user, $dest);
				$this->insertSQL("love", "user, likes", $value);
				$this->updateCol("users", array("popularity"), "id = $dest", array($popularity + 100));
				if ($this->inLove($user, $dest)) {
					$this->updateCol("users", array("popularity"), "id = $user", array($popularity + 100));
					$this->insertSQL("messages", "author, dest, message", array($dest, $user, "C'est un plaisir de te rencontrer :)"));
					$this->insertSQL("messages", "author, dest, message", array($user, $dest, "Faisons connaissance ! :)"));
					if ($this->selectSQL('users', 'mail_dlike', array('id'), array($dest))['mail_dlike'] == 0)
						return ;
					$subject = "C'est le coup de foudre !";
					$message = 'HOURRA ! On vous a renvoyé de l\'amour ! Votre message a été entendu, et une personne souhaite aller plus loin avec vous! Qui est la personne qui vous aime en retour? Cliquez ici pour le savoir TOUT DE SUITE:<br>
						<a href="'.$authorLink.'">Trop de stress?</a><br>
						Nous sommes heureux pour vous, et nous souhaitons sincèrement que cette aventure dure !<br><br>';
				}
			}
			elseif ($type == 'dislike' OR $type == 'break') {
				if ($this->inLove($user, $dest)) {
					$this->deleteSQL('notifications', "author = $user AND dest = $dest AND type = '$type' AND opened = 0");
					$this->insertSQL("notifications", "author, dest, type", array($user, $dest, 'break'));
					$subject = "Une aventure se termine...";
					$message = 'Un utilisateur vient de vous dire au revoir... Vous ne pouvez plus entrer en contact avec cette personne:<br>
					<a href="'.$authorLink.'">Qui a osé?</a><br>
					Mais ce n\'est pas grave ! TU MERITES MIEUX ! #emo<br><br>';
					$this->updateCol("users", array("popularity"), "id = $dest", array($popularity - 200));
					$this->deleteSQL('love', "user = $dest AND likes = $user");
					$this->deleteSQL('love', "user = $user AND likes = $dest");
				}
				else {
					$this->deleteSQL('notifications', "author = $user AND dest = $dest AND type = '$type'");
					$subject = "Je ne veux pas entrer en contact finalement";
					$message = 'Un utilisateur vient de changer d\'avis ! Au début, elle vous appréciait, puis finalement bah tanpis! Voici cette personne en question:<br>
					<a href="'.$authorLink.'">Qui s\'intéressait à moi?</a><br>
					C\'était soudain, comme changement d\'avis !<br><br>';
					$this->updateCol("users", array("popularity"), "id = $dest", array($popularity - 100));
				}
				$this->deleteSQL('notifications', "author = $user AND dest = $dest AND type = '$type' AND opened = 0");
				$this->deleteSQL('love', "user = $user AND likes = $dest");
			}
		if ($allowMail)
			$this->ft_sendMail($email, $subject, $message);
		}
	}

///////////////////////////////////////////////////////////////////////////////
	public function alertUser($user, $type) {
		if ($this->session == -1)
			return ;
		$userid = $this->session;
		if ($type == 'unalert') {
			$this->deleteSQL('alerts', "user = $userid AND to_ban = $user");
			return ;
		}
		if ($this->countOcc('alerts', 'id', "user = $userid AND to_ban = $user") > 0)
			return ;
		if ($this->countOcc('alerts', 'id', "to_ban = $user") >= 5)
			return ;
		$this->insertSQL("alerts", "user, to_ban", array($userid, $user));
		if ($this->countOcc('alerts', 'id', "to_ban = $user") >= 5)
			$this->updateCol("users", array("banned"), "id = $user", array('1'));
	}


///////////////////////////////////////////////////////////////////////////////
	public function newMessage($author, $dest, $msg) {
		if (($this->session == -1 OR $author == -1) AND $author != $dest)
			return ;
		$value = array($author, $dest, $msg);
		$this->insertSQL("messages", "author, dest, message", $value);
		$popularity = $this->selectSQL("users", "popularity", array("id"), array($dest))['popularity'];
		$this->updateCol("users", array("popularity"), "id = $dest", array($popularity + 1));
		$destInfos = $this->selectSQL('users', 'logged, mail_msg, email', array('id'), array($dest));
		$email = $destInfos['email'];
		$allowMail = $destInfos['mail_msg'];
		$logged = $destInfos['logged'];
		if ($allowMail == 0 OR $logged == 1)
			return ;
		$subject = "Nouveau message !";
		$message = 'Vous venez de recevoir un message sur Matcha ! Ce serait bien de répondre rapidement, ouais !<br>
						<a href="messages.php?messages='.$dest.'">Hop hop hop</a><br><br>';
		$this->ft_sendMail($email, $subject, $message);
	}


}

?>