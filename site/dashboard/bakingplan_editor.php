<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">
    <script src="../javascript/config.js"></script>

	<link rel="stylesheet" type="text/css" href="../style/odk.css">
</head>
<style type="text/css">
	.rec_entity{
			border-bottom: thin solid lightgray;
		}
		.rec_name {
			border-bottom: thin solid lightgray;
		}
		.rec_btn{
			border-radius: 0px 5px 0px 0px;
		}
		.rec_details {
			border-radius: 0px 0px 5px 5px;
			background-color: white;
		}
		.unselectable {
			-webkit-touch-callout: none;
			-webkit-user-select: none;
			-khtml-user-select: none;
			-moz-user-select: none;
			-ms-user-select: none;
			user-select: none;
		}
		.target{
			margin-top: 3px;
		 	border: thin solid lightgray;
		  border-collapse: collapse;
			border-radius: 5px;
		  background-color: white;
		  display: none;
		}
		.target_table{
			width: 100px;
			height: 40px;
		}

		#bp_name{
			font-size: 1.0rem;
			padding-right: 40px;
		}
		#reload_button{
		    vertical-align: middle;
		    align-items: center;
		}
</style>
<script src="../libs/jquery-3.6.0.min.js"></script>
<script src="../javascript/base64.js"></script>

<body>
	<!--
		einlesen der übergebenen Rezept-ID per PHP in unsichtbares DIV-Element
		- kann später per JS ausgelesen werden
		- Rezept-ID = 0 bedeutet "neues Rezept"
	-->
	<?php
		$data = 0;
		if (isset($_GET["data"])) {
			$data = $_GET["data"];
		}
	?>
	<div id="data" hidden><?php echo $data; ?></div>
	<header class="unselectable">
		<div class="fl_header_footer">
			<div class="btn_table" id="reload_button" onclick='location.reload(true)'>&#10226;</div>
			<span class="flexitem" id="bp_name" onclick="dlg_full_show('Backplan auswählen')">Backplan</span>
			<table class="flexitem "><tr><td></td></tr></table>
			<span class="mm" id="mm"></span><!-- ausfahrendes menü im header-->
			<span class="me" id="me" style="right: 50px; top: 50px;"></span><!-- ausfahrendes menü je rezept -->
		</div>
	</header>
	<main id="main" class="unselectable"></main>
</body>
<script type="text/javascript">

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

var btn_unselected_svg = "/HomeDashboard/images/btn_unselected.svg";
var btn_selected_svg = "/HomeDashboard/images/btn_selected.svg";

var MENU_MAIN;
var MENU_ELEMENTS;

var CUTTED = false;//flag for pasting

var gv_rec_id;

	document.addEventListener('DOMContentLoaded', init);
	function init(){
		bakingplan_get_all_recipes();
		MENU_MAIN = new Menu([
			// [name, function, color]
			["Backplan<br>neu", "MENU_MAIN.clicked('new')", "blue"],
			["Backplan<br>löschen", "MENU_MAIN.clicked('del')", "red"],
			["Backplan<br>umbenennen", "MENU_MAIN.clicked('rename')", "maroon"],
			["Rezept(e)<br>hinzufügen", "MENU_MAIN.clicked('add')", "green"]
		],"mm");
		MENU_ELEMENTS = new Menu([
			// [name, function, color]
			["Kopieren", "MENU_ELEMENTS.clicked('copy')", "red"],
			["Ausschneiden", "MENU_ELEMENTS.clicked('cut')", "green"],
			["Löschen", "MENU_ELEMENTS.clicked('delete')", "maroon"],
			["Duplizieren", "MENU_ELEMENTS.clicked('duplicate')", "blue"]
		],"me");
	}

window.addEventListener("scroll", hide_menus);
window.addEventListener("resize", hide_menus);
function hide_menus(){
	MENU_MAIN.hide();
	MENU_ELEMENTS.hide();
}

