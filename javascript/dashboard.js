//TODO load button in recipies for min/max temp

const server = location.host;
const home_dir = "/HomeDashboard";
const INTERVALL_MAIN_TICKER = 1000;
var INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
var REFRESHED = false;

var view = "vertical";//orientation of phone / width: <600 or >600
var mode = "show";//or move

class Grid extends Array{
	constructor(){
		super();
	}
	show(){
		console.log(this);
		console.log(JSON.stringify(this));
	}
	reset(){
		this.splice(0,this.length);
	}
	sort_asc(){
		if (this.size === 0)
			return;
		this.sort((obj1,obj2) => {return obj1.pos - obj2.pos;});
		for (var i = 0; i < this.length-2; i++){//removes gaps in order
				if (this[i].pos+1 != this[i+1].pos)
					this[i+1].pos--;
		}
	}
	add_element(){
		let size = document.getElementById("select_size").value;
		let type = document.getElementById("select_type").value;
		if (type === "dummy")
			size = "11";
		else if (view === "horizontal" && (size === "24" || size === "34" || size === "44")){
			alert("Big ones doesn't work properly in horizontal mode, yet.");
			return;
		}
 		if (size != "null" && type != "null") {
			grid.push(new GridObject(type,size));
			grid.update();
	 	}
		else
	 		alert("you need to select \'type\' and \'size\' before you can add");
	}
	fill_gaps(gaps){//fills gaps with dummies
		for (let i=0;i<gaps.length;i++){
			if (gaps[i] != ""){//i:entspricht id vor lücke
				while(gaps[i]-->0){
					for (let all=i;all<this.length;all++)//alle verscchieben von lücke bis ende
						this[all].pos++;

					this.push(new GridObject("dummy","11",i));
				}
				this.update();
				break;
			}
		}
	}
	update(){
		let grid_size = 1,grid_pos = 1, gaps = [], modulo = 4;
		set_Orientation();
		grid.sort_asc();
		if (view === "vertical")
			modulo = 4;
		else if (view === "horizontal")
			modulo = 8;
		for (var i=0;i<grid.length;i++){
			grid_size = Number(grid[i].size[0])*Number(grid[i].size[1]);
			if (grid_pos % modulo === 0 && grid_size >= 2)
				gaps[i] = 1;
			else if (grid_pos % modulo > 5 % modulo && grid_size > 3 || grid_size === 3 && grid_pos % modulo > 6 % modulo)
				gaps[i] = 5 - (grid_pos % 4);
			else
				gaps[i] = 0;
			grid_pos += grid_size;
			grid_pos += gaps[i];
			grid[i].stop = grid_pos-1;
			grid[i].start = grid_pos - grid_size;
		}
		grid.fill_gaps(gaps);
		grid.sort_asc();
		if (grid[grid.length-1].stop <= 32)
			this.render();
		else {
			alert("To big for current config!\nDelete existing elements to get enough space.");
			grid[grid.length-1].remove();	
		}
		console.log(JSON.stringify(grid));
		console.log(grid);	
	}
	render(){
		document.getElementById("container").innerHTML = "";
		let html = "", note = false;
		for (var g = 0; g < grid.length; g++){
			html += grid[g].createHTML();
			if (grid[g].name === "notes")
				note = true;	
		}
		document.getElementById("container").innerHTML = html;
		if (note && mode != "move"){
			InlineEditor							//TODO Notes as working grid element, currently are not interacting with grid 
				.create( document.querySelector('#editor')) //TODO notizen in cookie oder ähnliches
				.catch( error => {
				console.error( 'There was a problem initializing the editor.', error );
			});
		}
	}
}

