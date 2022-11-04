<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
    <script src="../javascript/config.js"></script>
</head>
<style>
	*{
		margin:0;
		padding:0;
	}
	.unselectable {
		-webkit-touch-callout: none;
		-webkit-user-select: none;
		-khtml-user-select: none;
		-moz-user-select: none;
		-ms-user-select: none;
		user-select: none;
        font-family: Arial, serif;
		width:100%;
		height:100%;
		display: block;
		justify-content: center;
	}

	.select {
		position: fixed;
		-moz-appearance: none;
		-webkit-appearance: none;
		appearance: none;
		border: none;
		color: #000000;
		cursor: pointer;
		text-align: center;
        font-family: Arial, serif;
		font-size: 1.0rem;
		height: 1.5rem;
		width: 100%;
		outline: none;
	}
	#bp_name {
		z-index: 10;
		border-bottom: thin solid lightgray;
		font-size: 1rem;
		width: 100%;
		text-align: center;
		position: fixed;
		margin-bottom: 0.5rem;
	}
	#bakingplan_table{
		position:absolute;
		width:95%;
		top: 2rem;
		font-size: 0.7rem;
		display: block;
		text-align: center;
		justify-content: center;
	}
	.recipe{
		margin-left:5%;
		cursor: pointer;
		margin-top: 0.25rem;
		margin-bottom: 0.25rem;
		border: thin solid lightgray;
		border-collapse: collapse;
		border-radius: 5px;
		background-color: #f3f3f3;
		position: relative;
		justify-content: center;
		display:block;
		height: 3rem;
		width: 90%;
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
	.active-recipe{
		background-color: #dadada;
	}
	#recipe_name {
		width: 100%;
		height: 50%;
		font-size: 1.25rem;
		position: relative;
	}
	/* recipe Information */
	.rec_info {
		display: flex;
		width:100%;
		justify-content: center;
	}
	.rec_info_text {
		position: relative;
		bottom: 0.5rem;
	}
	/* next/prev Buttons */
	.svg {
		padding: 5px;
		width: 15px;
		height: 15px;
	}

    #bp_name_label{
        position: fixed;
        color: black;
        top 10px;
        left 10px;
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
	<div id="main" class="unselectable">
        <label for="bp_name" id="bp_name_label"></label><select class="text_div select" id="bp_name"></select>
		<div id='bakingplan_table'></div>
	</div>
</body>
<script type="text/javascript">

var bakingplans = [];

var active_bp;
var active_recipe;
var bakingplan_recipes;

const BP_SELECT = document.getElementById("bp_name");

document.addEventListener("DOMContentLoaded", init);
function init(){
	xhttp_send("get_all_bakingplans");
	xhttp_send("get_active_bakingplan");
	xhttp_send("get_active_recipe");
	xhttp_send("get_all_bakingplan_recipes");
	create_html();
}
function create_html(){
	//bakingplan html
	BP_SELECT.options = 0;
	for (let i = 0; i < bakingplans.length; i++)
		BP_SELECT.options[i] = new Option(bakingplans[i].name, bakingplans[i].id);
	BP_SELECT.value = active_bp.id;
	//bakingplan recipes html
	var html = "";
    console.log(bakingplan_recipes);
    let firstactive = false;
	for (let i = 0; i < bakingplan_recipes.length; i++) {
        let active = !firstactive && active_recipe.id === bakingplan_recipes[i].r_id;//only let the first be active
        if (active) firstactive = true;

        html += get_recipe_html(i, bakingplan_recipes[i].r_name, bakingplan_recipes[i].r_bakingtemperature, bakingplan_recipes[i].r_bakingtime, (active));
    }
	document.getElementById("bakingplan_table").innerHTML = html;
}

function xhttp_send(request, value = null){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function(){
		if (this.readyState === 4 && this.status === 200){
			let response = this.responseText;
            handle_response(request, response);
		}
	}
	xhttp.open("GET", DB_URL + "?json={"+ createRequest(request, value) +"}", false);
	xhttp.send();
}

function createRequest(request, value){
	console.log(request+" "+value);
	var json_string = "\"request_name\":\""+ request +"\"";
	switch(request){
		case "get_all_bakingplans":
			break;
		case "get_active_bakinplan":
			//do nothing else
			break;
		case "bakingplan_activate":
			json_string += ",\"bp_id\":\"" + value + "\"";
			break;
		case "set_active_recipe":
			json_string += ",\"recipe_id\":\""+ value + "\"";
			break;
	}
	return json_string;
}

function activate_bp_recipe(id){
	const recs = document.getElementsByClassName("recipe");
	for (let i = 0; i < recs.length; i++)
		recs[i].classList.remove("active-recipe");
	document.getElementById(""+id).classList.add("active-recipe");
	xhttp_send("set_active_recipe",bakingplan_recipes[id].r_id);
	xhttp_send("get_active_recipe");
}

function handle_response(request, response){
	try {
        var json_response = JSON.parse(response);
    }
    catch{/*IGNORE*/}
	switch(request){
		case "get_active_bakingplan":
			active_bp = json_response[0];
			break;
		case "get_active_recipe":
			active_recipe = json_response[0];
			console.log(active_recipe);
			break;
		case "get_all_bakingplan_recipes":
            console.log("_________________");
            console.log(json_response);
			bakingplan_recipes = json_response;
			break;
		case "set_active_recipe":
			break;
		case "get_all_bakingplans":
			bakingplans = json_response;
			break;
	}
}
function get_recipe_html(id, name, temp, time, active = false){
	var act = (active)?" active-recipe":"";//add class active_recipe if active==true
	html = "\
		<div class='recipe"+act+"' id='"+id+"' onclick='activate_bp_recipe("+id+")'>\
			<div class='text_div name' id='recipe_name'>"+ name +"</div>\
			<div class='rec_info'>\
				<div class='bakingtime_container'>\
					<span class='rec_info_text' id='bakingtime'>"+ time +"</span>\
					<img class='svg' id='sym_clock' src='../images/sym_clock.svg' alt='sym_clock' style='margin-right: 5px;'>\
				</div>\
				<div class='bakingtemperature_container'>\
					<span class='rec_info_text' id='bakingtemperature'>"+ temp +"</span>\
					<img class='svg' id='sym_thermo' src='../images/sym_thermo.svg' alt='sym_thermo' style='margin-right: 5px; width:15px;'>\
				</div>\
			</div>\
		</div>\
	";
	return html;
}

//listener for bp_select change
document.querySelector('#bp_name').addEventListener("change", function() {
    document.getElementById("bp_name_label").innerHTML = BP_SELECT.value;
    xhttp_send("set_active_bakingplan",BP_SELECT.value);
	init();
});

//TODO move bug
// 4x2 over 2, 1, 1

</script>

</html>
