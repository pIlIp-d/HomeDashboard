//TODO - min max values only in specified range of values, curently doesnt work when changing bp mode

const DEVICE_ID = "odk";
var SENSOR_ID = "";

const HOMESERVER_URL = "/HomeDashboard/";
const DB_URL = HOMESERVER_URL + "odk_db.php";
const INTERVALL_MAIN_TICKER = 1000;
const TIMEOUT = 30;

var NAME = document.getElementById("display_name");
var TACT = document.getElementById("tact_value");
var TMIN = document.getElementById("tmin_select");
var TMAX = document.getElementById("tmax_select");
const canvas = document.getElementById("canvas");
var canv_context = canvas.getContext("2d");

var temp_history = [];

// @param recipe_id - recipe of active recipe for min/max temp in bp_mode
var recipe_id;//for recipe temp
//@param bp_mode - 	bp_mode true  -> min/max temp from active bakingplan recipe(recipe_id)
//					bp_mode false -> min/max temp from device/ manual mode
var bp_mode = true;
//@param alarm_mode - 	alarm_mode true  -> add alarm event
//						alarm_mode false -> no alarm for this device
var alarm_mode = true;
var alarm_value = 0; //value of alarm threshold, negative -> '<', positive -> '>', 0 -> not set
var top_alarm_count = 0;//count that temp is >3 sec over threshold
var bottom_alarm_count = 0;

const ALARM_SELECT = document.getElementById("alarm_select");

document.addEventListener("DOMContentLoaded", init());

TMIN.addEventListener('change', function(){tminmax_changed("tmin_select")});
TMAX.addEventListener('change', function(){tminmax_changed("tmax_select")});
ALARM_SELECT.addEventListener('change', set_alarm);

function init(){
	//set canvas width / height
	canvas.width  = window.screen.availWidth;
	canvas.height = window.screen.availHeight;

	//set alarm options
	ALARM_SELECT.options[1] = new Option("Min Temp reached", "min_reached");
	ALARM_SELECT.options[2] = new Option("Max Temp reached", "max_reached");
	ALARM_SELECT.options[3] = new Option("Min Temp drop", "min_drop");
	ALARM_SELECT.options[4] = new Option("Max Temp drop", "max_drop");
	ALARM_SELECT.options[5] = new Option("custom Temp min", "custom_drop");
	ALARM_SELECT.options[6] = new Option("custom temp max", "custom_reach");
	ALARM_SELECT.value = 'null';

	SENSOR_ID = document.getElementById("sensor_id").innerHTML;
	change_view();
	// Headername anpassen
	set_headername();
	// Min-Max-Anzeige initialisieren
	tminmax_set_options(TMIN, 0, 500);
	tminmax_set_options(TMAX, 0, 500);
	TMIN.value = 250;
	TMAX.value = 250;

	xhttp_send("get_active_recipe");
	toggle_bp_mode(false);
	// Haupt-Intervall einschalten
	INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
}
function set_headername(){
	document.getElementById("header_text").innerHTML = document.getElementById("display_name").innerHTML;
}

function set_alarm(){
	ALARM_SELECT.style.display = "none";
	switch( ALARM_SELECT.value){
		case "min_reached":
			alarm_value = TMIN.value;
			break;
		case "max_reached":
			alarm_value = TMAX.value;
			break;
		case "min_drop":
			alarm_value = -TMIN.value;
			break;
		case "max_drop":
			alarm_value = -TMAX.value;
			break;
		case "custom_drop":
			var input = prompt("Gib deine gewünschte mindest Temperatur ein.");
			if (!isNaN(input))
				alarm_value = -parseInt(input);
			else{
				alert("Bitte gib eine Zahl ein.");
				set_alarm();
			}
			break;
		case "custom_reach":
			var input = prompt("Gib deine gewünschte maximal Temperatur ein.");
			if (!isNaN(input))
				alarm_value = parseInt(input);
			else{
				alert("Bitte gib eine Zahl ein.");
				set_alarm();
			}
			break;
	}
	ALARM_SELECT.value  = 'null';
}

