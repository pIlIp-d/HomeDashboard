const server = location.host;
var server_root_url = "/HomeDashboard/";
var GV_URL = "";
var GV_FILTERMODE = "none";
var GV_CLIPBOARD;

var gv_active_bakingplan_id;

var mm_is_open;
var me_is_open;
var me_rec_id;

// alt (?)
var last_expanded = 0;
var global_result = "";
var gv_res = null;
//var gv_reclist_filter = "SELECT * FROM recipes ORDER BY name";
var gv_bpr_id;
var gv_order_no;

function sql_get_count(from, where){
	var xhttp = new XMLHttpRequest();
	var response;
	global_result = null;
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			response = this.responseText;
			if (response === undefined){
			}
			else{
				global_result = response;
				//alert("global_result :" + global_result);
			}
		}
	}
	xhttp.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"sql_get_count\",\"from\":\"" + from + "\",\"where\":\"" + where + "\"}", false);  
	xhttp.send();
}

/* ----- Allgemein -------------------------------------------------------------------- */

// Menüs einklappen bei "scroll" und "resize"
window.addEventListener("scroll", me_hide); 
window.addEventListener("resize", me_hide); 
window.addEventListener("scroll", mm_hide)
window.addEventListener("resize", mm_hide)

/* ------------------------------------------------------------------------------------ */
/* Full-Dialog
/* ------------------------------------------------------------------------------------ */

class full_dialog{
	constructor(dialog_name, btn_left_event, btn_left_content, btn_right_event, btn_right_content, content){		
		this.dialog_name = dialog_name;										// Name des Dialogs
		this.btn_left_event = btn_left_event;							// Event (Funktion) bei Klick auf linken Button
		this.btn_left_content = btn_left_content;					// Inhalt für linken Button
		this.btn_right_event = btn_right_event;						// Event (Funktion) bei Klick auf rechten Button
		this.btn_right_content = btn_right_content;				// Inhalt für rechten Button
		this.content = content;														// darzustellender HTML-Inhalt (initial leer)
		this.dlg_content = full_dialog.dlg_content;
		var dialogs = document.getElementById("dialogs");
		var dlg_header = full_dialog.dlg_header;
		var html = "";
		// HTML für Header erzeugen
		html += "<span id=\"header_left_button\" class=\"fdlg_header_button\" onclick=\"" + this.btn_left_event + "\">" + this.btn_left_content + "</span>"; // Pfeil-Symbol: &#11153;
		html += "<span id=\"header_dialog_name\">" + this.dialog_name + "</span>\n";
		html += "<span id=\"header_right_button\" class=\"fdlg_header_button\" onclick=\"" + this.btn_right_event + "\">" + this.btn_right_content + "</span>"; // Kreuz-Symbol: &#10005;
		// Dialog-Objekt erzeugen
		this.dlg = document.createElement("div");
		this.dlg.classList.add("fdlg");
		this.dlg.id = "dlg";
		this.dlg.innerHTML = "";
		dialogs.appendChild(this.dlg);
		// Header-Objekt erzeugen
		dlg_header = document.createElement("div");
		dlg_header.classList.add("fdlg_header");
		dlg_header.id = "dlg_header";
		dlg_header.innerHTML = html;
		this.dlg.appendChild(dlg_header);
		// Content-Objekt erzeugen
		this.dlg_content = document.createElement("div");
		this.dlg_content.classList.add("fdlg_content");
		this.dlg_content.id = "dlg_content";
		this.dlg_content.innerHTML = this.content;
		this.dlg.appendChild(this.dlg_content);
	}

	show(){
		// Animation einblenden von rechts
		this.dlg.style.left = "100%";
		this.dlg.style.display = "block";
		this.dlg.style.animation = "fdlg_show 0.5s";
		this.dlg.style.left = "0px";
	}

	hide(){
		const mydialog = this;
		// Animation ausblenden nach rechts
		mydialog.dlg.style.animation = "fdlg_hide 0.5s";
		// Dialog-Objekt löschen wenn Animation beendet ist
		setTimeout (function() { 
	    mydialog.dlg.style.display = "none";
	    }, 500);
	}
}

/* ------------------------------------------------------------------------------------ */
/* Filter-Dialog
/* ------------------------------------------------------------------------------------ */

class filter_dialog extends full_dialog{
	constructor(dialog_name, btn_left_event, btn_left_content, btn_right_event, btn_right_content, content, show, hide){		
		super (dialog_name, btn_left_event, btn_left_content, btn_right_event, btn_right_content, content, show, hide);
	}
	
	save_and_hide(){
		if (GV_FILTERMODE != "none"){
			var allItems = document.getElementsByClassName("item");
			var Items = [];
			var j = 0;
			var item_is_selected = false;
			// Liste aller selektierten Zutaten erstellen
			//{"request_name":"get_list_of_recipes","id_list":[{"id":"1"},{"id":"3"},{"id":"7"}],"count":"3",\"filtermode\":\"ingredients\"}
			var json = "{\"request_name\":\"get_list_of_recipes\",\"id_list\":[";
			for(var i = 0; i < allItems.length; i++) {
				if (allItems[i].title == "selected"){
					if (j == 0){
						json += "{\"id\":\"" + allItems[i].id.replace("item_", "") + "\"}";
					}
					else{
						json += ",{\"id\":\"" + allItems[i].id.replace("item_", "") + "\"}";
					}
					j++;
					item_is_selected = true;
				}
			}
			json += "],\"count\":\"" + j + "\",\"filtermode\":\"" + GV_FILTERMODE + "\"}";
			//alert(json);
			// url für Rezepte erstellen
			if (item_is_selected){
				GV_URL = server_root_url + "wfo_db.php?json=" + json;
				GV_FILTERMODE = "ingredients";
			}
			else{
				GV_URL = server_root_url + "wfo_db.php?json={\"request_name\":\"get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
				GV_FILTERMODE = "none";
			}
			reclist_get_all_recipes();
			dialog_filter.hide();
		}
	}
	
