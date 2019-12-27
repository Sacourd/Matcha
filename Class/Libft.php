<?php

namespace Matcha;

use \PDO;

class Libft {

	public $db;

	public function __construct($db) {
		$this->db = $db;
	}

	public function countOcc($table, $col, $cdt) {
		$statement = "SELECT $col FROM $table WHERE $cdt";
		$nbOcc = $this->db->query($statement);
		return ($nbOcc->rowCount());
	}

    public function selectSQL($table, $col, $cdt, $value) {
        if (count($cdt) != count($value)) {
            print("Une erreur dans Libft->selectSQL est survenue. Le nombre de colonnes à mettre à jour est différent du nombre de valeurs.<br>Trace:");
            var_dump($cdt);
            var_dump($value);
            exit();
        }
        $i = 0;
        $prepare = null;
        while ($i < count($cdt)) {
            $and = ' ';
            if (isset($cdt[$i + 1]))
                $and = 'AND';
            $prepare .= $cdt[$i].' = ?'.$and;
            $i++;
        }
        $statement = "SELECT $col FROM $table WHERE $prepare";
        $query = $this->db->prepare($statement);
        $query->execute($value);
        return ($query->fetch(PDO::FETCH_ASSOC));
    }

    public function updateCol($table, $col, $cdt, $value) {
        $i = 0;
        if (count($col) != count($value)) {
            print("Une erreur dans Libft->updateCol est survenue. Le nombre de colonnes à mettre à jour est différent du nombre de valeurs.<br>Trace:");
            var_dump($col);
            var_dump($value);
            exit();
        }
        $prepare = null;
        while ($i < count($col)) {
            $coma = ' ';
            if (isset($col[$i + 1]))
                $coma = ', ';
            $prepare .= $col[$i].' = ?'.$coma;
            $i++;
        }
        $statement = "UPDATE $table SET $prepare WHERE $cdt";
        $query = $this->db->prepare($statement);
        $query->execute($value);
    }

    public function insertSQL($table, $col, $value) {
        $tab = array();
        foreach ($value as $key => $val) {
            if (!is_numeric($val))
               $value[$key] = htmlentities($val);
            $tab[$key] = '?';
        }
        $prepare = implode(', ', $tab);
        $statement = "INSERT INTO $table ($col) VALUES ($prepare)";
        $query = $this->db->prepare($statement);
        $query->execute(array_values($value));
    }

    public function cifnexist($table, $col, $cdt, $value) {
        if ($this->countOcc($table, $col, $cdt) == 1)
            return ;
        $this->insertSQL($table, $col, $value);
    }

    public function deleteSQL($table, $cdt) {
        $statement = "DELETE FROM $table WHERE $cdt";
        $query = $this->db->query($statement);
    }

    public function selectAndFetch($table, $col, $cdt, $value, $all = 0) {
        $statement = "SELECT $col FROM $table WHERE $cdt";
        $query = $this->db->prepare($statement);
        $query->execute(array_values($value));
        if ($all == 1)
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
        else
            $result = $query->fetch(PDO::FETCH_ASSOC);
        return ($result);
    }

	public function ft_sendMail($to, $subject, $message, $senderEmail, $senderName, $files = array()) { 
        $from 			= $senderName." <".$senderEmail.">";  
        $headers 		= "De: $from"; 
        $semi_rand 		= md5(time());  
        $mime_boundary 	= "==Multipart_Boundary_x{$semi_rand}x";  
     
        $headers 		.= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";  
        $message 		= "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
        "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
     
        if (!empty($files)) { 
            for ($i = 0; $i < count( $files ); $i++) { 
                if (is_file($files[$i])) { 
                    $file_name = basename( $files[$i] ); 
                    $file_size = filesize( $files[$i] ); 

                    $message .= "--{$mime_boundary}\n"; 

                    $fp =    @fopen( $files[$i], "rb" ); 
                    $data =  @fread( $fp, $file_size ); 
                    @fclose( $fp ); 
 
                    $data = chunk_split(base64_encode($data));
                    $message .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .  
                    "Content-Description: ".$file_name."\n" . 
                    "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" . 
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
                }
            }
        }
        $message .= "--{$mime_boundary}--"; 
        $returnpath = "-f" . $senderEmail; 
        $mail = @mail($to, $subject, $message, $headers);  
        if ($mail)
            return (1); 
        return (0); 
    }

    public function uploadFile($file, $newName, $path, $legalExtensions, $legalSize, $outputExt) {

        $actualName = $file['tmp_name'];
        $actualSize = $file['size'];
        $extension 	= pathinfo($file['name'], PATHINFO_EXTENSION);

        if ($file['tmp_name'] === 0 OR $actualSize == 0)
            return ('Le fichier est invalide ! Taille max: '.($legalSize / 1000).' ko / Formats : '.implode(' ', $legalExtensions));

        if (file_exists($path.'/'.$newName.'.'.$extension))
            unlink($path.'/'.$newName.'.'.$extension);

        if ($actualSize < $legalSize) {
            if (in_array($extension, $legalExtensions))
                move_uploaded_file($actualName, $path.'/'.$newName.'.'.$outputExt);
            else
                return ('Le fichier est invalide ! Formats acceptés : '.implode(' ', $legalExtensions));
        }
        else
            return ('Le fichier est trop lourd ! Taille max: '.($legalSize / 1000).' ko');
        return (1);
    }

    public function humanTiming($time) {
        $time = strtotime($time);
        $time = strtotime(date('Y-m-d h:i:s', time())) - $time;
        $tokens = array(
            31536000 	=> 'an',
            2592000 	=> 'mois',
            604800 		=> 'semaine',
            86400 		=> 'jour',
            3600 		=> 'heure',
            60 			=> 'minute',
            1 			=> 'seconde'
        );
        foreach ($tokens as $unit => $text) {
            if ($time < $unit AND $time != 0) continue;
            $numberOfUnits = floor($time / $unit);
            if ($time == 0)
                return 'un instant';
            return strtoupper($numberOfUnits.' '.$text.(($numberOfUnits > 1 AND $text != 'mois')?'s':''));
        }
    }

    public function validEmail($email) {
    	if (filter_var($email, FILTER_VALIDATE_EMAIL)) 
    		return (1);
    	return (0);
    }

    public function br2nl($str) {
        return str_replace('<br />', "", $str);
    }

    public function checkPassword($password, $confirm) {
    	if ($password != $confirm)
    		return (0);

    	$uppercase 		= preg_match('@[A-Z]@', $password);
        $lowercase 		= preg_match('@[a-z]@', $password);
        $number    		= preg_match('@[0-9]@', $password);
        $specialChars 	= preg_match('@[^\w]@', $password);

        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8)
        	return (-1);
        return (1);
    }

    public function updateLastActivity($session) {
        $this->updateCol("users", array("last_activity"), "id = $session", array(date('Y-m-d H:i:s')));
    }

}


?>