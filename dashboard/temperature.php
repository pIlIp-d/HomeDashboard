<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="style/temperature.css">
	<script src="javascript/temperature_view.js"></script>
</head>

<body>
	<!--
	einlesen des übergebenen Sensors per PHP(adresse) in unsichtbares DIV-Element
	- kann später per JS ausgelesen werden
	-->
	<?php
		$sensor = 0;
		$display_name = "...";
		$show = 0;
		$id = 0;
		$unit = "°C";

		if (isset($_GET["json"])) {
	        $json = json_decode($_GET["json"]);
			if (isset($json->name)) {
    			$sensor = $json->name;
    		}
    		if (isset($json->display_name)) {
    			$display_name = $json->display_name;
    		}
    		if (isset($json->show)) {
    			$show = $json->show;
    		}
    		if (isset($json->id)) {
    			$id = $json->id;
    		}
    		if (isset($json->unit)) {
    			$unit = $json->unit;
    		}
	    }
	?>
	<div id="sensor_id" hidden><?php echo $sensor; ?></div>
	<div id="display_name" hidden><?php echo $display_name; ?></div>
	<div id="show" hidden><?php echo $show; ?></div>
	<div id="unit" hidden><?php echo $unit; ?></div>

	<div id="container">
		<div id="header_text">
			...
		</div>

		<select class="text_div select" id="alarm_select">
			<option value='null'>- select alarm -</option>
		</select>
		<div id="header_symbol">
			<img id="alarm_mode" src="/HomeDashboard/images/bell-slash.svg" alt="bell-slash" onclick='toggle_alarm_mode()'>
			<img id="bp_mode" src="/HomeDashboard/images/btn_list_regular.svg" onclick="toggle_bp_mode()" >

		</div>
		<div id="tact" onclick="change_view(1); window.parent.postMessage('<?php echo $id; ?> set_show', 'http://'+location.host+'/HomeDashboard/dashboard.php');">
				<img class="thermo" id="tact_symbol" src="/HomeDashboard/images/sym_thermo_black.svg" width="8%" height="8%" alt="thermometer">
				<span class="temp" id="tact_value">230</span><sup class="unit" id="tact_unit"><?php echo $unit; ?></sup>
		</div>

		<div id="tmin">
			<select class="select" id="tmin_select"></select><sup class="unit" id="tmin_unit"><?php echo $unit; ?></sup>
		</div>
		<div id="tmax">
			<select class="select" id="tmax_select"></select><sup class="unit" id="tmax_unit"><?php echo $unit; ?></sup>
		</div>
		<div id="tmin_label">
			min
		</div>
		<div id="tmax_label">
			max
		</div>
	</div>
	<canvas id="canvas" style="z-index:-5;width:100%;height:100%"></canvas>

</body>
<script src="javascript/temperature.js"></script>
<script type="text/javascript">

</script>
</html>