	createHTML(){
		var html = "";
		GV_FILTERMODE = "ingredients";
		// Elemente für Bereich "Suche" und "Filter" erstellen
		html += "<div class=\"dlg_filter_search\" id=\"dlg_filter_search\">";
		html += "</div>";
		html += "<div class=\"dlg_filter_filter\" id=\"dlg_filter_filter\">";
		html += "</div>";
		document.getElementById("dialog_filter_content").innerHTML = html;
		filter_dialog.area_search_createHTML();
		filter_dialog.area_filter_createHTML("ingredients");
	}

	static area_search_createHTML(){
		// Bereich "Suche"
		var html = "";
		html += "					<span><input type=\"radio\" name=\"searchmode\" id=\"ingredients\" value=\"Zutaten\" checked onclick=\"dialog_filter.toggle_filter_mode(this.id)\">Zutaten</span>\n";
		html += "					<span><input type=\"radio\" name=\"searchmode\" id=\"recipes\" value=\"Rezepte\" onclick=\"dialog_filter.toggle_filter_mode(this.id)\">Rezepte</span>\n";
		html += "					<span><input id=\"searchfield\" style=\"margin-right:10px;\" onkeyup=\"checkinput(this.value)\">&#128270</span>\n";
		document.getElementById("dlg_filter_search").innerHTML = html;
	}

	static area_filter_createHTML(filtermode){
		// Bereich "Filter"
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"get_ingredients_or_rezipes\",\"filtermode\":\"" + filtermode + "\"}";
		const xhttp = new XMLHttpRequest();
		var html = "";
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
				const json_object = JSON.parse(res);
				for (i = 0; i < json_object.length; ++i) {						
					html += "<div class=\"item item_main\" id=\"item_" + json_object[i].id + "\" title=\"unselected\" onclick=\"dialog_filter.toggle_item(" + json_object[i].id + ")\">";
					html += "	<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\"images/btn_unselected.svg\"></span>";
					html += "	<span class=\"flexitem item\" align=\"left\">" + base64_2_specialchars(json_object[i].name) + "</span>";
					html += "</div>";
				}
				document.getElementById("dlg_filter_filter").innerHTML = html;
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();		
	}

	toggle_filter_mode(filtermode){
		GV_FILTERMODE = filtermode;
		document.getElementById("searchfield").value = "";
		filter_dialog.area_filter_createHTML(filtermode);
	}

	toggle_item(id){
		var item = document.getElementById("item_" + id);
		if (item.title == "unselected"){
			item.style.backgroundColor = "#B0B0B0";
			item.title = "selected";
			document.getElementById("btn_sel_unsel_" + id).src = "images/btn_selected.svg";				
		}
		else{
			item.style.backgroundColor = "#F0F0F0";
			item.title = "unselected";
			document.getElementById("btn_sel_unsel_" + id).src = "images/btn_unselected.svg";				
		}
	}

} // class filter_dialog


	function checkinput(value){
		//alert(value);
		var allItems = document.getElementsByClassName("item_main");
		const regex = new RegExp(".*" + value + ".*", "i");
		if (value == ""){
			for(var i = 0; i < allItems.length; i++) {
				document.getElementById(allItems[i].id).style.backgroundColor = "#F0F0F0";
				document.getElementById(allItems[i].id).title = "unselected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = "images/btn_unselected.svg";				
			}
		return;
		}
		for(var i = 0; i < allItems.length; i++) {
			if (regex.test(allItems[i].innerText)){
				document.getElementById(allItems[i].id).style.backgroundColor = "#B0B0B0";
				document.getElementById(allItems[i].id).title = "selected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = "images/btn_selected.svg";				
			}
			else{
				document.getElementById(allItems[i].id).style.backgroundColor = "#F0F0F0";
				document.getElementById(allItems[i].id).title = "unselected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = "images/btn_unselected.svg";				
			}
		}
	}

/* ------------------------------------------------------------------------------------ */
/* Zutaten
/* ------------------------------------------------------------------------------------ */

class ingredients_dialog extends full_dialog {
	constructor(dialog_name, btn_left_event, btn_left_content, btn_right_event, btn_right_content, content, show, hide){		
		super (dialog_name, btn_left_event, btn_left_content, btn_right_event, btn_right_content, content, show, hide);
	}
	
	save_and_hide(){
		ACT_REZIPE.read_data_from_db();
		ACT_REZIPE.write_data_to_GUI()
		dialog_ingredients.hide();
	}
	
	createHTML(){
		var html = "";
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"get_all_ingredients\"}";
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
				const json_object = JSON.parse(res);
				for (i = 0; i < json_object.length; ++i) {						
					// Sonderzeichen im Zutaten-Name dekodieren
					json_object[i].name = base64_2_specialchars(json_object[i].name);
					html += "<div class=\"ingredient\" id=\"" + json_object[i].id + "\" title=\"unselected\" style=\"background-color: #F0F0F0;\">"
					html += "	<span class=\"flexitem ingredient\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\"images/btn_unselected.svg\" onclick=\"dialog_ingredients.toggle_ingredient(" + json_object[i].id + ")\"></span>"
					html += "	<span class=\"flexitem ingredient\" id=\"ingr_name_" + json_object[i].id + "\" style=\"margin-top: 4px; width:100%; text-align: left;\" onclick=\"dialog_ingredients.toggle_ingredient(" + json_object[i].id + ")\">" + json_object[i].name + "</span>"
					html += "	<span class=\"flexitem ingredient\"><img class=\"img_btn_medium\" id=\"btn_del_" + json_object[i].id + "\" src=\"images/btn_close.svg\" onclick=\"dialog_ingredients.delete_ingredient(" + json_object[i].id + ")\"></span>"
					html += "</div>"
				}
				document.getElementById("allIngredients").innerHTML = html;
				ingredients_dialog.select_selected_ingredients();
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();
	}
	
