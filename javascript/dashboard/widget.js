function add_string_to_array(arr, str){
    strarr = str.split(",");
    for (s in strarr)
        arr.push(strarr[s]);
    return arr;
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
class Sensor{//struct
	constructor(name,display_name,id,type,unit){
		this.name = name;
		this.display_name = display_name;
		this.id = id;
		this.type = type;
		this.unit = unit;
	}
}
class Widgets{//all possible properties of widget-types (~ Widget"presets")
	constructor(){
		this.sensors = [];
		this.widgets = [];
	}
    request(){
		var xhttp = new XMLHttpRequest();
		var response;
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200)
   	 			response = JSON.parse(this.responseText);
		};
		xhttp.open("GET", "config.json", false);
		xhttp.send();
		this.handle_response(response);
	}
	handle_response(response){
		//---sensors---
		for (let i = 0; i < Object.keys(response.devices).length; i++){
			for (let j = 0; j < response.devices[i].sensors.length; j++){
				let s = response.devices[i].sensors[j];
				this.sensors.push(new Sensor(
					s.sensor_name,
					s.display_name,
					s.sensor_id,
					s.type,
					s.unit));
			}
		}
		//---widgets---
		this.sizes = response.sizes;
		let html = "";
		for (let w=0;w<Object.keys(response.widget).length;w++){
			let special = null;
			let default_ = response.widget[w].default;
			if (response.widget[w].display_name === "sensor"){
				for (let s=0;s<this.sensors.length-1;s++){
					var widget_sizes = [];
					if ("sizes" in response.widget[w].default)
						widget_sizes = add_string_to_array(widget_sizes,response.widget[w].default.sizes);
					else
						widget_sizes = this.sizes;
					if (response.widget[w].display_name != "")
						html+="<option value='"+ this.sensors[s].name +"'>"+ this.sensors[s].display_name +"</option>";
					if ("special" in response.widget[w]){
						special = response.widget[w].special;
						if ("sizes" in response.widget[w].special)
							widget_sizes = add_string_to_array(widget_sizes,special.sizes);
					}
					this.widgets.push(new Widget(this.sensors[s].name,
											this.sensors[s].display_name,
											default_,special,widget_sizes,this.sensors[s].unit
											));
				}
			}
			else {
				var widget_sizes = [];
				if ("sizes" in response.widget[w].default)
						widget_sizes = add_string_to_array(widget_sizes,response.widget[w].default.sizes);
					else
						widget_sizes = this.sizes;
				if (response.widget[w].display_name != "")
					html+="<option value='"+ response.widget[w].name +"'>"+ response.widget[w].display_name +"</option>\n";
				if ("special" in response.widget[w]){
					special = response.widget[w].special;
					if ("sizes" in response.widget[w].special)
						widget_sizes = add_string_to_array(widget_sizes,special.sizes);
				}
				this.widgets.push(new Widget(response.widget[w].name,
											 response.widget[w].display_name,
											 default_,special,widget_sizes
											));
			}
		}
		document.getElementById("select_type").innerHTML += html;
		this.change_html_sizes();
	}
    change_html_sizes(){
        let name = document.getElementById("select_type").value;
        for (var w in this.widgets){
            if (this.widgets[w].name === name){
                let html ="<option value='null'>- choose size -</option>";
                for (var i=0;i<this.widgets[w].sizes.length;i++)
                    html += "<option value='"+this.widgets[w].sizes[i]+"'>"+Math.round(this.widgets[w].sizes[i]/10)+"x"+this.widgets[w].sizes[i]%10+"</option>";
                document.getElementById("select_size").innerHTML = html;
                document.getElementById("select_size").value = this.widgets[w].sizes[0];
            }
        }
    }
}