/*
-------------------------------------- HTTP-Requests -------------------------------------
*/
	function bakingplan_rename(){
		var name_old = document.getElementById("bp_name").innerHTML;
		name_old = name_old.replace("Backplan \"", "");
		name_old = name_old.substr(0, name_old.length - 1);
		var name_new = window.prompt("Neuer Name:", name_old);

		if ((name_new != "") && (name_new != null)){
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB_ERROR");
					}
					else {
						document.getElementById("bp_name").innerHTML = "Backplan \"" + name_new + "\"";
					}
				}
			}
			xhttp.open("GET", DB_URL +"?json={\"request_name\":\"rename_bakingplan\",\"bp_id\":\"" + gv_active_bakingplan_id + "\",\"bp_name\":\"" + specialchars_2_base64(name_new) + "\"}", false);
			xhttp.send();
		}
	}

	function bakingplan_new(bp_name){
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {/*do nothing*/}
		xhttp.open("GET", DB_URL +"?json={\"request_name\":\"add_bakingplan\",\"bp_name\":\"" + specialchars_2_base64(bp_name) + "\"}", false);
		xhttp.send();
	}

	function bakingplan_clipboard_write_rec_id(bpr_id){
		// Rezept-ID des zu klonenden Elementes ermitteln und in Clipboard speichern
		GV_CLIPBOARD = "";
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB_ERROR");
				}
				else {
					var rec_id = JSON.parse(res);
					for (i in rec_id)
						GV_CLIPBOARD = rec_id[i].recipes_id;
				}
			}
		}
		xhttp.open("GET", DB_URL + "?json={\"request_name\":\"get_bakingplan_rec_id\",\"bpr_id\":\"" + bpr_id + "\"}", false);
		xhttp.send();
	}

	function hide_shaking_dummies(){
		allitems = document.getElementsByClassName("shaking_dummy");
		for (var i=0; i<allitems.length; i++) {
			allitems[i].style.display = "none";
		}
	}

	function bakingplan_paste(bpr_id){
		hide_shaking_dummies();
		// "order_no" des selektierten Dummies ermitteln
		var xhttp1 = new XMLHttpRequest();
		xhttp1.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res1 = this.responseText;
				if (res1.substr(0, 8) == "DB_ERROR"){
					alert("DB_ERROR");
				}
				else {
					var response_order_no = JSON.parse(res1);
					for (i = 0; i < response_order_no.length; i++) {
						var order_no = parseInt(response_order_no[i].order_no) + 5;
						// Neuen Eintrag in DB-Tabelle "bakingplans_recipes" erzeugen

						var xhttp2 = new XMLHttpRequest();
						xhttp2.onreadystatechange = function() {/*do nothing*/}
						xhttp2.open("GET", DB_URL +"?json={\"request_name\":\"paste_bakingplan_rec\",\"rec_id\":\"" + GV_CLIPBOARD + "\",\"bp_id\":\"" + gv_active_bakingplan_id + "\",\"order_no\":\"" + order_no + "\"}", false);
						xhttp2.send();
						if (CUTTED){
							CUTTED = false;
							bakingplan_remove_recipe(gv_bpr_id);
						}
					}
				}
			}
		}
		xhttp1.open("GET", DB_URL +"?json={\"request_name\":\"get_bakingplan_recipe_order_no\",\"bpr_id\":\"" + bpr_id + "\"}", false);
		xhttp1.send();
		GV_CLIPBOARD = "";
		// Anzeige auffrischen
		bakingplan_get_all_recipes();
	}

	function bakingplan_duplicate_recipe(bpr_id, rec_id){
		var xhttp1 = new XMLHttpRequest();
		xhttp1.onreadystatechange = function() {
    		if (this.readyState == 4 && this.status == 200){
	   			var res1 = this.responseText;
				if (res1.substr(0, 8) == "DB_ERROR"){
					alert("DB-ERROR");
				}
				else {
					var response_order_no = JSON.parse(res1);
			  		for (i in response_order_no) {
						var order_no = parseInt(response_order_no[i].order_no) + 5;
						// Neuen Eintrag in DB-Tabelle "bakingplans_recipes" erzeugen
						var xhttp2 = new XMLHttpRequest();
						xhttp2.onreadystatechange = function() {/*do nothing*/}
						xhttp2.open("GET", DB_URL +"?json={\"request_name\":\"paste_bakingplan_rec\",\"rec_id\":\""+ rec_id +"\",\"bp_id\":\""+ gv_active_bakingplan_id +"\",\"order_no\":\""+ order_no +"\"}", false);
						xhttp2.send();
					}
				}
			}
		}
		// "order_no" des zu klonenden Elementes ermitteln
		xhttp1.open("GET", DB_URL + "?json={\"request_name\":\"get_bakingplan_recipe_order_no\",\"bpr_id\":\"" + bpr_id + "\"}", false);
  		xhttp1.send();
		// Anzeige auffrischen
		bakingplan_get_all_recipes();
	}

	function bakingplan_remove_recipe(bpr_id){
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {/*do nothing*/}
		xhttp.open("GET", DB_URL +"?json={\"request_name\":\"remove_bakingplan_recipe\",\"bpr_id\":\"" + bpr_id + "\"}", false);
  		xhttp.send();
	}

	function bakingplan_get_all_recipes(){
		// aktuellen Backplan ermitteln
		var xhttp0 = new XMLHttpRequest();
		xhttp0.onreadystatechange = function() {
	    	if (this.readyState == 4 && this.status == 200){
		   		var response0 = this.responseText;
					if (response0.substr(0, 8) == "DB_ERROR"){
						alert("DB_ERROR");
					}
					else {
						var active_bakingplan = JSON.parse(response0)[0];
						gv_active_bakingplan_id = active_bakingplan.id;
			  			document.getElementById("bp_name").innerHTML = "Backplan \"" + base64_2_specialchars(active_bakingplan.name)+"\"";
						// alle Rezepte einlesen die zum aktuellen Backplan gehören
						var xhttp1 = new XMLHttpRequest();
						xhttp1.onreadystatechange = function() {
							if (this.readyState == 4 && this.status == 200){
								var res1 = this.responseText;

								if (res1.substr(0, 8) == "DB_ERROR"){
									alert("DB_ERROR");
								}
								else {
									var all_bakingplan_recipes = JSON.parse(res1);
									var html = "";
									for (i in all_bakingplan_recipes) {
										html += recipe_createHTML(all_bakingplan_recipes[i].bpr_id,
													 			all_bakingplan_recipes[i].r_id,
																all_bakingplan_recipes[i].r_name,
																all_bakingplan_recipes[i].r_bakingtime,
																all_bakingplan_recipes[i].r_bakingtemperature);
									}
									document.getElementById("main").innerHTML = html;

									// Reihenfolge in DB speichern (Feld "order_no")
									var allItems = document.getElementsByClassName("rec");

									for(i = 0; i < allItems.length; i++) {
										var xhttp2 = new XMLHttpRequest();
										xhttp2.onreadystatechange = function() {/*do nothing*/}
										xhttp2.open("GET", DB_URL +"?json={\"request_name\":\"bakingplan_set_order_no\",\"order_no\":\""+ (i * 10) +"\",\"bpr_id\":\""+ allItems[i].id +"\"}", false);
										xhttp2.send();
									}
								}
							}
						}
						xhttp1.open("GET", DB_URL+"?json={\"request_name\":\"get_all_bakingplan_recipes\"}", false);
						xhttp1.send();
					}
				}
			}
	  	xhttp0.open("GET", DB_URL+"?json={\"request_name\":\"get_active_bakingplan\"}", false);
  		xhttp0.send();
	}

	function recipe_createHTML(bpr_id, r_id, r_name, r_bakingtime, r_bakingtemperature){//TODO recipe import not as string but as object
		var html = ""
		// HTML-String erzeugen
		html += "<div class=\"rec\" id=\"" + bpr_id + "\" title=\"" + r_id + "\">";
		html += "	<table class=\"rec_table\" id=\"rec_table_" + bpr_id + "\"><tr>";
		html += "		<td class=\"rec_entity\" style=\"width: 30px;\"></td>";
		html += " 		<td class=\"rec_entity rec_name\" onclick='show_recipe_full("+bpr_id+")'>"+base64_2_specialchars(r_name) + " (#" + r_id + ")</td>";
		html += " 		<td class=\"rec_entity rec_btn\" id=\"rec_btn_"+ bpr_id +"\" onclick=\"call_me_show(['"+ r_id +"','"+ bpr_id +"'])\">❮</td>";
		html += "	</tr></table>";
		html += "	<div class=\"recipe_div_"+bpr_id+"\" style='display: none'></div>";
		html += " 	<div class=\"rec_details\" id=\"rec_details_" + bpr_id + "\">";
		html += "		<span class=\"flexitem rec_details\" id=\"rec_time_" + bpr_id + "\">"
		html += "			<img class=\"img_btn\" id=\"sym_clock\" src=\"../images/sym_clock.svg\" alt=\"sym_clock\" style=\"margin-right: 5px;\">"+ base64_2_specialchars(r_bakingtime) + "</span>";
		html += "		<span class=\"flexitem rec_details\" id=\"rec_temp_" + bpr_id + "\">"
		html += "			<img id=\"sym_thermo\" src=\"../images/sym_thermo.svg\" alt=\"sym_thermo\" style=\"margin-right: 5px; width:15px; hight:15px\">" + base64_2_specialchars(r_bakingtemperature) + "</span>";
		html += "	</div>";
		html += "</div>";
		html += "<div class=\"shaking_dummy\" id=\"dummy_" + bpr_id + "\" onclick=\"bakingplan_paste('" + bpr_id + "')\">";
		html += "[ Klicken zum<br>Einfügen ]";
		html += "</div>";
		return html
	}
	function show_recipe_full(id){
		var div = document.getElementById("recipe_div_"+id);
	}
	function mm_show_hide(){
		if(MENU_MAIN.is_open)
			MENU_MAIN.hide();
		else
			MENU_MAIN.show();
	}
	function me_show_hide(){
		if(MENU_ELEMENTS.is_open)
			MENU_ELEMENTS.hide();
		else
			MENU_ELEMENTS.show();
	}
	function call_me_show(id_array){
		gv_rec_id = id_array[0];
		gv_bpr_id = id_array[1];
		MENU_ELEMENTS.show(id_array[1]);
	}
