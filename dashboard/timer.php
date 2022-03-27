<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<script src="javascript/timer.js"></script>
	<link rel="stylesheet" type="text/css" href="style/odk_timer_wfo.css">
</head>
<body>
	<?php
	$preset_id = 0;
	$timer_id = 0;
	$bakingplan_effected = 0;
	if (isset($_GET["preset_id"]))
		$preset_id = $_GET["preset_id"];
	if (isset($_GET["timer_id"]))
		$timer_id = $_GET["timer_id"];
	if (isset($_GET["bakingplan_effected"]))
		$bakingplan_effected = $_GET["bakingplan_effected"];
	?>
	<div id="preset_id" hidden><?php echo $preset_id ?></div>
	<div id="timer_id" hidden><?php echo $timer_id ?></div>
	<div id="bakingplan_effected" hidden><?php echo $bakingplan_effected ?></div>
	<div class="container unselectable">
		<area_header id="header_text">
			<span>Timer</span>
		</area_header>
		<area_button>
			<img src="/HomeDashboard/images/btn_start.svg" class="button" id="start" onclick="btn_start()">
			<img src="/HomeDashboard/images/btn_selected.svg" class="button" id="stop" onclick="btn_stop()">
		</area_button>
		<area_timer id="time">
			<select class="time_select" id="timer_hour"><option>00</option></select>
			<span class="dots">:</span>
			<select class="time_select" id="timer_minute"><option>00</option></select>
			<span class="dots">:</span>
			<select class="time_select" id="timer_second"><option>00</option></select>
		</area_timer>
		<img id="alarm_mode" src="/HomeDashboard/images/bell-slash.svg" alt="bell-slash" onclick='toggle_alarm_mode()'>
		<img id="bp_mode" src="/HomeDashboard/images/btn_list_regular.svg" onclick="toggle_bp_mode()" >
	</div>
</body>
<script>
	var TIMER_HOUR = document.getElementById("timer_hour");
	var TIMER_MINUTE = document.getElementById("timer_minute");
	var  TIMER_SECOND = document.getElementById("timer_second");
	const TIMER_START = document.getElementById("start");
	const TIMER_STOP = document.getElementById("stop");

	var ACT_HOUR;
	var ACT_MINUTE;
	var ACT_SECOND;
	var START_HOUR = "00";
	var START_MINUTE = "00";
	var START_SECOND = "00";
	var timer_exists = false;

	const INTERVALL_MAIN_TICKER = 1000;
	var INTERVALL_MAIN;

document.addEventListener("DOMContentLoaded", function(){
	PRESET_ID = document.getElementById("preset_id").innerHTML;
	TIMER_ID = document.getElementById("timer_id").innerHTML;

	set_options();
	check_clock();
	INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
});

function interval_main_tick(){
	if (timer_exists || bp_mode)
		check_clock();
}
//more js in javascript/timer.js
</script>
</html>
