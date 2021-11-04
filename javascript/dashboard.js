//TODO pdf export CSS

//TODO class

//TODO load button in recipies for min/max temp

//-----------------------------------------
//-------- INIT ---------------------------
//-----------------------------------------
	const TESTCLICK = document.getElementById("testbutton");
	const ADD_BUTTON = document.getElementById("add_button");
	const server = location.host;
	const home_dir = "/HomeDashboard";
	const INTERVALL_MAIN_TICKER = 1000;
	var INTERVALL_MAIN;
	INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
	let REFRESHED = false;
	
	var widgets = [];//from config.json
	const sizes = [11,12,13,14,24,44];
	var grid = [];
	var grid_v = [];//store grid for orientation change
	var grid_h = [];
	var view = "vertical";//orientation of phone / width: <600 or >600
	var mode = "show";//or move
	var widget = [];//swap
	var widget_size = [];//stores all widget constalations by widget size 
						//-> different constellation for each size(11,12,14,24,44) 
	//Presets
	var profile_name = [];
	var last_preset;

	//executes function(with value as para) when select_preset changes state
	document.querySelector('#select_preset').addEventListener("change", function() {
  		if (document.getElementById("new").value == this.value){
  			preset_action(this.value,"new");
  			document.getElementById("select_preset").value = "null";
  		}	
  		else if (document.getElementById("null").value == this.value)
  			preset_action(this.value,"null"); 			
  		else {
  			last_preset = this.value;
  			preset_action(this.value,"");
  			document.getElementById("select_preset").value = this.value;
  		}
	});

	window.addEventListener('DOMContentLoaded', init());

	function init(){
		set_Orientation();
		read_config_json();
		//get preset names
		xhttp("get_all_presets","");
		xhttp("get_preset","2");
		
		//fill temp_widget widged
		for (i=0;i<5;i++)
			widget_size[sizes[i]] = [];
	}

	var window_width = window.innerWidth;
	function interval_main_tick(){
		if (window_width <= 600){//checks if orientation has been changed
			if (window.innerWidth > 600){
				window_width = window.innerWidth;
				grid_v = grid;
				grid = grid_h;
				grid_update();
			}
		}
		else if (window_width > 600){
			if (window.innerWidth <= 600){
				window_width = window.innerWidth;
				grid_h = grid;
				grid = grid_v;
				grid_update();
			}
		}
	}			
	
	function body_onscroll(){
	  	let box = document.getElementById('body').getBoundingClientRect();
		// msg-Objekt erzeugen
		body = document.getElementById("body");
		msg = document.createElement("div");
		msg.classList.add("msg");
		msg.id = "msg";
		msg.innerHTML = "reloading...";
		body.appendChild(msg);
	  // Seite neu laden und msg für 1 Sekunde anzeigen
		if ((box.top > 100) && (REFRESHED == false)){
			REFRESHED = true;
			setTimeout (function() { 
	    	location.reload(true);
				// msg-Objekt entfernen
				body.removeChild(msg);
	  	  	}, 1000);
	   		alert("button");
			let new_gridbox = new grid_object(2,1);
			new_gridbox.create(100);
		}
		else{
			REFRESHED = false;
			body.removeChild(msg);
		}
	}

	function click_sidebar(sender){
		document.getElementById('settings-menu').classList.toggle("open");		
	}

	function set_Orientation(){
		if (window.innerWidth > 600)	
			view = "horizontal";
		else if (window.innerWidth <= 600)
			view = "vertical";
	}

//-----------------------------------------
//-------- Config -------------------------
//-----------------------------------------



function read_config_json(){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200) {
       	 	var obj = JSON.parse(this.responseText);
			console.log(obj);
			handle_config_json(obj);
    	}
	};
	xhttp.open("GET", "config.json", false);
	xhttp.send();
}

function handle_config_json(obj){ //fills html selects with names, values, sizes 
	var sensors = obj.sensor_names;
	let html = "";
	var count = 0;
	for (w=0;w<Object.keys(obj.widget).length;w++){
		if (obj.widget[w].display_name != "sensor"){
			if (obj.widget[w].display_name != "")
				html+="<option value='"+ obj.widget[w].name +"'>"+ obj.widget[w].display_name +"</option>\n";
			widgets.push({
				"name": obj.widget[w].name, 
				"display_name": obj.widget[w].display_name
			});
			if ("special" in obj.widget[w])
				widgets[Object.keys(widgets).length-1].special = obj.widget[w].special;
			widgets[Object.keys(widgets).length-1].default = obj.widget[w].default;
			count++;
		}
		else {
			for (s=0;s<Object.keys(sensors).length-1;s++){
				if (obj.widget[w].display_name != "")
					html+="<option value='"+ sensors[s].name +"'>"+ sensors[s].display_name +"</option>";
				widgets.push({
					"name": sensors[count].name,
					"display_name": sensors[count].display_name,
					"default": obj.widget[w].default
				});
				count++;
			}
		}
	}
	console.log(widgets);
	document.getElementById("select_type").innerHTML += html; 
	html ="";
	for (i=0;i<sizes.length;i++)
		html += "<option value='"+sizes[i]+"'>"+Math.round(sizes[i]/10)+"x"+sizes[i]%10+"</option>";
	document.getElementById("select_size").innerHTML += html; 
}

