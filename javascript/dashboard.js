//TODO pdf export CSS

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
			console.log(i+" + grid_size"+grid_size+"  "+modulo+" gridpos: "+grid_pos);
			
			//for "11","12","14","24","44" objects
			grid_size = Number(grid[i].size[0])*Number(grid[i].size[1]);
			if (view === "vertical")
				modulo = 4;
			else if (view === "horizontal")
				modulo = 8;
			if (grid_pos % modulo === 0 && grid_size >= 2){
				gap[i] = 1;
			
				console.log(i+"1");
			}
			else if (grid_pos % modulo > 5 % modulo && grid_size > 3 || grid_size === 3 && grid_pos % modulo > 6 % modulo){
				gap[i] = 5 - (grid_pos % 4);
			
				console.log(i+"2");
			}
			else{
				gap[i] = 0;
				console.log(i);
			}
			grid_pos += grid_size;
			grid_pos += gap[i];		
			
			//start and end coord of an element
			grid[i].stop = grid_pos-1;
			grid[i].start = grid_pos - grid_size;
		}
		console.log(gap);
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
					console.log("type: "+type+" size: "+size+" w: "+w+"  ");
					console.log(!"sizes" in widgets[w].default);
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
					if ("scrolling" in widgets[w] && widgets[w].scrolling === "yes")
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
							html += "><iframe scrolling="+ scrolling+" class=\"iframe\" id=\"iframe\" src=\"" + source + "\"></iframe>";
					}
					break;
				}	
			}
		}
		html += "</div>";	
		return html;

		/*case "temp_widget":
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
		console.log("response debug");
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

//-----------------------------------------
//-------- MOVE ---------------------------
//-----------------------------------------

	function swap(pos){//swaps in order pos[0] to pos[pos.length-1]
		console.log(pos);
		for (i=0;i<pos.length;i++){
			pos[i] = Number(pos[i]);
		}
		if (view === "vertical"){
			var swap_1 = grid[pos[0]].pos_v;
			for (i=0;i<pos.length-1;i++)
				grid[pos[i]].pos_v = grid[pos[i+1]].pos_v;
			grid[pos[pos.length-1]].pos_v = swap_1;
		}
		else if (view === "horizontal"){
			var swap_1 = grid[pos[0]].pos_h;
			for (i=0;i<pos.length-1;i++)
				grid[pos[i]].pos_h = grid[pos[i+1]].pos_h;
			grid[pos[pos.length-1]].pos_h = swap_1;
		}
	}

	function gridsize_at_id(size_,id_){
		for (s in size_){//more than one returns disjunct
	
			if (grid[id_].size === size_[s]){
			
				return true;
			}
		}
		return false;
	}

	//---- UP ----
	function click_up(id){ //TODO vertical moves 
		id = Number(id);
		var changed = false;
		if (view === "vertical" && id != 0){
			if (gridsize_at_id(["14","24","44"],id)){
				changed = true;
				switch(grid[id-1].size){
					case "14","24","44":
						swap([id, id-1]);//big one over big one	
						break;
					case "11":
						if (grid[id-2].size === "11" && grid[id-3].size === "11")
							swap([id,id-4,id-3,id-2,id-1]);//"11","11","11","11"
						else if(grid[id-2].size === "13")
							swap([id,id-2,id-1]);//11,13
						else
							swap([id,id-3,id-2,id-1]);//"12","11","11" and //"11","12","11"
						break;
					case "12":
						if (grid[id-2].size === "12")
							swap([id,id-2,id-1]);//"12","12"
						else
							swap([id,id-3,id-2,id-1]);//"11","11","12"
						break;
					case "13":
						swap([id,id-2,id-1]);//11,13
						break;
					default:
						changed = false;
				}
			}
			for (ele=id;ele>=0;ele--){//until 1 because size of grid isnt involved and it cant be below 0
				if (grid[id].start-4 === grid[ele].start){//ele is above id
					if (grid[id].size === grid[ele].size){
						changed = true;
						swap([id,ele]);
						console.log("same ele");
					}
					switch(grid[id].size){
						case "11":
							changed = true;
							if (grid[ele].size === "12" && grid[id+1].size === "11" ||
								gridsize_at_id(["14","24","44"],ele) && grid[id].size === "13" ||
								grid[ele].size === "13" && grid[id+1].size === "12"){
								let swap1 = grid[id].pos_v;//"12"over"11","11" or "13"over "11","12" or big over "11","13" 
								for (i=0;i<id-ele-1;i++)
									grid[id-i].pos_v = grid[id-1-i].pos_v;
								grid[Number(ele)+1].pos_v = grid[id+1].pos_v;
								grid[id+1].pos_v = grid[ele].pos_v;
								grid[ele].pos_v = swap1;
							}			
							else if(grid[ele].size === "13" && grid[id+1].size === "11" && grid[id+2].size === "11")
								swap([ele,id+2,id+1,id]);
							else if(gridsize_at_id(["14","24","44"],ele) && grid[id+1].size === "11" && grid[id+2].size === "12"||
									gridsize_at_id(["14","24","44"],ele) && grid[id+1].size === "12" && grid[id+2].size === "11"){
								swap([ele,id+2,id]);
								swap([id-1,id+1]);
							}	
							else
								changed = false;
							break;
						case "12":
							if (grid[ele].size === "11" && grid[Number(ele)+1].size === "11"){
								changed = true;//"11","11"over"12"
								let swap1 = grid[id].pos_v;
								grid[id].pos_v = grid[Number(ele)+1].pos_v;
								for (i=1;i<id-ele-1;i++)
									grid[Number(ele)+i].pos_v = grid[Number(ele)+i+1].pos_v;
								grid[id-1].pos_v = grid[ele].pos_v;
								grid[ele].pos_v = swap1;
							}
							else if(grid[ele].size === "11" && grid[Number(ele)+1].size === "12" && grid[id+1].size === "11" ||
									grid[ele].size === "11" && grid[Number(ele)+1].size === "13" && grid[id+1].size === "12"){
									swap([ele,id]);
									swap([ele+1,id+1]);
							}
							else if (grid[ele].size === "14" && grid[id+1].size === "11" && grid[id+2] === "11")
								swap([ele,id+2,id+1,id]);
							else if (grid[ele].size === "14" && grid[id+1].size === "12")
								swap([ele,id+1,id]);
							else if (grid[ele].size === "13" && grid[id+1].size === "11")
								swap([ele,id+1,id-1,id]);
							break;
						case "13":
							if (grid[id-1].size === "11"){
								changed = true;
								if (id >= 4 && grid[id-2].size === "11" && grid[id-3].size === "11" && grid[id-4].size === "11"){
									swap([id-2,ele,id]);
									swap([id-1,Number(ele)+1]);
								}
								else if (id >= 3 && (grid[id-2].size === "12" && grid[id-3].size === "11" || grid[id-2].size === "11" && grid[id-3].size === "12"))
									swap([ele,id-1,Number(ele)+1,id]);
								else changed = false;
							}
							else if (grid[ele].size === "14" && grid[id+1].size === "11")
								swap([ele,id+1,id]);
							break;
					}
				}
				else if (!changed && (grid[id].start != 1 && grid[id].start-5 === grid[ele].start || 
									  (grid[id].start == 3 || grid[id].start % 4 == 0) && grid[id].start-6 === grid[ele].start || 
									  grid[id].start == 0 && grid[id].start-7 === grid[ele].start)){
					click_up(id-1);
					console.log("loop");
					changed = false;
				}
			}
		}
		else if (view === "horizontal"){
			for (ele=id;ele>=0;ele--){
				console.log("ele_c: "+ele);
				if (grid[id].start-8 === grid[ele].start){	
					click_down(ele);
					ele=-1;
				}
				else if(grid[id].start-9 === grid[ele].start){
					click_down(ele);
					ele=-1;
				}	
				else if(grid[id].start-10 === grid[ele].start){
					click_down(ele);
					ele=-1;
				}	
				else if(grid[id].start-11 === grid[ele].start){
					click_down(ele);
					ele=-1;
				}	
			}
		}
		if (changed)
			grid_update();
	}

	//---- Down ----
	function click_down(id){	
		console.log("down");
		id = Number(id);
		var changed = false;
		if (view === "vertical"){
			var num = get_row_below(id);//amount of items in next row
			var start = start_of_next_row(id);
			if (id < grid.length-1){
				if (grid[id].size === "14" || grid[id].size === "24" ||grid[id].size === "44"){
				 	changed = true;
				 	if (num === 1)
				 		swap([id,id+1]); 
				 	else if (num === 2)
						swap([id,id+2,id+1]);
					else if (num === 3)//"12","11","11"
						swap([id,id+3,id+2,id+1]);
					else if (num === 4)
				 		swap([id,id+4,id+3,id+2,id+1]);
					else 
					 	changed = false;
				}
				else {
					for (ele=id;ele<grid.length;ele++){
						if (grid[ele].start-4 === grid[id].start){
				 			changed = true;	
							if (grid[ele].size === grid[id].size)
								swap([id,ele]);//"11" over "11" or "12"over "12"
							else 
								click_up(ele);
						}
						else if (grid[id].start === grid[ele].start-3 && grid[id].size === "11" && grid[id-1].size === "11" && grid[ele].size === "12"){
							click_down(id-1);//"11","11" over "12" and theh right one pressed
							changed = false;					
						}
						else if (grid[ele].stop-4 === grid[id].stop && grid[id].size === "13" && grid[id+1].size === "11"){
							changed = true;						
							if (grid.length > id+3 && grid[id+2].size == "12" && grid[id+3].size == "11" || grid[id+2].size == "11" && grid[id+3].size == "12")
								swap([id+1,ele-1,id,ele]);//13 over \n 	"11","12"or"12","11"
							else if (grid.length > id+4 && grid[id+2].size == "11" && grid[id+3].size == "11" && grid[id+4].size == "11" ){
								swap([id+1,ele-1]);//13 over \n 3x"11"
								swap([id+2,id,ele]);
							}
							else changed = false;
						}
					} 
				}
			}
		}
		else if (view === "horizontal"){
			for (ele=id;ele<grid.length;ele++){
				if (grid[id].start === grid[ele].start-8){//id above ele 
					console.log("normal"+ele);
					changed = true;
					if (grid[id].size === grid[ele].size){
						swap([ele,id]);
					}
			//------11------
					else if (grid[id].size === "11" && (grid[ele].size === "12" && grid[id+1].size === "11" || grid[ele].size === "13" && grid[id+1].size === "12" || grid[ele].size === "14" && grid[id+1].size === "13")){ 
						//11,11 \n 12 --or-- 11,12 \n 13
						console.log("11,11,12/11,12,13");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele-1].pos_h;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos_h = grid[ele-1-i].pos_h;
						grid[id+1].pos_h = swap1;
					}
					else if (grid[id].size === "11" && (grid[ele].size === "13" && grid[id+1].size === "11" && grid[id+2].size === "11" || grid[ele].size === "14" && grid[id+1].size === "12" && grid[id+2].size === "11" || grid[ele].size === "14" && grid[id+1].size === "11" && grid[id+2].size === "12")){
						//3x11 \n 13 --or-- 11,12,11 \n 14 --or-- 11,11,12 \n 14
						console.log("11,11,11,13");
						let swap1 = grid[id].pos_h;
						let swap2 = grid[ele-1].pos_h;
						grid[id].pos_h = grid[ele-2].pos_h;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos_h = grid[ele-i-2].pos_h;
						}
						grid[id+1].pos_h = swap2;
						grid[id+2].pos_h = grid[ele].pos_h;
						grid[ele].pos_h = swap1;
					}
					else if (grid[id].size === "11" && grid[ele].size === "14" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+3].size === "11"){
						//4x11 \n 14
						console.log("11,11,11,11,14");
						let swap1 = grid[id].pos_h;
						let swap2 = grid[ele-1].pos_h;
						let swap3 = grid[ele-2].pos_h;
						grid[id].pos_h = grid[ele-3].pos_h;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos_h = grid[ele-i-3].pos_h;
						}
						grid[id+1].pos_h = swap3;
						grid[id+2].pos_h = swap2;
						grid[id+3].pos_h = grid[ele].pos_h;
						grid[ele].pos_h = swap1;
					}
			//------12------	
					else if (grid[id].size === "12" && grid[ele].size === "11" && grid[ele+1].size === "11"){
						//"12"over "11","11"
						console.log("12,11,11");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele+1].pos_h;
						grid[ele+1].pos_h = grid[id+1].pos_h;
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos_h = grid[id+1+i].pos_h;
						grid[ele-1].pos_h = swap1;
					}
					else if (grid[id].size === "12" && (grid[id+1].size === "11" && grid[ele].size === "13" || grid[id+1].size === "12" && grid[ele].size === "14")){
						//12,11 \n 13 --or-- 12,12,14
						console.log("12,11,13/12,12,14");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele-1].pos_h;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos_h = grid[ele-1-i].pos_h;
						grid[id+1].pos_h = swap1;
					}
					else if (grid[id].size === "12" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[ele].size === "14"){
					//12,11,11 \n 14
						console.log("12,11,11,14");
						let swap1 = grid[id].pos_h;
						let swap2 = grid[ele-1].pos_h;
						grid[id].pos_h = grid[ele-2].pos_h;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos_h = grid[ele-i-2].pos_h;
						}
						grid[id+1].pos_h = swap2;
						grid[id+2].pos_h = grid[ele].pos_h;
						grid[ele].pos_h = swap1;
					}
			//------13------		
					else if (grid[id].size === "13" && (grid[ele].size === "12" && grid[ele+1].size === "11") || (grid[ele].size === "11" && grid[ele+1].size === "12")){
						//13 over "12","11" or "11","12"
						console.log("13, 12,11/ 13, 11,12");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele+1].pos_h;
						grid[ele+1].pos_h = grid[id+1].pos_h;
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos_h = grid[id+1+i].pos_h;
						grid[ele-1].pos_h = swap1;
					}
					else if (grid[id].size === "13" && (grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "11")){
						//"13" over 3x"11"
						console.log(13, 11,11,11);
						let swap1 = grid[id].pos_h;
						grid[id].pos_h = grid[ele+2].pos_h;
						grid[ele+2].pos_h = grid[id+2].pos_h;	
						let swap2 = grid[id+1].pos_h;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos_h = grid[id+i+2].pos_h;
						grid[ele].pos_h = swap1;
						grid[ele+1].pos_h = swap2;
					}
					else if (grid[id].size === "13" && grid[id+1].size === "11" && grid[ele].size === "14"){
						//13,11 \n 14
						console.log("13,11,14");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele-1].pos_h;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos_h = grid[ele-1-i].pos_h;
						grid[id+1].pos_h = swap1;
					}
			//------14------
					else if (grid[id].size === "14" && 
						(grid[ele].size === "12" && grid[ele+1].size === "12"||
						grid[ele].size === "13" && grid[ele+1].size === "11" || 
						grid[ele].size === "11" && grid[ele+1].size === "13")){
						//14 \n 12,12 --or-- 14 \n 13,11 --or 14 \n 11,13
						console.log("14, 12,12 / 14,13,11 / 14,11,13");
						let swap1 = grid[ele].pos_h;
						grid[ele].pos_h = grid[id].pos_h;
						grid[id].pos_h = grid[ele+1].pos_h;
						grid[ele+1].pos_h = grid[id+1].pos_h;s
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos_h = grid[id+1+i].pos_h;
						grid[ele-1].pos_h = swap1;
					}
					else if (grid[id].size === "14" && 
							(grid[ele].size === "11" && grid[ele+1].size === "12" && grid[ele+2].size === "11" ||
							grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "12" ||
							grid[ele].size === "12" && grid[ele+1].size === "11" && grid[ele+2].size === "11")){
						//14 \n 11,12,11 --or-- 14 \n 11,11,12 --or-- 14 \n 12,11,11
						console.log("14, 11,12,11...");
						let swap1 = grid[id].pos_h;
						grid[id].pos_h = grid[ele+2].pos_h;
						grid[ele+2].pos_h = grid[id+2].pos_h;	
						let swap2 = grid[id+1].pos_h;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos_h = grid[id+i+2].pos_h;
						grid[ele].pos_h = swap1;
						grid[ele+1].pos_h = swap2;
					}
					else if (grid[id].size === "14" && grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "11" && grid[ele+3].size === "11"){
						//14 \n 11,11,11,11
						console.log("14, 11,11,11,11");
						let swap1 = grid[id].pos_h;
						let swap2 = grid[id+1].pos_h;
						let swap3 = grid[id+2].pos_h;
						let swap4 = grid[id+3].pos_h;
						grid[id].pos_h = grid[ele+3].pos_h;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos_h = grid[id+i+3].pos_h;
						grid[ele].pos_h = swap1;
						grid[ele+1].pos_h = swap2;
						grid[ele+2].pos_h = swap3;
						grid[ele+3].pos_h = swap4;
					}
					else 
						changed = false;
				}
			}
			if (!changed){
				for (ele=grid.length-1;ele>id;ele--){
					console.log("offset ele: "+ele)
				//---not left one clicked but above---
					var id_col = grid[id].start;
					var ele_col = grid[ele].start;
					if (id_col === ele_col-7 && id_col % 8 != 1){
						if (grid[id].size === "11" && 
								(grid[id-1].size === "11" && grid[ele].size === "12" || 
								grid[id-1].size === "11" && grid[id+1].size === "12" && grid[ele].size === "14" || 
								grid[id-1].size === "11" && grid[id+1].size === "11" && grid[ele].size === "13"||
								grid[id-1].size === "11" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[ele].size === "14") ||
							grid[id].size === "12" && 
								(grid[id-1].size === "11" && grid[ele].size === "13" ||
								grid[id-1].size === "11" && grid[id+1].size === "11" && grid[ele].size === "14") ||
							grid[id].size === "13" && 
								grid[id-1].size === "11" && grid[ele].size === "14"){
							console.log("yes");
							click_down(id-1);//11,11\12 --or-- 11,12\13 --or-- 11,13\14 --or-- 11,12,11\14 --or-- 11,11,12\14 --or-- 11,11,11\13 --or-- 11,11,11,11\14
						}
					}
					else if (id_col === ele_col-6 && id_col % 8 != 1){
						if (grid[id-1].size === "12" && (grid[id].size === "11" && grid[ele].size === "13" || grid[id].size === "12" && grid[ele].size === "14" || grid[id].size === "11" && grid[id+1].size === "11" && grid[ele].size=== "14"))
							click_down(id-1);//12,11\13 --or-- 12,12\14 --or-- 12,11,11\14
						else if (grid[id-1].start % 8 != 1 && grid[id-2].start != 8 &&
							grid[id-1].size === "11" && grid[id-2].size === "11" && 
								(grid[id].size === "12" && grid[ele].size === "14" ||
								grid[id].size === "11" && grid[ele].size === "13" || 
								grid[id].size === "11" && grid[id+1].size === "11" && grid[ele].size === "14"))
							click_down(id-2);//11,11,12\14 --or-- 11,11,11\13 --or-- 11,11,11,11\14
					}
					else if (id_col === ele_col-5 && id_col % 8 != 1){
						console.log("1");
						if (grid[id-1].size === "13" && grid[ele].size === "14")
							click_down(id-1);//13,11\14 
						else if (grid[id-1].start % 8 != 1 && grid[id].size === "11" && (grid[id-1].size === "12" && grid[id-2].size === "11" || grid[id-1].size === "11" && grid[id-2].size === "12" ))
							click_down(id-2);//11,12,11\14 --or-- 12,11,11\14
						else if (grid[id-1].start % 8 != 1 && grid[id-2].start % 8 != 1 && grid[id].size === "11" && grid[id-1].size === "11" && grid[id-2].size === "11" && grid[id-3].size === "11" && grid[ele].size === "14")
						 	click_down(id-3);
					}						
				}
			}
		}
						
		if (changed)
			grid_update();
		
	}

	function start_of_next_row(id){	//checks for each first position of a row:i 
		for (i=1;i<9;i++){			//if it starts higher than the element at the id stops 
			if (grid[id].stop < 4*i+1){
				return 4*i+1;
				break;
			}
		}
	}

	function get_row_below(id){//row under the last -> id is higher
		var row_items = 0;
		var start = start_of_next_row(id);
		for (i=id+1;i<Number(grid.length);i++){
			for (s=Number(start);s<Number(start)+4;s++){//next row grids(1-4) 
				if (grid[i].start == s)//checks if start position of any element eqls item of next row
					row_items++;//counts how many items are in next row
			}
		}
		return row_items;
	}

	function move_big_to_left(id){//not finished at all
		//view needs to be horizontal
		var obj = [];
		for (i=0;i<grid.length;i++){
			var count = Number(grid[i].size[0]) * Number(grid[i].size[1]);
			obj[obj.length] = grid[i].size;
			let start = obj.length;
			for (k=start;k<start+count-1;k++)
				obj[k] = "";	
			
		}
		//a grid of coordinates and wich feilds are starting points of grid objects
		console.log(obj);
		
		if (grid[id].size === "24"){
			switch(grid[id].start % 8){
				case 1: 
					if (grid[id].start > 16){

						if (grid[id-1].size === "24"){}
						if (grid[id-1].size === "44"){}
						if (grid[id-1].size === "24"){}

					}
					break;
				//case,case...
			}
		}
		else if (grid[id].size === "44"){
			//...
		}
	}

	//---- left ----
	function click_left(id){
		id = Number(id);
		if (id > 0){
			if (view === "vertical"){
				if (grid[id].size === "14" || grid[id].size === "24" || grid[id].size === "44")
					click_up(id);
			 	var changed = true;
			 	if ((grid[id].size === grid[id-1].size) ||//2x gleich
			 		(grid[id].size === "11" && grid[id-1].size === "12" && grid[id].start % 4 != 1) ||//"12","11" and "11" not completely left
			 		(grid[id].size === "12" && grid[id-1].size === "11" && grid[id].start % 4 != 1))//"11","12" and "12" not completely left
					swap([id,id-1]);
				else if (id > 2 && grid[id].size === "12" && grid[id-1].size === "11" && grid[id-2].size === "11")
					swap([id-2, id-1, id]);//id 12 and left or the right 2 in row above are 11
				else if (id > 1 && grid[id].size === "11" && grid[id+1].size === "11" && grid[id-1].size === "12")	
					swap([id+1,id,id-1]);//"12"right above "11"at id and another "11"right from id
				else if (grid[id].size === "13")
					click_right(id-1);
				else if (grid[id].size === "11" && grid[id-1].size === "13")
					swap([id,id-1]);//"11","12"
				else
					changed = false;
			}
			else if (view === "horizontal"){
				click_right(id-1);//TODO f.e. "44" jump
			}
			if (changed) 
				grid_update();
		}
	}

	//---- right ----
	function click_right(id){
		id = Number(id);
		var changed = false;
		if (view === "vertical"){
			if (id < grid.length-1){
				if (grid[id].size === "14" || grid[id].size === "24" || grid[id].size === "44")
					click_down(id);
				else if (id < grid.length){//just for less loopthrough
				 	changed = true;
					if ((grid[id].size === "13" && grid[id+1].size === "11" || grid[id].size === "11" && grid[id+1].size === "13") && grid[id].start % 4 === 1)
						swap([id+1,id]);//"13","11" or "11","13"
				 	else if (grid[id].size === "13" && grid[id].stop % 4 === 0 && grid[id+1].size === "11" &&  grid[id+2].size === "11" && grid[id+3].size === "11")
				 		swap([id,id+3,id+2,id+1]);//13,11,11,11
				 	else if (grid[id].size === "13" && grid[id].stop % 4 === 0 && (
				 		grid[id+1].size === "11" &&  grid[id+2].size === "12" ||//13,11,12
				 		grid[id+1].size === "12" &&  grid[id+2].size === "11"))//13,12,11
				 		swap([id,id+2,id+1]);
				 	else if ((grid[id].size === grid[id+1].size) || (grid[id].size === "11" && grid[id+1].size === "12" && grid[id].stop % 4 != 0) || (grid[id].size === "12" && grid[id+1].size === "11" && grid[id].stop % 4 != 0))	
						swap([id+1,id]);//if left right one or row above right one are the same size
					else if (grid[id].size === "12" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+1].start % 4 === 1)
						swap([id+2,id+1,id]);//id 12 and left or the right 2 in row above are 11
					else if (grid[id].size === "11" && grid[id+1].size === "12" && grid[id-1].size === "11" && grid[id+1].start % 4 === 1)
						swap([id-1,id,id+1]);// id "11"left "11"and below left "12"
					else if (grid[id].size === "11" && grid[id].start % 4 === 0 && grid[id-1].size === "11" && grid[id-2].size === "11" && grid[id+1].size === "13")
						swap([id+1,id-2,id-1,id]);
					else if(grid[id+1].size === "13" && grid[id].stop % 4 === 0 && (grid[id].size === "11" && grid[id-1].size === "12" || grid[id].size === "12" && grid[id-1].size === "11"))
						swap([id+1,id-1,id]);
					else 
						changed = false;		
				}
			}
		}
		else if (view === "horizontal"){
			if (grid[id].size === grid[id+1].size ||
				(grid[id].stop % 8 != 0 && 
				(grid[id].size === "11"|| grid[id].size === "12" || grid[id].size === "13" || grid[id].size === "14") && 
				(grid[id+1].size === "11"|| grid[id+1].size === "12" || grid[id+1].size === "13" || grid[id+1].size === "14"))){
					swap([id,id+1]);//2x the same or height:1 and not last col
					changed = true;
			}
			//last col / on the right side end / overflow
			else if (grid[id].stop % 8 === 0){ //could be shorter, repetitions for better overview and readability
				changed = true;
		  //------11------
				if (grid[id].size === "11" && grid[id-1].size === "11" && grid[id+1].size === "12" || grid[id].size === "11" && grid[id+1].size === "13" && grid[id-1].size === "12" || grid[id].size === "11" && grid[id+1].size === "14" && grid[id-1].size === "13")
					swap([id-1,id,id+1]); //11,11 \n 12 --or-- 12,11 \n 13 --or-- 13,11 \n 14
				else if(grid[id].size === "11" && grid[id+1].size === "13" && grid[id-1].size === "11" && grid[id-2].size === "11" || grid[id].size === "11" && grid[id+1].size === "14" && (grid[id-1].size === "12" && grid[id-2].size === "11" || grid[id-1].size === "11" && grid[id-2].size === "12"))
					swap([id+1,id-2,id-1,id]);//11,11,11 \n 13 --or-- 11,12,11 \n 14 --or-- 12,11,11 \n 14
				else if(grid[id].size === "11" && grid[id+1].size === "14" && grid[id-1].size === "11" && grid[id-2].size === "11" && grid[id-3].size === "11")
					swap([id+1,id-3,id-2,id-1,id]);//4x11 \n 14
		  //------12------
				else if (grid[id].size === "12" && grid[id+1].size === "11" && grid[id+2].size === "11")
					swap([id,id+2,id+1]);//12\n "11","11"
				else if (grid[id].size === "12" && grid[id-1].size == "11" && grid[id+1].size === "13" || grid[id].size === "12" && grid[id-1].size == "12" && grid[id+1].size === "14")
		  			swap([id-1,id,id+1]);//11,12 \n 13 or 12,12\n 14
				else if (grid[id].size === "12" && grid[id-1].size === "11" && grid[id-2].size === "11" && grid[id+1].size === "14")
					swap([id+1,id-2,id-1,id]);//11,11,12 \n 14
		  //------13------
				else if (grid[id].size === "13" && (grid[id+1].size === "11" && grid[id+2].size === "12" || grid[id+1].size === "12" && grid[id+2].size === "11"))
					swap([id,id+2,id+1]);//"13"\n "12","11"or "11","12"
				else if (grid[id].size === "13" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+3].size === "11")
					swap([id,id+3,id+2,id+1]);//"13"\n 3x"11"
				else if (grid[id].size === "13" && grid[id+1].size === "14" && grid[id-1].size === "11")
					swap([id-1,id,id+1]);//11,13 \n 14
		  //------14------
				else if (grid[id].size === "14" && grid[id+1].size === "12" && grid[id+2].size === "12" || grid[id].size === "14" && (grid[id+1].size === "11" && grid[id+2].size === "13" || grid[id+1].size === "13" && grid[id+2].size === "11"))
					swap([id,id+2,id+1]);//"14"\n "12","12" --or-- 14 \n 13,11 --or-- 14 \n 11,13
				else if (grid[id].size === "14" && 
					(grid[id+1].size === "12" && grid[id+2].size === "11" && grid[id+3].size === "11") ||
					(grid[id+1].size === "11" && grid[id+2].size === "12" && grid[id+3].size === "11") ||
					(grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+3].size === "12"))
					swap([id,id+3,id+2,id+1]); //14 \n "12","11","11"or ...
				else if (grid[id].size === "14" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+3].size === "11" && grid[id+4].size === "11")
					swap([id,id+4,id+3,id+2,id+1]);	//"14" \n 4x"11"
				else 
					changed = false;
			}
			else if (grid[id].size === "24"){

			}
			else if (grid[id].size === "44"){

			}
		}
		if (changed)
			grid_update();
		
	}

	//---- ok ----
	function click_ok(id){
		mode = "show";
		for (i=grid.length-1;i>=0;i--){//delete all dummies are at the end
			//begins at the last element
			if (grid[i].name != "dummy"){
				grid.splice(i+1,grid.length-(i-1));
				i=0;
			}
		}
		grid_update();
	}

	//---- delete  ----
	function click_delete(id){
		if (grid[id].name != "settings" && grid[id].name != "dummy"){
			if (view === "vertical")
				grid.splice(id,1);	//dummies are deleted automatically, when they are at the end
			else if (view =="horizontal")
				grid.splice(id,1);//wird noch erweitert, für spezielle fälle
			grid_update();		
		}
	}

