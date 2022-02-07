<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">

</head>
<style type="text/css">
	.unselectable {
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;

		font-family: Arial;
		width:100%;
		height:100%;
		display: flex;
		justify-content: center;
	}

	#bp_name {

		border-bottom: thin solid lightgray;
		font-size: 1rem;
		width: 100%;
		position: relative;
	}
		.active_bakingplan_container{
			padding: 0;
			border: thin solid lightgray;
			border-collapse: collapse;
			border-radius: 5px;
			background-color: #f0f0f0;

			position: fixed;
			display: block;
			justify-content: center;
			height: 50%;
			width: 75%;
			text-align: center;
			top: 3rem;
		}
		.text_div {

		}
		#recipe_name {
			top: 0.5rem;
			height: 50%;
			font-size: 1.5rem;
			width: 100%;
			position: relative;
		}
	/* recipe Information */
		.rec_info {
			display: flex;
			width:100%;
		}
		.rec_info_text {
			font-size: 2em;
			position: relative;
			bottom: 0.2em;
		}
		#bakingtime_container {
			width: 100%;
			left:10%;
		}
		#bakingtemperature_container {
			width: 100%;
			right:10%;
		}

		.next_bakingplan_container{
			padding: 0;
			border: thin solid lightgray;
			border-collapse: collapse;
			border-radius: 5px;
			background-color: #f0f0f0;
			position: fixed;
			display: flex;
			height: 17%;
			width: 75%;
			bottom: 0.3rem;
			right:5%;
			font-size: 1rem;
		}

	/* next/prev Buttons */
		.svg {
			padding: 5px;
			width: 25px;
			height: 25px;
		}
		#btn_next{
			width: 30%;
			position: relative;
			top:0.3rem;
			left: 0.5rem;
		}
		#next_recipe{
			position: relative;
			top:0.3rem;
			width: 70%;
		}

</style>
<script src="../libs/jquery-3.6.0.min.js"></script>
<script src="../javascript/base64.js"></script>

<body>
	<!--
		einlesen der übergebenen Rezept-ID per PHP in unsichtbares DIV-Element
		- kann später per JS ausgelesen werden
		- Rezept-ID = 0 bedeutet "neues Rezept"
	-->
	<?php
		$preset_id = 0;
		if (isset($_GET["preset_id"])) {
			$data = $_GET["preset_id"];
		}

	?>
	<div id="preset_id" hidden><?php echo $preset_id; ?></div>
	<main id="main" class="unselectable">
	<!--	<div class="btn" id="btn_prev">❮</div>-->

		<div class="text_div" id="bp_name"></div>
		<div class="active_bakingplan_container">
			<div class="text_div" id="recipe_name"></div>
			<div class="rec_info">
				<div id="bakingtime_container">
					<span class="rec_info_text" id="bakingtime"></span>
					<img class="svg" id="sym_clock" src="../images/sym_clock.svg" alt="sym_clock" style="margin-right: 5px;">
				</div>
				<div id="bakingtemperature_container">
					<span class="rec_info_text" id="bakingtemperature"></span>
					<img class="svg" id="sym_thermo" src="../images/sym_thermo.svg" alt="sym_thermo" style="margin-right: 5px; width:15px;">
				</div>
			</div>
		</div>
		<div class="next_bakingplan_container" onclick="next_recipe();">
			<div class="btn" id="btn_next">Next ❯</div>
			<div class="text" id="next_recipe"></div>
		</div>
	</main>
</body>
<script type="text/javascript">

const server = location.host;
var server_root_url = "/HomeDashboard/";
var db_url = server_root_url+"odk_db.php";

var active_bp;
var active_recipe;
var bakingplan_recipes;
var active_recipe_list_id;

document.addEventListener("DOMContentLoaded", init);
function init(){
	xhttp_send("get_active_bakingplan");
	xhttp_send("get_active_recipe");
	xhttp_send("bakingplan_get_all_recipes");
	console.log(active_bp);
	console.log(active_recipe);
	console.log(bakingplan_recipes);

	load_active_recipe_html();
}

function next_recipe(){
	let next_recipe_id = (active_recipe_list_id++ + 1 ) % bakingplan_recipes.length;
	xhttp_send("set_active_recipe",bakingplan_recipes[next_recipe_id].r_id);
	xhttp_send("get_active_recipe");
	load_active_recipe_html();
}

function load_active_recipe_html(){
	document.getElementById("bp_name").innerHTML = "Backplan \""+active_bp.name+"\"";
	document.getElementById("recipe_name").innerHTML = active_recipe.name;
	document.getElementById("bakingtime").innerHTML = active_recipe.bakingtime;
	document.getElementById("bakingtemperature").innerHTML = active_recipe.bakingtemperature;
	let next_recipe_id = (active_recipe_list_id + 1 ) % bakingplan_recipes.length;
	document.getElementById("next_recipe").innerHTML = bakingplan_recipes[next_recipe_id].r_name;

}

function xhttp_send(request, value = null){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if (this.readyState == 4 && this.status == 200){
			let response = this.responseText;
			if (response.substr(0, 8) == "DB_ERROR")
				alert("DB_ERROR");
			else
				handle_response(request, response);
		}
	}
	xhttp.open("GET", db_url + "?json={"+ createRequest(request, value) +"}", false);
	xhttp.send();
}

function createRequest(request, value){
	var json_string = "\"request_name\":\""+ request +"\"";
	switch(request){
		case "get_active_bakinplan":
			//do nothing else
			break;
		case "set_active_recipe":
			json_string += ",\"recipe_id\":\""+ value + "\"";

	}
	return json_string;
}

function restart_bakingplan(){
	console.log("reset");
	active_recipe_list_id = 0;
	xhttp_send("set_active_recipe",bakingplan_recipes[0].r_id);
	xhttp_send("get_active_recipe");
}

function handle_response(request, response){
	if (response != "OK")
		var json_response = JSON.parse(response);
	switch(request){
		case "get_active_bakingplan":
			active_bp = json_response[0];
			break;
		case "get_active_recipe":
			active_recipe = json_response[0];
			break;
		case "bakingplan_get_all_recipes":
			bakingplan_recipes = json_response;
			let contains = false;
			for (let i = 0; i < bakingplan_recipes.length; i++){
				if (bakingplan_recipes[i].r_id == active_recipe.id){
					active_recipe_list_id = i;
					contains = true;
					break;
				}
			}
			if (!contains)//active recipe is not in active bakingplan
				restart_bakingplan();
			break;
		case "set_active_recipe":
			break;
	}
}



</script>

</html>
