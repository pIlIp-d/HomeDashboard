<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
 		<link rel="stylesheet" href="style/export.css">
	</head>
	<style type="text/css">

		html,body, #main {
			width: 100%;
			height: 100%;
		}
		#main{
			top: 0;
			left:0;
			position: absolute;
			width: 170px;
			font-size: 0.25rem;
		}
		.rec_bakingtime, .rec_bakingtemperature{
			font-size: 0.3rem;
		}
	</style>
<body>
	<?php
		$recipe = 0;
		if (isset($_GET["recipe"])) {
      		$recipe = $_GET["recipe"];
      		$request = $_GET["request"];
   		}
	?>
	<div id="php_message" hidden><?php echo $recipe;?></div>
	<div id="php_request" hidden><?php echo $request;?></div>


	<div class="main" id="main" onload="canvg()"></div>

	<!--LICENCE for html2canvas -MIT Licence https://github.com/niklasvh/html2canvas-->
	<script src="/HomeDashboard/libs/html2canvas/dist/html2canvas.min.js"></script>
	<!--LICENSE for jspdf -MIT Licence https://github.com/MrRio/jsPDF-->
	<script src="/HomeDashboard/libs/jspdf/dist/polyfills.umd.js"></script>
	<!--<script src="/HomeDashboard/libs/jspdf/dist/jspdf.umd.min.js"></script>
-->
	<script src="/HomeDashboard/libs/html2canvas/dist/html2canvas.min.js"></script>
	<script src="/HomeDashboard/libs/jspdf.umd.min.js"></script>
	<script src="/HomeDashboard/libs/canvg.min.js"></script>


	<script src="../javascript/base64.js"></script>
	<script src="javascript/recipes.js"></script>
	<script src="javascript/export.js"></script>

	</body>
</html>
