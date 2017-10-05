<?php
/**
 * productClass manages all the products from the backendof the eShop
 *
 *
 * @category   productClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class productClass{
	
	public function insertProduct($name, $category, $status, $quantity, $price, $description, $provider, $descriptionId=NULL, $cvalue=NULL){

		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		$log = new logClass('Produit');

		//calculer réference produit
		$offsetRef = $db->getQueryResult("SELECT c_offset FROM tb_categories WHERE id_category = '".$category."'", 'c_offset');
		$productOffset = $db->getQueryResult("SELECT p_ref_product FROM tb_products WHERE fk_category = '".$category."' ORDER BY id_product DESC LIMIT 1", 'p_ref_product');
		
		//Si un produit de la catégorie est déjà inscrit
		if($productOffset > 0){
			$finalOffset = $productOffset+1;
		}else{
		//Sinon on commence la numérotation a XXX1
			$finalOffset = $offsetRef+1;
		}
		

		$qry = $bdd->prepare("INSERT INTO tb_products(p_name, fk_category, fk_product_status, p_stock, p_price, p_details, fk_provider, p_ref_product)
		 VALUES(:name, :category, :status, :quantity, :price, :description, :provider, :providerRef)");

		$qry->execute(array(
					'name' => $db->clean($name),
					'category' => $category,
					'status' => $status,
					'quantity' => $quantity,
					'price' => $price,
					'description' => $description,
					'provider' => $provider,
					'providerRef' => $finalOffset
				));


		//On récupe l'id de l'utilisateur
		$productId = $bdd->lastInsertId();

		$log->writeLog(1, $db->clean($name));

		$cpt = 0;
		if(isset($descriptionId)){
			foreach ($descriptionId as $description) {
				$this->insertProductProperty($productId, $description, $cvalue[$cpt]);
				$cpt++;
			}
		}



		return $alert->returnSuccess("Le produit a été ajouté avec succès!");

	}

	public function updateProduct($name, $category, $status, $quantity, $price, $description, $provider, $providerRef, $productId, $descriptionId=NULL, $cvalue=NULL, $aImage){

		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		
		//Traitement de l'image
		if(isset($aImage) AND $aImage['tmp_name'] != ''){ 
		$dossier = '/volume1/web/events/images/';
		$fichier = $providerRef;
		if(rename($aImage['tmp_name'], $dossier . $fichier.'.jpg')) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
		{
			$pImage = $fichier.'.jpg';
		}else{ //Sinon (la fonction renvoie FALSE).
			$pImage = $db->getQueryResult('SELECT p_picture FROM tb_products WHERE id_product = '.$productId, 'p_picture');
		}
		}

		//Mise à jour du produit
		$qry = $bdd->prepare("UPDATE tb_products SET p_name=:name, fk_category=:category, fk_product_status=:status, p_stock=:quantity, p_price=:price, p_details=:description, fk_provider=:provider, p_ref_product=:providerRef, p_picture=:image WHERE id_product = ".$productId);

		$qry->execute(array(
					'name' => $name,
					'category' => $category,
					'status' => $status,
					'quantity' => $quantity,
					'price' => $price,
					'description' => $description,
					'provider' => $provider,
					'providerRef' => $providerRef,
					'image' => $pImage
				));

		//Mise à jour des caractéristiques
		$cpt = 0;
		if(isset($descriptionId)){
			foreach ($descriptionId as $description) {
				$this->insertProductProperty($productId, $description, $cvalue[$cpt]);
				$cpt++;
			}
		}

		return $alert->returnSuccess("Le produit a été modifié avec succès!".$aImage['tmp_name']);

	}

	public function deleteProduct($productId){
		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		$log = new logClass('Produit');

		/*$qry = "DELETE a, ab FROM tb_products AS a 
				INNER JOIN tb_properties AS ab ON a.id_product=ab.fk_product 
				WHERE id_product = ".$productId;*/
		//Suppression des caractéristique associé
		$qry = "DELETE FROM tb_properties WHERE fk_product = ".$productId;
		$result = $db->execQuery($qry);
		//Suppression du produit
		$qry = "DELETE FROM tb_products WHERE id_product = ".$productId;
		$result = $db->execQuery($qry);

		return $alert->returnSuccess('Produit effacé avec succès!');
	}
	
	public function deleteProductProperty($productId, $caractId){
		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		$log = new logClass('Produit');

		/*$qry = "DELETE a, ab FROM tb_products AS a 
				INNER JOIN tb_properties AS ab ON a.id_product=ab.fk_product 
				WHERE id_product = ".$productId;*/
		//Suppression des caractéristique associé
		$qry = "DELETE FROM tb_properties WHERE fk_product = ".$productId." AND id_property = ".$caractId;
		$result = $db->execQuery($qry);

		return $alert->returnSuccess('Caractéristique effacé avec succès!');
	}

	public function insertProductProperty($productId, $descriptionId, $value){

		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();
		$log = new logClass('Produit');

		$qry = $bdd->prepare("INSERT INTO tb_properties(fk_product, fk_description, pr_value)
		 VALUES(:productId, :descriptionId, :value)");

		$qry->execute(array(
					'productId' => $productId,
					'descriptionId' => $descriptionId,
					'value' => $value
				));

		//$log->writeLog(1, $name);

		return 1;

	}

	public function getProductTableList($searchParam=NULL, $categoryParam='*'){

		$db = new connectionClass();
		$html .= '<div class="panel panel-default">
				  <!-- Default panel contents -->
				 <div class="panel-heading">
				<form class="form-inline" action="index.php" method="POST">
				<div class="form-group">     
		            <input type="text" class="form-control" value="'.$searchParam.'" name="searchParam" id="searchParam" placeholder="Chercher un produit">     
		          </div><span class="divider"></span>';
		$html .= '<div class="form-group">';
		$html .= productClass::getCategorySelectList($categoryParam);
		$html .='</div>
				</form>
				</div>';
		$html .='<table id="tblAddress" class="table table-striped table-bordered">
          	<tr>
          		<th>#</th>
          		<th>Nom produit</th>
          		<th>Catégories</th>
          		<th>Prix</th>
          		<th>Nb stock</th>
          		<th style="width: 80px;"></th>
          	</tr>';
        if($categoryParam == '*') $categoryParam = '';
        $query = "SELECT * FROM tb_products WHERE fk_category LIKE '%".$categoryParam."%'";
        $query .= " AND p_name LIKE '%".$searchParam."%'";
        $query .= " ORDER BY id_product ASC";
        $result = $db->executeQuery($query);

        while($donnees = $result->fetch()){
            $html .= '<tr><td>'.$donnees['id_product'].'</td>';
            $html .= '<td>'.$donnees['p_name'].'</td>';
            $html .= '<td>'.$donnees['fk_category'].'</td>';
            $html .= '<td>'.$donnees['p_price'].'</td>';
            $html .= '<td>'.$donnees['p_stock'].'</td>';
            $html .= '<td><div class="btn-group">
						  <button type="button" class="btn btn-default btn-xs" onClick="redirect(\'edit.php?productId='.$donnees['id_product'].'\')"><span class="glyphicon glyphicon-pencil"></span></button>
						  <button type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button>
						</div></td></tr>';
        }

        $html .= '</tr></table>';
        return $html;
	}

	public function getCategorySelectList($selectValue='*'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="categoriesList" name="categoriesList" onChange="$(\'.form-inline\').submit();">
		<option value="*">*</option>';

		$query = 'SELECT * FROM tb_categories ORDER BY id_category';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_category'] == $selectValue){
				$html .= '<option value="'.$donnees['id_category'].'" selected>'.$donnees['id_category'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_category'].'">'.$donnees['id_category'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getStatusSelectList($selectValue='1'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="statusList" name="statusList">';

		$query = 'SELECT * FROM tb_products_status ORDER BY id_product_status';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_category'] == $selectValue){
				$html .= '<option value="'.$donnees['id_product_status'].'" selected>'.$donnees['ps_name'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_product_status'].'">'.$donnees['ps_name'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getProviderSelectList($selectValue='1'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="providerList" name="providerList">';

		$query = 'SELECT * FROM tb_providers ORDER BY id_provider';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_category'] == $selectValue){
				$html .= '<option value="'.$donnees['id_provider'].'" selected>'.$donnees['pv_name'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_provider'].'">'.$donnees['pv_name'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getProductPropertiesTables($selectValue='*'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="categoriesList" name="categoriesList" onChange="$(\'.form-inline\').submit();">
		<option value="*">*</option>';

		$query = 'SELECT * FROM tb_properties ORDER BY id_property';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_category'] == $selectValue){
				$html .= '<option value="'.$donnees['id_category'].'" selected>'.$donnees['id_category'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_category'].'">'.$donnees['id_category'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getDescriptionSelectList($selectValue='*'){

		$db = new connectionClass();

		$html = '<select class="form-control" id="descriptionList" name="descriptionList" onChange="$(\'.form-inline\').submit();">
		<option value="*">*</option>';

		$query = 'SELECT * FROM tb_descriptions ORDER BY id_description';
		$result = $db->executeQuery($query);

		while($donnees = $result->fetch()){
			if($donnees['id_category'] == $selectValue){
				$html .= '<option value="'.$donnees['id_description'].'" selected>'.$donnees['id_description'].'</option>';
			}else{
				$html .= '<option value="'.$donnees['id_description'].'">'.$donnees['id_description'].'</option>';
			}
			
		}

		$html .= '</select>';

		return $html;
	}

	public function getProductPropertiesTR($productId){

		$db = new connectionClass();

		$query = "SELECT * FROM tb_properties WHERE fk_product = ".$productId;
		$result = $db->executeQuery($query);

		$html = "";

        while($donnees = $result->fetch()){
        	$html .= '<tr>';
        	$html .= '<td>'.$donnees['fk_description'].'</td>';
        	$html .= '<td>'.$donnees['pr_value'].'</td>';
        	$html .= '<td><form method="POST" action="edit.php?delCar='.$donnees['id_property'].'&productId='.$donnees['fk_product'].'"><button type="submit" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button></form></td>';
        	$html .= '</tr>';
        }

        return $html;
	}

	public function getProductInfos($productId){
		$db = new connectionClass();

		$query = "SELECT * FROM tb_products WHERE id_product = ".$productId;
		$result = $db->executeQuery($query);

		return $result; 
	}

}

?>