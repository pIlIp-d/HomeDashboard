<?php

//REQUIRES - fully setup of db and cred.json
// GET-Variable einlesen
$json_string = $_GET["json"];

global $json_decoded;
$json_decoded = json_decode($json_string);
make_request($json_decoded->request_name, $json_decoded);

function make_request($request_name, $json_decoded)
{
    $credentials = json_decode(file_get_contents("cred.json"))->db_cred;
    $username = $credentials->username;
    $password = $credentials->password;
    $dbname = $credentials->db_name;
    $db_host = $credentials->db_host;

    $dbcon = new PDO("mysql:host=$db_host;dbname=$dbname", $username, $password);
    request_handling($request_name, $dbcon, $json_decoded);
}

function request_handling($request_name, $dbcon, $json_decoded)
{

    switch ($request_name)
    {
        //------------------------------------------------------------------------------
        //--------- RECIPE -------------------------------------------------------------
        //------------------------------------------------------------------------------
        //TODO full rename add -> insert
        case "insert_recipe":
            $stmt = $dbcon->prepare("INSERT INTO `recipes` (`id`, `name`, `bakingtime`, `bakingtemperature`, `preparation`) VALUES (NULL, :name, :bakingtime, :bakingtemperature, :preparation)");
            $stmt->bindParam(":name", $json_decoded->rec_name);
            $stmt->bindParam(":bakingtime", $json_decoded->rec_bakingtime);
            $stmt->bindParam(":bakingtemperature", $json_decoded->rec_bakingtemperature);
            $stmt->bindParam(":preparation", $json_decoded->rec_preparation);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        //TODO delete-> remove
        case "remove_recipe":
            $stmt = $dbcon->prepare("DELETE FROM `recipes` WHERE `recipes`.`id` = :id");
            $stmt->bindParam("id", $json_decoded->rec_id);
            $stmt->execute();
            echo "OK";
            break;

        case "update_recipe":
            $stmt = $dbcon->prepare("UPDATE `recipes` SET `name` = :name, `preparation` = :preparation, `bakingtime` = :bakingtime, `bakingtemperature` = :bakingtemperature WHERE recipes.id = :id;");
            $stmt->bindParam(":id", $json_decoded->rec_id);
            $stmt->bindParam(":name", $json_decoded->rec_name);
            $stmt->bindParam(":bakingtime", $json_decoded->rec_bakingtime);
            $stmt->bindParam(":bakingtemperature", $json_decoded->rec_bakingtemperature);
            $stmt->bindParam(":preparation", $json_decoded->rec_preparation);
            $stmt->execute();
            echo "OK";
            break;

        case "get_all_recipes":
            $stmt = $dbcon->prepare("SELECT id, name FROM `recipes` order by `id`;");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "get_recipe_data":
            $stmt = $dbcon->prepare("SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE `id` = :id;");
            $stmt->bindParam(":id", $json_decoded->id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        case "set_active_recipe":
            $stmt = $dbcon->prepare("UPDATE `recipes` SET `active` = 0");
            $stmt->execute();

            $stmt = $dbcon->prepare("UPDATE `recipes` SET `active` = 1 WHERE `recipes`.`id` = :id;");
            $stmt->bindParam(":id", $json_decoded->recipe_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_active_recipe":
            $stmt = $dbcon->prepare("SELECT `id`, `name`, `bakingtime`, `bakingtemperature`, `preparation` FROM `recipes` WHERE `active` = 1");
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        //TODO rename get_filtered_of_recipes->get_filtered_list_of_recipes
        case "get_filtered_list_of_recipes":
            /**
             * @param $json_decoded- >filtermode
             * @param $json_decoded- >id_list
             * @param $json_decoded- >count
             */
            switch ($json_decoded->filtermode)
            {
                case "none":
                    $stmt = $dbcon->prepare("SELECT `id`, `name` FROM `recipes` ORDER BY `name`");
                    break;
                case "ingredients":
                    $list = "";
                    for ($i = 0; $i < Count($json_decoded->id_list); $i++)
                    {
                        if ($i == 0)
                            $list .= ":$i";
                        else
                            $list .= ", :$i";
                    }
                    $stmt = $dbcon->prepare("SELECT RES.id, RES.name FROM (SELECT r.id AS id, r.name AS name, COUNT(r.id) AS count FROM `recipes` as r, recipes_ingredients as ri WHERE r.id = ri.recipes_id AND r.id IN($list) GROUP BY r.id ORDER BY `name`) AS RES WHERE RES.count = :count;");
                    foreach ($json_decoded->id_list as $key => $id)
                    {
                        $stmt->bindParam(":" . $key, $id);
                    }
                    $stmt->bindParam(":count", $json_decoded->count);
                    break;
                case "recipes":
                    $list = "";
                    for ($i = 0; $i < Count($json_decoded->id_list); $i++)
                    {
                        if ($i == 0)
                            $list .= ":$i";
                        else
                            $list .= ", :$i";
                    }
                    $stmt = $dbcon->prepare("SELECT id, name FROM `recipes` WHERE id IN($list) ORDER BY name");
                    foreach ($json_decoded->id_list as $key => $id)
                    {
                        $stmt->bindParam(":" . $key, $id);
                    }
                    break;
            }
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        //--------------------------------------------------------------------------
        //--------- RECIPE INGREDIENTS ---------------------------------------------
        //--------------------------------------------------------------------------

        case "add_ingredient_to_recipe":
            $stmt = $dbcon->prepare("INSERT INTO `recipes_ingredients` (`id`, `recipes_id`, `ingredients_id`, `amount`) VALUES (NULL, :rec_id, :i_id, :i_amount)");
            $stmt->bindParam(":rec_id", $json_decoded->rec_id);
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            if (isset($json_decoded->i_amount))
                $stmt->bindParam(":i_amount", $json_decoded->i_amount);
            else
            {
                $empty = "";
                $stmt->bindParam(":i_amount", $empty);
            }
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        case "update_recipe_ingredient":
            $stmt = $dbcon->prepare("UPDATE `recipes_ingredients` SET `amount` = :ri_amount WHERE `id` = :ri_id;");
            $stmt->bindParam(":ri_amount", $json_decoded->ri_amount);
            $stmt->bindParam(":ri_id", $json_decoded->ri_id);
            $stmt->execute();
            echo "OK";
            break;

        case "remove_ingredient_from_recipe":
            $stmt = $dbcon->prepare("DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = :rec_id AND `recipes_ingredients`.`ingredients_id` = :i_id;");
            $stmt->bindParam(":rec_id", $json_decoded->rec_id);
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_recipe_ingredient":
            $stmt = $dbcon->prepare("SELECT * FROM `recipes_ingredients` WHERE `recipes_ingredients`.`id` = :ri_id;");
            $stmt->bindParam(":ri_id", $json_decoded->ri_id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        case "get_recipe_ingredients":
            $stmt = $dbcon->prepare(<<<Query
			SELECT ri.amount AS amount, i.name
			    AS name, ri.id AS ri_id, i.id AS i_id FROM `recipes`
			        AS `r` INNER JOIN `recipes_ingredients`
			            AS `ri` ON ri.recipes_id = r.id INNER JOIN `ingredients`
			                AS `i` ON i.id = ri.ingredients_id WHERE r.id = :rec_id;
			Query
            );
            $stmt->bindParam(":rec_id", $json_decoded->rec_id);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "get_count_of_ingredient_recipes":
            $stmt = $dbcon->prepare("SELECT COUNT(*) AS count FROM `recipes_ingredients` WHERE `recipes_ingredients`.`ingredients_id` = :i_id");
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        case "remove_all_ingredients_from_recipe":
            $stmt = $dbcon->prepare("DELETE FROM `recipes_ingredients` WHERE `recipes_ingredients`.`recipes_id` = :rec_id;");
            $stmt->bindParam(":rec_id", $json_decoded->rec_id);
            $stmt->execute();
            echo "OK";
            break;

        //--------------------------------------------------------------------------
        //--------- INGREDIENTS ----------------------------------------------------
        //--------------------------------------------------------------------------
        case "insert_ingredient":
            $stmt = $dbcon->prepare("INSERT INTO ingredients (`id`, `name`) VALUES (NULL, :ingr_name)");
            $stmt->bindParam(":ingr_name", $json_decoded->ingr_name);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        case "get_all_ingredients":
            $stmt = $dbcon->prepare("SELECT * FROM `ingredients` ORDER BY `id`");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        //TODO rename remove, maybe delete from all recipes
        case "remove_ingredient":
            $stmt = $dbcon->prepare("DELETE FROM `ingredients` WHERE `id` = :i_id");
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_ingredient":
            $stmt = $dbcon->prepare("SELECT * FROM `ingredients` WHERE `id` = :i_id");
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        //TODO replace with proper functionality
        case "get_ingredients_or_recipes":
            $filterMode = htmlspecialchars($json_decoded->filtermode);
            $stmt = $dbcon->prepare("SELECT `id`, `name` FROM `$filterMode` ORDER BY `name`");
            $stmt->bindParam(":i_id", $json_decoded->i_id);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        //--------------------------------------------------------------------------
        //--------- BAKINGPLAN -----------------------------------------------------
        //--------------------------------------------------------------------------
        case "insert_bakingplan":
            $stmt = $dbcon->prepare("INSERT INTO `bakingplans` (`id`, `name`, `type`) VALUES (NULL, :bp_name, '')");
            $stmt->bindParam(":bp_name", $json_decoded->bp_name);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        case "rename_bakingplan":
            $stmt = $dbcon->prepare("UPDATE `bakingplans` SET `name` = :bp_name WHERE `bakingplans`.`id` = :bp_id;");
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->bindParam(":bp_name", $json_decoded->bp_name);
            $stmt->execute();
            echo "OK";
            break;

        case "delete_bakingplan":
            $stmt = $dbcon->prepare("DELETE FROM `bakingplans` WHERE `bakingplans`.`id` = :bp_id;");
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_bakingplan":
            $stmt = $dbcon->prepare("SELECT * FROM `bakingplans` WHERE `bakingplans`.`id` = :bp_id;");
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        //TODO rename bakingplan_get_rec_id -> get_bakingplan_rec_id
        case "get_bakingplan_rec_id":
            //TODO test
            $stmt = $dbcon->prepare("SELECT `recipes_id` AS `r_id` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = :bpr_id");
            $stmt->bindParam(":bpr_id", $json_decoded->bpr_id);
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        //TODO rename bakingplan_get_order_no -> get_bakingplan_recipe_order_no
        case "get_bakingplan_recipe_order_no":
            $stmt = $dbcon->prepare("SELECT `order_no` FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = :bpr_id;");
            $stmt->bindParam(":bpr_id", $json_decoded->bpr_id);
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        //TODO bakingplan_paste_rec -> paste_bakingplan_rec
        case "paste_bakingplan_rec":
            $stmt = $dbcon->prepare("INSERT INTO `bakingplans_recipes` (`id`, `recipes_id`, `bakingplans_id`, `order_no`) VALUES (NULL, :rec_id, :bp_id, :order_no);");
            $stmt->bindParam(":rec_id", $json_decoded->rec_id);
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->bindParam(":order_no", $json_decoded->order_no);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        //TODO paste_bakingplan_rec -> remove_bakingplan_recipe
        case "remove_bakingplan_recipe":
            $stmt = $dbcon->prepare("DELETE FROM `bakingplans_recipes` WHERE `bakingplans_recipes`.`id` = :bpr_id;");
            $stmt->bindParam(":bpr_id", $json_decoded->bpr_id);
            $stmt->execute();
            echo "OK";
            break;

        //TODO bakingplan_get_all_recipes -> get_all_bakingplan_recipes
        case "get_all_bakingplan_recipes":
            $stmt = $dbcon->prepare(<<<Query
				SELECT r.id AS r_id, r.name AS r_name, r.bakingtime AS r_bakingtime, r.bakingtemperature AS r_bakingtemperature,bpr.order_no AS bpr_orderno, bpr.id AS bpr_id, bp.name AS bp_name
				FROM recipes AS r, bakingplans_recipes AS bpr, bakingplans AS bp
				WHERE r.id = bpr.recipes_id AND bp.id = bpr.bakingplans_id AND bp.type = 'active'
				ORDER BY bpr.order_no;
			Query
            );
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "get_all_bakingplans":
            $stmt = $dbcon->prepare("SELECT `id`, `name`, `type` FROM `bakingplans`");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "bakingplan_set_order_no":
            $stmt = $dbcon->prepare("UPDATE `bakingplans_recipes` SET `order_no` = :order_no WHERE `bakingplans_recipes`.`id` = :bpr_id;");
            $stmt->bindParam(":order_no", $json_decoded->order_no);
            $stmt->bindParam(":bpr_id", $json_decoded->bpr_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_active_bakingplan":
            $stmt = $dbcon->prepare("SELECT `id`, `name` FROM `bakingplans` WHERE `bakingplans`.`type` = 'active'");
            $stmt->execute();
            echo json_encode($stmt->fetch());
            break;

        //TODO rename everywhere 'bakingplan_activate'->set_active_bakingplan
        case "set_active_bakingplan":
            // alle Backpläne ausser dem aktiven Backplan zurücksetzen
            $stmt = $dbcon->prepare("UPDATE `bakingplans` SET `type` = '' WHERE NOT `bakingplans`.`id` = :bp_id");
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->execute();

            // aktiven Backplan setzen
            $stmt = $dbcon->prepare("UPDATE `bakingplans` SET `type` = 'active' WHERE `bakingplans`.`id` = :bp_id");
            $stmt->bindParam(":bp_id", $json_decoded->bp_id);
            $stmt->execute();
            echo "OK";
            break;

        //--------------------------------------------------------------------------
        //--------- PRESETS --------------------------------------------------------
        //--------------------------------------------------------------------------
        //TODO set_new_preset -> add_preset
        case "add_preset":
            $grid_object_v = json_encode($json_decoded->grid_object_v);
            $grid_object_h = json_encode($json_decoded->grid_object_h);
            $stmt = $dbcon->prepare("INSERT INTO presets (`id`, `name`, `grid_object_v`, `grid_object_h`) VALUES (NULL, :preset_name, :grid_object_v, :grid_object_h)");
            $stmt->bindParam(":preset_name", $json_decoded->preset_name);
            $stmt->bindParam(":grid_object_v", $grid_object_v);
            $stmt->bindParam(":grid_object_h", $grid_object_h);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        case "get_preset_ids":
            $stmt = $dbcon->prepare("SELECT id FROM `presets` order by `id`");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "get_all_presets":
            $stmt = $dbcon->prepare("SELECT id, name, grid_object_v, grid_object_h FROM `presets` order by `id`");
            $stmt->execute();
            $resp = $stmt->fetchAll();
            $response = array();
            foreach ($resp as $key => $w)
            {//return grid object v/h as object instead of string
                $response[$key]["id"] = $w["id"];
                $response[$key]["name"] = $w["name"];
                $response[$key]["grid_object_v"] = json_decode($w["grid_object_v"]);
                $response[$key]["grid_object_h"] = json_decode($w["grid_object_h"]);
            }
            echo json_encode($response);
            break;

        case "get_preset":
            $stmt = $dbcon->prepare("SELECT `id`, `name`, `grid_object_v`, `grid_object_h` FROM presets WHERE `presets`.`id` = :preset_id;");
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->execute();
            $resp = $stmt->fetch();
            $response = array();
            $response["id"] = $resp["id"];
            $response["name"] = $resp["name"];
            $response["grid_object_v"] = json_decode($resp["grid_object_v"]);
            $response["grid_object_h"] = json_decode($resp["grid_object_h"]);
            echo json_encode($response);
            break;

        case "save_preset":
            $stmt = $dbcon->prepare("UPDATE `presets` SET name = :preset_name, grid_object_v = :grid_object_v,  grid_object_h = :grid_object_h WHERE presets.id = :preset_id;");
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->bindParam(":preset_name", $json_decoded->preset_name);
            $stmt->bindParam(":grid_object_v", $json_decoded->grid_object_v);
            $stmt->bindParam(":grid_object_h", $json_decoded->grid_object_h);
            $stmt->execute();
            echo "OK";
            break;

        //TODO delete_preset -> remove_preset
        case "remove_preset":
            $stmt = $dbcon->prepare("DELETE FROM presets WHERE id = :preset_id;");
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->execute();
            $stmt = $dbcon->prepare("DELETE FROM timers WHERE timers.preset_id = :preset_id;");
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->execute();
            echo "OK";
            break;


        //--------------------------------------------------------------------------
        //--------- DEVICES --------------------------------------------------------
        //--------------------------------------------------------------------------
        //TODO rename temp_act uniformly

        //TODO insert_device -> add_device
        case "add_device":
            $stmt = $dbcon->prepare("INSERT INTO devices (`id`, `name`) VALUES (NULL, :device_name)");
            $stmt->bindParam(":device_name", $json_decoded->device_name);
            $stmt->execute();
            echo $dbcon->lastInsertId();
            break;

        //TODO delete_device -> remove_device
        case "remove_device":
            $stmt = $dbcon->prepare("DELETE FROM devices WHERE devices.id = :device_id;");
            $stmt->bindParam(":device_id", $json_decoded->device_id);
            $stmt->execute();
            echo "OK";
            break;

        case "set_act_temps":
            $timecode = time();
            $timestring = date("d.m.Y-H:i:s", $timecode);
            /*@param
            {
                "request_name":"set_act_temps",
                "act_temps":{
                    "device_name":"temp",
                    "wfo_top":"200",
                    "grill_left":"180"
                }
            }*/
            $device_name = "";
            foreach ($json_decoded->act_temps as $key => $value)
            {
                if ($key == "device_name")
                    $device_name = $value;
                else
                {
                    $stmt = $dbcon->prepare("UPDATE devices SET devices.temp_act = :act_temp, devices.timecode = :timecode, devices.timestring = :timestring WHERE devices.name = :device;");
                    $stmt->bindParam(":act_temp", $value);
                    $stmt->bindParam(":timecode", $timecode);
                    $stmt->bindParam(":timestring", $timestring);
                    $stmt->bindParam(":device", $key);
                    $stmt->execute();
                }
            }
            echo "OK";
            break;

        case "set_act_temp":
            //@param (device_name OR device_id) AND temp_act
            $timecode = time();
            $timestring = date("d.m.Y-H:i:s", $timecode);
            if (isset($json_decoded->device_id))
            {
				$stmt = $dbcon->prepare("UPDATE devices SET devices.temp_act = :temp_act, devices.timecode = :timecode, devices.timestring = :timestring WHERE devices.id = :device_id;");
				$stmt->bindParam(":device_id",$json_decoded->device_id);
            } else
            {
				$stmt = $dbcon->prepare("UPDATE devices SET devices.temp_act = :temp_act, devices.timecode = :timecode, devices.timestring = :timestring WHERE devices.name = :device_name;");
				$stmt->bindParam(":device_name",$json_decoded->device_name);
            }
            $stmt->bindParam(":temp_act",$json_decoded->temp_act);
            $stmt->bindParam(":timecode",$timecode);
            $stmt->bindParam(":timestring",$timestring);
			$stmt->execute();
			echo "OK";
            break;

        case "get_device_values":
            //@param (device_id OR device_name) AND timout(optional, default timeout = 50)
            if (isset($json_decoded->device_id))
            {
                $stmt = $dbcon->prepare("SELECT `id`, `name`, `temp_act`, `temp_min`, `temp_max`, `timecode` FROM `devices` WHERE EXISTS (SELECT * FROM `devices` WHERE devices.id = :device_id) AND devices.id = :device_id;");
                $stmt->bindParam(":device_id", $json_decoded->device_id);
            } else
            {
                $stmt = $dbcon->prepare("SELECT `id`, `name`, `temp_act`, `temp_min`, `temp_max`, `timecode` FROM `devices` WHERE EXISTS (SELECT * FROM `devices` WHERE devices.name = :device_name) AND devices.`name` = :device_name;");
                $stmt->bindParam(":device_name", $json_decoded->device_name);
            }
            $stmt->execute();

            $timeout = $json_decoded->timeout ?? 50;

            $response = $stmt->fetch();
            if ((int)$response["timecode"] + (int)$timeout < time())
                $response["temp_act"] = "--";
            echo json_encode($response);
            break;

        case "get_all_devices":
            $stmt = $dbcon->prepare("SELECT `id`, `name` FROM `devices`;");
            $stmt->execute();
            echo json_encode($stmt->fetchAll());
            break;

        case "set_minmaxvalues":
            //@param temp_min, temp_max AND (device_id OR device_name)
            if (isset($json_decoded->device_id))
            {
                $stmt = $dbcon->prepare("UPDATE devices SET devices.temp_min = :temp_min, devices.temp_max = :temp_max WHERE devices.id = :device_id;");
                $stmt->bindParam(":device_id", $json_decoded->device_id);
            } else
            {
                $stmt = $dbcon->prepare("UPDATE devices SET devices.temp_min = :temp_min, devices.temp_max = :temp_max WHERE devices.name = :device_name;");
                $stmt->bindParam(":device_name", $json_decoded->device_name);
            }
            $stmt->bindParam(":temp_min", $json_decoded->temp_min);
            $stmt->bindParam(":temp_max", $json_decoded->temp_max);
            $stmt->execute();
            echo "OK";
            break;


        //--------------------------------------------------------------------------
        //--------- TIMERS ---------------------------------------------------------
        //--------------------------------------------------------------------------

        case "set_timer":
            $stmt = $dbcon->prepare(
                    "IF EXISTS(SELECT id FROM timers WHERE timers.preset_id = :preset_id AND timers.timer_id = :timer_id) THEN
						UPDATE timers SET timers.time = :time WHERE timers.preset_id = :preset_id AND timers.timer_id = :timer_id;
					ELSE
						INSERT INTO timers (`id`, `preset_id`, `timer_id`, `time`) VALUES (NULL, :preset_id, :timer_id, :time);
					END IF"
            );
            $stmt->bindParam(":timer_id", $json_decoded->timer_id);
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->bindParam(":time", $json_decoded->time);
            $stmt->execute();
            echo "OK";
            break;

        //TODO del_timer -> remove_timer
        case "remove_timer":
            $stmt = $dbcon->prepare("UPDATE timers SET time = :timecode WHERE timers.preset_id = :preset_id AND timers.timer_id = :timer_id;");
            $time_code = time();
            $stmt->bindParam(":timecode", $time_code);
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->bindParam(":timer_id", $json_decoded->timer_id);
            $stmt->execute();
            echo "OK";
            break;

        case "get_timer":
            $stmt = $dbcon->prepare("SELECT time FROM timers WHERE timers.preset_id = :preset_id AND timers.timer_id = :timer_id;");
            $stmt->bindParam(":preset_id", $json_decoded->preset_id);
            $stmt->bindParam(":timer_id", $json_decoded->timer_id);
            $stmt->execute();
            echo $stmt->fetch()["time"];
            break;

        //--------------------------------------------------------------------------
        //--------- Notes ----------------------------------------------------------
        //--------------------------------------------------------------------------
        case "save_note":
            //TODO ask how micha wants it

            //{"request_name":"save_note","note":{"heading":["dsgdsg", "sec","third"],"text_value":"dsgdsg sec third lalal"}}
            echo json_encode($json_decoded);
            $note = $json_decoded->note;
            $heading = base64_encode(json_encode($note->heading));
            $text_value = base64_encode($note->text_value);
            $id = null;
            if (isset($note->id))
            {
                $id = mysqli_real_escape_string($dbcon, $note->id);
                $sql = "UPDATE `notes` SET `heading` = '$heading', `id` = '$id', `text` = '$text_value' WHERE `id` = $id OUTPUT INSERTED.[id] INTO @inserted_id";
                sql_request($dbcon, $sql);
                echo $id;
            } else
            {
                $sql = "INSERT INTO `notes` (`heading`,`id`,`text`) VALUES ('$heading', '$id', '$text_value');";
                sql_request($dbcon, $sql);

            }
            break;

        case "send_alarm":
            //@param message - Message Content / Alarm name
            //sendig POST request to pushover API
            $message_cred = json_decode(file_get_contents("cred.json"))->message_cred;
            curl_setopt_array($ch = curl_init(), array(
                CURLOPT_URL => "https://api.pushover.net/1/messages.json",
                CURLOPT_POSTFIELDS => array(
                    "token" => $message_cred->api_key,
                    "user" => $message_cred->user,
                    "device" => "odk",
                    "message" => mysqli_real_escape_string($dbcon, $json_decoded->message),
                    "priority" => "1",
                    "expire" => "60",
                    "retry" => "30",
                    "sound" => "updown"
                ),
                CURLOPT_SAFE_UPLOAD => true,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $resp = curl_exec($ch);
            curl_close($ch);
            if (json_decode($resp)->status == 1)
                echo "OK";
            else
                echo $resp;
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