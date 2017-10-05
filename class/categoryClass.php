<?php
/**
 * category manages the product categorys of the backend pages of the eShop
 *
 *
 * @category   categoryClass
 * @author     Original Author jpmachacaz13@hotmail.com
 * @copyright  2011 João Machacaz
 * @license    GPL 3.0
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2
 * @deprecated Class deprecated in Release 1.0 - 2012
 */

class categoryClass{

	public function createCategory($categoryId){
		//Define variables
		$bdd = $GLOBALS['bdd'];
		$alert = new errorClass();
		$db = new connectionClass();

		//On vérifie que le categorie n'existe pas déjà
		$isExist = $db->getQueryResult('SELECT id_category FROM tb_categories WHERE id_category = "'.$categoryId.'"', 'id_category');


		//If user doesnt exist on DB
		if(!$isExist){
			$qry = $bdd->prepare("INSERT INTO tb_categories(id_category) VALUES(:id_category)");
			$qry->execute(array(
				'id_category' => $categoryId,
			));

			return $alert->returnSuccess(""); //Success

		}else{
			return $alert->returnError(""); //Error, cancel query
		}
	}

	public function updateCategory($categoryId, $oldCategoryId){

		$alert = new errorClass();
		$db = new connectionClass();

		//Update connected articles
		$qry = "UPDATE tb_products SET fk_category = '".$categoryId."' WHERE fk_category = '".$oldCategoryId."'";
		$db->executeQuery($qry);

		//update category
		$qry = "UPDATE tb_categories SET id_category = '".$categoryId."' WHERE id_category = '".$oldCategoryId."'";
		$db->executeQuery($qry);

		return $categoryId;

	}

	/***************************************************************************
	GETTERS FUNCTIONS
	***************************************************************************/

	public function getCategoryTableList($searchParam=NULL){

		$db = new connectionClass();

		$html .= '<div class="panel panel-default">
				  <!-- Default panel contents -->
				 <div class="panel-heading">
				<form class="form-inline" action="index.php" method="POST">
				<div class="form-group">     
		            <input type="text" class="form-control" value="'.$searchParam.'" name="searchParam" id="searchParam" placeholder="Chercher une catégorie">';
		$html .='</div>
				</form>
				</div>';
		$html .='<table id="tblAddress" class="table table-striped table-bordered">
          	<tr>
          		<th>Categories</th>
          		<th style="width: 80px;"></th>
          	</tr>';

        $query = "SELECT * FROM tb_categories";
        if(isset($searchParam)){
        	$query .= " WHERE id_category LIKE '%".$searchParam."%'";
        }
        $query .= " ORDER BY id_category ASC";
        $result = $db->executeQuery($query);

        while($donnees = $result->fetch()){
            $html .= '<tr><td>'.$donnees['id_category'].'</td>';
            $html .= '<td><div class="btn-group">
						  <button type="button" class="btn btn-default btn-xs" onClick="redirect(\'edit.php?categoryId='.$donnees['id_category'].'\')"><span class="glyphicon glyphicon-pencil"></span></button>
						  <button type="button" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-remove"></span></button>
						</div></td></tr>';
        }

        $html .= '</tr></table>';
        return $html;
	}

	public function getCategoryInfos($categoryId){
		$db = new connectionClass();

		$query = "SELECT * FROM tb_categories WHERE id_category = '".$categoryId."'";
		$result = $db->executeQuery($query);

		return $result; 
	}
	/***************************************************************************
	SETTERS FUNCTIONS
	***************************************************************************/
}

?>