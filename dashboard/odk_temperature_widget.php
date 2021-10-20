<!DOCTYPE HTML><html>
<head>
	<link rel="stylesheet" href="style/dashboard.css">

</head>

<body>
<style type="text/css">
	.button {
		background-color: #bbbbbb;
		border: none;
 		color: black;
 		cursor: pointer;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 1rem;
		position: relative;
		padding: 3px;
		top: 1.5rem;
		left: 0.5rem;
		margin-bottom: 0.5rem;
		}

	.button:hover {
		background-color: #dddddd;
	}

/*-----------------------------------
---------- Select obj ---------------
-------------------------------------*/
	#multiselect {
	  width: 8rem;
	  font-size: 0.8rem;

	}
	.selectBox {
	  position: absolute;
	  top: 0;
	  left: 0;
	}

	.selectBox select {
	  
	  width: 100%;
	  font-weight: bold;
	}

	.overSelect {/* hide original select obj */
	  position: absolute;
	  left: 0;
	  right: 0;
	  top: 0;
	  bottom: 0;
	}

	#checkboxes {
	  background-color: #ffffff;
	  position: absolute;
	  top: 1.15rem;
		width: 7rem;
	  font-size: 0.8rem;

	  display: none;
	  border: 0.8px #dadada solid;
	}

	#checkboxes label {
	  display: block;
	}

	#checkboxes label:hover {
	  background-color: #eeeeee;
	}
	.label_text{
		position: relative;
		top:  -0.12rem;
	}
/*-----------------------------------
-------- GRID obj -------------------
-------------------------------------*/
	
	.c0{
		top: calc(25% - 0.5rem);
		left: 1rem;
	}
	.c1{
		top: calc(25% - 0.5rem);
		left:  47%;
	}
	.c2{
		top: calc(75% - 0.5rem);
		left: 1rem;
	}
	.c3{
		top: calc(75% - 0.5rem);
		left: 47%;
	}
	.c4{
		top: calc(50% - 0.5rem);
		left: 30%;
	}
	
	.tact{
		position: absolute;
		font-size: 0.9rem;
		text-align: center;
	}
	.grid{
		
		top: 0;
		left:  0;
		width: 100%;
		height: calc(75% + 1rem);
		font-family: Arial;
	}


</style>
<?php
    $set = 0;
    if (isset($_GET["set"])) {
      $set = $_GET["set"];
    }
  ?>
<div id="php_message" hidden><?php echo $set; ?></div>
<div class="content" id="content-container"></div>

  <div id="multiselect">
  	<input type="button" class="button" onclick="_init_();" value="Init" >
    <div class="selectBox" onclick="showCheckboxes()">
      <select>
        <option >Select Options</option>
      </select>
      <div class="overSelect"></div>
    </div>
    <div id="checkboxes">
      <label for="top">
        <input type="checkbox" name="top" id="select_wfo_top" /><span class="label_text" id="tact_top">Backfach oben</span><label>
      <label for="bottom">
        <input type="checkbox" name="bottom" id="select_wfo_bottom" /><span class="label_text" id="tact_bottom">Backfach unten</span></label>
      <label for="left">
        <input type="checkbox" name="left" id="select_bbq_left" /><span class="label_text" id="tact_left">Grill links</span></label>
      <label for="right">
        <input type="checkbox" name="right" id="select_bbq_right" /><span class="label_text" id="tact_right">Grill rechts</span></label>
      <label for="meat">
        <input type="checkbox" name="meat" id="select_meat" /><span class="label_text" id="tact_meat">Fleisch</span></label>
    </div>
  </div>
</body>

<script>

var expanded = false;
function showCheckboxes() {
  var checkboxes = document.getElementById("checkboxes");
  if (!expanded) {
    checkboxes.style.display = "block";
    expanded = true;
  } else {
    checkboxes.style.display = "none";
    expanded = false;
  }
}

const HOMESERVER_URL= "/HomeDashboard/";
const INTERVALL_MAIN_TICKER = 1000;