//-----------------------------------------
//-------- Message Handling ---------------
//-----------------------------------------

	window.addEventListener('message', e => {
	  	if (e.origin == "http://"+location.host){
			let data_str = e.data.split(" ");
			switch (data_str[1]){
				//move with arrows
				case "click_up":
					click_up(data_str[0]);
					break;
				case "click_left":
					click_left(data_str[0]);
					break;
				case "click_right":
					click_right(data_str[0]);
					break;
				case "click_down":
					click_down(data_str[0]);
					break;  		
				case "click_ok":
					click_ok(data_str[0]);
					break;
				case "click_delete":
					click_delete(data_str[0]);
					break;
				//widget
				case "set_widget": //ssxxxxx s:size x:data
					let size = parseInt(String(data_str[0][0])+String(data_str[0][1]));
					widget_size[size] = data_str[0];	
					break;
			}	  	
		}
	}, false);

//-----------------------------------------
//-------- Grid Object --------------------
//-----------------------------------------     

	function select_add_element() {
		//create new element from select.values after <Add> was pressed 
		let size = document.getElementById("select_size").value;
		let type = document.getElementById("select_type").value;
		if (type === "dummy")
			size = "11";
		else if (view === "horizontal" && (size === "24" || size === "44"))
			alert("Big ones doesn't work properly in horizontal mode, yet.");
		else 
			add_element(type,size);
	 	move_big_to_left(0);
	 }

	 function add_element(type,size){
	 	var gap = 0;
	 	if (size != "null" && type != "null") {
			//filling overlaps with dummies			
			if (size === "12"){
				if (grid[grid.length-1].stop % 4 === 3 && view === "vertical")
					gap++;
				else if (grid[grid.length-1].stop % 8 === 7 && view === "horizontal")
					gap++;
			}
			else if (size === "13"){
				if (view === "vertical"){
					if (grid[grid.length-1].stop % 4 === 2)
						gap+=2;
					else if (grid[grid.length-1].stop % 4 === 3)
						gap++;
				}
				else if (view === "horizontal"){
					if (grid[grid.length-1].stop % 8 === 6)
						gap+=2;
					else if (grid[grid.length-1].stop % 8 === 7)
						gap++;
				}
			}
			else if (size === "14" || size === "24" || size === "44"){
				//loop for all positions, where element couldn't fit in (fill with dummies)
				for (modulo_value=1;modulo_value<4;modulo_value++){
					if (view === "vertical" && (grid[grid.length-1].stop % 4 === modulo_value))
						gap++;
					else if (view === "horizontal" && (grid[grid.length-1].stop % 8 > 4))
						gap++;
				}
				if (view === "horizontal" && (size === "44" && grid[grid.length-1].stop > 4 || size === "24" && grid[grid.length-1].stop > 20)){
					alert("This widget doesn't fit in anymore.");
					return;
				}
			}
			console.log(gap);
			for (i=0;i<gap;i++)//fill gaps with dummies
				grid_push("dummy","11");

			//Adding new Grid Object
			grid_push(type,size);
	 	}
		else
	 		alert("you need to select \'type\' and \'size\' before you can add");
	 }

	function grid_push(name,size){ //TODO eventuell über class lösen -> wegfall des push, update loops 
		console.log("create: "+name+" "+size);
		grid.push({
			"name": name,
			"size": size,
			"start": 0,//gets updated later
			"stop": 0,
			"pos_v": grid.length,
			"pos_h": grid.length
		});
		grid_update();
	}


	function fill_gaps(gap){//fills gaps with dummies
		for (i=0;i<gap.length;i++){
			if (gap[i] != ""){//i:entspricht id vor lücke
				for (j=0;j<gap[i];j++){//wdh so oft, wie es lücken gibt
					for (all=i;all<grid.length;all++){//alle verscchieben von lücke bis ende
						if (view === "vertical")
							grid[all].pos_v++;
						else if (view === "horizontal")
							grid[all].pos_h++;
					}
					grid.push({
						"name": "dummy",
						"size": "11",
						"start": 0,
						"stop": 0,
						"pos_v":i,
						"pos_h":i
					});
				}
			}
		}
		grid = sort_asc(grid);
	}

	function grid_update(){
		let grid_size = 1;
		let grid_pos = 1;
		let gap = [];
		var modulo = 4;
		set_Orientation();			
		grid = sort_asc(grid);
		console.log(JSON.stringify(grid));
						
		//errechnen der start und stop werte der Objekte	
		for (i=0;i<grid.length;i++){
			//for "11","12","14","24","44" objects
			grid_size = Number(grid[i].size[0])*Number(grid[i].size[1]);
			if (view === "vertical")
				modulo = 4;
			else if (view === "horizontal")
				modulo = 8;
			if (grid_pos % modulo === 0 && grid_size >= 2)
				gap[i] = 1;
			else if (grid_pos % modulo > 5 % modulo && grid_size > 3 || grid_size === 3 && grid_pos % modulo > 6 % modulo)
				gap[i] = 5 - (grid_pos % 4);
			else
				gap[i] = 0;
			grid_pos += grid_size;
			grid_pos += gap[i];		
			
			//start and end coord of an element
			grid[i].stop = grid_pos-1;
			grid[i].start = grid_pos - grid_size;
		}
		fill_gaps(gap);
		console.log(JSON.stringify(grid));
		if (grid[grid.length-1].stop <= 32)
			render();
		else { 
			alert("To big for current config!\nDelete existing elements to get enough space.")
			grid = grid.slice(0,-1);	
		}
		console.log(grid);
	}

	function sort_asc(obj_arr){
		//view is the para by wich is sorted
		if (view === "vertical"){
			result = obj_arr.sort(function(obj1,obj2){//sorts for vertical
				return obj1.pos_v - obj2.pos_v;
			});
			for (i=0;i<obj_arr.length-1;i++){//removes gaps in order
				if (obj_arr[i].pos_v+1 != obj_arr[i+1].pos_v)
					obj_arr[i+1].pos_v--;
			}
		}
		else if (view === "horizontal"){
			result = obj_arr.sort(function(obj1,obj2){//sorts for horizontal
				return obj1.pos_h - obj2.pos_h;
			});
			for (i=0;i<obj_arr.length-1;i++){//removes gaps in order
				if (obj_arr[i].pos_h+1 != obj_arr[i+1].pos_h)
					obj_arr[i+1].pos_h--;
			}
		}
		return result;//returns sorted object
	}

