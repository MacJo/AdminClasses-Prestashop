<?php
/**
 * errorClass manages the error/warming/messages of the backend pages of the eShop
 *
 *
 * @category   errorClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class errorClass{

	public function returnWarning($message){
		$error = '<div class="alert">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Attention!</strong><br />'.$message.'</div>';
		return $error;
	}
	public function returnError($message){
		$error = '<div class="alert alert-error">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Erreur!</strong><br />'.$message.'</div>';
		return $error;
	}
	public function returnSuccess($message){
		$error = '<div class="alert alert-success">
			  <button type="button" class="close" data-dismiss="alert">&times;</button>
			  <strong>Félicitations!</strong><br />'.$message.'</div>';
		return $error;
	}

}

?>