<?php

//// TODO: fix bug when firt timer ends second timer gets the wrong value (arr -> dict)

function remove_from_array($arr,$val){//$val cant be array
    return dict_to_array(array_diff($arr,array($val)));
}
function dict_to_array($dict){
    $arr = array();
    foreach($dict as $val)
      $arr[count($arr)] = $val;
    return $arr;
}

function delete_timer($json_content, $preset_id, $timer_id){
  if (isset($json_content[$preset_id][$timer_id])){
    //remove item at preset_id.timer_id
    $json_content[$preset_id] = remove_from_array($json_content[$preset_id], $json_content[$preset_id][$timer_id]);
    // >0 timers in current preset?
    $preset_timer_count = 0;
    if (isset($json_content[$preset_id]))
      $preset_timer_count = count($json_content[$preset_id]);
    //remove preset from timer list
    if ($preset_timer_count == 0)
      unset($json_content[$preset_id]);
  }
  return $json_content;
}

function delete_old_timers($json_content){
    foreach($json_content as $preset_id => $preset_timers) {
      $decrease_to_correct_previous_delete = 0;
      foreach ($preset_timers as $timer_id => $time) {
        if (time() >= $time){
          $json_content = delete_timer($json_content, $preset_id, $timer_id - $decrease_to_correct_previous_delete);
          $decrease_to_correct_previous_delete++;
        }
      }
    }
    return $json_content;
}


//only for debuging
ini_set('display_errors', 1);

$json_string = $_GET["json"];
$json_decoded = json_decode($json_string);
$filename = "../data/timers.json";


if (!file_exists($filename))//init new empty File
  fwrite(fopen($filename,"w"),"{}");

$file_content = file_get_contents($filename);
$json_content = json_decode($file_content, true);

if (isset($json_decoded->request_name)){
  switch ($json_decoded->request_name){
    case "new_timer":
        delete_old_timers($json_content);//once in a while garbage collect old timers
        $preset_id = $json_decoded->preset_id;
        $timer_time = $json_decoded->time;
        $preset_timer_count = 0;
        if (isset($json_content[$preset_id]))
          $preset_timer_count = count($json_content[$preset_id]);
        $json_content[$preset_id][$preset_timer_count] = (int)$timer_time;
        echo "OK";
      break;

    case "del_timer":
        $preset_id = $json_decoded->preset_id;
        $timer_id = $json_decoded->timer_id;
        $json_content = delete_timer($json_content,$preset_id,$timer_id);
        echo "OK";
        break;

    case "get_timer":
        $preset_id = $json_decoded->preset_id;
        $timer_id = $json_decoded->timer_id;
        if (isset($json_content[$preset_id]) && isset($json_content[$preset_id][$timer_id]))
          echo $json_content[$preset_id][$timer_id];
        else {
          echo "204";//no timer -> timer was deleted or finished
          exit;
        }
    }
}
else {
  echo "Error: no request name found";
  exit;
}

file_put_contents($filename, json_encode($json_content));

?>