	add_ingredient(){
		const result = window.prompt("Name der neuen Zutat:");  
		if (result != ""){
			const url = server_root_url + "wfo_db.php?json={\"request_name\":\"insert_ingredient\",\"ingr_name\":\"" + result + "\"}";
			const xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
				dialog_ingredients.createHTML();
				}
			}
			xhttp.open("GET", url, false);  
			xhttp.send();
		}
	}
	
	static select_selected_ingredients(){
		if (ACT_REZIPE.ingredients.length > 0){
			for (var i = 0; i < ACT_REZIPE.ingredients.length; ++i) {
				document.getElementById(ACT_REZIPE.ingredients[i].i_id).style.backgroundColor = "#B0B0B0";
				document.getElementById(ACT_REZIPE.ingredients[i].i_id).title = "selected";
				document.getElementById("btn_sel_unsel_" + ACT_REZIPE.ingredients[i].i_id + "").src="images/btn_selected.svg";
			}
		}
	}
	
	toggle_ingredient(id){
		var ingredient = document.getElementById(id);
		if (ingredient.title == "unselected"){
			// Darstellung anpassen
			ingredient.style.backgroundColor = "#B0B0B0";
			ingredient.title = "selected";
			document.getElementById("btn_sel_unsel_" + id).src = "images/btn_selected.svg";	
			// Zutat zum Rezept-Objekt hinzufügen	
			ingredients_dialog.add_ingredient_to_recipe(id);
		}
		else{
			ingredient.style.backgroundColor = "#F0F0F0";
			ingredient.title = "unselected";
			document.getElementById("btn_sel_unsel_" + id).src = "images/btn_unselected.svg";				
			// Zutat aus Rezept-Objekt entfernen	
			ingredients_dialog.remove_ingredient_from_recipe(id);
		}
	}
	
	static add_ingredient_to_recipe(id){
		// Zutat zum Rezept hinzufügen
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"" + ACT_REZIPE.id + "\",\"i_id\":\"" + id + "\"}";
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();
	}
	
	static remove_ingredient_from_recipe(id){
		// Zutat zum Rezept hinzufügen
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"remove_ingredient_from_recipe\",\"rec_id\":\"" + ACT_REZIPE.id + "\",\"i_id\":\"" + id + "\"}";
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();
	}

	delete_ingredient(id){
		// Prüfen ob Zutat in Rezepten verwendet wird
		const url1 = server_root_url + "wfo_db.php?json={\"request_name\":\"get_count_of_ingredient_recipes\",\"i_id\":\"" + id + "\"}";
		const xhttp1 = new XMLHttpRequest();
		xhttp1.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res1 = this.responseText;
				if (res1.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url1);
				}
				// Abfrage ob Zutat wirklich gelöscht werden soll wenn Zutat in keinem Rezept verwendet wird
				if (res1 == 0){
					if (confirm("Zutat #" + id + " wirklich löschen?") == true){
						// Zutat löschen
						const url2 = server_root_url + "wfo_db.php?json={\"request_name\":\"delete_ingredient\",\"i_id\":\"" + id + "\"}";
						const xhttp2 = new XMLHttpRequest();
						xhttp2.onreadystatechange = function() {
							if (this.readyState == 4 && this.status == 200){
								const res2 = this.responseText;
								if (res2.substr(0, 8) == "DB_ERROR"){
									alert("DB-Fehler bei Request: " + url2);
								}
								dialog_ingredients.createHTML();
							}
						}
						xhttp2.open("GET", url2, false);  
						xhttp2.send();
					}
				}
				else{
					alert("Zutat kann nicht gelöscht werden !\nZutat wird in " + res1 + " Rezepten verwendet.");
				}
			}
		}
		xhttp1.open("GET", url1, false);  
		xhttp1.send();
	}

}	// class ingredients_dialog

/* ------------------------------------------------------------------------------------ */
/* Rezept
/* ------------------------------------------------------------------------------------ */

class recipe_ingredient{
	constructor(ri_id, i_id, name, amount, action){
		this.ri_id = ri_id;														// Zutaten-ID (Tabelle recipes_ingredients)
		this.i_id = i_id;															// Zutaten-ID (Tabelle ingredients)
		this.name = name;															// Zutaten-Name
		this.amount = amount;													// Menge
		this.action = action;													// "del": Zutat wurde entfernt / "add": Zutat wurde hinzugefügt
	}
} //class recipe_ingredient

class recipe{
	constructor(id, name, time, temperature, preparation, ingredients, completed){
		this.id = id;																	// Rezept-ID
		this.name = name;															// Rezept-Name
		this.time = time;															// Backzeit
		this.temperature = temperature;								// Backtemperatur
		this.preparation = preparation;								// Zubereitung
		this.ingredients = ingredients;								// Zutaten
		this.completed = completed;										// Flag wenn Daten komplett sind (true, false)
	}

	//show(id){
	show(){
		ACT_REZIPE.read_data_from_db(ACT_REZIPE.id);
		ACT_REZIPE.write_data_to_GUI();
		dialog_recipe_edit.show();
	}
	
	createHTML(){
		return recipe.createHTML();
	}
	
	clear(){
		ACT_REZIPE.id = 0;
		ACT_REZIPE.name = "";
		ACT_REZIPE.time = "";
		ACT_REZIPE.temperature = "";
		ACT_REZIPE.preparation = "";
		ACT_REZIPE.ingredients = null;
		ACT_REZIPE.completed = false;	
	}
	
	read_data_from_db(){
		recipe.read_data_from_db();
	}

	write_data_to_db(){
		recipe.write_data_to_db()
	}
	
