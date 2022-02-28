<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="../style/odk.css">

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="apple-mobile-web-app-capable" content="yes">

</head>
<link rel="stylesheet" type="text/css" href="../style/odk.css">
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
		#reload_button_text {
			text-align: center;
			position: relative;
			top: 20%;
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
		#headername {
			position: absolute;

			width: 100%;
			align-items: center;
			justify-self: center;
		}
		#btn_h-back{
			margin-left:10px;
			font-size:1.0rem;
		}


</style>
<script src="../libs/jquery-3.6.0.min.js"></script>
<script src="../javascript/odk.js"></script>
<body>
	<!--mm - main menu ?
		me - menu einträge ?
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
			<div class="btn_table" id="reload_button" onclick='location.reload(true)'><div id="reload_button_text" >&#10226;</div></div>
			<span class="flexitem" id="bp_name" style="font-size: 1.0rem;padding-right: 40px;" onclick='mm_clicked_bakingplan()'>Backplan</span>
			<table class="flexitem "><tr><td></td></tr></table>
			<span class="mm" id="mm"></span>
			<span class="me" id="me" style="right: 50px; top: 50px;"></span>
		</div>
	</header>

	<main id="main" class="unselectable"></main>

	<footer class="unselectable">
		<div class="fl_header_footer">
			<span class="flexitem"><img class="img_btn" id="btn_f_cancel" src="../images/btn_cancel.svg" alt="Abbruch" onclick="btn_f_cancel_clicked()"></span>
  		<span class="flexitem" id="f_msg" style="font-size: 0.8rem;"></span>
  		<span class="flexitem"><img class="img_btn" id="btn_f_save" src="../images/btn_save.svg" alt="Speichern" onclick="btn_f_save_clicked()"></span>
		</div>
	</footer>
</body>
<script type="text/javascript">



/* ----------------------------------- Eventhandler ----------------------------------- */
		var gv_rec_id;
		var ACT_REZIPE = new recipe (null, null, null, null, null, null, null);

 		document.addEventListener('DOMContentLoaded', DOMContentLoaded);
		function DOMContentLoaded(){
			bakingplan_get_all_recipes();
			menu_main_init();
			menu_elem_init();
		}

//------------------------------------

	function menu_elem_init(){
			var menu_items = [
				// [mm_name, mm_function, mm_color]
				["", "", ""],
				["Kopieren", "me_clicked_copy()", "red"],
				["Ausschneiden", "me_clicked_cut()", "green"],
				["Löschen", "me_clicked_delete()", "maroon"],
				["Duplizieren", "me_clicked_duplicate()", "blue"],
			];
			me_init(menu_items)
			me_is_open = false;
		}

	function me_clicked_new(){
 			mm_hide();
			var result = window.prompt("Name des neuen Backplans:");
			if (result != ""){
				bakingplan_new(result);
			}
		}

		function me_clicked_bakingplan_del(){
 			mm_hide();
			dlg_full_create_HTML("Backplan löschen");
			dlg_full_show("Backplan löschen");
		}

		function mm_clicked_bakingplan_rename(){
 			mm_hide();
			bakingplan_rename();
		}

		function mm_clicked_recipe_add(){
			mm_hide();
			dlg_full_create_HTML("Neue Rezepte auswählen");
			dlg_full_show("Neue Rezepte auswählen");
		}

		function mm_clicked_bakingplan_toggle(){
 			mm_hide();
			dlg_full_create_HTML("Backplan auswählen");
			dlg_full_show("Backplan auswählen");
		}

function mm_hide(){
	menu_main_init();
	//$(".menu_main").animate({"left": "+=300px"}, "fast");
	document.getElementById("mm_arrow").innerHTML = "<;"
	mm_is_open = false;
}


//-----------------------------

function me_hide(){
	document.getElementById("me").style.display = "none";
	//$(".me").animate({"left": "+=300px"}, "fast");
	document.getElementById("me_arrow").innerHTML = "<";
	me_is_open = false;
}


