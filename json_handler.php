<?php

	//aktivate php debuging output 
	ini_set('display_errors', 1);

	// GET-Variable einlesen
	$json_string = $_GET["json"];
	// JSON dekodieren
	$json_decoded = json_decode($json_string);
	
//--------------------------------------------------------------------------
// JSON decryption if "data" exists -> only ESP requests 
//--------------------------------------------------------------------------
	if (isset($json_decoded->data)) {
		echo "entschlüsseln";
		//get ecrypted and b64encoded data
		$b64str = $json_decoded->data;
		//convert base64 to string
		$enc_data = base64_decode($b64str, TRUE);
		//XOR string for decryption
		$dec_data = xor_string($enc_data);
		//JSON decode
		$json_decoded = json_decode($dec_data);	
	}
	function xor_string($string) {
		$key = "";//long alphabetic pw string
		for($i = 0; $i < strlen($string); $i++) 
			$string[$i] = ($string[$i] ^ $key[$i % strlen($key)]);
		return $string;
	}
//-------------------------------------------------------------------------
//important values
//-------------------------------------------------------------------------
		
	$timestamp = time();
	$timecode = date("d.m.Y-H:i:s", $timestamp);
	$device_id = $json_decoded->device_id;
	$filename = "data/$device_id.json";

//-----------------------------------------------------------------------
//	Init JSON if no jsonfile exists
//-----------------------------------------------------------------------	
	if (file_exists($filename) != true){
		$content_string = "{";
		// allgemeine Werte
		$content_string .= "\"event\":\"\",";
		$content_string .= "\"device_id\":\"$device_id\",";
		//$content_string .= "\"sensor_id\":\"$sensor_id\",";
		$content_string .= "\"timestamp\":\"$timestamp\",";
		$content_string .= "\"timecode\":\"$timecode\",";
		$content_string .= "\"timeout\":\"\",";
		// Temperaturen Backofen
		$content_string .= "\"temp_act_top\":\"--\",";
		$content_string .= "\"temp_min_top\":\"250\",";
		$content_string .= "\"temp_max_top\":\"250\",";
		$content_string .= "\"temp_act_bottom\":\"--\",";
		$content_string .= "\"temp_min_bottom\":\"250\",";
		$content_string .= "\"temp_max_bottom\":\"250\",";
		// aktuelle Position im Backplan
		$content_string .= "\"bp_pos\":\"\",";
		// Timer Backofen
		$content_string .= "\"timer_stop_wfo\":\"\",";
		// Timer Grill
		$content_string .= "\"timer_stop_bbq\":\"\",";
		// Temperaturen Grill
		$content_string .= "\"temp_act_left\":\"--\",";
		$content_string .= "\"temp_min_left\":\"250\",";
		$content_string .= "\"temp_max_left\":\"250\",";
		$content_string .= "\"temp_act_right\":\"--\",";
		$content_string .= "\"temp_min_right\":\"250\",";
		$content_string .= "\"temp_max_right\":\"250\",";
		// Temperaturen Fleischthermometer
		$content_string .= "\"temp_act_meat\":\"--\",";
		$content_string .= "\"temp_min_meat\":\"50\",";
		$content_string .= "\"temp_max_meat\":\"50\"";
		$content_string .= "}";
		file_put_contents($filename, $content_string); 
	}	
//-----------------------------------------------------------------------
// handle JSON from File and send Response
//-----------------------------------------------------------------------
	// JSON-Datei einlesen
	$content_string = file_get_contents($filename);
	// JSON dekodieren
	$content_decoded = json_decode($content_string);

	// Event-Handler
	switch ($json_decoded->event){
		// -----------------------------------------------------------------------------------
    case "set_act_temp":
		// -----------------------------------------------------------------------------------
			$content_decoded->timestamp = $timestamp;
			$content_decoded->timecode = $timecode;
			$content_decoded->temp_act_top = $json_decoded->temp_act_top;
			$content_decoded->temp_act_bottom = $json_decoded->temp_act_bottom;
			$content_decoded->temp_act_left = $json_decoded->temp_act_left;
			$content_decoded->temp_act_right = $json_decoded->temp_act_right;
			$content_decoded->temp_act_meat = $json_decoded->temp_act_meat;
      break;
		// -----------------------------------------------------------------------------------
    case "get_allvalues":
		// -----------------------------------------------------------------------------------
			// wenn Timeout überschritten --> aktuelle Temperatur auf "--" setzen
			if (($content_decoded->timestamp + $json_decoded->timeout) < $timestamp) {
				$content_decoded->temp_act_top = "--";
				$content_decoded->temp_act_bottom = "--";
				$content_decoded->temp_act_left = "--";
				$content_decoded->temp_act_right = "--";
				$content_decoded->temp_act_meat = "--";
			}
      break;
		// -----------------------------------------------------------------------------------
    case "set_minmaxvalues":
		// -----------------------------------------------------------------------------------
			$content_decoded->temp_min_top = $json_decoded->temp_min_top;
			$content_decoded->temp_max_top = $json_decoded->temp_max_top;
		    $content_decoded->temp_min_bottom = $json_decoded->temp_min_bottom;
			$content_decoded->temp_max_bottom = $json_decoded->temp_max_bottom;
			$content_decoded->temp_min_meat = $json_decoded->temp_min_meat;
			$content_decoded->temp_max_meat = $json_decoded->temp_max_meat;
      break;
		// -----------------------------------------------------------------------------------
    case "set_timer_wfo":
		// -----------------------------------------------------------------------------------
			$content_decoded->timer_stop_wfo = $json_decoded->timer_stop_wfo;
      break;
		// -----------------------------------------------------------------------------------
    case "del_timer_wfo":
		// -----------------------------------------------------------------------------------
			$content_decoded->timer_stop_wfo = "";
      break;
		// -----------------------------------------------------------------------------------
    case "set_timer_bbq":
		// -----------------------------------------------------------------------------------
			$content_decoded->timer_stop_bbq = $json_decoded->timer_stop_bbq;
      break;
		// -----------------------------------------------------------------------------------
    case "del_timer_bbq":
		// -----------------------------------------------------------------------------------
			$content_decoded->timer_stop_bbq = "";
      break;
    case "send_message":
    		$message = "python libs/mail.py \"";
   			if ($json_decoded->message != "") {
   				$message .= $json_decoded->message;
   			}
    		else {
				$message .= "\"empty message - at json_handler\"";
			}
			$message .= "\"";
			$command = escapeshellcmd($message);
			shell_exec($command);
      break;
	}
	// JSON kodieren
	$content_string = json_encode($content_decoded);
	// JSON-Datei schreiben
	file_put_contents($filename, $content_string);
	// HTTP-Response zurückgeben
	echo $content_string;
?>