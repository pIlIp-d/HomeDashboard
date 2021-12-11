<?php
/*
Übergabe von Daten einer Seite auf die nächste:
Beispiel:
	Übergabe:
		window.location.href = "wfo_recipe.php?data=13";
	Übernahme:
		<?php echo $_GET["data"]; ?>
*/
	// GET-Variable einlesen
	$json_string = $_GET["json"];
	// JSON dekodieren
	$json_decoded = json_decode($json_string);
	// aktuellen Request ermitteln
	$request_name = $json_decoded->request_name;
	// Konstanten für DB-Verbindung
	$username = "sql";
	$password = "your_password";
	$dbname = "wfoven"; //TODO datenbank umbennenen
	// DB-Verbindung aufbauen
	$dbcon = db_connect($username, $password, $dbname);
	request_handling($request_name);
	// DB-Verbindung schliessen
	db_close($dbcon);
	
	// -------------------------------------------------------------------------------------
	// Funktionen
	// -------------------------------------------------------------------------------------
	
	function request_handling($request_name){
		switch ($request_name){
		// -----------------------------------------------------------------------------------
	    case "get_recipe_data":
			// -----------------------------------------------------------------------------------
				$id = $json_decoded->id;
				$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE id = '$id'";			
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "get_recipe_ingredients":
			// -----------------------------------------------------------------------------------
				$id = $json_decoded->id;
				$sql = "SELECT ri.amount AS amount, i.name AS name, ri.id AS ri_id, i.id AS i_id FROM `recipes` AS `r` INNER JOIN `recipes_ingredients` AS `ri` ON ri.recipes_id = r.id INNER JOIN `ingredients` AS `i` ON i.id = ri.ingredients_id WHERE r.id = '$id'";			
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "add_recipe":
			// -----------------------------------------------------------------------------------
				$rec_name = mysqli_real_escape_string($dbcon, $json_decoded->rec_name);
				$rec_bakingtime = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtime);
				$rec_bakingtemperature = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtemperature);
				$rec_preparation = mysqli_real_escape_string($dbcon, $json_decoded->rec_preparation);
				$sql = "INSERT INTO `recipes` (`id`, `name`, `bakingtime`, `bakingtemperature`, `preparation`) VALUES (NULL, '" . $rec_name . "', '" . $rec_bakingtime . "', '" . $rec_bakingtemperature . "', '" . $rec_preparation . "')";
				try{
					mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
					//echo $result;
					echo mysqli_insert_id($dbcon);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "update_recipe":
			// -----------------------------------------------------------------------------------
				// UPDATE recipes SET name = 'Sauerteigbrot', preparation = 'Zutaten abwiegen...', bakingtime = '30-45 min', bakingtemperature = '270 Grad' WHERE recipes.id = 1;
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$rec_name = mysqli_real_escape_string($dbcon, $json_decoded->rec_name);
				$rec_bakingtime = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtime);
				$rec_bakingtemperature = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtemperature);
				$rec_preparation = mysqli_real_escape_string($dbcon, $json_decoded->rec_preparation);			
				$sql = "UPDATE recipes SET name = '" . $rec_name . "', preparation = '" . $rec_preparation . "', bakingtime = '" . $rec_bakingtime . "', bakingtemperature = '" . $rec_bakingtemperature . "' WHERE recipes.id = '" . $rec_id . "'";			
				try{
					mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "update_recipe_ingredient":
			// -----------------------------------------------------------------------------------
				// UPDATE recipes_ingredients SET amount = '50 g' WHERE recipes_ingredients.id = 3;	
				$ri_id = mysqli_real_escape_string($dbcon, $json_decoded->ri_id);
				$ri_amount = mysqli_real_escape_string($dbcon, $json_decoded->ri_amount);

				$sql = "UPDATE recipes_ingredients SET amount = '" . $ri_amount . "' WHERE recipes_ingredients.id = '" . $ri_id . "'";			
				
				try{
					mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "get_all_ingredients":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT * FROM `ingredients`";			
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "get_count_of_ingredient_recipes":
			// -----------------------------------------------------------------------------------
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			  $sql = "SELECT COUNT(*) AS count FROM `recipes_ingredients` WHERE `ingredients_id` = '$i_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
			    foreach($result as $row){
						echo $row["count"];
					}
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "delete_ingredient":
			// -----------------------------------------------------------------------------------
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			  $sql = "DELETE FROM `ingredients` WHERE `id` = '$i_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
			    foreach($result as $row){
						echo "OK";
					}
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "add_ingredient_to_recipe":
			// -----------------------------------------------------------------------------------
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			  $sql = "INSERT INTO `recipes_ingredients` (`id`, `recipes_id`, `ingredients_id`, `amount`) VALUES (NULL, '$rec_id', '			$i_id', '')";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
			    foreach($result as $row){
						echo "OK";
					}
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "remove_ingredient_from_recipe":
			// -----------------------------------------------------------------------------------
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			  $sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id' AND `recipes_ingredients`.`ingredients_id` = '$i_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
			    foreach($result as $row){
						echo "OK";
					}
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "insert_ingredient":
			// -----------------------------------------------------------------------------------
				$ingr_name = mysqli_real_escape_string($dbcon, $json_decoded->ingr_name);
				$sql = "INSERT INTO ingredients (`id`, `name`) VALUES (NULL, '$ingr_name')";
				try{
					mysqli_report(MYSQLI_REPORT_ALL);
					//mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "get_ingredients_or_rezipes":
			// -----------------------------------------------------------------------------------
				$filtermode = mysqli_real_escape_string($dbcon, $json_decoded->filtermode);
				$sql = "SELECT `id`, `name` FROM `$filtermode` ORDER BY `name`";			
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "get_list_of_recipes":
			// -----------------------------------------------------------------------------------
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
							if ($i == 0){
								$sql = $sql . "ri.ingredients_id = $id";
							}
							else{
								$sql = $sql . " OR ri.ingredients_id = $id";
							}
						}
						$sql = $sql . ") GROUP BY r.id ORDER BY name) AS RES WHERE RES.count = $count";
						//echo $sql;
		      	break;
		      case "recipes":
						$sql = "SELECT id, name FROM `recipes` WHERE (";
						for($i = 0; $i < count($id_list); $i++){
							$id = mysqli_real_escape_string($dbcon, $id_list[$i]->id);
							if ($i == 0){
								$sql = $sql . "id = $id";
							}
							else{
								$sql = $sql . " OR id = $id";
							}
						}
						$sql = $sql . ") ORDER BY name";
						//echo $sql;
		      	break;
				}
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "remove_all_ingredients_from_recipe":
			// -----------------------------------------------------------------------------------
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			  $sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "delete_recipe":
			// -----------------------------------------------------------------------------------
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			  $sql = "DELETE FROM `recipes` WHERE `recipes`.`id` = '$rec_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "rename_bakingplan":
			// -----------------------------------------------------------------------------------
				$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				$bp_name = $json_decoded->bp_name;
				$sql = "UPDATE `bakingplans` SET `name` = '$bp_name' WHERE `bakingplans`.`id` = '$bp_id'";
				try{
					mysqli_report(MYSQLI_REPORT_ALL);
					//mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "insert_bakingplan":
			// -----------------------------------------------------------------------------------
				$bp_name = mysqli_real_escape_string($dbcon, $json_decoded->bp_name);
				$sql = "INSERT INTO `bakingplans` (`id`, `name`, `type`) VALUES (NULL, '$bp_name', '')";
				try{
					mysqli_report(MYSQLI_REPORT_ALL);
					//mysqli_report(MYSQLI_REPORT_ERROR);
					$result = mysqli_query($dbcon, $sql);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
				break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_get_rec_id":
			// -----------------------------------------------------------------------------------
		  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
				$sql = "SELECT `recipes_id` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_get_order_no":
			// -----------------------------------------------------------------------------------
		  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
				$sql = "SELECT `order_no` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_paste_rec":
			// -----------------------------------------------------------------------------------
				$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
				$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				$order_no = mysqli_real_escape_string($dbcon, $json_decoded->order_no);
				$sql = "INSERT INTO `bakingplans_recipes` (`id`, `recipes_id`, `bakingplans_id`, `order_no`) VALUES (NULL, '$rec_id', '$bp_id', '$order_no');";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_remove_recipe":
			// -----------------------------------------------------------------------------------
		  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
			  $sql = "DELETE FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_get_all_recipes":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT * FROM `ingredients`";			
				$sql = "SELECT r.id AS r_id, r.name AS r_name, r.bakingtime AS r_bakingtime, r.bakingtemperature AS r_bakingtemperature, bpr.order_no AS bpr_orderno, bpr.id AS bpr_id, bp.name AS bp_name";
				$sql = $sql . " FROM recipes AS r, bakingplans_recipes AS bpr, bakingplans AS bp";
				$sql = $sql . " WHERE r.id = bpr.recipes_id AND bp.id = bpr.bakingplans_id AND bp.type = 'active'";
				$sql = $sql . " ORDER BY bpr.order_no";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_set_order_no":
			// -----------------------------------------------------------------------------------
		  	$order_no = mysqli_real_escape_string($dbcon, $json_decoded->order_no); 
		  	$bpr_id = mysqli_real_escape_string($dbcon, $json_decoded->bpr_id); 
			  $sql = "UPDATE `bakingplans_recipes` SET `order_no` = '$order_no' WHERE `bakingplans_recipes`.`id` = '$bpr_id'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "get_active_bakingplan":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT `id`, `name` FROM `bakingplans` WHERE `bakingplans`.`type` = 'active'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "get_active_recipe":
				$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE active = 1";			
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	    	break;
	    case "get_all_bakingplans":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT `id`, `name` FROM `bakingplans`";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_activate":
			// -----------------------------------------------------------------------------------
		  	$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					// alle Backpläne ausser dem aktiven Backplan zurücksetzen
					$sql = "UPDATE `bakingplans` SET `type` = '' WHERE NOT `bakingplans`.`id` = '$bp_id'";
					$result = mysqli_query($dbcon, $sql);
					// aktiven Backplan setzen
					$sql = "UPDATE `bakingplans` SET `type` = 'active' WHERE `bakingplans`.`id` = '$bp_id'";
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_delete":
			// -----------------------------------------------------------------------------------
		  	$bp_id = mysqli_real_escape_string($dbcon, $json_decoded->bp_id);
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$sql = "DELETE FROM `bakingplans` WHERE `bakingplans`.`id` = '$bp_id'";
					$result = mysqli_query($dbcon, $sql);
					echo "OK";
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "bakingplan_basic_get_all_recipes":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT id FROM `bakingplans` WHERE `type` = 'active'";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					$id = json_decode(json_encode($rows))[0]->id;
					echo $id;
					request_handling();//$sql = "SELECT id, name FROM `recipes` order by `name`";
				
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;

	    case "get_all_recipes":
			// -----------------------------------------------------------------------------------
				$sql = "SELECT id, name FROM `recipes` order by `name`";
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;

			// -----------------------------------------------------------------------------------
	    case "sql_execute":
			// -----------------------------------------------------------------------------------
			// SQL-Statement ausführen (INSERT, UPDATE, DELETE ...)
				$sql = $json_decoded->sql;			
				$res = mysqli_query($dbcon, $sql);
				echo "SQL:\n" . $sql . "\nRes:\n" . $res;		  
	      break;
			// -----------------------------------------------------------------------------------
	    case "sql_get_count":
			// -----------------------------------------------------------------------------------
				$from = $json_decoded->from;
				$where = $json_decoded->where;
				if ($where == ""){
			  	$sql = "SELECT COUNT(*) AS count FROM " . $from;
				}
				else{
			  	$sql = "SELECT COUNT(*) AS count FROM " . $from . " WHERE " . $where;
				}
				$result = mysqli_query($dbcon, $sql);
		    foreach($result as $row){
					echo $row["count"];
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "sql2json_new":
			// -----------------------------------------------------------------------------------
		  	$sql = $json_decoded->sql; 
				// Fehlerbehandlung einschalten
				//mysqli_report(MYSQLI_REPORT_ALL);
				mysqli_report(MYSQLI_REPORT_ERROR);
				try{
					$result = mysqli_query($dbcon, $sql);
					$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
					echo json_encode($rows);  
				}
				catch (mysqli_sql_exception $e){
					error_log($e->__toString() . "------> SQL: " . $sql);
					echo "DB_ERROR:" . $e->__toString();
				}
	      break;
		
			// -----------------------------------------------------------------------------------
	    case "reclist_get_count":
			// -----------------------------------------------------------------------------------
		  	$sql = "SELECT COUNT(*) AS count FROM recipes"; 
				$result = mysqli_query($dbcon, $sql);
		    foreach($result as $row){
					echo $row["count"];
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "reclist_get_all_ids":
			// -----------------------------------------------------------------------------------
		  	$sql = "SELECT id FROM recipes ORDER BY name"; 
				echo sql2json($dbcon, $sql);
				/*
				$result = mysqli_query($dbcon, $sql);
		    foreach($result as $row){
					$data[] = $row;
				}
				if(isset($data)){
					$json = json_encode($data);
				}else{
					$json = null;
				}
				echo $json;
				*/
	      break;
			// -----------------------------------------------------------------------------------
	    case "reclist_get_all_recipes":
			// -----------------------------------------------------------------------------------
				/*
		  	$sql = "SELECT id, name, bakingtime, bakingtemperature FROM recipes ORDER BY name"; 
				echo sql2json($dbcon, $sql);
				*/
	      break;
			// -----------------------------------------------------------------------------------
	    case "recipe_expand_collapse":
			// -----------------------------------------------------------------------------------
				/*
				$recipe_id = $json_decoded->rec_id;			
	 	  	$sql = "SELECT r.name AS r_name, ri.amount AS ri_amount, ri.id AS ri_id, i.name AS i_name FROM `recipes` AS `r` INNER JOIN `recipes_ingredients` AS `ri` ON ri.recipes_id = r.id INNER JOIN `ingredients` AS `i` ON i.id = ri.ingredients_id WHERE	r.id = " . $recipe_id . ";";
				$result = mysqli_query($dbcon, $sql);
				echo sql2json($dbcon, $sql);
				*/
	      break;
			// -----------------------------------------------------------------------------------
	    case "sql2json":
			// -----------------------------------------------------------------------------------
		  	$sql = $json_decoded->sql; 
				// SQL-Statement an DB schicken und Ergebnis als json zurückliefern (SELECT)
				$result = mysqli_query($dbcon, $sql);
			  foreach($result as $row){
					$data[] = $row;
				}
				if(isset($data)){
					$json = json_encode($data);
				}
				else{
					$json = null;
				}
				echo $json;
	      break;
			// -----------------------------------------------------------------------------------
	    case "sql_get_count2":
			// -----------------------------------------------------------------------------------
				$sql = $json_decoded->sql;
				$result = mysqli_query($dbcon, $sql);
		    foreach($result as $row){
					echo $row["count"];
				}
	      break;
			// -----------------------------------------------------------------------------------
	    case "???":
			// -----------------------------------------------------------------------------------
	      break;

		}	

	}



	function sql2json($dbcon, $sql){
		// SQL-Statement an DB schicken und Ergebnis als json zurückliefern
		$result = mysqli_query($dbcon, $sql);
	  foreach($result as $row){
			$data[] = $row;
		}
		if(isset($data)){
			$json = json_encode($data);
		}
		else{
			$json = null;
		}
		return $json;
	}
	
	function db_connect($user, $pass, $db){
		$dbcon = mysqli_connect("localhost", $user, $pass, $db);
		return($dbcon);	
	}
	
	function db_close($dbcon){
		mysqli_close($dbcon);
	}		
	
?>