	read_data_from_GUI(){
		recipe.read_data_from_GUI();
	}
	
	write_data_to_GUI(){
		recipe.write_data_to_GUI()
	}
	
	static read_data_from_db(){
		// Rezeptdaten in ACT_RECIPE-Array einlesen
		// bei neuem Rezept: Rezept-Daten in Objekt und HTML zurücksetzen 
		if (ACT_REZIPE.id == 0){
			document.getElementById("header_dialog_name").innerText = "Rezept neu";
			ACT_REZIPE.id = 0;
			ACT_REZIPE.name = "";
			ACT_REZIPE.time = "";
			ACT_REZIPE.temperature = "";
			ACT_REZIPE.preparation = "";
			ACT_REZIPE.ingredients = null;
			ACT_REZIPE.completed = true;
			document.getElementById("recipename").value = "";
			document.getElementById("time").value = "";
			document.getElementById("temperature").value = "";
			prep_editor.setData("");
			document.getElementById("ingr_table").innerHTML = "";
		}		
		else{
			// bei bestehendem Rezept: Rezept-Daten aus DB laden 
			document.getElementById("header_dialog_name").innerText = "Rezept bearbeiten";
			const url = server_root_url + "wfo_db.php?json={\"request_name\":\"get_recipe_data\",\"id\":\"" + ACT_REZIPE.id + "\"}";
			const xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
					const json_object = JSON.parse(res);
					ACT_REZIPE.id = json_object[0].id;
					ACT_REZIPE.name = base64_2_specialchars(json_object[0].name);
					ACT_REZIPE.time = base64_2_specialchars(json_object[0].bakingtime);
					ACT_REZIPE.temperature = base64_2_specialchars(json_object[0].bakingtemperature);
					ACT_REZIPE.preparation = base64_2_specialchars(json_object[0].preparation);
					ACT_REZIPE.completed = false;
					recipe.read_ingredients_from_db();
				}
			}
			xhttp.open("GET", url, false);  
			xhttp.send();		
		}
	}

	static read_ingredients_from_db(){
		// Zutaten zum Rezept in ACT_RECIPE-Array einlesen
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"get_recipe_ingredients\",\"id\":\"" + ACT_REZIPE.id + "\"}";
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
				const json_object = JSON.parse(res);
				var act_rezipe_ingredient = new recipe_ingredient (null, null, null, null, null);
				var act_rezipe_ingredients = [];
				for (i = 0; i < json_object.length; ++i) {
					act_rezipe_ingredient = new recipe_ingredient (null, null, null, null, null);
					act_rezipe_ingredient.ri_id = json_object[i].ri_id;
					act_rezipe_ingredient.i_id = json_object[i].i_id;
					act_rezipe_ingredient.name = json_object[i].name;
					act_rezipe_ingredient.amount = json_object[i].amount;
					act_rezipe_ingredient.action = "";
					act_rezipe_ingredients[i] = act_rezipe_ingredient;
					act_rezipe_ingredient = null;
				}
				ACT_REZIPE.ingredients = act_rezipe_ingredients;
				ACT_REZIPE.completed = true;
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();
	}

	static write_data_to_db(){
		// speichert alle Werte eines Rezeptes in der DB		
		// Rezept in DB anlegen wenn neues Rezept
		if (ACT_REZIPE.id == 0){
			var url = server_root_url + "wfo_db.php?json={\"request_name\":\"add_recipe\"";
			url += ",\"rec_name\":\"" + specialchars_2_base64(ACT_REZIPE.name) + "\"";
			url += ",\"rec_bakingtime\":\"" + specialchars_2_base64(ACT_REZIPE.time) + "\"";
			url += ",\"rec_bakingtemperature\":\"" + specialchars_2_base64(ACT_REZIPE.temperature) + "\"";
			url += ",\"rec_preparation\":\"" + specialchars_2_base64(ACT_REZIPE.preparation) + "\"}";
			//alert(url);
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
					ACT_REZIPE.id = parseInt(res);
					recipe.write_ingredients_to_db();
				}
			}
			xhttp.open("GET", url, false);  
			xhttp.send();		
		}
		else{
			var url = server_root_url + "wfo_db.php?json={\"request_name\":\"update_recipe\"";
			url += ",\"rec_id\":\"" + specialchars_2_base64(ACT_REZIPE.id) + "\"";
			url += ",\"rec_name\":\"" + specialchars_2_base64(ACT_REZIPE.name) + "\"";
			url += ",\"rec_bakingtime\":\"" + specialchars_2_base64(ACT_REZIPE.time) + "\"";
			url += ",\"rec_bakingtemperature\":\"" + specialchars_2_base64(ACT_REZIPE.temperature) + "\"";
			url += ",\"rec_preparation\":\"" + specialchars_2_base64(ACT_REZIPE.preparation) + "\"}";
			//alert(url);
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
					//id = parseInt(res);
					recipe.write_ingredients_to_db();
				}
			}
			xhttp.open("GET", url, false);  
			xhttp.send();		
		}
	}

	static write_ingredients_to_db(){
		// speichert alle Zutaten eines Rezeptes in der DB
		var url = "";
		for (var i=0; i<ACT_REZIPE.ingredients.length; i++) {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					//alert(res);
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
				}
			}
			// JSON-String mit den Werten zusammenbauen und an DB schicken
			url = server_root_url + "wfo_db.php?json={\"request_name\":\"update_recipe_ingredient\"";
			url += ",\"ri_id\":\"" + ACT_REZIPE.ingredients[i].ri_id + "\"";
			url += ",\"ri_amount\":\"" + specialchars_2_base64(ACT_REZIPE.ingredients[i].amount) + "\"}";	
			xhttp.open("GET", url, false);  
			xhttp.send();
		}
	}
	
	static write_data_to_GUI(){
		// Rezeptdaten in Dialog anzeigen
		document.getElementById("recipename").value = ACT_REZIPE.name;
		document.getElementById("time").value = ACT_REZIPE.time;
		document.getElementById("temperature").value = ACT_REZIPE.temperature;
		prep_editor.setData(ACT_REZIPE.preparation);
		// Zutaten Liste
		var html = "";
		//if (ACT_REZIPE.ingredients.length > 0){
		if (ACT_REZIPE.ingredients != null){
			for (i = 0; i < ACT_REZIPE.ingredients.length; ++i) {
				html += "	<tr>";
				html += "		<td class=\"amount\" id=\"amount_" + ACT_REZIPE.ingredients[i].ri_id + "\" contenteditable nowrap style=\"background-color: #ffffff; min-width: 40px;\" onfocus=\"select_text(this)\">" + base64_2_specialchars(ACT_REZIPE.ingredients[i].amount) + "</td>";
				html += "		<td id=\"ingr_" + ACT_REZIPE.ingredients[i].ri_id + "\">" + base64_2_specialchars(ACT_REZIPE.ingredients[i].name) + "</td>";
				html += "	</tr>";
			}
			document.getElementById("ingr_table").innerHTML = html;
		}
	}
		
	static read_data_from_GUI(){
		// Rezeptdaten in Rezept-Objekt speichern
		ACT_REZIPE.name = document.getElementById("recipename").value;
		ACT_REZIPE.time = document.getElementById("time").value;
		ACT_REZIPE.temperature = document.getElementById("temperature").value;
		ACT_REZIPE.preparation = prep_editor.getData();
		// Zutaten aus GUI in Rezept-Objekt speichern
		const ingredients = document.querySelectorAll('td[class="amount"]');
		for (var i=0; i<ingredients.length; i++) {
			ACT_REZIPE.ingredients[i].ri_id = ingredients[i].id.replace("amount_", "");
			ACT_REZIPE.ingredients[i].amount = ingredients[i].innerText;
		}
		ACT_REZIPE.completed = true;
	}
		
	static createHTML(){
		var html = "";
		html += "<div class=\"dlg_rec\" id=\"rec_c_0\">";
		html += "	<div class=\"dlg_rec_name\">";
		html += "		<input class=\"dlg_recipename\" id=\"recipename\" type=\"text\" placeholder=\"Rezeptname...\" value=\"\">";
		html += "	</div>";
		html += "	<div class=\"dlg_rec_details\" id=\"rec_details\">";
		html += "		<span class=\"flexitem dlg_rec_details\" id=\"rec_time\">";
		html += "			<img class=\"img_btn\" id=\"sym_clock\" src=\"images/sym_clock.svg\" alt=\"sym_clock\" style=\"margin-right: 5px;\">";
		html += "			<input class=\"dlg_time_temp\" id=\"time\" type=\"number\" min=\"0\" max=\"999\" step=\"1\" inputmode=\"decimal\" onkeypress=\"validate_number(event)\" value=\"\"> min";
		html += "		</span>";
		html += "		<span class=\"flexitem dlg_rec_details\" id=\"rec_temp\">";
		html += "			<img id=\"sym_thermo\" src=\"images/sym_thermo.svg\" alt=\"sym_thermo\" style=\"margin-right: 5px; width:15px; hight:15px\">";
		html += "			<input class=\"dlg_time_temp\" id=\"temperature\" type=\"number\" min=\"0\" max=\"999\" step=\"1\" inputmode=\"decimal\" onkeypress=\"validate_number(event)\" value=\"\"> °C";
		html += "		</span>";
		html += "	</div>";
		html += "		<table class=\"dlg_header_table\">";
		html += "			<tr>";
		html += "				<td style=\"text-align: left;\">Zutaten:</td>";
		html += "				<td style=\"width: 30px; height: 30px; background-color: gray; font-size: 1.5rem;\" onclick=\"ingredients_edit()\">&#177;</td>";
		html += "			</tr>";
		html += "		</table>";
		html += "		<table class=\"dlg_ingr_table\" id=\"ingr_table\">";
		html += "		</table>";
		html += "		<table class=\"dlg_header_table\">";
		html += "			<tr>";
		html += "				<td style=\"text-align: left; padding: 3px;\">Zubereitung:</td>";
		html += "			</tr>";
		html += "		</table>";
		html += " 	<div id=\"prep_editor\"></div>";
		html += "</div>";
		return html;
	}		
