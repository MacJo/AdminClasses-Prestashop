<?php
/**
 * userClass manages the login/logout of the backend pages of the eShop
 *
 *
 * @category   AccessClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class accessClass{
	
	function __construct()
	{
	}

	public function login($username, $password='')
	{

		$db = new connectionClass();
		$alert = new errorClass();

		$qry = "SELECT * FROM tb_users WHERE u_email_address = '".$username."' AND u_password = '".$password."'";
		$result = $db->getQueryResult($qry, 'id_user');
		if ($password){
			if($result > 0){
				$_SESSION['userId'] = $result;
				echo  'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php';
			}else{
				return $alert->returnError('Email ou mot de passe incorrect !');
			}
		}elseif($_SESSION['SESS_ACCESS'] == 0){
			$_SESSION['userId'] = $_SESSION['SESS_MEMBER_ID'];
		
		}

	}

	public function logout(){
		$_SESSION['userId'] = 0;
		session_destroy();
		$this->isLogged();
	}

	public function isLogged(){

		if(isset($_SESSION['userId']) AND $_SESSION['userId'] > 0){
			return 1;
		}else{
			header('Location: login.php');
		}
	}

	public function checkRight(){
		$this->isLogged();
	}
}

?>