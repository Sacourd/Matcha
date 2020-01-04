<?php

require_once('../Class/Libft.php');

include('../config/db.php');
include('../config/log.php');

$usernameFile = @fopen('randomUsernames', 'r'); 
$listUsername = explode("\n", fread($usernameFile, filesize('randomUsernames')));

$hobbyFile = @fopen('randomHobby', 'r'); 
$listHobby = explode("\n", fread($hobbyFile, filesize('randomHobby')));

$nbHobby = count($listHobby);
$nbUsers = count($listUsername);

$nbRows  	= $DB->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'matcha'");
$nbRowFetch = $nbRows->fetch()[0];
if ($nbRowFetch != 9) {
	$value = array(
		"nbHobby" 			=> null,
		"nbUsers" 			=> null,
		"progHobby" 		=> null,
		"distributedTags" 	=> null,
		"progUsers" 		=> null,
		"popularTags" 		=> null,
		"dbRow" 			=> $nbRowFetch);
		echo json_encode($value);
		exit();
}

$query_1 = $DB->query("SELECT * FROM users 		WHERE 1");
$query_2 = $DB->query("SELECT * FROM tags 		WHERE 1");
$query_3 = $DB->query("SELECT * FROM tags_users WHERE 1");

$progUsers 			= $query_1->rowCount();
$progHobby 			= $query_2->rowCount();
$distributedTags    = $query_3->rowCount();

if ($progUsers == $nbUsers) {
	$query_4 = $DB->query("SELECT *, COUNT(tag) AS occ, name AS tagname FROM tags_users INNER JOIN tags ON tags.id = tag GROUP BY tag ORDER BY `occ` DESC LIMIT 10");
	$tags = null;
	while ($data = $query_4->fetch(PDO::FETCH_ASSOC))
		$tags .= '#'.$data['tagname'].' ';
	$value = array(
		"nbHobby" 			=> $nbHobby,
		"nbUsers" 			=> $nbUsers,
		"progHobby" 		=> $progHobby,
		"distributedTags" 	=> $distributedTags,
		"progUsers" 		=> $progUsers,
		"popularTags" 		=> $tags,
		"dbRow" 			=> $nbRowFetch);
}
else {
	$value = array(
		"nbHobby" 			=> $nbHobby,
		"nbUsers" 			=> $nbUsers,
		"progHobby" 		=> $progHobby,
		"distributedTags" 	=> $distributedTags,
		"progUsers" 		=> $progUsers,
		"popularTags" 		=> null,
		"dbRow" 			=> $nbRowFetch);
}

echo json_encode($value);

?>
