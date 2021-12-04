//-----------------------------------------
//-------- MOVE ---------------------------
//-----------------------------------------

	function swap(pos){//swaps postionts
		console.log(pos);
		for (i=0;i<pos.length;i++)
			pos[i] = Number(pos[i]);
		var swap_1 = grid[pos[0]].pos;
		for (i=0;i<pos.length-1;i++)
			grid[pos[i]].pos = grid[pos[i+1]].pos;
		grid[pos[pos.length-1]].pos = swap_1;
	}

	function gridsize_at_id(size_,id_){
		for (s in size_){//more than one returns disjunct
			if (grid[id_].size === s)
				return true;
		}
		return false;
	}
	function width_is_four(id){
		if (grid[id].size[1] === "4")
			return true;
		else
			return false;	
	}
	function get_above(id){
		for (ele=id;ele>=0;ele--){
			if (grid[id].start-4 === grid[ele].start || 
				(grid[id].start-8 === grid[ele].start && grid[ele].size === "24")||
				(grid[id].start-12 === grid[ele].start && grid[ele].size === "34") ||
				(grid[id].start-16 === grid[ele].start && grid[ele].size === "44"))
				return ele;
		}
		return -1;
	}

	//---- UP ----
	function swap_two_with_one(id,ele){
		let swap1 = grid[id].pos;//"12"over"11","11" or "13"over "11","12" or big over "11","13"/"12","12" 
		for (i=0;i<id-ele-1;i++)
			grid[id-i].pos = grid[id-1-i].pos;
		grid[Number(ele)+1].pos = grid[id+1].pos;
		grid[id+1].pos = grid[ele].pos;
		grid[ele].pos = swap1;
	}
	function click_up(id){
		console.log("up");
		id = Number(id);
		var changed = false;
		if (view === "vertical"){
			var ele = get_above(id);
			changed = true;
			console.log(ele);
			if (ele === -1){
				if (grid[id].start % 4 != 1)
					click_up(id-1);
				return;
			}
			else if (grid[id].size === grid[ele].size)
				swap([ele,id]);
			else if ("11" === grid[ele].size && "12" === grid[ele+1].size && //special case
					"12" === grid[id].size && "11" === grid[id+1].size ||
					"11" === grid[id].size && "12" === grid[id+1].size &&
					"12" === grid[ele].size && "11" === grid[ele+1].size){
				swap([id,ele]);//12,11 over 11,12 
				swap([id+1,ele+1]);
			}
			else if (grid[id].size > grid[ele].size && grid[ele+1].size != "13" || grid[id].size == "11" && grid[id+1].size == "13"){//13 is part of special cases
				click_down(ele);
				return;
			}
			else if ("11" === grid[ele].size && "13" === grid[ele+1].size || 
					"13" === grid[ele].size && "11" === grid[ele+1].size){
				//overlaps 11,13 -- 12,12/13,11 or 12,11,11
				if (grid[id].start+3 === grid[id+1].stop){//two
					swap([id,ele]);
					swap([id+1,ele+1]);
				}
				else if (grid[id].start+3 === grid[id+2].stop)//three
					swap([ele,id+1,ele+1,id+2,id]);
				else change = false;
			}
			else if ("12" === grid[ele].size){
				if (grid[id].start+1 === grid[id+1].stop){//two in row
					var swap1 = grid[ele].pos;
					grid[ele].pos = grid[id+1].pos;
					grid[id+1].pos = grid[ele+1].pos;
					grid[ele+1].pos = grid[ele+2].pos;
					if (ele-id === 4)
						grid[ele+2].pos = grid[ele+3].pos;
					grid[id].pos = swap1;
				}
				else change = false;
			}
			else if ("13" === grid[ele].size){
				if (grid[id].start+2 === grid[id+1].stop)//two in row
					swap([id,ele+1,id+1,ele]);
				else if (grid[id].start+2 === grid[id+2].stop){//three in row
					swap([ele,id,id+2]);
					swap([ele+1,id+1]);
				}
				else change = false;
			}
			else if (width_is_four(ele)){
				if (grid[id+3] != null && grid[id].start+3 === grid[id+3].stop)//four in row
					swap([ele,id+3,id+2,id+1,id]);
				else if(grid[id+2] != null && grid[id].start+3 === grid[id+2].stop)//three in row
					swap([ele,id+2,id+1,id]);
				else if(grid[id].start+3 === grid[id+1].stop)//two in row
					swap([ele,id+1,id]);
				else change = false;
			}
			else if (!changed && (grid[id].start % 4 != 1)){//sideways loop though until aligns with above
				click_up(id-1);
				return;
			}
			else changed = false;
		}
		else if (view === "horizontal"){//todo überarbeiten
			for (ele=id;ele>=0;ele--){
				console.log("ele_c: "+ele);
				if (grid[id].start-8 === grid[ele].start)
					click_down(ele--);
				else if(grid[id].start-9 === grid[ele].start)
					click_down(ele--);
				else if(grid[id].start-10 === grid[ele].start)
					click_down(ele--);
				else if(grid[id].start-11 === grid[ele].start)
					click_down(ele--);
			}
		}
		if (changed)
			grid.update();
	}

	function get_below(id){
		for (ele=id;ele<grid.length;ele++){
			if (grid[id].start+4 === grid[ele].start || 
				(grid[id].start+8 === grid[ele].start && grid[ele].size === "24")||
				(grid[id].start+12 === grid[ele].start && grid[ele].size === "34") ||
				(grid[id].start+16 === grid[ele].start && grid[ele].size === "44"))
				return ele;
		}
		return -1;
	}
	//---- Down ----
	function click_down(id){	
		console.log("down");
		id = Number(id);
		var changed = false;
		if (view === "vertical"){
			var ele = get_below(id);
			console.log(ele);

			if (ele === -1){ 
				if (grid[id].start % 4 != 1)
					click_down(id-1);
				return;
			}
			changed = true;
			if (grid[id].size === grid[ele].size)
				swap([ele,id]);
			if (grid[id].size > grid[ele].size && grid[ele+1].size != "13" || grid[id].size == "11" && grid[id+1].size == "13"){
				click_up(ele);
				return;
			}
			else if ("11" === grid[ele].size && "13" === grid[ele+1].size || 
					"13" === grid[ele].size && "11" === grid[ele+1].size){
				//overlaps 11,13 -- 12,12/13,11 or 12,11,11
				if (grid[id].start+3 === grid[id+1].stop){//two
					swap([id,ele]);
					swap([id+1,ele+1]);
				}
				else if (grid[id].start+3 === grid[id+2].stop)//three
					swap([ele,id+1,ele+1,id+2,id]);
				else change = false;
			}
			else if ("12" === grid[ele].size)
				if (grid[id].start+1 === grid[id+1].stop){//two in row
					var swap1 = grid[ele].pos;
					grid[ele].pos = grid[id].pos;
					grid[id].pos = grid[id+2].pos;
					if (id-ele === 4)
						grid[id+2].pos = grid[ele-1].pos;
					grid[ele-1].pos = grid[id+1].pos;
					grid[id+1].pos = swap1;
				}
				else if(grid[ele+1].size == "11" && grid[id].size == "11" && grid[id+1].size == "12"){
					swap([id,ele]);
					swap([id+1,ele+1]);
				}
				else change = false;
			else if ("13" === grid[ele].size){
				if (grid[id].start+2 === grid[id+1].stop)//two in row
					swap([ele,id,id+1,id+2]);
				else if (grid[id].start+2 === grid[id+2].stop)//three in row
					swap([ele,id,id+1,id+2,id+3]);
				else change = false;
			}
			else if (width_is_four(ele)){
				if (grid[id].start+3 === grid[id+3].stop)//four in row
					swap([ele,id,id+1,id+2,id+3]);
				else if(grid[id].start+3 === grid[id+2].stop)//three in row
					swap([ele,id,id+1,id+2]);
				else if(grid[id].start+3 === grid[id+1].stop)//two in row
					swap([ele,id,id+1]);
				else change = false;
			}
			else changed = false;
		}
		else if (view === "horizontal"){
			for (ele=id;ele<grid.length;ele++){
				if (grid[id].start === grid[ele].start-8){//id above ele 
					console.log("normal"+ele);
					changed = true;
					if (grid[id].size === grid[ele].size)
						swap([ele,id]);
			//------11------
					else if (grid[id].size === "11" && (grid[ele].size === "12" && grid[id+1].size === "11" || grid[ele].size === "13" && grid[id+1].size === "12" || grid[ele].size === "14" && grid[id+1].size === "13")){ 
						//11,11 \n 12 --or-- 11,12 \n 13
						console.log("11,11,12/11,12,13");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele-1].pos;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos = grid[ele-1-i].pos;
						grid[id+1].pos = swap1;
					}
					else if (grid[id].size === "11" && (grid[ele].size === "13" && grid[id+1].size === "11" && grid[id+2].size === "11" || grid[ele].size === "14" && grid[id+1].size === "12" && grid[id+2].size === "11" || grid[ele].size === "14" && grid[id+1].size === "11" && grid[id+2].size === "12")){
						//3x11 \n 13 --or-- 11,12,11 \n 14 --or-- 11,11,12 \n 14
						console.log("11,11,11,13");
						let swap1 = grid[id].pos;
						let swap2 = grid[ele-1].pos;
						grid[id].pos = grid[ele-2].pos;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos = grid[ele-i-2].pos;
						}
						grid[id+1].pos = swap2;
						grid[id+2].pos = grid[ele].pos;
						grid[ele].pos = swap1;
					}
					else if (grid[id].size === "11" && grid[ele].size === "14" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[id+3].size === "11"){
						//4x11 \n 14
						console.log("11,11,11,11,14");
						let swap1 = grid[id].pos;
						let swap2 = grid[ele-1].pos;
						let swap3 = grid[ele-2].pos;
						grid[id].pos = grid[ele-3].pos;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos = grid[ele-i-3].pos;
						}
						grid[id+1].pos = swap3;
						grid[id+2].pos = swap2;
						grid[id+3].pos = grid[ele].pos;
						grid[ele].pos = swap1;
					}
			//------12------	
					else if (grid[id].size === "12" && grid[ele].size === "11" && grid[ele+1].size === "11"){
						//"12"over "11","11"
						console.log("12,11,11");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele+1].pos;
						grid[ele+1].pos = grid[id+1].pos;
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos = grid[id+1+i].pos;
						grid[ele-1].pos = swap1;
					}
					else if (grid[id].size === "12" && (grid[id+1].size === "11" && grid[ele].size === "13" || grid[id+1].size === "12" && grid[ele].size === "14")){
						//12,11 \n 13 --or-- 12,12,14
						console.log("12,11,13/12,12,14");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele-1].pos;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos = grid[ele-1-i].pos;
						grid[id+1].pos = swap1;
					}
					else if (grid[id].size === "12" && grid[id+1].size === "11" && grid[id+2].size === "11" && grid[ele].size === "14"){
					//12,11,11 \n 14
						console.log("12,11,11,14");
						let swap1 = grid[id].pos;
						let swap2 = grid[ele-1].pos;
						grid[id].pos = grid[ele-2].pos;
						for (i=1;i<ele-id-2;i++) {
							grid[ele-i].pos = grid[ele-i-2].pos;
						}
						grid[id+1].pos = swap2;
						grid[id+2].pos = grid[ele].pos;
						grid[ele].pos = swap1;
					}
			//------13------		
					else if (grid[id].size === "13" && (grid[ele].size === "12" && grid[ele+1].size === "11") || (grid[ele].size === "11" && grid[ele+1].size === "12")){
						//13 over "12","11" or "11","12"
						console.log("13, 12,11/ 13, 11,12");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele+1].pos;
						grid[ele+1].pos = grid[id+1].pos;
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos = grid[id+1+i].pos;
						grid[ele-1].pos = swap1;
					}
					else if (grid[id].size === "13" && (grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "11")){
						//"13" over 3x"11"
						console.log(13, 11,11,11);
						let swap1 = grid[id].pos;
						grid[id].pos = grid[ele+2].pos;
						grid[ele+2].pos = grid[id+2].pos;	
						let swap2 = grid[id+1].pos;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos = grid[id+i+2].pos;
						grid[ele].pos = swap1;
						grid[ele+1].pos = swap2;
					}
					else if (grid[id].size === "13" && grid[id+1].size === "11" && grid[ele].size === "14"){
						//13,11 \n 14
						console.log("13,11,14");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele-1].pos;
						for (i=1;i<ele-id-1;i++)
							grid[ele-i].pos = grid[ele-1-i].pos;
						grid[id+1].pos = swap1;
					}
			//------14------
					else if (grid[id].size === "14" && 
						(grid[ele].size === "12" && grid[ele+1].size === "12"||
						grid[ele].size === "13" && grid[ele+1].size === "11" || 
						grid[ele].size === "11" && grid[ele+1].size === "13")){
						//14 \n 12,12 --or-- 14 \n 13,11 --or 14 \n 11,13
						console.log("14, 12,12 / 14,13,11 / 14,11,13");
						let swap1 = grid[ele].pos;
						grid[ele].pos = grid[id].pos;
						grid[id].pos = grid[ele+1].pos;
						grid[ele+1].pos = grid[id+1].pos;s
						for (i=1;i<ele-id-1;i++)
							grid[id+i].pos = grid[id+1+i].pos;
						grid[ele-1].pos = swap1;
					}
					else if (grid[id].size === "14" && 
							(grid[ele].size === "11" && grid[ele+1].size === "12" && grid[ele+2].size === "11" ||
							grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "12" ||
							grid[ele].size === "12" && grid[ele+1].size === "11" && grid[ele+2].size === "11")){
						//14 \n 11,12,11 --or-- 14 \n 11,11,12 --or-- 14 \n 12,11,11
						console.log("14, 11,12,11...");
						let swap1 = grid[id].pos;
						grid[id].pos = grid[ele+2].pos;
						grid[ele+2].pos = grid[id+2].pos;	
						let swap2 = grid[id+1].pos;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos = grid[id+i+2].pos;
						grid[ele].pos = swap1;
						grid[ele+1].pos = swap2;
					}
					else if (grid[id].size === "14" && grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "11" && grid[ele+3].size === "11"){
						//14 \n 11,11,11,11
						console.log("14, 11,11,11,11");
						let swap1 = grid[id].pos;
						let swap2 = grid[id+1].pos;
						let swap3 = grid[id+2].pos;
						let swap4 = grid[id+3].pos;
						grid[id].pos = grid[ele+3].pos;
						for (i=1;i<ele-id;i++)
							grid[id+i].pos = grid[id+i+3].pos;
						grid[ele].pos = swap1;
						grid[ele+1].pos = swap2;
						grid[ele+2].pos = swap3;
						grid[ele+3].pos = swap4;
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
			grid.update();
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
			var changed = false;
			if (view === "vertical"){
				swap([id,id-1]);
				changed = true;
			}
			else if (view === "horizontal")
				click_right(id-1);//TODO f.e. "44" jump
			if (changed) 
				grid.update();
		}
	}

	//---- right ----
	function click_right(id){
		id = Number(id);
		var changed = false;
		if (view === "vertical"){
			if (id < grid.length-2){
				swap([id,id+1]);
				changed = true;

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
			grid.update();
	}

	//---- ok ----
	function click_ok(id){
		mode = "show";
		for (i=grid.length-1;i>0;i--){//delete all dummies are at the end
			//begins at the last element
			if (grid[i].type != "dummy"){
				grid.splice(i+1,grid.length-(i-1));
				i=0;
			}
		}
		grid.update();
	}

	//---- delete  ----
	function click_delete(id){
		if (grid[id].type != "settings" && grid[id].type != "dummy"){
			if (view === "vertical")
				grid.splice(id,1);	//dummies are deleted automatically, when they are at the end
			else if (view =="horizontal")
				grid.splice(id,1);//wird noch erweitert, für spezielle fälle
			grid.update();		
		}
	}