var TACT_TOP = 250;
var TACT_BOTTOM = 250;
var TACT_LEFT = 250;
var TACT_RIGHT = 250;
var TACT_MEAT = 250;
var TMIN_TOP = 250;
var TMAX_TOP = 250;
var TMIN_BOTTOM = 250;
var TMAX_BOTTOM = 250;
var TMIN_LEFT = 250;
var TMAX_LEFT = 250;
var TMIN_RIGHT = 250;
var TMAX_RIGHT = 250;
var TMIN_MEAT = 250;
var TMAX_MEAT = 250;

var bool_top = false;
var bool_bottom = false;
var bool_left = false;
var bool_right = false;
var bool_meat = false;

var widget = [];
var size = 0;

window.addEventListener('DOMContentLoaded', init());

function init(){
	for (i=0;i<6;i++){
	widget[i] = document.getElementById("php_message").innerHTML[i];
	}
	size = parseInt(widget[0]+widget[1]);
	
	if (widget[2] == "1"){
		document.getElementById('select_wfo_top').checked = true;
	} else {
		document.getElementById('select_wfo_top').checked = false;
	}
	if (widget[3] == "1"){
		document.getElementById('select_wfo_bottom').checked = true;
	} else {
		document.getElementById('select_wfo_bottom').checked = false;
	}
	if (widget[4] == "1"){
		document.getElementById('select_bbq_left').checked = true;	
	} else {
		document.getElementById('select_bbq_left').checked = false;
	}
	if (widget[5] == "1"){
		document.getElementById('select_bbq_right').checked = true;	
	} else {
		document.getElementById('select_bbq_right').checked = false;
	}
	if (widget[6] == "1"){
		document.getElementById('select_meat').checked = true;
	} else {
		document.getElementById('select_meat').checked = false;
	}

}

function _init_(){
	check_checkboxes()
	createHTML();	
	// Haupt-Intervall einschalten
	INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
}

function check_checkboxes(){
	if (document.getElementById("select_wfo_top").checked){
		bool_top=true;
		widget[2] = "1";
	} else {
		widget[2] = "0";
	}	
	if (document.getElementById("select_wfo_bottom").checked){
		bool_bottom=true;
		widget[3] = "1";
	} else {
		widget[3] = "0";
	}
	if (document.getElementById("select_bbq_left").checked){
		bool_left=true;
		widget[4] = "1";
	} else {
		widget[4] = "0";
	}
	if (document.getElementById("select_bbq_right").checked){
		bool_right=true;
		widget[5] = "1";
	} else {
		widget[5] = "0";
	}
	if (document.getElementById("select_meat").checked){
		bool_meat=true;
		widget[6] = "1";
	} else {
		widget[6] = "0";
	}
	var payload = "";
	for (i=0;i<6;i++){
		payload += widget[i];
	}
	window.parent.postMessage(payload+' set_widget', '/HomeDashboard/dashboard.php');
	document.getElementById("multiselect").innerHTML = "";
}

function createHTML(){
	var html = "<div class='grid'>";
	document.getElementById("content-container").innerHTML = "";
	var id = 0;
	if (bool_top){
		html += "<span class='tact c" + id + "' id='temp_act_top'> Backfach oben: "+ TACT_TOP +"&deg;C</span>";
		id++;
	} 
	if (bool_bottom){
		html += "<span class='tact c" + id + "' id='temp_act_bottom'> Backfach unten:  "+ TACT_TOP +"&deg;C</span>";
		id++;
	} 
	if (bool_left){
		html += "<span class='tact c" + id + "' id='temp_act_left'> Grill links:  "+ TACT_TOP +"&deg;C</span>";
		id++;
	} 
	if (bool_right){
		html += "<span class='tact c" + id + "' id='temp_act_right'> Grill rechts:  "+ TACT_TOP +"&deg;C</span>";
		id++;
	} 
	if (bool_meat){
		html += "<span class='tact c" + id + "' id='temp_act_meat'> Fleisch:  "+ TACT_TOP +"&deg;C</span>";
		id++;
	} 
	html += "</div>";
	document.getElementById("content-container").innerHTML = html;
}