class GridObject {
	constructor(type,size,pos=grid.length){
		this.type = type;
		this.name = type;//----replace
		this.size = size;
		this.pos = pos;
		this.start = 0;
		this.stop = 0;
	}
	remove(){
		grid.splice(this.pos-1,1);
	}
	show(){
		console.log(this);
	}
	createHTML(){
		let type = this.type, scrolling = "no", source = "/HomeDashboard/dashboard/", html = "";
		if (mode === "move")
			type = "move";
		//---div for grid---
		html += "<div id=\"" + this.pos + "\" ";
		if (type === "settings"){
			html += "class=\"tile tile_" + this.size + " grid_object menu-toggle\"";
			html += "onclick=\"click_sidebar();\"><img src=\"images/btn_edit.svg\" style=\"width: 4rem;height:4rem;\">";
		}
		else {
			for (let g = 0; g < widgets.widgets.length; g++){
				const widget = widgets.widgets[g];
				if (type === widget.name){
					if (!("sizes" in widget.default) || widget.default.sizes.includes(this.size))
						source += widget.default.filename;//size equals spec for default widget
					else if ("special" in widget && widget.special.sizes.includes(this.size))
						source += widget.special.filename;//size equals spec for special widget		
					else if ("sizes" in widget.default && !(widget.default.sizes.includes(this.size))){
						source += widget.default.filename;//no fitting this.size
						for (d in this.sizes){//sets size to first allowed size
							if (widget.default.sizes.includes(d)){
								this.size = this.sizes[d];
								break;
							}
						}
					}
					if ("scrolling" in widget.default && widget.default.scrolling == "yes")
						scrolling = "yes";
					if ("type" in widget.default){
						switch (widget.default.type){
							case "temp":
								source += "?sensor="+widget.name;
								source += "&display_name="+widget.display_name;
								break;
							case "move":
								source +="?id="+ this.pos +"&name="+grid[this.pos].name;
								break;
							case "url":
								source = widget.default.filename;
								break;
						} 
					}
					switch (type){
							//Custom Widget HTML
						case "notes":
							html += "class=\"tile tile_" + this.size + " grid_item\"><div class='editor' id='editor'><p>Notes</p></div";	
							break;
						case "dummy":
							html += "class=\"tile tile_11 dummy\">"
							break;
						default:
							//Standart Widget HTML
							html += "class=\"tile tile_" + this.size + " grid_item\"";	
							html += "><iframe scrolling='"+ scrolling+"' class=\"iframe\" id=\"iframe\" src=\"" + source + "\"></iframe>";
					}
					break;
				}	
			}
		}
		html += "</div>";	
		return html;
	}
}

class Widgets extends Array{
	constructor(){
		super();
		this.sensors = [];
		this.widgets = [];
	}
	request(){
		var xhttp = new XMLHttpRequest();
		var obj;
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200)
   	 			obj = JSON.parse(this.responseText);
		};
		xhttp.open("GET", "config.json", false);
		xhttp.send();
		this.handle_response(obj);
	}
	handle_response(obj){
		this.sensors = obj.sensor_names;
		this.sizes = obj.sizes;
		let html = "", count = 0;
		for (let w=0;w<Object.keys(obj.widget).length;w++){
			let special = null;
			let default_ = obj.widget[w].default;
			if (obj.widget[w].display_name != "sensor"){
				if (obj.widget[w].display_name != "")
					html+="<option value='"+ obj.widget[w].name +"'>"+ obj.widget[w].display_name +"</option>\n";
				if ("special" in obj.widget[w])
					special = obj.widget[w].special;
				this.widgets.push(new Widget(obj.widget[w].name,
											 obj.widget[w].display_name,
											 default_,special
											));
			}
			else {
				for (let s=0;s<Object.keys(this.sensors).length-1;s++){
					if (obj.widget[w].display_name != "")
						html+="<option value='"+ this.sensors[s].name +"'>"+ this.sensors[s].display_name +"</option>";
					if ("special" in obj.widget[w])
						special = obj.widget[w].special;
					this.widgets.push(new Widget(this.sensors[count].name,
											this.sensors[count].display_name,
											default_,special
											));
					count++;
				}
			}
		}
		console.log(this.widgets);
		document.getElementById("select_type").innerHTML += html; 
		html ="";
		for (var i=0;i<widgets.sizes.length;i++)
			html += "<option value='"+widgets.sizes[i]+"'>"+Math.round(widgets.sizes[i]/10)+"x"+widgets.sizes[i]%10+"</option>";
		document.getElementById("select_size").innerHTML += html; 
	}
}

class Widget{
	constructor(name,display_name,default_,special=null){
		this.name = name;
		this.display_name = display_name;
		this.default = default_;
		this.sizes = [];
		if (special != null)
			this.special = special;
	}
}