/*
	static recipe_new(){
		// Prüfen ob schon ein neues Rezept existiert
		sql_get_count("recipes", "name = '[new]'");
		// Neues Dummy-Rezept anlegen, wenn kein neues Rezept existiert
		if (global_result == 0){
			var sql1 = "INSERT INTO recipes (id, name, preparation, bakingtime, bakingtemperature) VALUES (NULL, '[new]', '', '', '')";
			var xhttp1 = new XMLHttpRequest();
			xhttp1.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					var res1 = this.responseText;
					//alert(res1);
				}
			}
			xhttp1.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"sql_execute\",\"sql\":\"" + sql1 + "\"}", false);  
			xhttp1.send();
		}
		// Rezept-ID des neuen Rezepts ermitteln
		sql2 = "SELECT id FROM recipes WHERE name = '[new]'";
		var xhttp2 = new XMLHttpRequest();
		xhttp2.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res2 = this.responseText;
				if (res2.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei:\n" + sql2);
				}
				//alert("SQL: \n" + sql2 + "\nJSON: \n" + res2);
				var json_object = JSON.parse(res2);
				for (i = 0; i < json_object.length; ++i) {
					me_rec_id = json_object[i].id;
				}
				//document.getElementById("f_msg").innerText = "[recipe-id: " + id + "]";
			}
			if (this.readyState == 4 && this.status != 200){
				alert(this.status);
			}
		}
		xhttp2.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"sql2json_new\",\"sql\":\"" + sql2 + "\"}", false);  
		xhttp2.send();		
	}		
*/		
}	//class recipe

