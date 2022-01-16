<?php
	// GET-Variable einlesen
	$json_string = $_GET["json"];
	$json_decoded = json_decode($json_string);
	$request_name = $json_decoded->request_name;

	//DB
	$username = "sql";
	$password = "your_password";
	$dbname = "wfoven"; //TODO datenbank umbennenen

	$dbcon = db_connect($username, $password, $dbname);
	request_handling($request_name);
	db_close($dbcon);

	// -------------------------------------------------------------------------------------
	// Funktionen
	// -------------------------------------------------------------------------------------

	function request_handling($request_name){
		GLOBAL $dbcon;
		GLOBAL $json_decoded;
		switch ($request_name){
			case "get_recipe_data":
				$id = $json_decoded->id;
				$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE id = '$id'";			
				echo sql_request_encode_json($dbcon, $sql);	
		      break;
		    case "get_recipe_ingredients":
				$id = $json_decoded->id;
				$sql = "SELECT ri.amount AS amount, i.name AS name, ri.id AS ri_id, i.id AS i_id FROM `recipes` AS `r` INNER JOIN `recipes_ingredients` AS `ri` ON ri.recipes_id = r.id INNER JOIN `ingredients` AS `i` ON i.id = ri.ingredients_id WHERE r.id = '$id'";
				echo sql_request_encode_json($dbcon, $sql);
		      break;

			case "add_recipe":
				$rec_name = mysqli_real_escape_string($dbcon, $json_decoded->rec_name);
				$rec_bakingtime = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtime);
				$rec_bakingtemperature = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtemperature);
				$rec_preparation = mysqli_real_escape_string($dbcon, $json_decoded->rec_preparation);
				$sql = "INSERT INTO `recipes` (`id`, `name`, `bakingtime`, `bakingtemperature`, `preparation`) VALUES (NULL, '" . $rec_name . "', '" . $rec_bakingtime . "', '" . $rec_bakingtemperature . "', '" . $rec_preparation . "')";
				$result = sql_request($dbcon, $sql);
				echo mysqli_insert_id($dbcon);
			  break;

			case "update_recipe":
				// UPDATE recipes SET name = 'Sauerteigbrot', preparation = 'Zutaten abwiegen...', bakingtime = '30-45 min', bakingtemperature = '270 Grad' WHERE recipes.id = 1;
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$rec_name = mysqli_real_escape_string($dbcon, $json_decoded->rec_name);
				$rec_bakingtime = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtime);
				$rec_bakingtemperature = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtemperature);
				$rec_preparation = mysqli_real_escape_string($dbcon, $json_decoded->rec_preparation);			
				$sql = "UPDATE recipes SET name = '" . $rec_name . "', preparation = '" . $rec_preparation . "', bakingtime = '" . $rec_bakingtime . "', bakingtemperature = '" . $rec_bakingtemperature . "' WHERE recipes.id = '" . $rec_id . "'";
				sql_request($dbcon, $sql);
				echo "OK";	
			  break;

		    case "update_recipe_ingredient":
				// UPDATE recipes_ingredients SET amount = '50 g' WHERE recipes_ingredients.id = 3;	
				$ri_id = mysqli_real_escape_string($dbcon, $json_decoded->ri_id);
				$ri_amount = mysqli_real_escape_string($dbcon, $json_decoded->ri_amount);
				$sql = "UPDATE recipes_ingredients SET amount = '" . $ri_amount . "' WHERE recipes_ingredients.id = '" . $ri_id . "'";	
				sql_request($dbcon, $sql);
				echo "OK";
			  break;

			case "get_all_ingredients":
				$sql = "SELECT * FROM `ingredients`";			
				echo sql_request_encode_json($dbcon, $sql);
			  break;
			
			case "get_count_of_ingredient_recipes":
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
				$sql = "SELECT COUNT(*) AS count FROM `recipes_ingredients` WHERE `ingredients_id` = '$i_id'";
				$result = sql_request($dbcon, $sql);
			    foreach($result as $row)
					echo $row["count"];
		      break;
		
			case "delete_ingredient":
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
				$sql = "DELETE FROM `ingredients` WHERE `id` = '$i_id'";
				$result = sql_request($dbcon, $sql);
				foreach($result as $row)
					echo "OK";
					
		      break;

		    case "add_ingredient_to_recipe":
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
				$sql = "INSERT INTO `recipes_ingredients` (`id`, `recipes_id`, `ingredients_id`, `amount`) VALUES (NULL, '$rec_id', '			$i_id', '')";	
				$result = sql_request($dbcon, $sql);
				foreach($result as $row)
					echo "OK";
		      break;

			case "remove_ingredient_from_recipe":
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			 	$sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id' AND `recipes_ingredients`.`ingredients_id` = '$i_id'";	
				$result = sql_request($dbcon, $sql);
				foreach($result as $row)
					echo "OK";	
		      break;

		    case "insert_ingredient":
				$ingr_name = mysqli_real_escape_string($dbcon, $json_decoded->ingr_name);
				$sql = "INSERT INTO ingredients (`id`, `name`) VALUES (NULL, '$ingr_name')";
				mysqli_report(MYSQLI_REPORT_ALL);
				sql_request($dbcon, $sql);		
			  	echo "OK";
			  break;

		    case "get_ingredients_or_rezipes":
				$filtermode = mysqli_real_escape_string($dbcon, $json_decoded->filtermode);
				$sql = "SELECT `id`, `name` FROM `$filtermode` ORDER BY `name`";			
				echo sql_request_encode_json($dbcon, $sql);
			  break;

		    case "get_list_of_recipes":
				$id_list = $json_decoded->id_list;
				$count = mysqli_real_escape_string($dbcon, $json_decoded->count);
				$filtermode = mysqli_real_escape_string($dbcon, $json_decoded->filtermode);

				switch ($filtermode){
					case "none":
						$sql = "SELECT `id`, `name` FROM `recipes` ORDER BY `name`";			
						break;
					case "ingredients":
						$sql = "SELECT RES.id, RES.name FROM (SELECT r.id AS id, r.name AS name, COUNT(r.id) AS count FROM recipes as r, recipes_ingredients as ri WHERE r.id = ri.recipes_id AND (";
						for($i = 0; $i < count($id_list); $i++){
							$id = mysqli_real_escape_string($dbcon, $id_list[$i]->id);
							if ($i == 0)
								$sql = $sql . "ri.ingredients_id = $id";
							else
								$sql = $sql . " OR ri.ingredients_id = $id";
						}
						$sql = $sql . ") GROUP BY r.id ORDER BY name) AS RES WHERE RES.count = $count";
					  break;
					case "recipes":
						$sql = "SELECT id, name FROM `recipes` WHERE (";
						for($i = 0; $i < count($id_list); $i++){
							$id = mysqli_real_escape_string($dbcon, $id_list[$i]->id);
							if ($i == 0)
								$sql = $sql . "id = $id";
							else
								$sql = $sql . " OR id = $id";
						}
						$sql = $sql . ") ORDER BY name";
					  break;
				}
				echo sql_request_encode_json($dbcon, $sql);
		      break;

		    case "remove_all_ingredients_from_recipe":
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id'";
				sql_request($dbcon, $sql);
				echo "OK";
					
		      break;

			case "delete_recipe":
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$sql = "DELETE FROM `recipes` WHERE `recipes`.`id` = '$rec_id'";
				sql_request($dbcon, $sql);
				echo "OK";
			  break;

		    case "rename_bakingplan":
				$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				$bp_name = $json_decoded->bp_name;
				$sql = "UPDATE `bakingplans` SET `name` = '$bp_name' WHERE `bakingplans`.`id` = '$bp_id'";
				mysqli_report(MYSQLI_REPORT_ALL);//übernahme ohne test ob notwendig
				sql_request($dbcon, $sql);
				echo "OK";
			  break;

		    case "insert_bakingplan":
				$bp_name = mysqli_real_escape_string($dbcon, $json_decoded->bp_name);
				$sql = "INSERT INTO `bakingplans` (`id`, `name`, `type`) VALUES (NULL, '$bp_name', '')";
				mysqli_report(MYSQLI_REPORT_ALL);//übernahme ohne test ob notwendig
				sql_request($dbcon, $sql);
				echo "OK";
			break;

		    case "bakingplan_get_rec_id":
			  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
				$sql = "SELECT `recipes_id` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				echo sql_request_encode_json($dbcon, $sql);
		      break;

		    case "bakingplan_get_order_no":
			  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id);
				$sql = "SELECT `order_no` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";		
				echo sql_request_encode_json($dbcon, $sql);
			  break;
			
			case "bakingplan_paste_rec":
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				$order_no = mysqli_real_escape_string($dbcon, $json_decoded->order_no);
				$sql = "INSERT INTO `bakingplans_recipes` (`id`, `recipes_id`, `bakingplans_id`, `order_no`) VALUES (NULL, '$rec_id', '$bp_id', '$order_no');";
				sql_request($dbcon, $sql);
				echo "OK";
		      break;

		    case "bakingplan_remove_recipe":
			  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id);
				$sql = "DELETE FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				sql_request($dbcon, $sql);
				echo "OK";
		      break;

			case "bakingplan_get_all_recipes":
				$sql = "SELECT r.id AS r_id, r.name AS r_name, r.bakingtime AS r_bakingtime, r.bakingtemperature AS r_bakingtemperature,bpr.order_no AS bpr_orderno, bpr.id AS bpr_id, bp.name AS bp_name";
				$sql = $sql . " FROM recipes AS r, bakingplans_recipes AS bpr, bakingplans AS bp";
				$sql = $sql . " WHERE r.id = bpr.recipes_id AND bp.id = bpr.bakingplans_id AND bp.type = 'active'";
				$sql = $sql . " ORDER BY bpr.order_no";
				echo sql_request_encode_json($dbcon, $sql);	
		      break;

		    case "bakingplan_set_order_no":
			  	$order_no = mysqli_real_escape_string($dbcon, $json_decoded->order_no); 
			  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
				$sql = "UPDATE `bakingplans_recipes` SET `order_no` = '$order_no' WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				sql_request($dbcon, $sql);
				echo "OK";
		      break;

		    case "get_active_bakingplan":
				$sql = "SELECT `id`, `name` FROM `bakingplans` WHERE `bakingplans`.`type` = 'active'";
				echo sql_request_encode_json($dbcon, $sql);
		      break;

		    case "get_active_recipe":
				$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE active = 1";			
				echo sql_request_encode_json($dbcon, $sql);			
			  break;

		    case "get_all_bakingplans":
				$sql = "SELECT `id`, `name`, `type` FROM `bakingplans`";
				echo sql_request_encode_json($dbcon, $sql);  
		      break;

		    case "bakingplan_activate"://Set
			  	$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				// alle Backpläne ausser dem aktiven Backplan zurücksetzen
				$sql = "UPDATE `bakingplans` SET `type` = '' WHERE NOT `bakingplans`.`id` = '$bp_id'";
				sql_request($dbcon, $sql);
				// aktiven Backplan setzen
				$sql = "UPDATE `bakingplans` SET `type` = 'active' WHERE `bakingplans`.`id` = '$bp_id'";
				sql_request($dbcon, $sql);
				echo "OK";
		      break;

		    case "bakingplan_delete":
			  	$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);				
				$sql = "DELETE FROM `bakingplans` WHERE `bakingplans`.`id` = '$bp_id'";
				$result = sql_request($dbcon, $sql);
				echo "OK";
		      break;

		    case "bakingplan_basic_get_all_recipes":
				$sql = "SELECT id FROM `bakingplans` WHERE `type` = 'active'";
				$result = sql_request_encode_json($dbcon, $sql);
				//unpack and read id
				echo json_decode($result)[0]->id;
		      break;

		    case "get_all_recipes":
				$sql = "SELECT id, name FROM `recipes` order by `name`";
				echo sql_request_encode_json($dbcon, $sql);
		      break;

		    case "???":
		        break;
		}	
	}
	function sql_request_encode_json($dbcon, $sql_request){
		return json_encode(mysqli_fetch_all(sql_request($dbcon, $sql_request),MYSQLI_ASSOC));
	}

	function sql_request($dbcon, $sql_request){
		mysqli_report(MYSQLI_REPORT_ERROR);
		try {
			return mysqli_query($dbcon, $sql_request);
		}
		catch(mysql_sql_exception $e){
			error_log($e->__toString() . "------> SQL: " . $sql);
			echo "DB_ERROR:" . $e->__toString();
		}
	}

	function db_connect($user, $pass, $db){
		$dbcon = mysqli_connect("localhost", $user, $pass, $db);
		return($dbcon);	
	}

	function db_close($dbcon){
		mysqli_close($dbcon);
	}
?>