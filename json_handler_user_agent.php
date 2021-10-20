<?php 
ini_set('display_errors',1);
//read php argv json
$json_string = $_GET["json"];
//decode json
$json_decoded = json_decode($json_string); 
$filename = "data/dashboard_profile.json";

	//INIT file with 9 empty profiles
	if (file_exists($filename) != true){
		$content_string = "{";
		for ($i=1;$i<10;$i++){
			$content_string .= "\"";
			$content_string .= $i;
			$content_string .= "\":{";	
			//grid_list -- list of element sizes
			$content_string .= "\"profile_name\":\"\",";
			$content_string .= "\"grid_object_v\":\"\",";
			$content_string .= "\"grid_pbject_h\":\"\"}";
			if ($i<9){
				$content_string .= ",";
			}
		}
		$content_string .= "}";
		echo $content_string;
		file_put_contents($filename, $content_string);
	}

//read file
$content_decoded = json_decode(file_get_contents($filename));

switch($json_decoded->event){
	case "set_new_preset":
		$num = "1";
		$profile_id = $json_decoded->profile_id;
		$content_decoded->$profile_id->profile_name = $json_decoded->profile_name;
		$content_decoded->$profile_id->grid_object_v = $json_decoded->grid_object_v;
		$content_decoded->$profile_id->grid_object_h = $json_decoded->grid_object_h;
		break;
	case "get_preset":
		$profile_id = $json_decoded->profile_id;
		$response_decoded = array("profile_name"=>$content_decoded->$profile_id->profile_name, "profile_id"=>$profile_id, "grid_object_v"=> $content_decoded->$profile_id->grid_object_v, "grid_object_h"=> $content_decoded->$profile_id->grid_object_h);
		$response = json_encode($response_decoded);
		echo $response;
		break;
	case "delete_preset":
		$profile_id = $json_decoded->profile_id;
		$content_decoded->$profile_id->profile_name = "";
		$content_decoded->$profile_id->grid_object_v = "";
		$content_decoded->$profile_id->grid_object_h = "";
		break;
	case "get_all_presets":
		$response_decoded = array("1"=>"", "2"=>"", "3"=>"", "4"=>"", "5"=>"", "6"=>"", "7"=>"", "8"=>"", "9"=>"");
		for ($i=1;$i<10;$i++){
			$response_decoded["$i"] = $content_decoded->$i->profile_name;	
		}
		$response = json_encode($response_decoded);
		echo $response;
		break;
}
$content_string = json_encode($content_decoded);
file_put_contents($filename, $content_string);

?>