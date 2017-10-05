<?php
/**
 * connectionClass manages all the db connection, query generators and requests of the backend pages of the eShop
 *
 *
 * @category   connectionClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class connectionClass {

    public function connector(){
        define('DB_HOST', 'localhost');
        define('DB_USER', 'root');
        define('DB_PASSWORD', '');
        define('DB_DATABASE', 'coffeeshop');
        define('DEBUG_MODE', true);

        //Connect to DB
        try{
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $bdd = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USER, DB_PASSWORD, $pdo_options);

        }catch (Exception $e){//Return connection error 
            if(DEBUG_MODE){
                $e->getMessage();
            }
            die('Erreur : connexion à la base de données impossible');
        }
        $bdd->exec("set names utf8");

        //Returns DB connection
        return $bdd;
    }

    public function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
    }
    public function pass_exe(){
        //Including a static salt
        connectionClass::staticsalt();
        //salt code, create a random code within 16 carac.
        $saltcode = substr(str_shuffle('+-?!@&%0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 16);
        //adding the salt to the password 
        $password .= $saltcode;
        //adding a sha256bits hashing
        $password = hash_hmac('SHA512', $password, $saltcode);
        
        $password = $password . $static_salt;
    }
    public function staticsalt(){
        $static_salt = 'a&b=c*21$x$';
    }

    //function request for query; insert, update ou delete
    public function execQuery($query){
        //DB connection
        $bdd = $GLOBALS['bdd'];

        //Executes quesry
        $bdd->exec($query);

    }

    //Returns the query result
    public function getQueryResult($query, $champ){
        
        $reponse = connectionClass::executeQuery($query);
        
        while($donnees = $reponse->fetch()){
            //retourne le resultat
            return $donnees[$champ];
        }
        
        $reponse->closeCursor();
    }

    public function executeQuery($query){
    
        $bdd = $GLOBALS['bdd'];
        
        try{
            $reponse = $bdd->query($query);
        }catch(Exception $e){
            if(DEBUG_MODE){
                $e->getMessage();
            }
            die('Erreur pendant la récuperation des données ! - '.$query);
        }
        return $reponse;
    }


}

$db = new connectionClass();
$bdd = $db->connector();
?>
