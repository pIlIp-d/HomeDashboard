//-----------------------------------------
//-------- MOVE ---------------------------
//-----------------------------------------

	function swap(pos){//swaps postionts
		console.log(pos);
		for (i=0;i<pos.length;i++)
			pos[i] = Number(pos[i]);
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
			if (grid[id_].size === s)
				return true;
		}
		return false;
	}
	function width_is_four(id){
		size = grid[id].size;
		if (size == "14" || size == "24" || size == "44")
			return true;
		else
			return false;	
	}

	//---- UP ----
	function click_up(id){
		id = Number(id);
		var changed = false;
		if (view === "vertical" && id != 0){
			if (width_is_four(id)){
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
				if (grid[id].start-4 === grid[ele].start || 
					(grid[id].start-8 === grid[ele].start && grid[ele].size === "24")||
					(grid[id].start-12 === grid[ele].start && grid[ele].size === "34") ||
					(grid[id].start-16 === grid[ele].start && grid[ele].size === "44")){//ele is above id
					console.log("ele: "+ele);
					if (grid[id].size === grid[ele].size){
						changed = true;
						swap([id,ele]);
						console.log("same ele");
					}
					switch(grid[id].size){
						case "11":
							changed = true;
							if (grid[ele].size === "12" && grid[id+1].size === "11" ||
								grid[ele].size === "13" && grid[id+1].size === "12"){
								let swap1 = grid[id].pos_v;//"12"over"11","11" or "13"over "11","12" or big over "11","13" 
								for (i=0;i<id-ele-1;i++)
									grid[id-i].pos_v = grid[id-1-i].pos_v;
								grid[Number(ele)+1].pos_v = grid[id+1].pos_v;
								grid[id+1].pos_v = grid[ele].pos_v;
								grid[ele].pos_v = swap1;
							}	
							else if (width_is_four(ele) && grid[id+1].size === "13")
								swap([id+1,id,ele]);		
							else if(grid[ele].size === "13" && grid[id+1].size === "11" && grid[id+2].size === "11")
								swap([ele,id+2,id+1,id]);
							else if(width_is_four(ele) && grid[id+1].size === "11" && grid[id+2].size === "12"||
									width_is_four(ele) && grid[id+1].size === "12" && grid[id+2].size === "11"){
								swap([ele,id+2,id]);
								swap([id-1,id+1]);
							}
							else if (grid[ele].size === "12" && grid[ele+1].size === "12" && grid[id+1].size === "13" ||
									 grid[ele].size === "13" && grid[ele+1].size === "11" && grid[id+1].size === "13"){
								swap([ele,id]);
								swap([ele+1,id+1]);
							}
							else
								changed = false;
							break;
						case "12":
							changed = true;
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
									grid[ele].size === "11" && grid[Number(ele)+1].size === "13" && grid[id+1].size === "12" ||
									grid[ele].size === "13" && grid[1+ele].size === "11" && grid[1+id].size === "12"){
									swap([ele,id]);
									swap([ele+1,id+1]);
							}
							else if (width_is_four(ele) && grid[id+1].size === "11" && grid[id+2] === "11")
								swap([ele,id+2,id+1,id]);
							else if (width_is_four(ele) && grid[id+1].size === "12")
								swap([ele,id+1,id]);
							else if (grid[ele].size === "13" && grid[id+1].size === "11")
								swap([ele,id+1,id-1,id]);
							else
								changed = false;
							break;
						case "13":
							changed = true;
							if (grid[id-1].size === "11"){			
								if (id >= 4 && grid[id-2].size === "11" && grid[id-3].size === "11" && grid[id-4].size === "11"){
									swap([id-2,ele,id]);
									swap([id-1,Number(ele)+1]);
								}
								else if (id >= 3 && (grid[id-2].size === "12" && grid[id-3].size === "11" || grid[id-2].size === "11" && grid[id-3].size === "12"))
									swap([ele,id-1,Number(ele)+1,id]);
								else
									changed = false;
							}
							else if (width_is_four(ele) && grid[id+1].size === "11")
								swap([ele,id+1,id]);
							else if (grid[ele].size === "12" && grid[ele+1].size === "12" && grid[id+1].size === "11" ||
									grid[ele].size === "11" && grid[ele+1].size === "13" && grid[id+1].size === "11"){
									swap([ele,id]);
									swap([ele+1,id+1]);
							}
							else if (grid[id+1].size === "11" && grid[ele].size === "11" && grid[ele+1].size === "11" && grid[ele+2].size === "12")
								swap([ele,id,ele+1,id+1,ele+2]);
							else 
								changed = false;
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
			else if (view === "horizontal")
				click_right(id-1);//TODO f.e. "44" jump
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