class Menu{
	constructor(menu_items, name){
		this.name = name;
		this.menu_items = menu_items;
		this.is_open = false;
		this.html = this.create_html();
		this.init();
	}
	create_html(){
		var html = "";
		html += "<table class=\""+this.name+"_table\" id=\""+this.name+"_table\">";
		html += "<td class=\""+this.name+"_arrow\" id=\""+this.name+"_arrow\" onclick=\""+this.name+"_show_hide()\">&#10094;</td>";
		for (i in this.menu_items)
			html += "<td class='mm_item' bgcolor="+ this.menu_items[i][2] +" onclick=\""+ this.menu_items[i][1] +"\">" + this.menu_items[i][0] +"</td>";
		html += "</tr></table>";
		return html;
	}
	init(){
		if (document.getElementById(this.name)){
			if (this.name === "mm"){
				//löschen
				var mm = document.getElementById("mm");
				var parent = mm.parentNode;
				parent.removeChild(mm);
				//neu erzeugen
				var mm_new = document.createElement("span");
				mm_new.classList.add("mm");
				mm_new.id = "mm";
				parent.appendChild(mm_new);
			}
			document.getElementById(this.name).innerHTML = this.html;
		}
	}
	show(id = null){
		//set width of td elements
		var mm_items = document.getElementsByClassName("mm_item");
		mm_items.width = 300 / (this.menu_items.length - 1);
		//show menu
		if (this.name === "me" && id != null){
			var box = document.getElementById("rec_table_" + id);
			var menu_elem = document.getElementById("me");
			var box_BCR = box.getBoundingClientRect();
			//set me position + height
			menu_elem.style.top = box_BCR.top + "px";
			menu_elem.style.left = (box_BCR.left + box.offsetWidth - 330) + "px";
			menu_elem.style.height = box.offsetHeight + "px";

			menu_elem.style.boxShadow = "3px 3px 20px black";
			menu_elem.style.width = "0px";
			var newleft = parseInt(menu_elem.style.left.replace("px", ""));
			menu_elem.style.left = newleft + 330 + "px";
			$(".me").animate({"left": "-=330px", "width": "330px"}, "fast");
			menu_elem.style.display = "block";
			document.getElementById("me_arrow").innerHTML = "❯";
			this.is_open = true;
		}
		else if (this.name === "mm"){
			if (this.is_open)
				this.hide();
			else {
				document.getElementById("mm").style.boxShadow = "3px 3px 20px black";
				$(".mm").animate({"left": "-=300px"}, "fast");
				document.getElementById("mm_arrow").innerHTML = "❯"
				this.is_open = true;
			}
		}
	}
	hide(){
		if (this.name === "mm"){
			this.init();
			document.getElementById("mm_arrow").innerHTML = "❮"
			this.is_open = false;
		}
		else if (this.name === "me"){
			document.getElementById("me").style.display = "none";
			document.getElementById("me_arrow").innerHTML = "❮";
			this.is_open = false;
		}
	}
	clicked(action = null){
		if (action == null){
			alert("ClickError");
			return;
		}
		this.hide();
		if (this.name === "me"){
			switch(action){
				case "copy":
					// Dummies anzeigen
					var allitems = document.getElementsByClassName("shaking_dummy");
					for (i = 0; i < allitems.length; i++)
						allitems[i].style.display = "block";
					// Rezept-ID des zu kopierenden Elementes im Clipboard speichern
					bakingplan_clipboard_write_rec_id(gv_bpr_id);
					break;
				case "cut":
					this.clicked("copy");
					CUTTED = true;
					break;
				case "delete":
					if (confirm("Rezept #" + gv_rec_id + " wirklich aus Backplan entfernen?") == true)
						bakingplan_remove_recipe(gv_bpr_id);
					bakingplan_get_all_recipes();
					break;
				case "duplicate":
					bakingplan_duplicate_recipe(gv_bpr_id, gv_rec_id);
					break;
			}
			//gv_bpr_id = "";
		}
		else if (this.name === "mm"){
			switch(action){
				case "new":
					var result = window.prompt("Name des neuen Backplans:");
					if (result != "")
						bakingplan_new(result);
				  break;
				case "add":
					dlg_full_create_HTML("Neue Rezepte auswählen");
					dlg_full_show("Neue Rezepte auswählen");
				  break;
				case "del":
					dlg_full_create_HTML("Backplan löschen");
					dlg_full_show("Backplan löschen");
				  break;
				case "rename":
					bakingplan_rename();
				  break;
			}
		}

	}

}

