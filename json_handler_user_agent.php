<?php 
ini_set('display_errors',1);

function getEmptyPreset(){
	return array("profile_name"=>"","grid_object_v"=>"","grid_object_h"=>"");
}
function len($obj){
	$count = 0;
	foreach ($obj as &$ele)
		$count++;
	return $count;
}

//read php argv json
$json_string = $_GET["json"];
//decode json
$json_decoded = json_decode($json_string); 
$filename = "data/dashboard_profile.json";


//INIT file with empty profile
if (!file_exists($filename))
	file_put_contents($filename, json_encode(getEmptyPreset()));

//read file
$content_decoded = json_decode(file_get_contents($filename));

switch($json_decoded->event){
	case "set_new_preset":
		if ($json_decoded->profile_id == "new"){
			$profile_id = len($content_decoded);
			array_push($content_decoded, getEmptyPreset());//append new empty preset
		}
		else {
			$profile_id = $json_decoded->profile_id;
		}
		$content_decoded[$profile_id]["profile_name"] = $json_decoded->profile_name;
		$content_decoded[$profile_id]["grid_object_v"] = $json_decoded->grid_object_v;
		$content_decoded[$profile_id]["grid_object_h"] = $json_decoded->grid_object_h;

		break;
	case "get_preset":
		$profile_id = $json_decoded->profile_id;
		$response_decoded = array("profile_name"=>$content_decoded[$profile_id]->profile_name, 
								  "profile_id"=>$profile_id,
								  "grid_object_v"=> $content_decoded[$profile_id]->grid_object_v,
								  "grid_object_h"=> $content_decoded[$profile_id]->grid_object_h
								);
		echo json_encode($response_decoded);
		break;
	case "delete_preset":
		$profile_id = $json_decoded->profile_id;
		array_splice($content_decoded,$profile_id,1);//remove item wit profile ID
		break;
	case "get_all_presets":
		echo json_encode($content_decoded);
		break;
}
$content_string = json_encode($content_decoded);
file_put_contents($filename, $content_string);

?>