/**
* sets the select options for min and max temp selects in html
* @param target - document.object (TMIN or TMAX)
* @param target_min - lower end of range
* @param target_max - upper end of range
*/
function tminmax_set_options(target, target_min, target_max){
	const min = parseInt(target_min);
	const max = parseInt(target_max);
	var value = parseInt(target.value);
	if (value < min)
		value = min;
	if (value > max)
		value = max;
	target.options.length = 0;
	for (i = min; i < max + 1; i = i + 10) {
		target.options[target.options.length] = new Option(i);
		target.value = value;
	}
}

function interval_main_tick(){
	draw_temp_graph();
	if (bp_mode) xhttp_send("get_active_recipe");
	xhttp_send("get_device_values");
	temperature_set_color();
	check_alarm();
}
function check_alarm(){
	set = false;
	if (alarm_mode && alarm_value != 0){
		console.log("chekcing");
		var temp = TACT.innerHTML;
		if (!isNaN(temp)){
			if (alarm_value > 0){//top threshold
				console.log("top"+ top_alarm_count);
				top_alarm_count++;
				if (temp > alarm_value && 3 < top_alarm_count){//3 times in a row -> 3sec over threashold required
					xhttp_send("send_alarm","Die Temperatur von " + NAME.innerHTML + " ist größer als " + alarm_value + "!");
					toggle_alarm_mode(false);
					return;
				}
			}
			else if (alarm_value < 0){//bottom threshold
				bottom_alarm_count++;
				if (temp < alarm_value && 3 < bottom_alarm_count++){//3 times in a row -> 3sec over threashold required
					xhttp_send("send_alarm","Die Temperatur von " + NAME.innerHTML + " ist kleiner als " + (-alarm_value) + "!");
					toggle_alarm_mode(false);
					return;
				}
			}
			else {
				top_alarm_count = 0;
				bottom_alarm_count = 0;
			}
		}
		else{
			top_alarm_count = 0;
			bottom_alarm_count = 0;
		}
	}
}

function toggle_alarm_mode(bool = null){
	if (bool != null)//set
		alarm_mode = bool^1;//toogle twice -> set to actual bool value
	//toggle
	if (alarm_mode){
		ALARM_SELECT.style.display = "none";
		alarm_value = 0;
		document.getElementById("alarm_mode").src = HOMESERVER_URL+"images/bell-slash.svg";
	}
	else {
		ALARM_SELECT.style.display = "block";
		document.getElementById("alarm_mode").src = HOMESERVER_URL+"images/bell-solid.svg";
	}
	alarm_mode = 1^alarm_mode;
}

function toggle_bp_mode(bool = null){
	if (bool != null)//set
		bp_mode = bool^1;//toogle twice -> set to actual bool value
	//toggle
	if (bp_mode)
		document.getElementById("bp_mode").src = HOMESERVER_URL+"images/btn_list_regular.svg";
	else
		document.getElementById("bp_mode").src = HOMESERVER_URL+"images/btn_list_solid.svg";
	bp_mode = 1^bp_mode;
}

function temperature_set_color(){
	var tact = parseInt(TACT.innerHTML);
	var tmin = parseInt(TMIN.value);
	var tmax = parseInt(TMAX.value);

	document.getElementById("tact_value").style.color = "green";
	document.getElementById("tact_symbol").src = "/HomeDashboard/images/sym_thermo_green.svg";
	document.getElementById("tact_unit").style.color = "green";
	if (tact < tmin){
		document.getElementById("tact_value").style.color = "blue";
		document.getElementById("tact_symbol").src = "/HomeDashboard/images/sym_thermo_blue.svg";
		document.getElementById("tact_unit").style.color = "blue";
	}
	if (tact > tmax || TACT.innerHTML == "--"){
		document.getElementById("tact_value").style.color = "red";
		document.getElementById("tact_symbol").src = "/HomeDashboard/images/sym_thermo_red.svg";
		document.getElementById("tact_unit").style.color = "red";
	}
	//for drawing the graph
	temp_history.push([TACT.innerHTML, document.getElementById("tact_value").style.color]);
	if (temp_history.length > 50)
		temp_history.shift();//only save the last 50 temps
}

