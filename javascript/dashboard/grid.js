var view = "vertical";//orientation of phone or just width < 600 or width > 600
set_Orientation();
var mode = "show";//or move


function set_Orientation(){
    if (window.innerWidth > 600)
        view = "horizontal";
    else if (window.innerWidth <= 600)
        view = "vertical";
}

//---- ok ----
function click_ok(id){//exit move mode
    mode = "show";
    for (i=DASHBOARD.grid.length-1;i>0;i--){//delete all dummies are at the end
        //begins at the last element
        if (DASHBOARD.grid[i].type != "dummy"){
            DASHBOARD.grid.splice(i+1,length-(i-1));
            i=0;
        }
    }
    DASHBOARD.grid.update();
}

//---- delete  ----
function click_delete(id){
    if (DASHBOARD.grid[id].type != "settings" && DASHBOARD.grid[id].type != "dummy"){
        if (view === "vertical")
            DASHBOARD.grid.splice(id,1);//dummies are deleted automatically, when they are at the end
        else if (view === "horizontal")
            DASHBOARD.grid.splice(id,1);//wird noch erweitert, für spezielle fälle
        DASHBOARD.grid.update();
    }
}

class GridObject { //single widget with properties
	static timer_count = 0;
	constructor(type,size,pos,display=0,widgets){
		this.type = type;
		this.size = size;
		this.pos = pos;
		this.start = 0;
		this.stop = 0;
		this.display = display;
        this.widgets = widgets;
	}
    toString(){
        return JSON.stringify({
            type: this.type,
            size: this.size,
    		pos: this.pos,
    		start: this.start,
    		stop: this.stop,
    		display: this.display
        });
    }
	remove(){
		grid.splice(this.pos,1);
	}
	show(){
        console.log(this);
	}
    createHTML(){
        let type = this.type, scrolling = "no", source = "dashboard/", html = "", size=this.size;
        if (mode === "move")
            type = "move";
        html += "<div id=\"" + this.pos + "\" ";

        if (type === "settings"){
    		html += "class=\"tile tile_" + this.size + " grid_object menu-toggle\"";
    		html += "onclick=\"click_sidebar('settings');\"><img src=\"images/btn_edit.svg\" style=\"width: 4rem;height:4rem;\">";
        }
        else if (type === "dummy")
            html += "class=\"tile tile_11 dummy\">"
        else {
            if (type === "move"){
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
                if (this.stop % mod === 0)
                    right = 1;
                if (this.stop <= mod)
                    top = 1;
                for (let len = DASHBOARD.grid.length-1; len >= 0; len--){
                    if (DASHBOARD.grid[len].start % mod === 1){
                        if (this.pos >= len)
                            bottom = 1; //if in last row
                        break;
                    }
                }
                source += "move_with_arrows.php";
                var arrow_string = ""+top+right+bottom+left;
                var iframe_json = {
                    arrows: arrow_string,
                    name: this.type,
                    id: this.pos
                };
            }
            else {
    			for (let g in this.widgets.widgets){
    				var widget = this.widgets.widgets[g];
    				if (type === widget.name){
    					source += widget.filename;
    					if ("scrolling" in widget && widget.scrolling == "yes")
    						scrolling = "yes";
                        var iframe_json = JSON.parse(JSON.stringify(widget));//copy obj / create new instance
                        delete iframe_json.sizes;//remove sizes array from general widget
                        delete iframe_json.filename;
                        iframe_json.show = this.display;//state of display 1 / 0
                        iframe_json.id = this.pos;
                        iframe_json.preset_id = DASHBOARD.preset_ids[last_preset];//TODO maybe get value through different way

                        switch (type){
                            case "url":
                                source = widget.filename;
                                break;
                            case "...":
                                /*custom widgets
                                add parameter to iframe_json
                                iframe_json.custom_value_name = value;
                                */
                                break;
                        }
                    }
                }
            }
            html += "class=\"tile tile_" + this.size + " grid_item\"";
            html += "><iframe scrolling='"+ scrolling +"' class=\"iframe\" id=\"iframe\" src='" + source + "?json="+JSON.stringify(iframe_json)+"\'></iframe>";
        }
        html += "</div>";
        return html;
    }
}