/* ----- Dialog full ------------------------------------------------------------------ */

function dlg_full_show(headername) {
	let re = document.getElementsByClassName("rec");
	for (let rec in re)
		re[rec].hidden = "true";
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
	let rec_list = document.getElementsByClassName("rec");
	for (let rec in rec_list)
		rec_list[rec].hidden = "false";
	dlg_full.animate([{width: "100% "}, {width: "0px"}], {duration: 300, iterations: 1, fill: 'forwards'});
	// Dialog ausblenden, wenn Animation beendet ist
	setTimeout(function() {
		dlg_full.style.display = "none";
	}, 300);
}

function create_div(name){
	div = document.createElement("div");
	div.classList.add(name);
	div.id = name;
	div.innerHTML = "";
	return div;
}

function dlg_full_create_HTML(headername){
	// Dialog-Objekt erzeugen
	main = document.getElementById("main");
	dlg_full = create_div("dlg_full");
	main.appendChild(dlg_full);
	dlg_full.appendChild(create_div("dlg_full_header"));
	dlg_full.appendChild(create_div("dlg_full_content"));
	// Header erzeugen
	var html = "<span id=\"btn_h_back\" style=\"margin-left:10px; margin-right:10px; font-size:1.0rem;\">&#11153;</span>\n";
	html += "<span id=\"headername\">" + headername + "</span>\n";
	html += "<span style=\"margin-left:10px; margin-right:10px; font-size:1.0rem;\"></span>\n";
	document.getElementById("dlg_full_header").innerHTML = html;
}

