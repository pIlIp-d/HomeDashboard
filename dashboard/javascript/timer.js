const HOMESERVER_URL= "/HomeDashboard/";
const TIMER_HANDLER = HOMESERVER_URL+"dashboard/timer_handler.php";
const DEVICE_ID = "001";
const DEVICE_NAME = "Backofen";

var PRESET_ID;
var TIMER_ID;


//---------------------------------------
//------- Request -----------------------
//---------------------------------------
function http_request(request_name, value = null){
	console.log("request: "+request_name+" p_id"+PRESET_ID+" t_id: "+TIMER_ID);
		var json_string = "{\"request_name\":\""+request_name+"\"";
		json_string += ",\"preset_id\":\""+PRESET_ID+"\"";

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
				if (json_response == 204)
					btn_stop();
				else {
					//json_response = end time of timer
					var timenow = new Date();
					timenow = parseInt(timenow.getTime());
					var diff = Math.round((json_response - timenow) / 1000);
					//rest t > 0
					if (diff > 0){
						//Timer-Uhr setzten
						ACT_HOUR = parseInt((diff / 3600) % 24);
						ACT_MINUTE = parseInt((diff / 60) % 60);
						ACT_SECOND = parseInt(diff % 60);
						//werte zweistellig machen und anzeigen
						TIMER_HOUR.value = ((ACT_HOUR < 10)?"0":"")+ ACT_HOUR.toString();
						TIMER_MINUTE.value = ((ACT_MINUTE < 10)?"0":"")+ ACT_MINUTE.toString();
						TIMER_SECOND.value = ((ACT_SECOND < 10)?"0":"")+ ACT_SECOND.toString();
					}
					else {
						btn_stop();
					}
				}
			break;
	}
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
//-------------------------------
//-- Set Options Normal clock ---
//-------------------------------

	function set_options(){
		var i_string;
		TIMER_HOUR.options = 0;
		TIMER_MINUTE.options = 0;
		TIMER_SECOND.options = 0;
		for (i = 0; i < 60; ++i) {
			i_string = ((i < 10)?"0":"")+ i.toString();//zahl zu 2 stelligen string (1->01)
			if (i<24)
				TIMER_HOUR.options[TIMER_HOUR.options.length] = new Option(i_string);
			TIMER_MINUTE.options[TIMER_MINUTE.options.length] = new Option(i_string);
			TIMER_SECOND.options[TIMER_SECOND.options.length] = new Option(i_string);
		}
		reset_timer();
	}

	function btn_start(){
		if ((parseInt(TIMER_HOUR.value) == 0) && (parseInt(TIMER_MINUTE.value) == 0) && (parseInt(TIMER_SECOND.value) == 0)){
			alert("Bitte erst Timer setzen!");
			return;
		}
		// Startindex für nächsten Durchlauf merken
		START_HOUR = TIMER_HOUR.value;
		START_MINUTE = TIMER_MINUTE.value;
		START_SECOND = TIMER_SECOND.value;
		// Zeit bis zum Timer-Start berechnen
		var timerstart = new Date();
		let srv_timerstop  = timerstart.getTime() + (parseInt(TIMER_HOUR.value) * 3600 + parseInt(TIMER_MINUTE.value) * 60 + parseInt(TIMER_SECOND.value)) * 1000;
		// Timer-Ende per http-Request auf Server schreiben
		http_request("new_timer", srv_timerstop);
		TIMER_START.style.display = "none";
		TIMER_STOP.style.display = "block";
		// Button "Stop" erst aktivieren wenn sich Sekundenanzeige geändert hat (Server hat reagiert)
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

//--------------------------------------
//-------- clock quadrat ---------------
//--------------------------------------

	function set_time(json_response){
		var timestop = json_response.timer_stop_wfo;
		var timenow = new Date();
		timenow = parseInt(timenow.getTime());
		var diff = Math.round((timestop - timenow) / 1000);
		var canv_context = canvas.getContext("2d");
		//clear
		canv_context.clearRect(0, 0, canvas.width, canvas.height);
		canv_context.lineWidth = 5;

		if (diff > 0){
			//Timer-Uhr setzten
			var h = parseInt((diff / 3600) % 24);
			h = (h>24)?24:h;//h is maximal 24
			var m = parseInt((diff / 60) % 60);
			var s = parseInt(diff % 60);

			var h_deg = (h / 24) * 2 * Math.PI;
			var m_deg = (m / 60) * 2 * Math.PI;
			var s_deg = (s / 60) * 2 * Math.PI;
			//korrektur, weil Arc rechts startet, die uhr aber oben starten soll
			h_deg = (h_deg < 0.5 * Math.PI)?(h_deg + 1.5 * Math.PI):(h_deg - 0.5 * Math.PI);
			m_deg = (m_deg < 0.5 * Math.PI)?(m_deg + 1.5 * Math.PI):(m_deg - 0.5 * Math.PI);
			s_deg = (s_deg < 0.5 * Math.PI)?(s_deg + 1.5 * Math.PI):(s_deg - 0.5 * Math.PI);

			half_canvas_width = canvas.width / 2;
			half_canvas_height = canvas.height / 2;
			try{
				canv_context.strokeStyle = "red";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3, 1.5 * Math.PI,s_deg);
				canv_context.stroke();
				canv_context.strokeStyle = "blue";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3 -10, 1.5 * Math.PI,m_deg);
				canv_context.stroke();
				canv_context.strokeStyle = "green";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3 -20, 1.5 * Math.PI,h_deg);
				canv_context.stroke();
			}catch {
				console.log("window to small to dislpay all times");
			}
		}
		else {//if timer == 00:00:00
			try{
				canv_context.strokeStyle = "red";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3,1.5*Math.PI ,1*Math.PI);
				canv_context.stroke();
				canv_context.strokeStyle = "blue";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3 -10,0 ,2*Math.PI);
				canv_context.stroke();
				canv_context.strokeStyle = "green";
				canv_context.beginPath();
				canv_context.arc(half_canvas_width, half_canvas_height, window.innerHeight / 3 -20,0 ,1.5*Math.PI);
				canv_context.stroke();
			}catch {
				console.log("window to small to dislpay all times at timer 1x1");
			}
		}
	}

//TODO recieve next timer values and if stop is presed or timer is ready then nextRecipe
	window.addEventListener('message', e => {
	  	if (e.origin == "http://"+location.host){
			let data_str = e.data.split(" ");
			switch (data_str[1]){
				//move with arrows
				case "timer_set":
						btn_stop();
						let time = data_str[0].split(":");
						TIMER_HOUR.options = time[0];
						TIMER_MINUTE.options = time[1];
						TIMER_SECOND.options = time[2];
						btn_start();
					break;
				case "timer_reset":
						btn_stop();
						TIMER_HOUR.value = "00";
						TIMER_MINUTE.value = "00";
						TIMER_SECOND.value = "00";
					break;
			}
		}
	}, false);
