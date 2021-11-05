var DEVICE_ID = "";
var SENSOR_ID = "";

const HOMESERVER_URL= "/HomeDashboard/";
const INTERVALL_MAIN_TICKER = 1000;
const TIMEOUT = 30;

const TACT = document.getElementById("tact_value");
const TMIN = document.getElementById("tmin_select");
const TMAX = document.getElementById("tmax_select");
TMIN.addEventListener("change", tminmax_changed(TMIN.id));
TMAX.addEventListener("change", tminmax_changed(TMAX.id));

document.addEventListener("DOMContentLoaded", init());

function init(){
	// Gerät festlegen
	DEVICE_ID = "odk";
	SENSOR_ID = document.getElementById("sensor_id").innerHTML;
	// Headername anpassen
	set_headername();
	// Min-Max-Anzeige initialisieren
	tminmax_set_options(TMIN, 0, 500);
	tminmax_set_options(TMAX, 0, 500);
	TMIN.value = 250;
	TMAX.value = 250;
	// Haupt-Intervall einschalten
	INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
}

function interval_main_tick(){
	// alle Werte vom Server lesen
	xhttp_get_all_values();
	// Farbe der Temperaturanzeige steuern
	temperature_set_color();
}
	
function set_headername(){
	document.getElementById("header_text").innerHTML = document.getElementById("display_name").innerHTML;
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
			target_max = document.getElementById(id).value;;
			break;
	}
	tminmax_set_options(target, target_min, target_max);
	// aktuelle Werte auf Server schreiben
	xhttp_send("set_minmaxvalues", "", "");
}

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

function xhttp_send(event, timeout, timer_stop){
	var xhttp = new XMLHttpRequest();
	var response = "";
	// JSON-String erzeugen
	var json_string = "{"
	json_string += "\"event\":\"" + event + "\",";
	json_string += "\"device_id\":\"" + DEVICE_ID + "\",";	
	switch(event){
		
		case "get_allvalues":
			json_string += "\"timeout\":\"" + TIMEOUT + "\"";
			break;

		case "set_minmaxvalues":
		// ---------------------------------------------------------------------------------
			switch(SENSOR_ID){
				case "wfo_top":
					json_string += "\"temp_min_top\":\"" + TMIN.value + "\",";
					json_string += "\"temp_max_top\":\"" + TMAX.value + "\"";	
					break;
				case "wfo_bottom":
					json_string += "\"temp_min_bottom\":\"" + TMIN.value + "\",";
					json_string += "\"temp_max_bottom\":\"" + TMAX.value + "\"";	
					break;
				case "bbq_left":
					json_string += "\"temp_min_left\":\"" + TMIN.value + "\",";
					json_string += "\"temp_max_left\":\"" + TMAX.value + "\"";	
					break;
				case "bbq_right":
					json_string += "\"temp_min_right\":\"" + TMIN.value + "\",";
					json_string += "\"temp_max_right\":\"" + TMAX.value + "\"";	
					break;
				case "meat":
					json_string += "\"temp_min_meat\":\"" + TMIN.value + "\",";
					json_string += "\"temp_max_meat\":\"" + TMAX.value + "\"";	
					break;
			}
			break;
		
	}
	json_string += "}";
	// HTTP-Request an Server schicken
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			response = this.responseText;
			return response;
		}
	};
	try
	{
		xhttp.open("GET", HOMESERVER_URL + "json_handler.php?json=" + json_string, false);  
		xhttp.send();
	}
	catch(err){
		console.log("Error sending XLMHttpRequest: "+err);
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
			if (this.status > 399)	// TODO: Fehlerfall
				console.log("error or wrong response code");
		}
	}
	xhttp.open("GET", HOMESERVER_URL + "json_handler.php?json=" + create_get_allvalues("30"), false);  
	xhttp.send();
}

function create_get_allvalues(timeout){
var json_string = "{"
	json_string += "\"device_id\":\"" + DEVICE_ID + "\",";	
	json_string += "\"event\":\"get_allvalues\",";
	json_string += "\"timeout\":\"" + timeout + "\"";
	json_string += "}";
	return json_string;
}


function temperature_refresh(json_obj){
	//response Handler
	var json_obj = JSON.parse(json_obj);
	// aktuelle Temperatur und Min-Max-Werte anzeigen
	switch(SENSOR_ID){
		case "wfo_top":
			TACT.innerHTML = json_obj.temp_act_top;
			TMIN.value = json_obj.temp_min_top;
			TMAX.value = json_obj.temp_max_top;
			break;
		case "wfo_bottom":
			TACT.innerHTML = json_obj.temp_act_bottom;
			TMIN.value = json_obj.temp_min_bottom;
			TMAX.value = json_obj.temp_max_bottom;
			break;
		case "bbq_left":
			TACT.innerHTML = json_obj.temp_act_left;
			TMIN.value = json_obj.temp_min_left;
			TMAX.value = json_obj.temp_max_left;
			break;
		case "bbq_right":
			TACT.innerHTML = json_obj.temp_act_right;
			TMIN.value = json_obj.temp_min_right;
			TMAX.value = json_obj.temp_max_right;
			break;
		case "meat":
			TACT.innerHTML = json_obj.temp_act_meat;
			TMIN.value = json_obj.temp_min_meat;
			TMAX.value = json_obj.temp_max_meat;
			break;
	}
}