function me_clicked_copy(){
			me_hide();
			bakingplan_copy(gv_bpr_id);
			gv_bpr_id = "";
		}

		function me_clicked_cut(){
			me_hide();
			bakingplan_cut(gv_bpr_id);
			gv_bpr_id = "";
		}

		function me_clicked_delete(){
			me_hide();
			if (confirm("Rezept #" + gv_rec_id + " wirklich aus Backplan entfernen?") == true) {
				bakingplan_remove_recipe(gv_bpr_id);
			}
			bakingplan_get_all_recipes();
			gv_bpr_id = "";
		}

		function me_clicked_duplicate(){
			me_hide();
			//bakingplan_duplicate_recipe(gv_rec_id);
			//gv_rec_id = "";
			bakingplan_duplicate_recipe(gv_bpr_id, gv_rec_id);
			gv_bpr_id = "";
		}

//--------------------------------------------------------------
function menu_main_init(){
	var menu_items = [
		// [mm_name, mm_function, mm_color]
		["", "", ""],
		["Backplan<br>neu", "me_clicked('new')", "blue"],
		["Backplan<br>löschen", "me_clicked('bakingplan_del')", "red"],
		["Backplan<br>umbenennen", "mm_clicked('bakingplan_rename')", "maroon"],
		["Rezept(e)<br>hinzufügen", "mm_clicked('recipe_add')", "green"],
	];
	menu_main_repaint(menu_items)
	mm_is_open = false;
}


function menu_main_repaint(menu_items){
	if (document.getElementById("mm")) {
		var col_width = 300 / (menu_items.length - 1);
		var html = "";
		html += "<table class=\"mm_table\" id=\"mm_table\">";
		html += "<td class=\"mm_arrow\" id=\"mm_arrow\" onclick=\"mm_show_hide()\">&#10094;</td>";
		for (var i = 1; i < menu_items.length; ++i) {
			html += "<td width=" + col_width + "px bgcolor=" + menu_items[i][2] + " onclick=\"" + menu_items[i][1] + "\">" + menu_items[i][0] + "</td>";
		}
		html += "</tr></table>";
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
	if (mm_is_open)
		mm_hide();
	else
		mm_show();
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
//---------------------------------



//--Header Backingplan Menu--
	function mm_clicked_bakingplan(){//_toogle(){
		mm_hide();
		dlg_full_create_HTML("Backplan auswählen");
		dlg_full_show("Backplan auswählen");
	}



function create_div(innerHTML_,class_){
	obj = document.createElement("div");
	obj.classList.add(class_);
	obj.id = class_;
	obj.innerHTML = innerHTML_;
	return obj;
}

function dlg_full_create_HTML(headername){
	//HTML Objekte erzeugen
	main = document.getElementById("main");
	classList = ["dlg_full","dlg_full_header","dlg_full_content" ];
	for (class_ in classList)
		main.appendChild(create_div("",classList[class_]));
	// Header erzeugen
	var html = "";
	html += "<span id=\"btn_h_back\" onclick='dlg_select_bakingplan_close();' >&#11153;</span>\n";
	html += "<span id=\"headername\">" + headername + "</span>\n";

	document.getElementById("dlg_full_header").innerHTML = html;
}


function dlg_select_bakingplan_create_HTML(headername){//get bakingplans
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200){
			var res = this.responseText;
			if (res.substr(0, 8) == "DB_ERROR"){
				alert("DB-Fehler");
			}
			else {
				create_bakingplan_HTML(res);
			}
		}
	}
	xhttp.open("GET", db_url+"?json={\"request_name\":\"get_all_bakingplans\"}", false);
	xhttp.send();
}

