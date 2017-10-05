<?php

/**
 * orderClass manages all the orders and presents them for preparation in backend pages of the eShop
 *
 *
 * @category   orderClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */
 
class orderClass{

	public function updateOrder($orderId, $orderStatus){

		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		
		//Mise à jour du statut
		$qry = $bdd->prepare("UPDATE tb_orders SET fk_status_code=:fk_status WHERE id_order = ".$orderId);

		$qry->execute(array(
					'fk_status' => $orderStatus
				));
		

		return $alert->returnSuccess("La commande a été mise à jour avec succès!");

	}

	public function getOrderTableList($searchParam=NULL, $statusParam='*'){

		$db = new connectionClass();
		$html .= '<div class="panel panel-default">
				  <!-- Default panel contents -->
				 <div class="panel-heading">
				<form class="form-inline" action="index.php" method="POST">
				<div class="form-group">     
		            <input type="text" class="form-control" value="'.$searchParam.'" name="searchParam" id="searchParam" placeholder="Chercher un client">     
		          </div>';
		$html .= '<div class="form-group">';
		$html .= $this->getStatusSelectList($statusParam);
		$html .='</div>';
		$html .='</form>
				</div>';
		$html .='<table id="tblOrders" class="table table-striped table-bordered">
          	<tr>
          		<th># Commande</th>
          		<th>Client</th>
          		<th>Statut</th>
          		<th>Date de commande</th>
          		<th style="width: 80px;"></th>
          	</tr>';
        $query = "SELECT * FROM tb_orders";
        if($statusParam == '*') $statusParam = '';
        $query .= " WHERE fk_status_code LIKE '%".$statusParam."%'";
        if($searchParam != NULL){
        	$searchParam = $this->getFkUser($searchParam);
        	$query .= " AND fk_user = ".$searchParam;
        }
        
        $query .= " ORDER BY id_order ASC";
        $result = $db->executeQuery($query);
        $iTbl = 0;

        while($donnees = $result->fetch()){
            $html .= '<tr><td>'.$donnees['id_order'].'</td>';
            $html .= '<td>'.$this->getCustomerEmail($donnees['fk_user']).'</td>';
            $html .= '<td>'.$this->getOrderStatus($donnees['fk_status_code']).'</td>';
            $html .= '<td>'.$donnees['o_date'].'</td>';
            $html .= '<td><div class="btn-group">
						  <button type="button" class="btn btn-default btn-xs" onClick="redirect(\'edit.php?orderId='.$donnees['id_order'].'\')"><span class="glyphicon glyphicon-pencil"></span></button>
						  <button type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button>
						</div></td></tr>';
			$iTbl++;
        }

        $html .= '</tr></table>';
        return $html;
	}

	public function getOrderStatus($statusId){
		$db = new connectionClass();

		$qry = "SELECT os_name FROM tb_orders_status WHERE id_order_status = ".$statusId;
		return $db->getQueryResult($qry, 'os_name');
	}

	public function getCustomerEmail($customerId){
		$db = new connectionClass();

		$qry = "SELECT u_email_address FROM tb_users WHERE id_user = ".$customerId;
		return $db->getQueryResult($qry, 'u_email_address');
	}

	public function getFkUser($userEmail){
		$db = new connectionClass();

		$qry = "SELECT id_user FROM tb_users WHERE u_email_address = '".$userEmail."'";
		return $db->getQueryResult($qry, 'id_user');
	}

	public function getStatusSelectList($selectValue='*'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="statusList" name="statusList" onChange="$(\'.form-inline\').submit();">
		<option value="*">*</option>';

		$query = 'SELECT * FROM tb_orders_status ORDER BY id_order_status';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_order_status'] == $selectValue){
				$html .= '<option value="'.$donnees['id_order_status'].'" selected>'.$donnees['os_name'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_order_status'].'">'.$donnees['os_name'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getProductInfos($productId){
		$db = new connectionClass();

		$query = "SELECT * FROM tb_products WHERE id_product = ".$productId;
		$result = $db->executeQuery($query);

		return $result; 
	}

	public function getOrderInfos($orderId){
		$db = new connectionClass();

		$query = "SELECT * FROM tb_orders WHERE id_order = ".$orderId;
		$result = $db->executeQuery($query);

		return $result; 
	}

	public function getOrderedProducts($orderId){
		$db = new connectionClass();

		$qry = "SELECT * FROM tb_order_products INNER JOIN tb_products ON fk_product = id_product WHERE fk_order = ".$orderId;
		$result = $db->executeQuery($qry);

		$totalCmd = 0;
		$iTbl = 0;

		while($donnees = $result->fetch()){
			echo '<tr id="p'.$donnees['id_product'].'">';
			echo '<td>'.$donnees['p_name'].'</td>';
			echo '<td>'.$donnees['op_quantity'].'</td>';
			echo '<td>'.$donnees['op_price'].'</td>';
			echo '<td>'.$donnees['op_price']*$donnees['op_quantity'].'</td>';
			echo '<td></td>';
			$totalCmd += $donnees['op_price']*$donnees['op_quantity'];
			$iTbl;

		}

		echo '<tr><td></td><td></td><td></td><td>'.$totalCmd.'</td><td></td></tr>';
	}

}

?>