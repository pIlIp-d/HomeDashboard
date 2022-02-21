<?php

//only for debuging
ini_set('display_errors', 1);

$json_string = $_GET["json"];
$json_decoded = json_decode($json_string);
$filename = "../data/timers.json";

if (!file_exists($filename))//init new empty File
  fwrite(fopen($filename,"w"),"[]");

$file_content = file_get_contents($filename);
$json_content = json_decode($file_content, true);

if (isset($json_decoded->request_name)){
    if (isset($json_decoded->preset_id))
        $preset_id = $json_decoded->preset_id;
    if (isset($json_decoded->time))
        $timer_time = $json_decoded->time;
    if (isset($json_decoded->timer_id))
        $timer_id = $json_decoded->timer_id;

    switch ($json_decoded->request_name){
        case "set_timer":
            $json_content[$preset_id][$timer_id] = (int)$timer_time;
            echo "OK";
          break;
        case "del_timer":
            $json_content[$preset_id][$timer_id] = (int)time();//set to current time == delete
            echo "OK";
            break;
        case "get_timer":
            if (isset($json_content[$preset_id]) && isset($json_content[$preset_id][$timer_id]))
              echo $json_content[$preset_id][$timer_id];
            else {
            http_response_code(204);
//              echo "HTTP:204";//no timer -> timer hasnt been declared, yet
              exit;
            }
          break;

        case "get_bp_timer"://sets new bp_timer too
            //if bp_timer runs -> return current value, else set bp timer to the requested end time
            $sended_bp_time = $json_decoded->bp_time;//@param sended_bp_time is bakingtime from active bakingplan
            $bp_file = "../data/bp_timer.data";
            if (!file_exists($bp_file))//init new empty File
                fwrite(fopen($bp_file,"w"),"0");
            $bp_time = (int)file_get_contents($bp_file);
            if ($bp_time <= time())
                $bp_time = "$sended_bp_time";
            file_put_contents($bp_file,$bp_time);
            echo $bp_time;
            exit();
          break;
    }
}
else {
    echo "Error: no request name found";
    exit;
}

file_put_contents($filename, json_encode($json_content));

?>