function tminmax_changed(id){
	recipe_changed = false;
	// Min-Max-Werte für korrespondierendes Element einstellen
	var target;
	var target_min = 0;
	var target_max = 500;
	switch (id) {
		case "tmin_select":
			target = TMAX;
			target_min = document.getElementById(id).value;
			break;
		case "tmax_select":
			target = TMIN;
			target_max = document.getElementById(id).value;
			break;
	}
	tminmax_set_options(target, target_min, target_max);
	// aktuelle Werte auf Server schreiben
	xhttp_send("set_minmaxvalues");
}

/**
* http request sending and -> to response handling
* @param request_name used on serverside to create response
*/
function xhttp_send(request_name, value = null){
	var json_string = "{"
	json_string += "\"request_name\":\"" + request_name + "\",";
	json_string += "\"device_name\":\"" + SENSOR_ID + "\"";
	switch(request_name){
		case "get_device_values":
			json_string += ",\"timeout\":\"" + TIMEOUT + "\"";
			break;

		case "set_minmaxvalues":
			toggle_bp_mode(false);//-> manual mode because min/max were changed
			json_string += ",\"temp_min\":\"" + TMIN.value + "\"";
			json_string += ",\"temp_max\":\"" + TMAX.value + "\"";
			break;

		case "get_active_recipe":
			break;

		case "send_alarm":
			json_string += ",\"message\":\"" + value + "\"";
			break;
	}
	json_string += "}";
	// HTTP-Request an Server schicken
	var response = "";
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
			response = this.responseText;
		else if (this.readyState != 1 || this.status != 0)//weird response exception, wich the browser can handle fine
			console.log("error or wrong response code");
	};
	xhttp.open("GET", DB_URL + "?json=" + json_string, false);
	xhttp.send();
	response_handler(request_name, response);
}

function response_handler(request_name, response){
	switch (request_name){
		case "get_device_values":
			temperature_refresh(response);
			break;
		case "get_active_recipe":
			set_Active_Recipe(response);
			break;
	}
}

function temperature_refresh(json_obj){
	var json_obj = JSON.parse(json_obj);
	// aktuelle Temperatur und Min-Max-Werte anzeigen
	TACT.innerHTML = json_obj.temp_act;
	if (!bp_mode){
		TMIN.value = json_obj.temp_min;
		TMAX.value = json_obj.temp_max;
	}
}

/**
* response handler
* changes minmax_values if active recipe has changed
* @param json_response json recipe
*/
function set_Active_Recipe(json_response){
	let response = JSON.parse(json_response)[0];
	if (recipe_id != response.id){//recipe changed
		toggle_bp_mode(true);
		recipe_changed = true;
		recipe_id = response.id;
	}
	let recipe_temp = response["bakingtemperature"];
	let temp_min = recipe_temp-20;
	let temp_max = recipe_temp-0+20;
	tminmax_set_options(TMIN, 0, temp_max);
	TMIN.value = temp_min;
	TMAX.value = temp_max;
}

function draw_temp_graph(){
	if (canvas.getContext){
		//clear
		canv_context.beginPath();
		canv_context.clearRect(-10, -10, canvas.width+10, canvas.height+10);
		canv_context.stroke();

		if (document.getElementById("show").innerHTML == '0'){
			let width = canvas.width;
			let height = canvas.height;
			//get highest temp to make relative height
			let largest = 0;
			for (let temp in temp_history){
				if (temp_history[temp][0] != "--" && temp_history[temp][0] > largest)
					largest = temp_history[temp][0];
			}
			largest = parseInt(largest) + 120;//padding on top

			let one_part = width / 50;//wdth of single datapoint
			let x = width;
			let last_temp = 1000;//for seamless line
			for (let i = temp_history.length-1; i >= 0; i--){
				if (temp_history[i][0] != '--'){
					canv_context.lineWidth = 7;
					canv_context.strokeStyle = temp_history[i][1];
					canv_context.beginPath();
					canv_context.moveTo(x + one_part, last_temp);
					canv_context.lineTo(x, largest - parseInt(temp_history[i][0]));
					canv_context.stroke();
					last_temp = largest - parseInt(temp_history[i][0]);
				}
				x -= one_part;//move one datapoint to the left
			}
		}
	}
}
