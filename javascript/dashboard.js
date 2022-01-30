//TODO load button in recipies for min/max temp
	
//TODO Preset deletes randomly

const server = location.host;
const home_dir = "/HomeDashboard";
const INTERVALL_MAIN_TICKER = 1000;
var INTERVALL_MAIN = setInterval(interval_main_tick, INTERVALL_MAIN_TICKER);
var REFRESHED = false;

var view = "vertical";//orientation of phone / width: <600 or >600
set_Orientation();
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
		this.sort((obj1,obj2) => {return obj1.pos - obj2.pos;});//sort
		for (var i = 0; i < this.length; i++)//remove gaps
				this[i].pos = i;
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
			this.push(new GridObject(type,size));
			this.update();
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
		this.sort_asc();
		console.log(grid);
		if (view === "vertical")
			modulo = 4;
		else if (view === "horizontal")
			modulo = 8;
		for (var i=0;i<this.length;i++){
			grid_size = Number(this[i].size[0])*Number(this[i].size[1]);
			if (grid_pos % modulo === 0 && grid_size >= 2)
				gaps[i] = 1;
			else if (grid_pos % modulo > 5 % modulo && grid_size > 3 || grid_size === 3 && grid_pos % modulo > 6 % modulo)
				gaps[i] = 5 - (grid_pos % 4);
			else
				gaps[i] = 0;
			grid_pos += grid_size;
			grid_pos += gaps[i];
			this[i].stop = grid_pos-1;
			this[i].start = grid_pos - grid_size;
		}
		this.fill_gaps(gaps);
		this.sort_asc();
		if (this[this.length-1].stop <= 32)
			this.render();
		else {
			alert("To big for current config!\nDelete existing elements to get enough space.");
			this[this.length-1].remove();	
		}
		console.log(JSON.stringify(this));
		console.log(this);	
	}
	render(){
		document.getElementById("container").innerHTML = "";
		let html = "", note = false;
		for (var g = 0; g < this.length; g++){
			html += this[g].createHTML();
			if (this[g].type === "notes")
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

class GridObject { //single widget with properties
	constructor(type,size,pos=grid.length,display=0,parent=grid){
		this.type = type;
		this.size = size;
		this.pos = pos;
		this.start = 0;
		this.stop = 0;
		this.display = display;
	}
	remove(){
		grid.splice(this.pos,1);
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
			html += "onclick=\"click_sidebar('settings');\"><img src=\"images/btn_edit.svg\" style=\"width: 4rem;height:4rem;\">";
		}
		else {
			for (let g = 0; g < widgets.widgets.length; g++){
				const widget = widgets.widgets[g];
				if (type === widget.name){
					if (!("sizes" in widget.default) || widget.default.sizes.includes(this.size)){
						source += widget.default.filename;//size equals spec for default widget
					}
					else if ("special" in widget && widget.special.sizes.includes(this.size)){
						source += widget.special.filename;//size equals spec for special widget		
					}
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
								source += "&show="+this.display;
								source += "&id="+this.pos;
								source += "&unit="+widget.unit;
								break;
							case "move":
								//check for gray Arrows
								var left = 0;
								var right = 0;
								var top = 0;
								var bottom = 0;
								var mod;
								if (view === "vertical")
									mod = 4;
								else if (view === "horizontal")
									mod = 8;
								if (this.start % mod === 1)
									left = 1;
								if (this.stop % mod === 0 || this.pos === grid.length-1)
									right = 1;
								if (this.stop <= mod)
									top = 1;
								for (let len = grid.length-1; len >= 0; len--){
									if (grid[len].start % mod === 1){
										if (this.pos >= len)
											bottom = 1; //if in last row
										break;
									}
								}
								var arrow_string = ""+top+right+bottom+left;
								source += "?id="+ this.pos + "&name=" + grid[this.pos].type+"&arrows="+arrow_string;
								break;
							case "url":
								source = widget.default.filename;
								break;
							case "button":
								source += "?name="+ widget.name;
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

class Widgets{ //all possible properties of widgets
	constructor(){
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
		//---sensors--- 
		for (let i = 0; i < Object.keys(obj.devices).length; i++){
			console.log(i);
			for (let j = 0; j < obj.devices[i].sensors.length; j++){
				let s = obj.devices[i].sensors[j];
				this.sensors.push(new Sensor(
					s.sensor_name,
					s.display_name,
					s.sensor_id,
					s.type,
					s.min_value,
					s.max_value,
					s.unit));
			}
		}
		//---widgets---
		this.sizes = obj.sizes;
		let html = "";
		for (let w=0;w<Object.keys(obj.widget).length;w++){
			let special = null;
			let default_ = obj.widget[w].default;
			if (obj.widget[w].display_name === "sensor"){
				for (let s=0;s<this.sensors.length-1;s++){
					var widget_sizes = [];
					if ("sizes" in obj.widget[w].default)
						widget_sizes = add_string_to_array(widget_sizes,obj.widget[w].default.sizes);
					else
						widget_sizes = this.sizes;
					if (obj.widget[w].display_name != "")
						html+="<option value='"+ this.sensors[s].name +"'>"+ this.sensors[s].display_name +"</option>";
					if ("special" in obj.widget[w]){
						special = obj.widget[w].special;
						if ("sizes" in obj.widget[w].special)
							widget_sizes = add_string_to_array(widget_sizes,special.sizes);
					}					
					console.log(widget_sizes);
					this.widgets.push(new Widget(this.sensors[s].name,
											this.sensors[s].display_name,
											default_,special,widget_sizes,this.sensors[s].unit
											));
				}
			}
			else {
				var widget_sizes = [];
				if ("sizes" in obj.widget[w].default)
						widget_sizes = add_string_to_array(widget_sizes,obj.widget[w].default.sizes);
					else 
						widget_sizes = this.sizes;
				if (obj.widget[w].display_name != "")
					html+="<option value='"+ obj.widget[w].name +"'>"+ obj.widget[w].display_name +"</option>\n";
				if ("special" in obj.widget[w]){
					special = obj.widget[w].special;
					if ("sizes" in obj.widget[w].special)
						widget_sizes = add_string_to_array(widget_sizes,special.sizes);
				}
				console.log(widget_sizes);

				this.widgets.push(new Widget(obj.widget[w].name,
											 obj.widget[w].display_name,
											 default_,special,widget_sizes
											));
			}
		}
		console.log(this.widgets);
		document.getElementById("select_type").innerHTML += html;

		change_html_sizes();
	}
}

class Widget{//struct
	constructor(name,display_name,default_,special=null,sizes,unit){
		this.name = name;
		this.display_name = display_name;
		this.default = default_;
		this.sizes = sizes.sort();
		this.unit = unit;
		if (special != null)
			this.special = special;
	}
}

class Sensor{ //struct
	constructor(name,display_name,id,type,min,max,unit){
		this.name = name;
		this.display_name = display_name;
		this.id = id;
		this.type = type;
		this.min_value = min;
		this.max_value = max;
		this.unit = unit;
	}
}

class Presets extends Array{
	constructor(){
		super();
		this.last_preset = null;//last selected preset id null,1,2...
		this.profile_names = [];
	}
	init(){
		this.standard_value = document.getElementById("preset").innerHTML;
		this.last_preset = this.standard_value;
		this.get_all_presets();
		this.get_preset(this.standard_value);
	}
	get_all_presets(){
		this.request("get_all_presets","")
	}
	get_preset(id){
		this.request("get_preset",id)
	}
	request(request, value){
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
			xhttp.open("GET", "http://"+server+home_dir+"/json_handler_user_agent.php?json=" + this.create_request(request,value), false);
			xhttp.send();
			this.response_handler(json_response, request);
		}
		catch (err){
			console.log("Request Error: "+err);
		}
	}
	create_request(request,value){
		console.log(JSON.stringify(this.profile_names)+" "+request + " "+this.last_preset);
		console.log(this.profile_names);
		var json_string = "{\"event\":\""+request+"\"";
		switch (request){
			case "save_preset":
			case "set_new_preset": //set new and save
				var id, name;
				if (value === "set_new_preset"){
					id = "new";
					name = value;
				}
				else {
					name = this.profile_names[this.last_preset];
					id = this.last_preset; 
				}
				json_string += ",\"profile_id\":\""+id+"\"";
				json_string += ",\"profile_name\":\"" + name +"\"";
				json_string += ",\"grid_object_v\":"+ JSON.stringify(grid_vertical) +"";
				json_string += ",\"grid_object_h\":"+ JSON.stringify(grid_horizontal) +"";
				break;
			case "delete_preset": //deletes selected preset
			console.log("delete: "+value);
				json_string += ",\"profile_id\":\""+ value +"\"";
				break;
			case "get_all_presets": //returns all preset names
				break;
			case "get_preset": //returns all preset values of specific preset
				json_string += ",\"profile_id\":\""+ this.last_preset +"\"";
				break;
		}
		json_string += "}";
		return json_string;
	}
	createHTML(json_response){
		var response = JSON.parse(json_response);
		//TODO if length == 0 -> init empty
		var html = "<option value='null' id='null'>- select preset -</option>";
		for (var i = 0; i < response.length; i++){
			if (response[i] != "" && response[i] != null){
				this.profile_names[i] = response[i].profile_name;
				html += "<option value='"+i+"' id='element' >"+ response[i].profile_name +"</option>";
			}
		}
		html += "<option value='"+i+"' id='new'>save as new preset</option>";
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
					grid_vertical.push(new GridObject(response["grid_object_v"][key].type,
													  response["grid_object_v"][key].size,
													  response["grid_object_v"][key].pos,
													  response["grid_object_v"][key].display));
				}
				for (key in response["grid_object_h"]){
					grid_horizontal.push(new GridObject(response["grid_object_h"][key].type,
														response["grid_object_h"][key].size,
														response["grid_object_h"][key].pos,
													    response["grid_object_h"][key].display));
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
			console.log(grid);
		}
		else if (request == "get_all_presets")
			this.createHTML(json_response);
		else
			this.request("get_all_presets","");
	}
	action(value,name){//parameter value currently unused
		console.log(this.last_preset);
		console.log();

		switch(name){
			case "null":
				break;
			case "new":
				let value = prompt("Bitte einen Namen für das Preset eingeben!");
				if (value != null && value != "" && !Object.values(this.profile_names).includes(value))
					this.request("set_new_preset",value);
				else {
					delete this.profile_names[value];
					alert("Das hat nicht funktioniert!")
				}
				break;
			case "delete":
				if (this.last_preset != null && this.last_preset != 0 && confirm("Möchtest du wirklich das Preset '"+this.profile_names[this.last_preset]+"' löschen?")){
					this.request("delete_preset",this.last_preset);
					this.last_preset = 0;
					document.getElementById("select_preset").value = "null";
				}
				break;
			case "save":
				if (this.last_preset != null && this.last_preset != 0 && confirm("Möchtest du die Aktuelle Konfiguration in Preset '"+this.profile_names[this.last_preset]+"' speichern?"))
					this.request("save_preset",this.last_preset);
				break;
			case "load":
				if (this.last_preset != null)
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
	
	const SELECT = document.getElementById("select_type");

	presets = new Presets();
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
	
	function change_html_sizes(){
		console.log("changed");
		let name = document.getElementById("select_type").value;
		for (var w in widgets.widgets){
			console.log(w+"  "+widgets.widgets[w]);
			if (widgets.widgets[w].name === name){
				let html ="<option value='null'>- choose size -</option>";
				console.log(name+"  "+widgets.widgets[w].sizes);
				for (var i=0;i<widgets.widgets[w].sizes.length;i++)
					html += "<option value='"+widgets.widgets[w].sizes[i]+"'>"+Math.round(widgets.widgets[w].sizes[i]/10)+"x"+widgets.widgets[w].sizes[i]%10+"</option>";
				document.getElementById("select_size").innerHTML = html;
				document.getElementById("select_size").value = widgets.widgets[w].sizes[0];
			}
		}		
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
	function init(){
		//DOMCONTENTLOADED
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
	document.getElementById('container').addEventListener("click",function() {
		if (document.getElementById('settings-menu').className.includes('open')){
			click_sidebar();
		}
	});

	var toggle_time = new Date().valueOf();
	function click_sidebar(sender){
		if (new Date().valueOf() > toggle_time){
			document.getElementById('settings-menu').classList.toggle("open");
			toggle_time = new Date().valueOf();
		}	
	}
	function set_Orientation(){
		if (window.innerWidth > 600)	
			view = "horizontal";
		else if (window.innerWidth <= 600)
			view = "vertical";
	}
	function add_string_to_array(arr, str){
		strarr = str.split(",");
		for (s in strarr)
			arr.push(strarr[s]);
		return arr;
	}

//-----------------------------------------
//-------- Message Handling ---------------
//-----------------------------------------

	window.addEventListener('message', e => {//TODO session id for moving
	  	if (e.origin == "http://"+location.host){
			let data_str = e.data.split(" ");
			switch (data_str[1]){
				//move with arrows
				case "button_up":
					click_up(data_str[0]);
					break;
				case "button_left":
					click_left(data_str[0]);
					break;
				case "button_right":
					click_right(data_str[0]);
					break;
				case "button_down":
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
				case "set_show"://for temp widget view
					grid[data_str[0]].display = grid[data_str[0]].display ^ 1;
					break;
				case "reload":
					grid.update();
					break;
			}	  	
		}
	}, false);