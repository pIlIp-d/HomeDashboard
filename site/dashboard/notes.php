<?php
    $preset_id = 0;
	$timer_id = 0;
	if (isset($_GET["preset_id"]))
        $preset_id = $_GET["preset_id"];
	if (isset($_GET["timer_id"]))
        $timer_id = $_GET["timer_id"];
	if (isset($_GET["bakingplan_effected"]))
        $bakingplan_effected = $_GET["bakingplan_effected"];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <script src="javascript/notes.js"></script>
    <link rel="stylesheet" type="text/css" href="style/notes.css">
    <script src="../javascript/config.js"></script>
</head>
<body>
    <div id="head">
        <select class="select" id="note_select">
            <option value='null'>- select note -</option>
        </select>
        <img id="bp_mode" src="/images/btn_list_regular.svg" onclick="toggle_bp_mode()" >
    </div>
    <div class="text" id="bp_text"></div>
    <textarea type="text" placeholder="Write Notes..."  id="text_area"  class="text" aria-label="Notes input."></textarea>

    <script>
        var content = "";
        const INTERVALL_MAIN_TICKER = 1000;
        var INTERVALL_MAIN;
        let bp_mode = false;
        let changed = false;
        let act_recipe;
        let text_value_id = null;

        TEXT_AREA = document.getElementById("text_area");
        BP_TEXT = document.getElementById("bp_text");

        NOTE_SELECT = document.getElementById("note_select");
        INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);

        document.addEventListener("DOMContentLoaded", function(){
            /*TODO
                NOTE_SELECT.options[1] = new Option("Min Temp reached", "active_recipe");
            */
            //ONCHANGE EVENT for TEXTAREA
            if (TEXT_AREA.addEventListener) {
                TEXT_AREA.addEventListener('input', function() {
                    changed = true;
                }, false);
            } else if (TEXT_AREA.attachEvent) {
                TEXT_AREA.attachEvent('onpropertychange', function() {
                    // IE-specific event handling code
                changed = true;
                });
            }
        });

        function interval_main_tick(){
            if (bp_mode)
                set_active_recipe_text();
            if (changed){
                changed = false;
                if (bp_mode)
                    set_active_recipe_text();
                else
                    save_input();
            }
        }

    </script>
</body>