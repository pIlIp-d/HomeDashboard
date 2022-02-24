//TODO - min max values only in specified range of values, curently doesnt work when changing bp mode

const DEVICE_ID = "odk";
var SENSOR_ID = "";

const HOMESERVER_URL = "/HomeDashboard/";
const INTERVALL_MAIN_TICKER = 1000;
const TIMEOUT = 30;

var TACT = document.getElementById("tact_value");
var TMIN = document.getElementById("tmin_select");
var TMAX = document.getElementById("tmax_select");

// @param recipe_id - recipe of active recipe for min/max temp in bp_mode
var recipe_id;//for recipe temp
//@param bp_mode - 	bp_mode true  -> min/max temp from active bakingplan recipe(recipe_id)
//					bp_mode false -> min/max temp from device/ manual mode
var bp_mode = true;

document.addEventListener("DOMContentLoaded", init());

TMIN.addEventListener('change', function(){tminmax_changed("tmin_select")});
TMAX.addEventListener('change', function(){tminmax_changed("tmax_select")});

function init(){
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
	if (bp_mode) xhttp_send("get_active_recipe");
	xhttp_send("get_device_values");
	temperature_set_color();
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
	if ((tact > tmax) || (TACT.innerHTML == "--")){
		document.getElementById("tact_value").style.color = "red";
		document.getElementById("tact_symbol").src = "/HomeDashboard/images/sym_thermo_red.svg";
		document.getElementById("tact_unit").style.color = "red";
	}
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
function xhttp_send(request_name){
	var json_string = "{"
	json_string += "\"request_name\":\"" + request_name + "\",";
	json_string += "\"device_name\":\"" + SENSOR_ID + "\"";
	switch(request_name){
		case "get_device_values":
			json_string += ",\"timeout\":\"" + TIMEOUT + "\"";
			break;

		case "set_minmaxvalues":
			toggle_bp_mode(false);//-> manual mode because min/max were changed
			json_string += ",\"temp_min\":\"" + TMIN.value + "\",";
			json_string += "\"temp_max\":\"" + TMAX.value + "\"";
			break;

		case "get_active_recipe":
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
	xhttp.open("GET", HOMESERVER_URL + "odk_db.php?json=" + json_string, false);
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
//----------------------------
//---- BP_MODE handling ------
//----------------------------

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