function dlg_full_toggle_item(id){
	var item = document.getElementById("item_" + id);
	if (item.title == "unselected"){
		item.style.backgroundColor = "#B0B0B0";
		item.title = "selected";
		document.getElementById("btn_sel_unsel_" + id).src = btn_selected_svg;
	}
	else {
		item.style.backgroundColor = "#F0F0F0";
		item.title = "unselected";
		document.getElementById("btn_sel_unsel_" + id).src = btn_unselected_svg;
	}
}

/* ----- Dialog Backplan auswählen / löschen ------------------------------------------ */

function dlg_select_bakingplan_create_HTML(headername){
	document.getElementById("btn_h_back").addEventListener("click", dlg_select_bakingplan_close);
	var html = "";
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			var res = this.responseText;
			if (res.substr(0, 8) == "DB_ERROR"){
				alert("DB-Fehler");
			}
			else {
				var bakingplans = JSON.parse(res);
				for (i = 0; i < bakingplans.length; ++i) {
					var background_color = "gray";
					var btn = btn_selected_svg;
					var onclick = "dlg_select_bakingplan_close([" + bakingplans[i].id + ", '[active bakingplan]'])";
					if (bakingplans[i].type != "active"){
						background_color = "lightgray";
						btn = btn_unselected_svg;
						onclick = "dlg_select_bakingplan_close([" + bakingplans[i].id + ", '" + bakingplans[i].name + "'])";
					}
					html += "<div class=\"item\" id=\"item_"+ bakingplans[i].id +"\" style=\"background-color:"+background_color+";\" onclick=\""+onclick+"\">";
					html += "<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_"+ bakingplans[i].id +"\" src=\""+ btn +"\"></span>";
					html += "<span class=\"flexitem item\" align=\"left\">"+ base64_2_specialchars(bakingplans[i].name) +"</span>";
					html += "</div>";
				}
				document.getElementById("dlg_full_content").innerHTML = html;
			}
		}
	}
	xhttp.open("GET", DB_URL+"?json={\"request_name\":\"get_all_bakingplans\"}", false);
	xhttp.send();
}

