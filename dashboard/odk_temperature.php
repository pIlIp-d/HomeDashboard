<!DOCTYPE HTML><html>
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
	<!-- 
	einlesen des übergebenen Sensors per PHP(adresse) in unsichtbares DIV-Element
	- kann später per JS ausgelesen werden
	-->
	<?php
		$sensor = 0;
		if (isset($_GET["sensor"])) {
			$sensor = $_GET["sensor"];
		}
	?>
	<div id="sensor_id" hidden><?php echo $sensor; ?></div>
	<div id="container"> 
		<div id="header_text">
			...
		</div>
		<div id="header_symbol">
			<img id="bell_1" src="/HomeDashboard/images/bell-slash.svg" alt="bell-slash">
		</div>
		<div id="tact">
				<img class="thermo" id="tact_symbol" src="/HomeDashboard/images/sym_thermo_black.svg" width="8%" height="8%" alt="thermometer">
				<span class="temp" id="tact_value">230</span><sup class="unit" id="tact_unit">&deg;C</sup>				
		</div>
		
		<div id="tmin">
			<select class="select" id="tmin_select"></select><sup class="unit" id="tmin_unit"> &deg;C</sup>
		</div>
		<div id="tmax">
			<select class="select" id="tmax_select"></select><sup class="unit" id="tmax_unit"> &deg;C</sup>
		</div>
		<div id="tmin_label">
			min
		</div>
		<div id="tmax_label">
			max
		</div>
	</div>
</body>
<script src="javascript/temperature.js"></script>
</html>