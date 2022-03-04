<?php
// GET-Variable einlesen
$json_string = $_GET["json"];

global $json_decoded;
$json_decoded = json_decode($json_string);
make_request($json_decoded->request_name);

function make_request($request_name){
	$credentials = json_decode(file_get_contents("cred.json"))->db_cred;
	$username = $credentials->username;
	$password =  $credentials->password;
	$dbname =  $credentials->db_name;
	//TODO db_initialisation
	$dbcon = db_connect($username, $password, $dbname);
	request_handling($request_name,$dbcon);
	db_close($dbcon);
}

function request_handling($request_name,$dbcon){
	GLOBAL $json_decoded;
	switch ($request_name){

	//------------------------------------------------------------------------------
	//--------- RECIPE -------------------------------------------------------------
	//------------------------------------------------------------------------------
		case "add_recipe":
			$rec_name = mysqli_real_escape_string($dbcon, $json_decoded->rec_name);
			$rec_bakingtime = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtime);
			$rec_bakingtemperature = mysqli_real_escape_string($dbcon, $json_decoded->rec_bakingtemperature);
			$rec_preparation = mysqli_real_escape_string($dbcon, $json_decoded->rec_preparation);
			$sql = "INSERT INTO `recipes` (`id`, `name`, `bakingtime`, `bakingtemperature`, `preparation`) VALUES (NULL, '" . $rec_name . "', '" . $rec_bakingtime . "', '" . $rec_bakingtemperature . "', '" . $rec_preparation . "')";
			$result = sql_request($dbcon, $sql);
			echo mysqli_insert_id($dbcon);
			break;

		case "delete_recipe":
			$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			$sql = "DELETE FROM `recipes` WHERE `recipes`.`id` = '$rec_id'";
			sql_request($dbcon, $sql);
			echo "OK";
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

		case "get_all_recipes":
			$sql = "SELECT id, name FROM `recipes` order by `name`";
			echo sql_request_encode_json($dbcon, $sql);
			break;

		case "get_recipe_data":
			$id = mysqli_real_escape_string($dbcon, $json_decoded->id);
			$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE id = '$id'";
			echo sql_request_encode_json($dbcon, $sql);
	 		break;

		case "set_active_recipe":
			$recipe_id = mysqli_real_escape_string($dbcon, $json_decoded->recipe_id);
			// alle Backpläne ausser dem aktiven Backplan zurücksetzen
			$sql = "UPDATE `recipes` SET `active` = 0";
			sql_request($dbcon, $sql);
			// aktiven Backplan setzen
			$sql = "UPDATE `recipes` SET `active` = 1 WHERE `recipes`.`id` = '$recipe_id'";
			sql_request($dbcon, $sql);
			echo "OK";
		  	break;

		case "get_active_recipe":
			$sql = "SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE active = 1";
			echo sql_request_encode_json($dbcon, $sql);
		  	break;

		case "get_list_of_recipes":
			$id_list = mysqli_real_escape_string($dbcon, $json_decoded->id_list);
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

	//--------------------------------------------------------------------------
	//--------- RECIPE INGREDIENTS ---------------------------------------------
	//--------------------------------------------------------------------------
		case "add_ingredient_to_recipe":
			$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			$sql = "INSERT INTO `recipes_ingredients` (`id`, `recipes_id`, `ingredients_id`, `amount`) VALUES (NULL, '$rec_id', '			$i_id', '')";
			$result = sql_request($dbcon, $sql);
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

		case "remove_ingredient_from_recipe":
			$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
		 	$sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id' AND `recipes_ingredients`.`ingredients_id` = '$i_id'";
			$result = sql_request($dbcon, $sql);
				foreach($result as $row)
					echo "OK";
			break;

		case "get_recipe_ingredients":
			$id = mysqli_real_escape_string($dbcon, $json_decoded->id);
			$sql = "SELECT ri.amount AS amount, i.name AS name, ri.id AS ri_id, i.id AS i_id FROM `recipes` AS `r` INNER JOIN `recipes_ingredients` AS `ri` ON ri.recipes_id = r.id INNER JOIN `ingredients` AS `i` ON i.id = ri.ingredients_id WHERE r.id = '$id'";
			echo sql_request_encode_json($dbcon, $sql);
	      	break;

		case "get_count_of_ingredient_recipes":
			$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			$sql = "SELECT COUNT(*) AS count FROM `recipes_ingredients` WHERE `ingredients_id` = '$i_id'";
			$result = sql_request($dbcon, $sql);
		    foreach($result as $row)
				echo $row["count"];
	    	break;

		case "remove_all_ingredients_from_recipe":
			$rec_id = mysqli_real_escape_string($dbcon, $json_decoded->rec_id);
			$sql = "DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = '$rec_id'";
			sql_request($dbcon, $sql);
			echo "OK";
  	  		break;

	//--------------------------------------------------------------------------
	//--------- INGREDIENTS ----------------------------------------------------
	//--------------------------------------------------------------------------
	    case "insert_ingredient":
			$ingr_name = mysqli_real_escape_string($dbcon, $json_decoded->ingr_name);
			$sql = "INSERT INTO ingredients (`id`, `name`) VALUES (NULL, '$ingr_name')";
			mysqli_report(MYSQLI_REPORT_ALL);
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "get_all_ingredients":
			$sql = "SELECT * FROM `ingredients`";
			echo sql_request_encode_json($dbcon, $sql);
			break;

		case "delete_ingredient":
			$i_id = mysqli_real_escape_string($dbcon, $json_decoded->i_id);
			$sql = "DELETE FROM `ingredients` WHERE `id` = '$i_id'";
			$result = sql_request($dbcon, $sql);
			foreach($result as $row)
				echo "OK";
			break;

	    case "get_ingredients_or_rezipes"://TODO fix typo
			$filtermode = mysqli_real_escape_string($dbcon, $json_decoded->filtermode);
			$sql = "SELECT `id`, `name` FROM `$filtermode` ORDER BY `name`";
			echo sql_request_encode_json($dbcon, $sql);
			break;

	//--------------------------------------------------------------------------
	//--------- BAKINGPLAN -----------------------------------------------------
	//--------------------------------------------------------------------------
	  	case "insert_bakingplan":
			$bp_name = mysqli_real_escape_string($dbcon, $json_decoded->bp_name);
			$sql = "INSERT INTO `bakingplans` (`id`, `name`, `type`) VALUES (NULL, '$bp_name', '')";
			mysqli_report(MYSQLI_REPORT_ALL);//übernahme ohne test ob notwendig
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
			$sql .= " FROM recipes AS r, bakingplans_recipes AS bpr, bakingplans AS bp";
			$sql .= " WHERE r.id = bpr.recipes_id AND bp.id = bpr.bakingplans_id AND bp.type = 'active'";
			$sql .= " ORDER BY bpr.order_no";
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

	//--------------------------------------------------------------------------
	//--------- PRESETS --------------------------------------------------------
	//--------------------------------------------------------------------------
		case "set_new_preset":
			$preset_name = mysqli_real_escape_string($dbcon, $json_decoded->preset_name);
			$grid_object_v = mysqli_real_escape_string($dbcon, json_encode($json_decoded->grid_object_v));
			$grid_object_h = mysqli_real_escape_string($dbcon, json_encode($json_decoded->grid_object_h));
			$sql = "INSERT INTO presets (`id`, `name`, `grid_object_v`, `grid_object_h`) VALUES (NULL, '$preset_name', '$grid_object_v', '$grid_object_h')";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "get_preset_ids":
			$sql = "SELECT id FROM `presets` order by `id`";
		  	echo sql_request_encode_json($dbcon, $sql);
		  	break;

		case "get_all_presets":
			$sql = "SELECT id, name, grid_object_v, grid_object_h FROM `presets` order by `id`";
			$resp = json_decode(sql_request_encode_json($dbcon, $sql));
			foreach ($resp as $key => $w){//return grid object v/h as object instead of string
				$resp[$key]->grid_object_v = json_decode($resp[$key]->grid_object_v);
				$resp[$key]->grid_object_h = json_decode($resp[$key]->grid_object_h);
			}
			echo json_encode($resp);
			break;

		case "get_preset":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$sql = "SELECT id, name, grid_object_v, grid_object_h FROM presets WHERE `presets`.`id` = $preset_id";
			$resp = json_decode(sql_request_encode_json($dbcon, $sql))[0];
			$resp->grid_object_v = json_decode($resp->grid_object_v);
			$resp->grid_object_h = json_decode($resp->grid_object_h);
			echo json_encode($resp);
			break;

		case "save_preset":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$preset_name = mysqli_real_escape_string($dbcon, $json_decoded->preset_name);
			$grid_object_v = mysqli_real_escape_string($dbcon, json_encode($json_decoded->grid_object_v));
			$grid_object_h = mysqli_real_escape_string($dbcon, json_encode($json_decoded->grid_object_h));
			$sql = "UPDATE presets SET name = '$preset_name', grid_object_v = '$grid_object_v',  grid_object_h = '$grid_object_h' WHERE presets.id = '$preset_id'";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "delete_preset":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$sql = "DELETE FROM presets WHERE presets.id = $preset_id";
			sql_request($dbcon, $sql);
			//delete the timers associated with the preset
			$sql = "DELETE FROM timers WHERE timers.preset_id = $preset_id";
			sql_request($dbcon, $sql);
			echo "OK";
			break;


	//--------------------------------------------------------------------------
	//--------- DEVICES --------------------------------------------------------
	//--------------------------------------------------------------------------
		case "insert_device":
			$name = mysqli_real_escape_string($dbcon, $json_decoded->device_name);
			$sql = "INSERT INTO devices (`id`, `name`) VALUES (NULL, '$name')";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "delete_device":
			$device_id = mysqli_real_escape_string($dbcon, $json_decoded->device_id);
			$sql = "DELETE FROM devices WHERE devices.id = $device_id";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "set_act_temp":
			//@param (device_name OR device_id) + temp_act
			$temp_act = mysqli_real_escape_string($dbcon, $json_decoded->temp_act);
			$timecode = time();
			$timestring = date("d.m.Y-H:i:s", $timecode);
			$sql =  "UPDATE devices SET devices.temp_act = $temp_act, devices.timecode = $timecode, devices.timestring = '$timestring' ";
			if (isset($json_decoded->device_id)){
				$device_id = mysqli_real_escape_string($dbcon, $json_decoded->device_id);
				$sql .= "WHERE devices.id = $device_id";
			}
			else {
				$device_name = mysqli_real_escape_string($dbcon, $json_decoded->device_name);
				$sql .= "WHERE devices.name = '$device_name'";
			}
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "get_device_values":
			//@param (device_id OR device_name) AND timout(optional, default timeout = 50)
			$sql = "SELECT id, name, temp_act, temp_min, temp_max, timecode FROM devices ";
			if (isset($json_decoded->device_id)){
				$device_id = mysqli_real_escape_string($dbcon, $json_decoded->device_id);
				$sql .= "WHERE devices.id = $device_id";
			}
			else {
				$device_name = mysqli_real_escape_string($dbcon, $json_decoded->device_name);
				$sql .= "WHERE devices.name = '$device_name'";
			}
			$timeout = 50;
			if (isset($json_decoded->$timeout))
				$timeout = mysqli_real_escape_string($dbcon, $json_decoded->timeout);
			$resp = json_decode(sql_request_encode_json($dbcon, $sql))["0"];
			if ((int)$resp->timecode + (int)$timeout  < time())
				$resp->temp_act = "--";
			echo json_encode($resp);
			break;

		case "get_all_devices":
			//not tested
			$sql = "SELECT id, name FROM devices";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "set_minmaxvalues":
			//@param temp_min, temp_max AND (device_id OR device_name)
			$temp_min = mysqli_real_escape_string($dbcon, $json_decoded->temp_min);
			$temp_max = mysqli_real_escape_string($dbcon, $json_decoded->temp_max);
			$sql = "UPDATE devices SET devices.temp_min = '$temp_min', devices.temp_max = '$temp_max' ";
			if (isset($json_decoded->device_id)){
				$device_id = mysqli_real_escape_string($dbcon, $json_decoded->device_id);
				$sql .= "WHERE devices.id = $device_id";
			}
			else {
				$device_name = mysqli_real_escape_string($dbcon, $json_decoded->device_name);
				$sql .= "WHERE devices.name = '$device_name'";
			}
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "set_all_values":
			//TODO request from sensor device to set multiple values with one request
			break;

	//--------------------------------------------------------------------------
	//--------- TIMERS ---------------------------------------------------------
	//--------------------------------------------------------------------------
		case "set_timer":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$timer_id = mysqli_real_escape_string($dbcon, $json_decoded->timer_id);
			$time = mysqli_real_escape_string($dbcon, $json_decoded->time);
			$sql = "IF EXISTS(SELECT id FROM timers WHERE timers.preset_id = $preset_id AND timers.timer_id = $timer_id) THEN
						UPDATE timers SET timers.time = $time WHERE timers.preset_id = $preset_id AND timers.timer_id = $timer_id;
					ELSE
						INSERT INTO timers (`id`, `preset_id`, `timer_id`, `time`) VALUES (NULL, $preset_id, $timer_id, $time);
					END IF";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "del_timer":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$timer_id = mysqli_real_escape_string($dbcon, $json_decoded->timer_id);
			$timecode = time();
			$sql = "UPDATE timers SET time = $timecode WHERE timers.preset_id = $preset_id AND timers.timer_id = $timer_id;";
			sql_request($dbcon, $sql);
			echo "OK";
			break;

		case "get_timer":
			$preset_id = mysqli_real_escape_string($dbcon, $json_decoded->preset_id);
			$timer_id = mysqli_real_escape_string($dbcon, $json_decoded->timer_id);
			$sql = "SELECT time FROM timers WHERE timers.preset_id = $preset_id AND timers.timer_id = $timer_id;";
			$resp = sql_request_encode_json($dbcon, $sql);
			echo json_decode($resp)[0]->time;
			break;

		case "send_message"://send mail message through python
		//not tested
			$message = "python libs/export_mail.py \"";
			if ($json_decoded->message != "")
				$message .= $json_decoded->message;
			else
				$message .= "\"empty message - at json_handler\"";
			$message .= "\"";
			$command = escapeshellcmd($message);
			shell_exec($command);
			echo "OK";
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