function dlg_select_bakingplan_close(id){
	// wenn eine id übergeben wurde ...
	if (typeof(id[0]) == "number"){
		switch (document.getElementById("headername").innerHTML){
			case "Backplan auswählen":
				// alle Backpläne zurücksetzen (nicht aktiv)
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {/*do nothing*/}
				xhttp.open("GET", DB_URL+"?json={\"request_name\":\"set_active_bakingplan\",\"bp_id\":\""+ id[0] +"\"}", false);
				xhttp.send();
				//send to dashboard
				location.reload(true);
				break;
			case "Backplan löschen":
				if (id[1] == "[active bakingplan]"){
					alert("Aktiver Backplan kann nicht gelöscht werden!");
					break;
				}
				if (confirm("Backplan \"" + base64_2_specialchars(id[1]) + "\" wirklich löschen?") == true){
					// ausgewählten Backplan löschen
					var xhttp = new XMLHttpRequest();
					xhttp.onreadystatechange = function() {/*do nothing*/}
					xhttp.open("GET", DB_URL+"?json={\"request_name\":\"bakingplan_delete\",\"bp_id\":\"" + id[0] + "\"}", false);
					xhttp.send();
				}
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
	html += "<div class=\"dlg_full_content_search\" id=\"dlg_full_content_search\">\n";
	html += "</div>\n";
	html += "<div class=\"dlg_full_content_filter\" id=\"dlg_full_content_filter\">\n";
	html += "</div>\n";
	document.getElementById("dlg_full_content").innerHTML = html;
	dlg_bakingplan_select_recipe_search_createHTML();
	dlg_bakingplan_select_recipe_filter_createHTML();
}

function dlg_bakingplan_select_recipe_search_createHTML(){
	// Bereich "Suche"
	var html = "<span><input id=\"searchfield\" style=\"margin-right:10px;\" onkeyup=\"checkinput(this.value)\">&#128270</span>\n";
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
			else {
				var json_object = JSON.parse(res);
				for (i = 0; i < json_object.length; ++i) {
					// Sonderzeichen im Rezept-Name dekodieren
					json_object[i].name = base64_2_specialchars(json_object[i].name);
					html += "<div class=\"item item_main\" id=\"item_" + json_object[i].id + "\" title=\"unselected\" onclick=\"dlg_full_toggle_item(" + json_object[i].id + ")\">\n";
					html += "<span class=\"flexitem item\"><img class=\"img_btn_small\" id=\"btn_sel_unsel_" + json_object[i].id + "\" src=\""+btn_unselected_svg+"\"></span>\n";
					html += "<span class=\"flexitem item\" align=\"left\">" + json_object[i].name + "</span>\n";
					html += "</div>\n";
				}
				document.getElementById("dlg_full_content_filter").innerHTML = html;
			}
		}
	}
	xhttp.open("GET", DB_URL+"?json={\"request_name\":\"get_all_recipes\"}", false);
	xhttp.send();
}

