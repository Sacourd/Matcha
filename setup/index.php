<?php

$username = 'root';
$pass = null;


$db = new PDO("mysql:host=localhost", 'root', $pass);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

try {
$db->exec("CREATE DATABASE IF NOT EXISTS matcha 
	       DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
	       CREATE USER '$username'@zina IDENTIFIED BY '$pass';
	       GRANT ALL ON zina.* TO '$username'@matcha;
	       FLUSH PRIVILEGES;");
} catch (PDOException $e) {
	    exit();
}

$DB = new PDO("mysql:dbname=matcha;host=localhost", $username, $pass);

$nbRows  	= $DB->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'matcha'");
$nbRowFetch = $nbRows->fetch()[0];

if (isset($_GET['db']) AND $nbRowFetch < 9) {
	$sql = "DROP TABLE IF EXISTS `alerts`;
	CREATE TABLE IF NOT EXISTS `alerts` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `user` int(11) NOT NULL,
	  `to_ban` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `blocked_users`;
	CREATE TABLE IF NOT EXISTS `blocked_users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `user` int(11) NOT NULL,
	  `block` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `forgot_password`;
	CREATE TABLE IF NOT EXISTS `forgot_password` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `keylock` varchar(50) NOT NULL,
	  `email` varchar(50) NOT NULL,
	  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `love`;
	CREATE TABLE IF NOT EXISTS `love` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `user` int(11) NOT NULL,
	  `likes` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `messages`;
	CREATE TABLE IF NOT EXISTS `messages` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `author` int(11) NOT NULL,
	  `dest` int(11) NOT NULL,
	  `message` text NOT NULL,
	  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `opened` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `notifications`;
	CREATE TABLE IF NOT EXISTS `notifications` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `author` int(11) NOT NULL,
	  `dest` int(11) NOT NULL,
	  `type` varchar(10) NOT NULL COMMENT 'view / like / unlike / dlike / break',
	  `opened` tinyint(1) NOT NULL DEFAULT '0',
	  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `tags`;
	CREATE TABLE IF NOT EXISTS `tags` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `tags_users`;
	CREATE TABLE IF NOT EXISTS `tags_users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `userid` int(11) NOT NULL,
	  `tag` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;

	DROP TABLE IF EXISTS `users`;
	CREATE TABLE IF NOT EXISTS `users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `username` varchar(20) NOT NULL,
	  `email` varchar(70) NOT NULL,
	  `password` varchar(255) NOT NULL,
	  `photos` varchar(100) NOT NULL DEFAULT 'default.jpg////',
	  `gender` varchar(1) NOT NULL DEFAULT 'O' COMMENT 'M = male / F = female / O = others',
	  `firstname` varchar(20) DEFAULT NULL,
	  `lastname` varchar(15) DEFAULT NULL,
	  `birthday` date DEFAULT NULL,
	  `latitude` double NOT NULL,
	  `longitude` double NOT NULL,
	  `kink` tinyint(4) NOT NULL DEFAULT '2' COMMENT '0 = hetero / 1 = gay / 2 = bi',
	  `registrationkey` varchar(255) NOT NULL,
	  `bio` text,
	  `logged` tinyint(1) NOT NULL DEFAULT '0',
	  `last_activity` datetime DEFAULT NULL,
	  `mail_like` tinyint(1) NOT NULL DEFAULT '1',
	  `mail_dislike` tinyint(1) NOT NULL DEFAULT '1',
	  `mail_view` tinyint(1) NOT NULL DEFAULT '1',
	  `mail_dlike` tinyint(1) NOT NULL DEFAULT '1',
	  `mail_msg` tinyint(1) NOT NULL DEFAULT '1',
	  `popularity` int(11) NOT NULL DEFAULT '0',
	  `banned` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	try {
	    $DB->exec($sql);
	}
	catch (PDOException $e)
	{
	    echo $e->getMessage();
	    die();
	}
	exit();
}

require_once('../Class/Libft.php');

$universalPass = "CKSu!R5}3tw.efdz";

$Libft = new Matcha\Libft($DB);

$registrationkey = 0;
$mul = 1000000;

$availableGender 	= array("M", "F", "O"); 

$usernameFile = @fopen('randomUsernames', 'r'); 
$listUsername = explode("\n", fread($usernameFile, filesize('randomUsernames')));

foreach ($listUsername as $key => $value)
	$listUsername[$key] = trim($value);

$hobbyFile = @fopen('randomHobby', 'r'); 
$listHobby = explode("\n", fread($hobbyFile, filesize('randomHobby')));

foreach ($listHobby as $key => $value)
	$listHobby[$key] = trim($value);

$nameFile = @fopen('randomNames', 'r'); 
$listName = explode("\n", fread($nameFile, filesize('randomNames')));

$bioFile = @fopen('randomBio', 'r'); 
$listBio = explode("\n", fread($bioFile, filesize('randomBio')));

$minDate = strtotime('1954-08-31');
$maxDate = strtotime('2000-01-01');

$minAct = strtotime('2019-03-25 00:00:00');
$maxAct = strtotime('2020-01-01 23:59:59');

$lat42 = 48.862725;
$lon42 = 2.287592;

$latTo = 46.5658304;
$lonTo = 3.3277376;

$photos = 'robot////';

$z = 0;
while ($z < count($listHobby)) {
	$Libft->insertSQL("tags", "name", array($listHobby[$z]));
	$z++;
}

$col = "username, email, password, photos, gender, firstname, lastname, birthday, latitude, longitude, kink, registrationkey, bio, logged, mail_like, mail_dislike, mail_view, mail_dlike, mail_msg, popularity, banned, last_activity";

$i = 0;

while ($i < count($listUsername)) {

	$username = $listUsername[$i];

	$correspondant 	= uniqid();
	$domain 		= '@'.uniqid().'.fr';
	$email 			= $correspondant . $domain;

	$randGIndex 	= array_rand($availableGender);
	$gender 		= $availableGender[$randGIndex];

	$randNameIndex 	= array_rand($listName);
	$firstname 		= $listName[$randNameIndex];

	$randFNameIndex = array_rand($listName);
	$lastname 		= $listName[$randFNameIndex];

	$randBioIndex 	= array_rand($listBio);
	$bio 			= $listBio[$randBioIndex];

	$timestampRand 	= rand($minDate, $maxDate);
	$birthday 		= date('Y-m-d', $timestampRand);

	$lat 			= rand($latTo * $mul, $lat42 * $mul) / $mul;
	$lon 			= rand($lon42 * $mul, $lonTo * $mul) / $mul;

	$kink 			= rand(0, 2);
	$logged 		= rand(0, 1);

	$timestampRand 	= rand($minAct, $maxAct);
	$last_activity 	= date('Y-m-d', $timestampRand);

	$popularity 	= rand(0, 5783);

	$banned 		= rand(0, 1);

	$nbHobby 		= rand(1, 10);
	$j 				= 0;
	$hobby 			= array();
	while ($j < $nbHobby) {
		$hobbyKey = rand(0, count($listHobby));
		if (!in_array($hobbyKey, $hobby))
			array_push($hobby, $hobbyKey);
		$j++;
	}
	$j = 0;
	while ($j < count($hobby)) {
		$Libft->insertSQL("tags_users", "userid, tag", array($i + 1, $hobby[$j]));
		$j++;
	}

	$value = array($username, $email, password_hash($universalPass, PASSWORD_DEFAULT), $photos, $gender, $firstname, $lastname, $birthday, $lat, $lon, $kink, $registrationkey, $bio, $logged, 0, 0, 0, 0, 0, $popularity, $banned, $last_activity);

	$Libft->insertSQL("users", $col, $value);

	$i++;

}

?>