//-----------------------------------------
//-------- HTML ---------------------------
//-----------------------------------------     
	
	function render() {
		//clear html
		document.getElementById("container").innerHTML = "";
		var html = "";
		var note = false;
		//create html String
		for (i=0;i<grid.length;i++){
			html += createHTML(grid[i].size,i,grid[i].name);
			if (grid[i].name === "notes")
				note = true;		
		}
		document.getElementById("container").innerHTML = html;
		if (note && mode != "move"){
			InlineEditor							//TODO Notes as working grid element, currently are not interacting with grid 
				.create( document.querySelector('#editor')) //TODO notizen in cookie oder ähnliches
				.catch( error => {
				console.error( 'There was a problem initializing the editor.', error );
			});
			note = false;
		}
	
	}

	function createHTML(size,pos,type){	//TODO besseres Temp_Widget bauen
		let scrolling = "no";
		let source = "/HomeDashboard/dashboard/";
		if (mode === "move")
			type = "move";
		//---div for grid---
		html = "<div id=\"" + pos + "\" ";

		if (type === "settings"){
			html += "class=\"tile tile_" + size + " grid_object menu-toggle\"";
			html += "onclick=\"click_sidebar();\"><img src=\"images/btn_edit.svg\" style=\"width: 4rem;height:4rem;\">";
		}
		else {
			for (w=0;w<Object.keys(widgets).length;w++){
				if (type === widgets[w].name){
					if (!("sizes" in widgets[w].default) || widgets[w].default.sizes.includes(size))
						source += widgets[w].default.filename;//size equals spec for default widget
					else if ("special" in widgets[w] && widgets[w].special.sizes.includes(size))
						source += widgets[w].special.filename;//size equals spec for special widget		
					else if ("sizes" in widgets[w].default && !(widgets[w].default.sizes.includes(size))){
						source += widgets[w].default.filename;//no fitting size
						for (d in sizes){//sets size to first allowed size
							if (widgets[w].default.sizes.includes(d)){
								size = sizes[d];
								break;
							}
						}
					}
					if ("scrolling" in widgets[w].default && widgets[w].default.scrolling == "yes")
						scrolling = "yes";
					if ("type" in widgets[w].default){
						switch (widgets[w].default.type){
							case "temp":
								source += "?sensor="+widgets[w].name;
								break;
							case "move":
								source +="?id="+ pos +"&name="+grid[pos].name;
								break;
							case "url":
								source = widgets[w].default.filename;
								break;
						} 
					}
					switch (type){
							//Custom Widget HTML
						case "notes":
							html += "class=\"tile tile_" + size + " grid_item\"><div class='editor' id='editor'><p>Notes</p></div";	
							break;
						case "dummy":
							html += "class=\"tile tile_11 dummy\">"
							break;
						default:
							//Standart Widget HTML
							html += "class=\"tile tile_" + size + " grid_item\"";	
							html += "><iframe scrolling='"+ scrolling+"' class=\"iframe\" id=\"iframe\" src=\"" + source + "\"></iframe>";
					}
					break;
				}	
			}
		}
		html += "</div>";	
		return html;

		/*Legacy code 
			case "temp_widget":
				//set widget values
				widget = widget_size[parseInt(size)];
				widget[0] = String(size[0]);
				widget[1] = String(size[1]);
				var payload = "";
				for (i=0;i<7;i++){
					if (widget[i] == null || widget[i] == "undefined")
						widget[i] = "1";
					widget[6] = "0";
					payload += widget[i];
				}//format ssxxxxx s:size x:visible temps
				source = "dashboard/odk_temperature_widget.php?set="+payload;
				break;
		*/
	}