function dlg_bakingplan_select_recipe_close(){
	var recipe_data = [];
	var allItems = document.getElementsByClassName("item");
	var item_is_selected = false;
	for(var i = 0; i < allItems.length; i++) {
		if (allItems[i].title == "selected"){
			recipe_data.push( {"rec_id": allItems[i].id.replace("item_", ""), "bp_id": ""+gv_active_bakingplan_id, "order_no":"1000"} );
			item_is_selected = true;
		}
	}
	dlg_full_hide();
	if (item_is_selected){
		for (i = 0; i < recipe_data.length; i++){
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function(){/*do nothing*/}
			xhttp.open("GET", DB_URL +"?json={\"request_name\":\"paste_bakingplan_rec\",\"rec_id\":\"" + recipe_data[i].rec_id + "\",\"bp_id\":\"" + recipe_data[i].bp_id + "\",\"order_no\":\"" + recipe_data[i].order_no + "\"}", false);
			xhttp.send();
		}
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

	function checkinput(value){
		//alert(value);
		var allItems = document.getElementsByClassName("item_main");
		const regex = new RegExp(".*" + value + ".*", "i");
		if (value == ""){
			for(var i = 0; i < allItems.length; i++) {
				document.getElementById(allItems[i].id).style.backgroundColor = "#F0F0F0";
				document.getElementById(allItems[i].id).title = "unselected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = btn_unselected_svg;
			}
			return;
		}
		for(var i = 0; i < allItems.length; i++) {
			if (regex.test(allItems[i].innerText)){
				document.getElementById(allItems[i].id).style.backgroundColor = "#B0B0B0";
				document.getElementById(allItems[i].id).title = "selected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = btn_selected_svg;
			}
			else{
				document.getElementById(allItems[i].id).style.backgroundColor = "#F0F0F0";
				document.getElementById(allItems[i].id).title = "unselected";
				document.getElementById(allItems[i].id.replace("item_", "btn_sel_unsel_")).src = btn_unselected_svg;
			}
		}
	}

//TODO rezepte entfernen übersicht (genau wie rezepte hinzufügen)
</script>

</html>