function select_text(elem){
	// selektiert den gesamten Text in einem Element
	var range = document.createRange();
	range.selectNodeContents(elem);
	var sel = window.getSelection();
	sel.removeAllRanges();
	sel.addRange(range);
}

function validate_number(evt) {
	// schliesst alle nicht-numerischen Zeichen bei der Eingabe aus
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode( key );
	var regex = /[0-9]|\./;
	if( !regex.test(key) ) {
		theEvent.returnValue = false;
	if(theEvent.preventDefault) theEvent.preventDefault();
	}
}

/* ----- Kodierung / Dekodierung ------------------------------------------------------ */

function string_2_base64(string){
// Dekodiert einen kompletten String aus Base64-Kodierung
//			"&+#
//	result = "[ENCODEDstring]" + btoa(string);
	result = btoa(string);
	return result;
}

function base64_2_string(string){
// Kodiert einen kompletten String mit Base64-Kodierung
//			"&+'#
/*
	if (string.substr(0,15) == "[ENCODEDstring]"){
		var result = string.replace("[ENCODEDstring]", "");
		result = atob(result);
		return result;
	}
	else{
		return string;
	}
*/
	result = atob(string);
	return result;
}

function specialchars_2_base64(string){
// ersetzt die folgenden Sonderzeichen durch eine Base64-Kodierung in eckige Klammen gehüllt
// " , & , + , #
	string = string + "";//force string-type
	string = string.replace(/\"/g, btoa("[\"]"));
	string = string.replace(/&/g, btoa("[&]"));
	string = string.replace(/\+/g, btoa("[+]"));
	string = string.replace(/#/g, btoa("[#]"));
	return string;
}

function base64_2_specialchars(string){
// ersetzt die folgenden in eckige Klammen gehüllten kodierten Sonderzeichen durch den eigentlichen Wert
// "(WyJd) , &(WyZd) , +(Wytd) , #(WyNd)
	string = string + "";//force string-type
	string = string.replace(/WyJd/g, "\"");
	string = string.replace(/WyZd/g, "&");
	string = string.replace(/Wytd/g, "+");
	string = string.replace(/WyNd/g, "#");
	return string;
}

/* ----- Templates -------------------------------------------------------------------- */

function template_HTTPRequest(){
		const url = server_root_url + "wfo_db.php?json={\"request_name\":\"get_recipe_data\",\"id\":\"" + ACT_REZIPE.id + "\"}";
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
				const json_object = JSON.parse(res);
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();		
}

/* ----- Element-Menü ----------------------------------------------------------------- */

function me_init(menu_items){
	if (document.getElementById("me")) {
		var html = "";
		html += "					<table class=\"me_table\" id=\"me_table\"><tr>";
		html += "						</tr>";
		var col_width = 300 / (menu_items.length - 1);
		html += "							<td class=\"me_arrow\" id=\"me_arrow\" onclick=\"me_hide()\"><</td>";
		for (var i = 1; i < menu_items.length; ++i) {
			html += "							<td width=" + col_width + "px bgcolor=" + menu_items[i][2] + " onclick=\"" + menu_items[i][1] + "\">" + menu_items[i][0] + "</td>";
		}
		html += "						</tr>";
		html += "					</table>";
		document.getElementById("me").innerHTML = html;
	}
}

function me_show(id){
	//var rec_id = id.replace("rec_btn_", "")
	var box = document.getElementById("rec_table_" + id);
	var menu_elem = document.getElementById("me");
	var box_BCR = box.getBoundingClientRect();	

	var box_top = box_BCR.top;
	var box_left = box_BCR.left;
	var box_height = box.offsetHeight;
	var box_width = box.offsetWidth;

	var elem_top = box_top;
	var elem_left = box_left + box_width - 330;
	var elem_height = box_height;

	menu_elem.style.top = elem_top + "px";
	menu_elem.style.left = elem_left + "px";
	menu_elem.style.height = elem_height + "px";

	menu_elem.style.boxShadow = "3px 3px 20px black";
	menu_elem.style.width = "0px";
	var newleft = parseInt(menu_elem.style.left.replace("px", ""));
	menu_elem.style.left = newleft + 330 + "px";
	$(".me").animate({"left": "-=330px", "width": "330px"}, "fast");
	menu_elem.style.display = "block";
	document.getElementById("me_arrow").innerHTML = ">";
	me_is_open = true;
	//me_rec_id = id;
	ACT_REZIPE.clear();
	ACT_REZIPE.id = id;

}

function me_hide(){
	document.getElementById("me").style.display = "none";
	//$(".me").animate({"left": "+=300px"}, "fast");
	document.getElementById("me_arrow").innerHTML = "<";
	me_is_open = false;
}

/* ----- Haupt-Menü ------------------------------------------------------------------- */

function menu_main_repaint(menu_items){
	if (document.getElementById("mm")) {
		var html = "";
		html += "					<table class=\"mm_table\" id=\"mm_table\"><tr>";
		html += "						</tr>";
		var col_width = 300 / (menu_items.length - 1);
		html += "							<td class=\"mm_arrow\" id=\"mm_arrow\" onclick=\"mm_show_hide()\">&#10094;</td>";
		for (var i = 1; i < menu_items.length; ++i) {
			html += "							<td width=" + col_width + "px bgcolor=" + menu_items[i][2] + " onclick=\"" + menu_items[i][1] + "\">" + menu_items[i][0] + "</td>";
		}
		html += "						</tr>";
		html += "					</table>";
		// menu_main löschen
		var menu_main = document.getElementById("mm");
		var parent_node = menu_main.parentNode;
		parent_node.removeChild(menu_main);
		// menu_main neu erzeugen
		var child = document.createElement("span");
		child.classList.add("mm");
		child.id = "mm";
		parent_node.appendChild(child);
		document.getElementById("mm").innerHTML = html;
	}
}

function mm_show_hide(){
	if (mm_is_open){
		mm_hide();
	}
	else{
		mm_show();
	}
}

function mm_show(){
	document.getElementById("mm").style.boxShadow = "3px 3px 20px black";
	$(".mm").animate({"left": "-=300px"}, "fast");
	document.getElementById("mm_arrow").innerHTML = "&#10095;"
	mm_is_open = true;
}

function mm_hide(){
	menu_main_init();
	//$(".menu_main").animate({"left": "+=300px"}, "fast");
	document.getElementById("mm_arrow").innerHTML = "&#10094;"
	mm_is_open = false;
}

/* ----- Dialog full ------------------------------------------------------------------ */

function dlg_full_show(headername) { 
	dlg_full_create_HTML(headername);
	switch (headername){
		case "Filter":
			dlg_filter_create_HTML();
			break;
		case "Backplan auswählen":
			dlg_select_bakingplan_create_HTML(headername);
			break;			
		case "Backplan löschen":
			dlg_select_bakingplan_create_HTML(headername);
			break;			
		case "Neue Rezepte auswählen":
			dlg_bakingplan_select_recipe_create_HTML();
			break;			
		default:
			alert("Kein Dialog-Name angegeben!");
			break;
	}
	var dlg_full = document.getElementById("dlg_full");
	dlg_full.style.display = "block";
	dlg_full.animate([{width: "0px"}, {width: "100%"}], {duration: 300, iterations: 1, fill: 'forwards'});
}

function dlg_full_hide() { 
	var dlg_full = document.getElementById("dlg_full");
	dlg_full.animate([{width: "100% "}, {width: "0px"}], {duration: 300, iterations: 1, fill: 'forwards'});
	// Dialog ausblenden, wenn Animation beendet ist
	setTimeout(function() {
		dlg_full.style.display = "none";
	}, 300);
}

function dlg_full_create_HTML(headername){
	// Dialog-Objekt erzeugen
	main = document.getElementById("main");
	dlg_full = document.createElement("div");
	dlg_full.classList.add("dlg_full");
	dlg_full.id = "dlg_full";
	dlg_full.innerHTML = "";
	main.appendChild(dlg_full);
	// Header-Objekt erzeugen
	dlg_full_header = document.createElement("div");
	dlg_full_header.classList.add("dlg_full_header");
	dlg_full_header.id = "dlg_full_header";
	dlg_full_header.innerHTML = "";
	dlg_full.appendChild(dlg_full_header);
	// Content-Objekt erzeugen
	dlg_full_content = document.createElement("div");
	dlg_full_content.classList.add("dlg_full_content");
	dlg_full_content.id = "dlg_full_content";
	dlg_full_content.innerHTML = "";
	dlg_full.appendChild(dlg_full_content);
	// Header erzeugen
	var html = "";
	html += "				<span id=\"btn_h_back\" style=\"margin-left:10px; margin-right:10px; font-size:1.0rem;\">&#11153;</span>\n";
	html += "				<span id=\"headername\">" + headername + "</span>\n";
	html += "				<span style=\"margin-left:10px; margin-right:10px; font-size:1.0rem;\"></span>\n";
	document.getElementById("dlg_full_header").innerHTML = html;
}

function dlg_full_toggle_item(id){
	var item = document.getElementById("item_" + id);
	if (item.title == "unselected"){
		item.style.backgroundColor = "#B0B0B0";
		item.title = "selected";
		document.getElementById("btn_sel_unsel_" + id).src = "images/btn_selected.svg";				
	}
	else{
		item.style.backgroundColor = "#F0F0F0";
		item.title = "unselected";
		document.getElementById("btn_sel_unsel_" + id).src = "images/btn_unselected.svg";				
	}
}


/* ----- Dialog Backplan auswählen / löschen ------------------------------------------ */

function dlg_select_bakingplan_create_HTML(headername){
	document.getElementById("btn_h_back").addEventListener("click", dlg_select_bakingplan_close);	 
	var html = "";
	var html = "";
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			var res = this.responseText;
			if (res.substr(0, 8) == "DB_ERROR"){
				alert("DB-Fehler");
			}
			//alert("SQL: \n" + sql + "\nJSON: \n" + res);
			var json_object = JSON.parse(res);
			for (i = 0; i < json_object.length; ++i) {						
				if (json_object[i].type != "active"){
					html += "					<div class=\"item\" id=\"item_" + json_object[i].id + "\" style=\"background-color: lightgray;\" onclick=\"dlg_select_bakingplan_close([" + json_object[i].id + ", '" + json_object[i].name + "'])\">\n";
					html += "						<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\"images/btn_unselected.svg\"></span>\n";
				}
				else{
					html += "					<div class=\"item\" id=\"item_" + json_object[i].id + "\" style=\"background-color: gray;\" onclick=\"dlg_select_bakingplan_close([" + json_object[i].id + ", '[active bakingplan]'])\">\n";
					html += "						<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\"images/btn_selected.svg\"></span>\n";
				}
				html += "						<span class=\"flexitem item\" align=\"left\">" + base64_2_specialchars(json_object[i].name) + "</span>\n";
				html += "					</div>\n";
			}
			document.getElementById("dlg_full_content").innerHTML = html;
		}
	}
	xhttp.open("GET", server_root_url + "wfo_db.php?json=" + "{\"request_name\":\"get_all_bakingplans\"}", false);  
	xhttp.send();
}

