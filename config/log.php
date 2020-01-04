    <?php
    if (!isset($DB_DSN) || !isset($USER_DB) || !isset($PASSWORD_DB)) {
		try {
		    throw new Exception("Les identifiants de la base de données ne sont pas correctement saisis. Merci de vérifier le fichier de configuration.");
		} catch(PDOException $ex) {
		    echo $e->getMessage();
		    die();
		}
	}

	try {
	    $DB = new PDO($DB_DSN, $USER_DB, $PASSWORD_DB);
	    $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $ex) {
		throw new Exception("Les identifiants de la base de données sont incorrects. Merci de les corriger dans le fichier de configuration.");
		echo $e->getMessage();
	    die();
	}
	?>