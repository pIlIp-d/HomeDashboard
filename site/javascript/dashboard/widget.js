function add_string_to_array(arr, str){
    strarr = str.split(",");
    for (s in strarr)
        arr.push(strarr[s]);
    return arr;
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
        //TODO testing if values arent doubled, wrong etc...
		//---sensors---
		for (let i = 0; i < Object.keys(response.devices).length; i++){
			for (let j = 0; j < response.devices[i].sensors.length; j++){
                let s = response.devices[i].sensors[j];
				this.sensors.push({
					name: s.sensor_name,
					display_name: s.display_name,
					id: s.sensor_id,
					type: s.type,
					unit: s.unit,
                    device_id: response.devices[i].device_id
                });
            }
		}
		//---widgets---
		this.sizes = response.sizes;
		let html = "";
		for (let w = 0; w < Object.keys(response.widget).length; w++){
			var widget = response.widget[w];
            if (!("sizes" in widget))
                widget.sizes = this.sizes;
            if (widget.type === "device"){
				for (let s = 0; s < this.sensors.length-1; s++){
                    if (this.sensors[s].device_id == widget.device_id){
                        if (this.sensors[s].display_name != "")
    						html+="<option value='"+ this.sensors[s].name +"'>"+ this.sensors[s].display_name +"</option>";
    					this.widgets.push({
                            name: this.sensors[s].name,
                            display_name: this.sensors[s].display_name,
                            device_id: widget.device_id,
    						sizes: widget.sizes,
                            unit: this.sensors[s].unit,
                            filename: widget.filename
    					});
                    }
                }
			}
			else {
				if (widget.display_name != "")
					html+="<option value='"+ widget.name +"'>"+ widget.display_name +"</option>\n";
                this.widgets.push(widget);
            }
		}
        console.log(this.widgets);
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