var mode = "show";//or move

class Grid extends Array{
	constructor(widgets){
        super();
        this.width = 4;
        this.widgets = widgets;
    }
    toString(){
        var str = "[";
        for (let i = 0; i < this.length; i++){
            str += this[i].toString();
            if (i < this.length-1)
                str += ",";
        }
        return str+"]";
    }
    show(){
		console.log(this);
        console.log(this.toString());
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
	get_above(id, end = "start"){//@return widget id of widget above
		for (let ele=id;ele>=0;ele--){
			for (let factor = 1; factor <= 16 / this.width; factor++){
				if (end === "start"){
					if (this[id].start-4 === this[ele].start || this[id].start-(4*factor) === this[ele].start && this[ele].size === ""+factor+"4")
						return this[ele];
				}
				else if (end === "stop"){
					if (this[id].stop-4 === this[ele].stop || this[id].stop-(4*factor) === this[ele].stop && this[ele].size === ""+factor+"4")
						return this[ele];
				}
			}
		}
		return -1;
	}
	get_below(id, end = "start"){//@return widget id of widget below
		for (let ele=id;ele<this.length;ele++){
			for (let factor = 1; factor <= 16 / this.width; factor++){
				if (end === "start"){
					if (this[id].start+4 === this[ele].start || this[id].start+(4*factor) === this[ele].start && this[id].size === ""+factor+"4")
						return this[ele];
				}
				else if (end === "stop"){
					if (this[id].stop+4 === this[ele].stop || this[id].stop+(4*factor) === this[ele].stop && this[id].size === ""+factor+"4")
						return this[ele];
				}
			}
		}
		return -1;
	}
	/** Places pos of widgets in from array after to
		requires sort_asc() or update call afterwards to affect order
		@param from - int-Array of pos-ids
		@param to - int where the from ids will be placed after (position directly not id)
	*/
	swap(from,to){
		let offset = 0.0;
		for (let id in from){
			offset += 1 / (from.length+1);
			this[from[id]].pos = to + offset;
			console.log(to+"  "+from[id]+" "+offset);
		}
	}
	/** move widget upwards if possible or checks if possible
		@param id - pos-id if widget
		@return bool only if check = true, otherwise return = undefined
	*/
	move_up(id){
		let coords = this.get_coords(this[id].start);
		console.log(coords);
		if (coords.row > 1){
			for (let i = coords.col; i >= 1;i--){
				let w = this.get_widget_by_coords(coords.row-1,i);
				if (w != -1){
					this.move_down(w.pos);
				}
			}
		}
	}
	move_down(id){
		id = Number(id);
		let coords = this.get_coords(this[id].start);
		let left_bottom,left_top, row = coords.row+1, col, delta_id = id;
		//start below
		while (this[delta_id] != null && this.get_coords(this[delta_id].start).row === coords.row){
			col = this.get_coords(this[delta_id--].start).col;
			let w = this.get_widget_by_coords(row,col);
			if (col === 1){
				col = coords.col;
				row++;
				if (w == -1)
					delta_id = id;
			}
			if(w != -1){
				left_top = this[delta_id+1].pos;
				left_bottom = w.pos;
				break;
			}
		}
		//stop below
		row = coords.row+1;
		delta_id = id;
		let right_bottom,right_top;
		while (this[delta_id] != null && this.get_coords(this[delta_id].start).row === coords.row){
			col = this.get_coords(this[delta_id++].stop).col;
			let w = this.get_widget_by_coords(row,col, "stop");
			if (w.pos === delta_id-1)//big ones have more than one row -> they find them selfs first
				w = -1;
			if (col === this.width){
				col = coords.col;
				row++;
				if (w == -1)
					delta_id = id;
			}
			if(w != -1){
				right_top = this[delta_id-1].pos;
				right_bottom = w.pos;
				break;
			}
		}
		let elements_to_move = [];
		for(let i = left_top; i <= right_top; i++)
			elements_to_move[elements_to_move.length] = i;
		console.log(elements_to_move);
		let below_elements_to_move = [];
		for(let i = left_bottom; i <= right_bottom; i++)
			below_elements_to_move[below_elements_to_move.length] = i;
		console.log(below_elements_to_move);
        let swap = this[elements_to_move[0]].pos;
    	this.swap(elements_to_move, below_elements_to_move[0]);
    	this.swap(below_elements_to_move, swap);
    	console.log(this);
    	this.update();
	}
	move_right(id){
		id = Number(id);
		console.log(this[id].stop );
		if (this[id].stop % this.width != 0){
			let swap = this[id].pos;
			this[id].pos = this[id+1].pos;
			this[id+1].pos = swap;
		}
		this.update();
	}
	move_left(id){
		this.move_right(id-1);
	}
    /**
     * @param pos - position in grid (4x8 / 8x4), not the id of widget
     * @return object of position
     */
	get_coords(pos){
		return {
			"col":((pos % this.width == 0)? this.width: pos % this.width),
			"row":(parseInt((pos-1) / this.width)+1)
		};
	}
	get_widget_by_pos(pos, end = "start"){
		for (let i = 0; i < this.length; i++)
			if (end === "start" && this[i].start == pos || end === "stop" && this[i].stop == pos)
				return this[i];
		return -1;
	}
	get_widget_by_coords(row,col, end = "start"){
		return this.get_widget_by_pos(this.width*(row-1)+col, end);
	}
	add_element(){
		let size = document.getElementById("select_size").value;
		let type = document.getElementById("select_type").value;
		if (type === "dummy")
			size = "11";
		else if (false && view === "horizontal" && (size === "24" || size === "34" || size === "44")){
			alert("Big ones doesn't work properly in horizontal mode, yet.");
			return;
		}
 		if (size != "null" && type != "null") {
			this.push(new GridObject(type,size,this.length,0,this.widgets));
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
					this.push(new GridObject("dummy","11",i,0,this.widgets));
				}
				this.update();
				break;
			}
		}
		// add dummies to the end to assure moveing is possible
		if (mode === "move"){
			var count = this[this.length-1].stop % 4;
			if (count == 0) return;
			for (var i = 3; i >= count; i--)//TODO horiozontal mode
				this.push(new GridObject("dummy","11",this.length,0,this.widgets));

			this.update();//set start, stop of dummies
			//TODO workaround for double update() call
		}
	}
	update(){//updates + reloads grid after every change that occurs
		let grid_size = 1, grid_pos = 1, gaps = [];
		set_Orientation();
		this.sort_asc();
		if (view === "vertical")
			this.width = 4;
		else if (view === "horizontal")
			this.width = 8;
		for (var i=0;i<this.length;i++){
			grid_size = Number(this[i].size[0])*Number(this[i].size[1]);//widget_size: width*height
			if (grid_pos % this.width === 0 && grid_size >= 2)//right side overlap (f.e. 3width+2width > max4 width)->one dummie
				gaps[i] = 1;
			else if (grid_pos % this.width > 5 % this.width && grid_size > 3 || grid_size === 3 && grid_pos % this.width > 6 % this.width)//every other dummie amount and constellation
				gaps[i] = 5 - (grid_pos % 4);
			else
				gaps[i] = 0;//no dummies needed
			grid_pos += grid_size;
			grid_pos += gaps[i];
			this[i].stop = grid_pos-1;
			this[i].start = grid_pos - grid_size;
		}
		this.fill_gaps(gaps);
		this.sort_asc();
		if (this[this.length-1].stop <= 32)//check <=max amount of widgets
			this.render();
		else {
			alert("To big for current config!\nDelete existing elements to get enough space.");
			this[this.length-1].remove();
		}
	}
	render(){
		GridObject.timer_count = 0;//reset timer_count
		document.getElementById("container").innerHTML = "";
		let html = "", note = false;
		for (var g = 0; g < this.length; g++){//create html of widgets
			html += this[g].createHTML();
			if (this[g].type === "notes")
				note = true;
		}
		document.getElementById("container").innerHTML = html;
		if (note && mode != "move"){//Note widget activate
			InlineEditor							//TODO Notes as working grid element, currently are not interacting with grid
				.create( document.querySelector('#editor')) //TODO notizen in cookie oder ähnliches
				.catch( error => {
				console.error( 'There was a problem initializing the editor.', error );
			});
		}
	}
}