function interval_main_tick(){
	// alle Werte vom Server lesen
	xhttp_get_all_values();
	// Farbe der Temperaturanzeige steuern	
	temperature_set_color();
}
	
function temperature_set_color(){
	for (i=0;i<5;i++){
		switch(i){
			case 0: 
				var TACT = TACT_TOP;
				var tmin = TMIN_TOP;
				var tmax = TMAX_TOP;
				var tempsource = document.getElementById("temp_act_top");
				break;
			case 1: 
				var TACT = TACT_BOTTOM;
				var tmin = TMIN_BOTTOM;
				var tmax = TMAX_BOTTOM;
				var tempsource = document.getElementById("temp_act_bottom");
				break;
			case 2: 
				var TACT = TACT_LEFT;
				var tmin = TMIN_LEFT;
				var tmax = TMAX_LEFT;
				var tempsource = document.getElementById("temp_act_left");
				break;
			case 3: 
				var TACT = TACT_RIGHT;
				var tmin = TMIN_RIGHT;
				var tmax = TMAX_RIGHT;
				var tempsource = document.getElementById("temp_act_right");
				break;	
			case 4: 
				var TACT = TACT_MEAT;
				var tmin = TMIN_MEAT;
				var tmax = TMAX_MEAT;
				var tempsource = document.getElementById("temp_act_meat");
				break;
		}
	
		if (typeof tempsource != "undefined" && tempsource != null){
			tempsource.style.color = "green";
			if (TACT < tmin){
				tempsource.style.color = "blue";
			}
			if ((TACT > tmax) || (TACT == "--")){
				tempsource.style.color = "red";
			}
		}
	}
}

function xhttp_get_all_values(){
	// Lesen aller Werte vom Server per HTTP-Request
	var xhttp = new XMLHttpRequest();
	var json_response = "";
	xhttp.onreadystatechange = function() {
		//console.log("http status: "+ this.status);
		if (this.readyState == 4 && this.status == 200) {
			json_response = this.responseText;
			temperature_refresh(json_response);
		}
		else {
			if (this.status > 399){
				// TODO: Fehlerfall
				console.log("error or wrong response code");
			}
		}
	}
	xhttp.open("GET", HOMESERVER_URL + "json_handler.php?json=" + create_get_allvalues("30"), false);  
	xhttp.send();
}

function create_get_allvalues(timeout){
	var json_string = "{"
	json_string += "\"device_id\":\"001\",";	
	json_string += "\"event\":\"get_allvalues\",";
	json_string += "\"timeout\":\"" + timeout + "\"";
	json_string += "}";
	return json_string;
}

function temperature_refresh(json_obj){
	//response Handler
	var json_obj = JSON.parse(json_obj);
	// aktuelle Temperatur und Min-Max-Werte anzeigen	
	TACT_TOP = json_obj.temp_act_top;
	TMIN_TOP = json_obj.temp_min_top;
	TMAX_TOP = json_obj.temp_max_top;
	
	TACT_BOTTOM = json_obj.temp_act_bottom;
	TMIN_BOTTOM = json_obj.temp_min_bottom;
	TMAX_BOTTOM = json_obj.temp_max_bottom;
	
	TACT_LEFT = json_obj.temp_act_left;
	TMIN_LEFT = json_obj.temp_min_left;
	TMAX_LEFT = json_obj.temp_max_left;

	TACT_RIGHT = json_obj.temp_act_right;
	TMIN_RIGHT = json_obj.temp_min_right;
	TMAX_RIGHT = json_obj.temp_max_right;
	
	TACT_MEAT = json_obj.temp_act_meat;
	TMIN_MEAT = json_obj.temp_min_meat;
	TMAX_MEAT = json_obj.temp_max_meat;	
	createHTML();
}

</script>
</html>