function dlg_select_bakingplan_close(id){
	// wenn eine id übergeben wurde ...
	if (typeof(id[0]) == "number"){
		switch (document.getElementById("headername").innerHTML){
			case "Backplan auswählen":
				// alle Backpläne zurücksetzen (nicht aktiv)
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200){
						var res = this.responseText;
					}
				}
				xhttp.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"bakingplan_activate\",\"bp_id\":\"" + id[0] + "\"}", false);  
				xhttp.send();	
				//dlg_full_hide();
				//bakingplan_get_all_recipes();
				break;			
			case "Backplan löschen":
				if (id[1] == "[active bakingplan]"){
					alert("Aktiver Backplan kann nicht gelöscht werden!");
					break;
				}
				if (confirm("Backplan \"" + base64_2_specialchars(id[1]) + "\" wirklich löschen?") == true){
					// ausgewählten Backplan löschen
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200){
							var res = this.responseText;
						}
					}
					xhttp.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"bakingplan_delete\",\"bp_id\":\"" + id[0] + "\"}", false);  
					xhttp.send();	
				}
				//dlg_full_hide();
				//bakingplan_get_all_recipes();
				break;			
			default:
				alert("Kein Dialog-Name angegeben!");
				break;
		}
	}
	dlg_full_hide();
	bakingplan_get_all_recipes();
}

