const HOMESERVER_URL= "/HomeDashboard/";
const TIMER_HANDLER = HOMESERVER_URL+"dashboard/timer_handler.php";
const DEVICE_ID = "001";
const DEVICE_NAME = "Backofen";

const BP_BTN = document.getElementById("bp_mode"); //toggle for bp_mode

var PRESET_ID;
var TIMER_ID;

var bp_mode = true;

/**
* http request sending and -> to response handling
* @param request_name used on serverside to create response
* @value used for single values in some requests
*/
function http_request(request_name, value = null){
	console.log("request: "+request_name+" p_id"+PRESET_ID+" t_id: "+TIMER_ID);
		var json_string = "{\"request_name\":\""+request_name+"\"";
		//if (!bp_mode)
			json_string += ",\"preset_id\":\""+PRESET_ID+"\"";
		//TODO -- bp_switch
		switch (request_name){
			case "del_timer":
			case "get_timer":
					json_string += ",\"timer_id\":\""+TIMER_ID+"\"}";
				break;
			case "new_timer":
					json_string += ",\"time\":\""+value+"\"}";
				break;
		}
		var json_response
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function(){
			if (this.readyState == 4 && this.status == 200)
				json_response = this.responseText;
		}
		xhttp.open("GET", TIMER_HANDLER+ "?json="+json_string,false);
		xhttp.send();
		handleHTTPresponse(request_name,json_response);
}

/**
* @param request_name for different response handling
*			get timer -> parsing time the timer has left into hours, minutes, seconds
* @param json_response response data or "OK" to reassure request worked
*/
function handleHTTPresponse(request_name,json_response){
	switch (request_name){
		case "new_timer":
				if (json_response != "OK")
					alert("Fehler beim Timer speichern!");
			break;

		case "del_timer":
				if (json_response != "OK")
					alert("Fehler beim Timer löschen!")
			break;
		case "get_timer":
				if (json_response != 204){
					var timenow = new Date();
					timenow = parseInt(timenow.getTime());
					var diff = Math.round((json_response - timenow) / 1000);
					if (diff > 0){
						//Timer-Uhr setzten
						ACT_HOUR = parseInt((diff / 3600) % 24);
						ACT_MINUTE = parseInt((diff / 60) % 60);
						ACT_SECOND = parseInt(diff % 60);
						set_timer_values();
						btn_start(false);
					}
					else btn_stop();
				}
				else
					btn_stop();
			break;
	}
}

function set_timer_values(){
	//werte zweistellig machen und anzeigen
	TIMER_HOUR.value = ((ACT_HOUR < 10)?"0":"")+ ACT_HOUR.toString();
	TIMER_MINUTE.value = ((ACT_MINUTE < 10)?"0":"")+ ACT_MINUTE.toString();
	TIMER_SECOND.value = ((ACT_SECOND < 10)?"0":"")+ ACT_SECOND.toString();
}
function check_clock(){
	http_request("get_timer");
}
function reset_timer(){
	timer_exists = false;
	TIMER_HOUR.value = "00";
	TIMER_MINUTE.value = "00";
	TIMER_SECOND.value = "00";
}
/**
* Set Options for timer selects
*/function set_options(){
		var i_string;
		TIMER_HOUR.options = 0;
		TIMER_MINUTE.options = 0;
		TIMER_SECOND.options = 0;
		for (let i = 0; i < 60; ++i) {
			i_string = ((i < 10)?"0":"")+ i.toString();//zahl zu 2 stelligen string (1->01)
			if (i < 24)
				TIMER_HOUR.options[TIMER_HOUR.options.length] = new Option(i_string);
			TIMER_MINUTE.options[TIMER_MINUTE.options.length] = new Option(i_string);
			TIMER_SECOND.options[TIMER_SECOND.options.length] = new Option(i_string);
		}
		reset_timer();
	}

/**
* @param make_new_timer true 	-> makes request, saves and starts timer
*						false 	-> saves and starts timer but don't makes request to create a new one
*								   is used to start timers, wich are already running when reloading
*/function btn_start(make_new_timer = true){
		if (make_new_timer && parseInt(TIMER_HOUR.value) == 0 && parseInt(TIMER_MINUTE.value) == 0 && parseInt(TIMER_SECOND.value) == 0){
			alert("Bitte erst Timer setzen!");
			return;
		}
		//Startzeit für nächsten Durchlauf merken
		START_HOUR = TIMER_HOUR.value;
		START_MINUTE = TIMER_MINUTE.value;
		START_SECOND = TIMER_SECOND.value;
		// Zeit bis zum Timer-Start berechnen
		var timerstart = new Date();
		if (make_new_timer){
			let srv_timerstop = timerstart.getTime() + (parseInt(TIMER_HOUR.value) * 3600 +//sec -> min*hour
														parseInt(TIMER_MINUTE.value) * 60 +//sec -> min
														parseInt(TIMER_SECOND.value)) * 1000;//ms -> sec
			// Timer-Ende per http-Request auf Server schreiben (neuen Timer anlegen)
			http_request("new_timer", srv_timerstop);
		}
		TIMER_START.style.display = "none";
		TIMER_STOP.style.display = "block";
		STOP_BUTTON_REQUEST = parseInt(TIMER_SECOND.value);
		timer_exists = true;
	}
	function btn_stop(){
		// gemerkten Startindex wieder eintragen
		TIMER_HOUR.value = START_HOUR;
		TIMER_MINUTE.value = START_MINUTE;
		TIMER_SECOND.value = START_SECOND;
		TIMER_START.style.display = "block";
		TIMER_STOP.style.display = "none";
		timer_exists = false;
		http_request("del_timer");
	}

function toggle_bp_mode(bool = null){
	if (bool != null)//set
		bp_mode = bool^1;//toogle twice -> set to actual bool value

	if (bp_mode)//toggle
		BP_BTN.src = HOMESERVER_URL+"images/btn_list_solid.svg";
	else
		BP_BTN.src = HOMESERVER_URL+"images/btn_list_regular.svg";
	bp_mode = 1^bp_mode;
}
