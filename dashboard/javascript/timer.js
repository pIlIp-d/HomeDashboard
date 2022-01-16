const HOMESERVER_URL= "/HomeDashboard/";
const DEVICE_ID = "001";
const DEVICE_NAME = "Backofen";
const INTERVALL_MAIN_TICKER = 1000;
var INTERVALL_MAIN;


//---------------------------------------
//------- Request ----------------------- 
//---------------------------------------

	function check_clock(){
		var xhttp = new XMLHttpRequest();
		var json_string = "{\"device_id\":\""+DEVICE_ID+"\"";
		json_string += ",\"device_name\":\""+DEVICE_NAME+"\"";
		json_string += ",\"event\":\"get_allvalues\"";
		json_string += ",\"timeout\":\"30\"}";

		xhttp.onreadystatechange = function(){
			if (this.readyState == 4 && this.status == 200) {
			    json_response = this.responseText;
					handleHTTPresponse(json_response);
		    }
		}
		xhttp.open("GET", HOMESERVER_URL + "json_handler.php?json="+json_string,true);
		xhttp.send();
	}


	function handleHTTPresponse(json_response){
		var timestop = JSON.parse(json_response).timer_stop_wfo;
		var timenow = new Date();
		timenow = parseInt(timenow.getTime());
		var diff = Math.round((timestop - timenow) / 1000);

		//rest t > 0 
		if (diff > 0){
			//Timer-Uhr setzten
			ACT_HOUR = parseInt((diff / 3600) % 24);
			ACT_MINUTE = parseInt((diff / 60) % 60);
			ACT_SECOND = parseInt(diff % 60);
			//werte zweistellig machen
			if (ACT_HOUR < 10){
				var h_string = "0" + ACT_HOUR.toString();
			}
			else{
				var h_string = ACT_HOUR.toString();
			}
			if (ACT_MINUTE < 10){
				var m_string = "0" + ACT_MINUTE.toString();
			}
			else{
				var m_string = ACT_MINUTE.toString();
			}
			if (ACT_SECOND < 10){
				var s_string = "0" + ACT_SECOND.toString();
			}
			else{
				var s_string = ACT_SECOND.toString();
			}		
			// Werte anzeigen
			TIMER_HOUR.value = h_string;
			TIMER_MINUTE.value = m_string;
			TIMER_SECOND.value = s_string;
		}		
	}