/* ----- Dialog Backplan - neues Rezept ----------------------------------------------- */

function dlg_bakingplan_select_recipe_create_HTML(){
	document.getElementById("btn_h_back").addEventListener("click", dlg_bakingplan_select_recipe_close);	 
	var html = "";
	// Elemente "dlg_full_content_search" und "dlg_full_content_filter" erstellen
	html += "			<div class=\"dlg_full_content_search\" id=\"dlg_full_content_search\">\n";
	html += "			</div>\n";
	html += "			<div class=\"dlg_full_content_filter\" id=\"dlg_full_content_filter\">\n";
	html += "			</div>\n";
	document.getElementById("dlg_full_content").innerHTML = html;
	dlg_bakingplan_select_recipe_search_createHTML();
	dlg_bakingplan_select_recipe_filter_createHTML();
}

function dlg_bakingplan_select_recipe_search_createHTML(){
	// Bereich "Suche"
	var html = "";
	html += "					<span><input id=\"searchfield\" style=\"margin-right:10px;\" onkeyup=\"checkinput(this.value)\">&#128270</span>\n";
	document.getElementById("dlg_full_content_search").innerHTML = html;
}

function dlg_bakingplan_select_recipe_filter_createHTML(){
	var html = "";
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			var res = this.responseText;
			if (res.substr(0, 8) == "DB_ERROR"){
				alert("DB-Fehler");
			}
			var json_object = JSON.parse(res);
			for (i = 0; i < json_object.length; ++i) {						
				// Sonderzeichen im Rezept-Name dekodieren
				json_object[i].name = base64_2_specialchars(json_object[i].name);
				html += "					<div class=\"item item_main\" id=\"item_" + json_object[i].id + "\" title=\"unselected\" onclick=\"dlg_full_toggle_item(" + json_object[i].id + ")\">\n";
				html += "						<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\"images/btn_unselected.svg\"></span>\n";
				html += "						<span class=\"flexitem item\" align=\"left\">" + json_object[i].name + "</span>\n";
				html += "					</div>\n";
			}
			document.getElementById("dlg_full_content_filter").innerHTML = html;
		}
	}
	xhttp.open("GET", server_root_url + "wfo_db.php?json=" + "{\"request_name\":\"get_all_recipes\"}", false);  
	xhttp.send();
}

function dlg_bakingplan_select_recipe_close(id){
//INSERT INTO `bakingplans_recipes` (`id`, `recipes_id`, `bakingplans_id`, `order_no`) VALUES (NULL, '40', '1', '1000'), (NULL, '1', '1', '1000')
	var sql = "INSERT INTO `bakingplans_recipes` (`id`, `recipes_id`, `bakingplans_id`, `order_no`) VALUES ";	
	var allItems = document.getElementsByClassName("item");
	var j = 0;
	var item_is_selected = false;
	for(var i = 0; i < allItems.length; i++) {
		if (allItems[i].title == "selected"){
			if (j == 0){
				sql += " (NULL, '" + allItems[i].id.replace("item_", "") + "', '" + gv_active_bakingplan_id + "', '1000')";
			}
			else{
				sql += ", (NULL, '" + allItems[i].id.replace("item_", "") + "', '" + gv_active_bakingplan_id + "', '1000')";
			}
			j++;
			item_is_selected = true;
		}
	}
	//alert(sql);
	dlg_full_hide();
	if (item_is_selected){
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res = this.responseText;
				//alert(res);
			}
		}
		xhttp.open("GET", "wfo_db.php?json=" + "{\"request_name\":\"sql_execute\",\"sql\":\"" + sql + "\"}", false);  
		xhttp.send();
	}
	dlg_full_hide();
	bakingplan_get_all_recipes();
}

/* ----- PopUp-Dialog ----------------------------------------------------------------- */

function popup_max_show() { 
	var popup_max = document.getElementById("popup_max");
	maxheight = document.documentElement.clientHeight - 130;
	popup_max.style.display = "block";
	popup_max.animate([{height: "0px"}, {height: "calc(100% - 120px)"}], {duration: 300, iterations: 1, fill: 'forwards'});
}

function popup_max_hide() { 
	var popup_max = document.getElementById("popup_max");
	popup_max.animate([{height: "calc(100% - 120px)"}, {height: "0px"}], {duration: 300, iterations: 1, fill: 'forwards'});
	// Dialog ausblenden, wenn Animation beendet ist
	setTimeout(function() {
		popup_max.style.display = "none";
	}, 300);
}


/* ----- alter Code ------------------------------------------------------------------- */

function showcheck(message){			
	var check = confirm(message);
	if (check == true) {
		return true;
	}
	else{
		return false;
	}
}

function showalert(msg){
	alert(msg);
}