function create_bakingplan_HTML(response){
	var json_object = JSON.parse(response);
	var html;
	for (i = 0; i < json_object.length; ++i) {
		var background_color = "lightgray";
		if (json_object[i].type == "active")
			background_color = "gray";
		html += "<div class=\"item\" id=\"item_" + json_object[i].id + "\" style=\"background-color:"+background_color;
		html += ";\"onclick=\"dlg_select_bakingplan_close([" + json_object[i].id + ", '" + json_object[i].name + "'])\">";
		html += "<span class=\"flexitem item\" align=\"left\">" + base64_2_specialchars(json_object[i].name) + "</span>";
		html += "</div>";
	}
	document.getElementById("dlg_full_content").innerHTML = html;
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
				xhttp.open("GET", db_url+"?json={\"request_name\":\"bakingplan_activate\",\"bp_id\":\"" + id[0] + "\"}", false);
				xhttp.send();
				//send to dashboard
				window.parent.postMessage('null reload', 'http://'+location.host+'/HomeDashboard/dashboard.php');
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
					xhttp.open("GET", db_url+"?json={\"request_name\":\"bakingplan_delete\",\"bp_id\":\"" + id[0] + "\"}", false);
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

function dlg_full_toggle_item(id){
	var item = document.getElementById("item_" + id);
	if (item.title == "unselected"){
		item.style.backgroundColor = "#B0B0B0";
		item.title = "selected";
		document.getElementById("btn_sel_unsel_" + id).src = btn_unselected_svg;
	}
	else{
		item.style.backgroundColor = "#F0F0F0";
		item.title = "unselected";
		document.getElementById("btn_sel_unsel_" + id).src = btn_unselected_svg;
	}
}


// requests


	function bakingplan_rename(){
		var name_old = document.getElementById("bp_name").innerHTML;
		name_old = name_old.replace("Backplan \"", "");
		name_old = name_old.substr(0, name_old.length - 1);
		var result = window.prompt("Neuer Name:", name_old);
		if ((result != "") && (result != null)){
			bp_name = specialchars_2_base64(result);
			var xhttp = new XMLHttpRequest();
			var response = "";
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					response = this.responseText;
					//alert(response);
		  		document.getElementById("bp_name").innerHTML = "Backplan \"" + result + "\"";
				}
			}
			xhttp.open("GET", "odk_db.php?json=" + "{\"request_name\":\"rename_bakingplan\",\"bp_id\":\"" + gv_active_bakingplan_id + "\",\"bp_name\":\"" + bp_name + "\"}", false);
			xhttp.send();
		}
	}

	function bakingplan_new(bp_name){
		bp_name = specialchars_2_base64(bp_name);
		var xhttp = new XMLHttpRequest();
		var response = "";
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				response = this.responseText;
				//alert(response);
			}
		}
		xhttp.open("GET", "odk_db.php?json=" + "{\"request_name\":\"insert_bakingplan\",\"bp_name\":\"" + bp_name + "\"}", false);
		xhttp.send();
	}

	function bakingplan_copy(bpr_id){
		// Dummies anzeigen
		allitems = document.getElementsByClassName("shaking_dummy");
		for (var i=0; i<allitems.length; i++) {
			allitems[i].style.display = "block";
		}
		// Rezept-ID des zu kopierenden Elementes im Clipboard speichern
		bakingplan_clipboard_write_rec_id(bpr_id);
	}

	function bakingplan_clipboard_write_rec_id(bpr_id){
		// Rezept-ID des zu klonenden Elementes ermitteln und in Clipboard speichern
		GV_CLIPBOARD = "";
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res = this.responseText;
				if (res.substr(0, 8) == "DB_ERROR"){
				}
				var json_object = JSON.parse(res);
				for (i = 0; i < json_object.length; ++i) {
					GV_CLIPBOARD = json_object[i].recipes_id;
				}
			}
		}
		xhttp.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_get_rec_id\",\"bpr_id\":\"" + bpr_id + "\"}", false);
		xhttp.send();
	}

	function bakingplan_cut(bpr_id){
		allitems = document.getElementsByClassName("shaking_dummy");
		for (var i=0; i<allitems.length; i++) {
			allitems[i].style.display = "block";
		}
		// Rezept-ID des auszuschneidenden Elementes im Clipboard speichern
		bakingplan_clipboard_write_rec_id(bpr_id);
		// ausgeschnittenes Element in DB löschen
		bakingplan_remove_recipe(bpr_id);
		// ausgeschnittenes Element und zugehöriges Dummy-Element in Liste löschen
		var dummy_rec = document.getElementById("dummy_" + bpr_id);
		var rec = document.getElementById(bpr_id);
		var parent_node = dummy_rec.parentNode;
		parent_node.removeChild(dummy_rec);
		parent_node.removeChild(rec);
	}

	function bakingplan_paste(bpr_id){
		allitems = document.getElementsByClassName("shaking_dummy");
		for (var i=0; i<allitems.length; i++) {
			allitems[i].style.display = "none";
		}
		// "order_no" des selektierten Dummies ermitteln
		var xhttp1 = new XMLHttpRequest();
		xhttp1.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				var res1 = this.responseText;
				if (res1.substr(0, 8) == "DB_ERROR"){
				}
				var json_object = JSON.parse(res1);
				for (i = 0; i < json_object.length; ++i) {
					var order_no = parseInt(json_object[i].order_no) + 5;
					// Neuen Eintrag in DB-Tabelle "bakingplans_recipes" erzeugen
					var xhttp2 = new XMLHttpRequest();
					xhttp2.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200){
							var res2 = this.responseText;
						}
					}
					xhttp2.open("GET", "odk_db.php?json=" + "{\"request_name\":\"bakingplan_paste_rec\",\"rec_id\":\"" + GV_CLIPBOARD + "\",\"bp_id\":\"" + gv_active_bakingplan_id + "\",\"order_no\":\"" + order_no + "\"}", false);
					xhttp2.send();
				}
			}
		}
		xhttp1.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_get_order_no\",\"bpr_id\":\"" + bpr_id + "\"}", false);
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
					alert("DB-Fehler bei:\n" + sql1);
				}
				var json_object = JSON.parse(res1);
		  	for (i = 0; i < json_object.length; ++i) {
		  		//alert(json_object[i].order_no);
					var order_no = parseInt(json_object[i].order_no) + 5;
					// Neuen Eintrag in DB-Tabelle "bakingplans_recipes" erzeugen
					var xhttp2 = new XMLHttpRequest();
					xhttp2.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200){
							var res2 = this.responseText;
						}
					}
					xhttp2.open("GET", "odk_db.php?json=" + "{\"request_name\":\"bakingplan_paste_rec\",\"rec_id\":\"" + rec_id + "\",\"bp_id\":\"" + gv_active_bakingplan_id + "\",\"order_no\":\"" + order_no + "\"}", false);
					xhttp2.send();
				}
			}
		}
		// "order_no" des zu klonenden Elementes ermitteln
		xhttp1.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_get_order_no\",\"bpr_id\":\"" + bpr_id + "\"}", false);
  	xhttp1.send();
		// Anzeige auffrischen
		bakingplan_get_all_recipes();
	}

	function bakingplan_remove_recipe(bpr_id){
		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200){
	   		var res = this.responseText;
			}
		}
		xhttp.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_remove_recipe\",\"bpr_id\":\"" + bpr_id + "\"}", false);
  	xhttp.send();
	}

	function bakingplan_get_all_recipes(){
		// aktuellen Backplan ermitteln
		var xhttp0 = new XMLHttpRequest();
		xhttp0.onreadystatechange = function() {
    	if (this.readyState == 4 && this.status == 200){
	   		var res0 = this.responseText;
				if (res0.substr(0, 8) == "DB_ERROR"){
				}
				var json_object = JSON.parse(res0);
		  	for (i = 0; i < json_object.length; ++i) {
		  		gv_active_bakingplan_id = json_object[i].id;
		  		document.getElementById("bp_name").innerHTML = "Backplan \"" + base64_2_specialchars(json_object[i].name) + "\"";
				}
				// alle Rezepte einlesen die zum aktuellen Backplan gehören
				var xhttp1 = new XMLHttpRequest();
				xhttp1.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200){
						var res1 = this.responseText;
						if (res1.substr(0, 8) == "DB_ERROR"){
						}
						var json_object = JSON.parse(res1);
						var html = "";
						for (i = 0; i < json_object.length; ++i) {
							html += recipe_createHTML(json_object[i].bpr_id, json_object[i].r_id, json_object[i].r_name, json_object[i].r_bakingtime, json_object[i].r_bakingtemperature);
						}
						document.getElementById("main").innerHTML = html;
						// Reihenfolge in DB speichern (Feld "order_no")
						var allItems = document.getElementsByClassName("rec");
						for(var i = 0; i < allItems.length; i++) {
							var xhttp2 = new XMLHttpRequest();
							xhttp2.onreadystatechange = function() {
								if (this.readyState == 4 && this.status == 200){
									var res2 = this.responseText;
								}
							}
							xhttp2.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_set_order_no\",\"order_no\":\"" + (i * 10) + "\",\"bpr_id\":\"" + allItems[i].id + "\"}", false);
							xhttp2.send();
						}
					}
				}
				xhttp1.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"bakingplan_get_all_recipes\"}", false);
				xhttp1.send();
			}
		}
	  xhttp0.open("GET", server_root_url + "odk_db.php?json=" + "{\"request_name\":\"get_active_bakingplan\"}", false);
  	xhttp0.send();
	}

	function recipe_createHTML(bpr_id, r_id, r_name, r_bakingtime, r_bakingtemperature){
		var html = ""
		// HTML-String erzeugen
		html += "<div class=\"rec\" id=\"" + bpr_id + "\" title=\"" + r_id + "\">";
		html += "	<table class=\"rec_table\" id=\"rec_table_" + bpr_id + "\"><tr>";
		html += " 		<td class=\"rec_entity\" style=\"width: 30px;\"></td>";
		html += " 		<td class=\"rec_entity rec_name\">"+ base64_2_specialchars(r_name) + " (#" + r_id + ")</td>";
		html += " 		<td class=\"rec_entity rec_btn\" id=\"rec_btn_" + bpr_id + "\" onclick=\"call_me_show(['" + r_id + "','" + bpr_id + "'])\"><</td></tr>";
		html += "	</table>";
		html += "<div class=\"rec_details\" id=\"rec_details_" + bpr_id + "\">";
		html += "	<span class=\"flexitem rec_details\" id=\"rec_time_" + bpr_id + "\">"
		html += "		<img class=\"img_btn\" id=\"sym_clock\" src=\"../images/sym_clock.svg\" alt=\"sym_clock\" style=\"margin-right: 5px;\">" + base64_2_specialchars(r_bakingtime) + "</span>";
		html += "	<span class=\"flexitem rec_details\" id=\"rec_temp_" + bpr_id + "\">"
		html += "		<img id=\"sym_thermo\" src=\"../images/sym_thermo.svg\" alt=\"sym_thermo\" style=\"margin-right: 5px; width:15px; hight:15px\">" + base64_2_specialchars(r_bakingtemperature) + "</span>";
		html += "</div>";
		html += "</div>";
		html += "<div class=\"shaking_dummy\" id=\"dummy_" + bpr_id + "\" onclick=\"bakingplan_paste('" + bpr_id + "')\">";
		html += "	[ Klicken zum<br>Einfügen ]";
		html += "</div>";
		return html
	}

	function call_me_show(id_array){
		gv_rec_id = id_array[0];
		gv_bpr_id = id_array[1];
		me_show(id_array[1]);
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
</script>
</html>
