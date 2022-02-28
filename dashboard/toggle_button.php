<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<?php
		$name = "";
		if (isset($_GET["name"]))
			$name = $_GET["name"];
	?>
	<div id="name" hidden><?php echo $name;?></div>
	<img id="button" src="../images/btn_selected.svg" onclick="clicked()" style="width: 90%;height:90%; align-content: center;">
</body>
<script type="text/javascript">
	
	var click_state = false;
	function clicked(){
		if (!click_state)
			document.getElementById("button").src = "../images/btn_unselected.svg";
		else if (click_state)
			document.getElementById("button").src = "../images/btn_selected.svg";
		click_state ^= 1;
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function(){};
		xhttp.open("GET", location.host +"/HomeDashboard/php_handler/"+ document.getElementById("name").innerHTML +"_handler.php?value="+(click_state^1), false);
		xhttp.send();
	}

</script>
</html>