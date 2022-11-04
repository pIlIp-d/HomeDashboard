
last_preset = null;//last selected preset id null,1,2...
/**
 *@class requires Variables in main js file
 *@param DB_URL - path to <db_file>.php as String
*/
class Dashboard {
	constructor(){
        this.widgets = new Widgets();
        this.widgets.request();
		this.presets = [];
		this.grid_vertical = new Grid(this.widgets);//store grids for orientation change
		this.grid_horizontal = new Grid(this.widgets);
		this.grid;//current grid (horizontal/vertical)
		this.preset_names = [];
		this.preset_ids = [];
    }
	init(){
		let standard_value = document.getElementById("preset").innerHTML;
		this.get_all_presets();
		last_preset = (this.preset_names.length > standard_value)?standard_value:0;//checks if standart value is a possible preset id to start with else just use empty preset
		this.get_preset(last_preset);
	}
	get_all_presets(){
		this.request("get_all_presets","")
	}
	get_preset(id){
		this.request("get_preset",id)
	}
	request(request_name, value){
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
		xhttp.open("GET", DB_URL + "?json=" + this.create_request(request_name,value), false);
		xhttp.send();
		console.log(request_name);
		console.log(json_response);
		this.response_handler(request_name, json_response);
	}
	create_request(request_name,value){
		var json_string = "{\"request_name\":\""+request_name+"\"";
		switch (request_name){
			case "set_new_preset":
				json_string += ",\"preset_name\":\""+ value +"\"";
				json_string += ",\"grid_object_v\":"+ this.grid_vertical.toString();
				json_string += ",\"grid_object_h\":"+ this.grid_horizontal.toString();
				break;
			case "save_preset": //set new preset
				json_string += ",\"preset_id\":\""+ this.preset_ids[value] +"\"";
				json_string += ",\"preset_name\":\""+ this.preset_names[value] +"\"";
				json_string += ",\"grid_object_v\":"+ this.grid_vertical.toString();
				json_string += ",\"grid_object_h\":"+ this.grid_horizontal.toString();
				break;
			case "delete_preset": //deletes selected preset
				json_string += ",\"preset_id\":\""+ this.preset_ids[value] +"\"";
				break;
			case "get_all_presets": //returns all preset names
				break;
			case "get_preset": //returns all preset values of specific preset
				this.request("get_preset_ids");
				json_string += ",\"preset_id\":\""+ this.preset_ids[value]+"\"";
				break;
			case "get_preset_ids":
				break;
		}
		json_string += "}";
		return json_string;
	}
	response_handler(request_name, json_response){
		switch(request_name){
			case "get_preset":
					console.log(json_response);
					//set the response values in active dashboard
					var response = JSON.parse(json_response);
					console.log(response);
					if (response["grid_object_v"] != "" && response["grid_object_v"] != null &&
						response["grid_object_h"] != "" && response["grid_object_h"] != null){
						this.grid_vertical.reset();
						this.grid_horizontal.reset();
						for (var key in response["grid_object_v"]){
							this.grid_vertical.push(new GridObject(response["grid_object_v"][key].type,
															  response["grid_object_v"][key].size,
															  response["grid_object_v"][key].pos,
															  response["grid_object_v"][key].display,
                                                              this.widgets));
						}
						for (key in response["grid_object_h"]){
							this.grid_horizontal.push(new GridObject(response["grid_object_h"][key].type,
																response["grid_object_h"][key].size,
																response["grid_object_h"][key].pos,
															    response["grid_object_h"][key].display,
                                                                this.widgets));
						}
						this.grid_horizontal.sort();
						this.grid_vertical.sort();
					}
					else
						console.log("bad response: recieved empty 'grid_object'");
					if (view === "vertical")
						this.grid = this.grid_vertical;
					if (view === "horizontal")
						this.grid = this.grid_horizontal;
					this.grid.update();
					document.getElementById("select_preset").value = last_preset;
					console.log(this.grid);
				break;
			case "get_all_presets":
					this.createHTML(json_response);
				break;
			case "get_preset_ids":
					let resp = JSON.parse(json_response);
					for (let i = 0; i < resp.length; i++)
						this.preset_ids[i] = resp[i].id;
				break;
			default:
				this.request("get_all_presets","");
		}
	}
    /**
     * @function createHTML creates HTML-options for preset select in settings menu
	 * 			if response is empty there is only an settings button to start with
     */
	createHTML(json_response){
		var response = JSON.parse(json_response);
		var html = "<option value='null' id='null'>- select preset -</option>";

		//create default empty widet if no widget exists
		if (Object.entries(response).length == 0){
			this.grid_horizontal.push(new GridObject("settings","11","0","0",this.widgets));
			this.grid_vertical.push(new GridObject("settings","11","0","0",this.widgets));
			this.create_request("add_preset","empty");
		}
		for (var i = 0; i < response.length; i++){
			if (response[i] != "" && response[i] != null){
				this.preset_names[i] = response[i].name;
				html += "<option value='"+i+"' id='element' >"+ response[i].name +"</option>";
			}
		}
		html += "<option value='"+i+"' id='new'>save as new preset</option>";
		document.getElementById("select_preset").innerHTML = html;
		document.getElementById("select_preset").value = (last_preset < this.preset_ids.length)?last_preset:null;
	}
	/**
	 * 	@function action button handling, in dashboard.php button.onclick()
 	 *	@param value - int for preset (starting by empty=0, ends at last preset)
 	 *	@param name - name of request to be send
 	 */
	action(value,name){
		switch(name){
			case "null":
				break;
			case "new":
				let value = prompt("Bitte einen Namen für das Preset eingeben!");
				if (value != null && value != "" && !Object.values(this.preset_names).includes(value))
					this.request("add_preset",value);
				else {
					delete this.preset_names[value];
					alert("Das hat nicht funktioniert!")
				}
				break;
			case "delete":
				if (last_preset != null && last_preset != 0 && confirm("Möchtest du wirklich das Preset '"+this.preset_names[last_preset]+"' löschen?")){
					this.request("remove_preset",last_preset);
					last_preset = 0;
					document.getElementById("select_preset").value = "null";
				}
				break;
			case "save":
                console.log(last_preset);
				if (last_preset != null && last_preset != 0 && confirm("Möchtest du die Aktuelle Konfiguration in Preset '"+this.preset_names[last_preset]+"' speichern?"))
					this.request("save_preset",last_preset);
				else
					alert("Das Preset 'Empty' kann nicht überschrieben werden.");
				break;
			case "load":
				if (last_preset != null)
					this.request("get_preset",last_preset);
				break;
		}
	}
}