class Presets extends Array{
	constructor(standard_value = 2){
		super();
		this.last_preset = null;
		this.profile_name = [];
		this.standard_value = standard_value;
	}
	init(){
		this.get_all_presets();
		this.get_preset(this.standard_value);
	}
	get_all_presets(){
		this.request("get_all_presets","")
	}
	get_preset(id){
		this.request("get_preset",id)
	}
	request(request, profile_num){
		var xhttp = new XMLHttpRequest();
		var json_response = "";
		xhttp.onreadystatechange = function(me = this) {
			if (this.readyState == 4 && this.status == 200)
				json_response = this.responseText;
			else {
				if (this.status > 399)
					console.log("error or wrong response code");
			}
		}
		try{
			xhttp.open("GET", "http://"+server+home_dir+"/json_handler_user_agent.php?json=" + this.create_request(request,profile_num), false);
			xhttp.send();
			this.response_handler(json_response, request);
		}
		catch (err){
			console.log("Request Error: "+err);
		}
	}
	create_request(request,profile_num){
		var json_string = "{\"event\":\""+request+"\""; 
		switch (request){
			case "set_new_preset": //set new and save
				json_string += ",\"profile_id\":\""+ profile_num +"\"";//range 1 to 9
				json_string += ",\"profile_name\":\"" + this.profile_name[profile_num] +"\"";
				json_string += ",\"grid_object_v\":"+ JSON.stringify(grid_vertical) +"";
				json_string += ",\"grid_object_h\":"+ JSON.stringify(grid_horizontal) +"";
				break;
			case "delete_preset": //deletes selected preset
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
	createHTML(json_response){
		var response = JSON.parse(json_response);
		var html = "<option value='null' id='null'>- select preset -</option>";
		for (var i=1;i<10;i++){
			if (response[i] != "" && response[i] != null){
				this.profile_name[i] = response[i];
				html += "<option value='"+i+"' id='element' >"+ response[i] +"</option>";
			}
			else {
				html += "<option value='"+i+"' id='new'>save as new preset</option>";
				break;
			}
		}
		document.getElementById("select_preset").innerHTML = html;
	}
	response_handler(json_response, request){
		if (request == "get_preset"){
			//set the response values in active dashboard
			var response = JSON.parse(json_response);
			if (response["grid_object_v"] != "" && response["grid_object_v"] != null && 
				response["grid_object_h"] != "" && response["grid_object_h"] != null){
				grid_vertical.reset();
				grid_horizontal.reset(); 	
				for (var key in response["grid_object_v"]){
					grid_vertical.push(new GridObject(response["grid_object_v"][key].name,
													  response["grid_object_v"][key].size,
													  response["grid_object_v"][key].pos));
				}
				for (key in response["grid_object_h"]){
					grid_horizontal.push(new GridObject(response["grid_object_h"][key].name,
														response["grid_object_h"][key].size,
														response["grid_object_h"][key].pos));
				}
				grid_horizontal.sort();
				grid_vertical.sort();
			}
			else
				console.log("bad response: recieved empty 'grid_object'");
			if (view === "vertical")
				grid = grid_vertical;
			if (view === "horizontal")
				grid = grid_horizontal;
			grid.update();
		}
		else if (request == "get_all_presets")
			this.createHTML(json_response);
		else
			this.request("get_all_presets","");
	}
	action(value,name){
		switch(name){
			case "null":
				break;
			case "new":
				this.profile_name[value] = prompt("Bitte einen Namen für das Preset eingeben!"); 
				if (this.profile_name[value] != null && this.profile_name != "")
					this.request("set_new_preset",value);
				else 
					delete this.profile_name[value];
				break;
			case "delete":
				if (this.last_preset != 0){
					this.request("delete_preset",this.last_preset);
					this.last_preset=0;
					document.getElementById("select_preset").value = "null";
				}
				break;
			case "save":
				if (this.last_preset != 0)
					this.request("set_new_preset",this.last_preset);
				break;
			case "load":
				if (this.last_preset != 0)
					this.request("get_preset",this.last_preset);
				break;	
		}
	}
}
	
//------------------------------
//----- init and Window --------
//------------------------------
	widgets = new Widgets();
	widgets.request();
	grid = new Grid();
	grid_vertical = new Grid();//store grids for orientation change
	grid_horizontal = new Grid();
	
	presets = new Presets(2);
	presets.init();

	//executes function(with value as para) when select_preset changes state
	document.querySelector('#select_preset').addEventListener("change", function() {
  		if (document.getElementById("new").value == this.value){
  			presets.action(this.value,"new");
  			document.getElementById("select_preset").value = "null";
  		}	
  		else if (document.getElementById("null").value == this.value)
  			return;
  		else {
  			presets.last_preset = this.value;
  			document.getElementById("select_preset").value = this.value;
  		}
	});

	window.addEventListener('DOMContentLoaded', init());

	function init(){
		set_Orientation();		
	}

	var window_width = window.innerWidth;
	function interval_main_tick(){
		if (window_width <= 600){//checks if orientation has been changed
			if (window.innerWidth > 600){
				window_width = window.innerWidth;
				grid_vertical = grid;
				grid = grid_horizontal;
				grid.update();
			}
		}
		else if (window_width > 600 && window.innerWidth <= 600){
			window_width = window.innerWidth;
			grid_horizontal = grid;
			grid = grid_vertical;
			grid.update();
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