//------------------------------------
//-------- Presets -------------------
//------------------------------------

	function xhttp(request, profile_num){
		var xhttp = new XMLHttpRequest();
		var json_response = "";
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				json_response = this.responseText;
				response_handler(json_response, request);
			}
			else {
				if (this.status > 399)
					console.log("error or wrong response code");
			}
		}
		try{
			xhttp.open("GET", "http://"+server+home_dir+"/json_handler_user_agent.php?json=" + create_request(request,profile_num), false);
			xhttp.send();
		}
		catch (err){
			console.log("Request Error: "+err);
		}
	}

	function create_request(request,profile_num){
		//create json_string for request
		var json_string = "{\"event\":\""+request+"\""; 
		switch (request){
			case "set_new_preset": //set new and save
				json_string += ",\"profile_id\":\""+ profile_num +"\"";//range 1 to 9
				json_string += ",\"profile_name\":\"" + profile_name[profile_num] +"\"";
				json_string += ",\"grid_object_v\":"+ JSON.stringify(grid_v) +"";
				json_string += ",\"grid_object_h\":"+ JSON.stringify(grid_h) +"";
				break;
			case "delete_preset": //delete
				json_string += ",\"profile_id\":\""+ profile_num +"\"";
				break;
			case "get_all_presets": //returns all preset names
				break;
			case "get_preset": //returns all preset values of preset
				json_string += ",\"profile_id\":\""+ profile_num +"\"";
				break;
		}
		json_string += "}";
		return json_string;
	}
	
	function response_handler(json_response, request){
		if (request == "get_preset"){
			//set the response values in active dashboard
			var response = JSON.parse(json_response);
			if (response["grid_object_v"] != "" && response["grid_object_v"] != null && 
				response["grid_object_h"] != "" && response["grid_object_h"] != null){
				grid_v = response["grid_object_v"];
				grid_h = response["grid_object_h"];
			}
			else
				console.log("bad response: recieved empty 'grid_object'");
			if (view === "vertical")
				grid = grid_v;
			if (view === "horizontal")
				grid = grid_h;
			grid_update();
		}
		else if (request == "get_all_presets")
			createHTML_for_presets(json_response);
		else 
			xhttp("get_all_presets","");
	}

	function createHTML_for_presets(json_response){
		var response = JSON.parse(json_response);
		var html = "<option value='null' id='null'>- select preset -</option>";
		for (i=1;i<10;i++){
			if (response[i] != "" && response[i] != null) {
				profile_name[i] = response[i];
				html += "<option value='"+i+"' id='element' >"+ response[i] +"</option>";
			}
			else {
				html += "<option value='"+i+"' id='new'>save as new preset</option>";
				break;
			}
		}
		document.getElementById("select_preset").innerHTML = html;
	}

	//if preset <select> changed
	function preset_action(value,name){
		switch(name){
			case "null":
				break;
			case "new":
				profile_name[value] = prompt("Bitte einen Namen für das Preset eingeben!"); 
				if (profile_name[value] != null && profile_name != "")
					xhttp("set_new_preset",value);
				else 
					delete profile_name[value];
				break;
			case "delete":
				if (last_preset != 0){
					xhttp("delete_preset",last_preset);
					last_preset=0;
					document.getElementById("select_preset").value = "null";
				}
				break;
			case "save":
				if (last_preset != 0)
					xhttp("set_new_preset",last_preset);
				break;
			case "load":
				if (last_preset != 0)
					xhttp("get_preset",last_preset);
				break;	
		}
	}