//-------------------------------
//------ Buttons Normal clock ---
//-------------------------------

	function set_options(){
		var i_string;
		TIMER_HOUR.options = 0;
		TIMER_MINUTE.options = 0;
		TIMER_SECOND.options = 0;
		for (i = 0; i < 60; ++i) {
			i_string = i.toString();
			if (i<10){
				i_string = "0" + i_string;
			}
			if (i<24){
				TIMER_HOUR.options[TIMER_HOUR.options.length] = new Option(i_string);
			}
			TIMER_MINUTE.options[TIMER_MINUTE.options.length] = new Option(i_string);
			TIMER_SECOND.options[TIMER_SECOND.options.length] = new Option(i_string);	
		}
		TIMER_HOUR.value = "00";
		TIMER_MINUTE.value = "00";
		TIMER_SECOND.value = "00";
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
		srv_timerstop  = timerstart.getTime() + (parseInt(TIMER_HOUR.value) * 3600 + parseInt(TIMER_MINUTE.value) * 60 + parseInt(TIMER_SECOND.value)) * 1000;	
		// Timer-Ende per http-Request auf Server schreiben	
		xhttp_send("set_timer_wfo", srv_timerstop)
		TIMER_START.style.display = "none";
		TIMER_STOP.style.display = "block";
		// Button "Stop" erst aktivieren wenn sich Sekundenanzeige geändert hat (Server hat reagiert)
		STOP_BUTTON_REQUEST = parseInt(TIMER_SECOND.value);

	}
	function btn_stop(){
		// gemerkten Startindex wieder eintragen
		TIMER_HOUR.value = START_HOUR;
		TIMER_MINUTE.value = START_MINUTE;
		TIMER_SECOND.value = START_SECOND;
		TIMER_START.style.display = "block";
		TIMER_STOP.style.display = "none";
		xhttp_send("del_timer_wfo", false)
	}

	function xhttp_send(event, value){
		var json_string = "{"
		json_string += "\"device_id\":\"" + DEVICE_ID + "\",";	
		json_string += "\"device_name\":\"" + DEVICE_NAME + "\",";
		json_string += "\"event\":\"" + event + "\"";
		if (event == "set_timer_wfo"){
			json_string += ",\"timer_stop_wfo\":\"" + value + "\"";
		}
		json_string += "}";
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET",HOMESERVER_URL + "json_handler.php?json=" + json_string, true);
		xhttp.send();
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
		//hour
		canv_context.lineWidth = 5;
			
		if (diff > 0){
			//Timer-Uhr setzten
			var h = parseInt((diff / 3600) % 24);
			var m = parseInt((diff / 60) % 60);
			var s = parseInt(diff % 60);
			
			//0 = 1.5Pi -- 3 = 0Pi -- 6 = 0.5Pi -- 9 = 1Pi
			if (h>24){
				h=24;//allowed maximum
			}
			var h_deg = (h / 24) * 2 * Math.PI;
			var m_deg = (m / 60) * 2 * Math.PI;
			var s_deg = (s / 60) * 2 * Math.PI;
			//korrektur, weil Arc rechts startet, die uhr aber oben starten soll 
			if (h_deg < 0.5 * Math.PI){
				h_deg = h_deg + 1.5 * Math.PI;
			}
			else {
				h_deg = h_deg - 0.5 * Math.PI;	
			}
			if (m_deg < 0.5 * Math.PI){
				m_deg = m_deg + 1.5 * Math.PI;
			}
			else {
				m_deg = m_deg - 0.5 * Math.PI;	
			}
			if (s_deg < 0.5 * Math.PI){
				s_deg = s_deg + 1.5 * Math.PI;
			}
			else {
				s_deg = s_deg - 0.5 * Math.PI;
			}
			try{
				canv_context.strokeStyle = "red";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3, 1.5 * Math.PI,s_deg);
				canv_context.stroke();
				canv_context.strokeStyle = "blue";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3 -10, 1.5 * Math.PI,m_deg);
				canv_context.stroke();
				canv_context.strokeStyle = "green";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3 -20, 1.5 * Math.PI,h_deg);
				canv_context.stroke();
			}catch {
				console.log("window to small to dislpay all times");
			}
		}
		else {//if timer == 00:00:00
			try{
				canv_context.strokeStyle = "red";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3,1.5*Math.PI ,1*Math.PI);
				canv_context.stroke();
				canv_context.strokeStyle = "blue";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3 -10,0 ,2*Math.PI);
				canv_context.stroke();
				canv_context.strokeStyle = "green";
				canv_context.beginPath();
				canv_context.arc(canvas.width / 2,canvas.height / 2, window.innerHeight / 3 -20,0 ,1.5*Math.PI);
				canv_context.stroke();
			}catch {
				console.log("window to small to dislpay all times at timer 1x1");
			}
		}
	}

//-------------------------------
//------ Session Handling -------
//-------------------------------
	function gui_mode(){
		//vorerst weggelassen
	}

	function cookie_write(name, value){
		var a = new Date();
		a = new Date(a.getTime() + 1000 * 60 * 60 * 24 * 365);
		document.cookie = name + "=" + value + "; expires=" + a.toGMTString() + ";";
	}

	function cookie_read(name){
		a = document.cookie;
		res = "";
		while(a != ""){
			while(a.substr(0,1) == " "){
				a = a.substr(1, a.length);
			}
			cookiename = a.substring(0,a.indexOf("="));
			if(a.indexOf(";") != -1){
				cookiewert = a.substring(a.indexOf("=")+1, a.indexOf(";"));
			}
			else{
				cookiewert = a.substr(a.indexOf("=")+1, a.length);
			}
			if(name == cookiename){
				res = cookiewert;
			}
			i = a.indexOf(";")+1;
			if(i == 0){
				i = a.length
			}
			a = a.substring(i,a.length);
		}
		return(res);
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
						TIMER_HOUR.options = 0;
						TIMER_MINUTE.options = 0;
						TIMER_SECOND.options = 0;
					break;
			}	  	
		}
	}, false);