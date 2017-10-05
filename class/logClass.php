<?php
/**
 * logClass logs all of the moification made in the products/items of the backend pages of the eShop
 *
 *
 * @category   logClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class logClass{

	public $moduleName;

	public function __construct($moduleName){
		$this->moduleName = $moduleName;
	}

	public function writeLog($eventType, $value){

		switch ($eventType) {
			case 1:
				$eventType = 'Creation->';
				break;
			case 2:
				$eventType = 'Modification->';
				break;
			case 3:
				$eventType = 'Suppression->';
				break;

			default:
				$eventType = 'Modification->';
				break;
		}

		$db = new connectionClass();

		$lmModification = $this->moduleName.': '.$eventType.$value;

		$qry = "INSERT INTO tb_log_modifications(lm_modification, lm_datetime) VALUES('".$lmModification."', NOW() )";

		$db->executeQuery($qry);
	}

}

?>