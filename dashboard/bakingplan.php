<!DOCTYPE html>
<html>
<head>
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


</style>
<script src="../libs/jquery-3.6.0.min.js"></script>
<script src="../javascript/odk.js"></script>
<body>
	<header class="unselectable">
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
		<div class="fl_header_footer">
			<div class="btn_table" id="reload_button" onclick='location.reload(true)'>&#10226;</div>
			<span class="flexitem" id="bp_name" style="font-size: 1.0rem;padding-right: 40px;" onclick='mm_clicked_bakingplan_toggle()'>Backplan</span>
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
		
/* ----------------------------------- Eventhandler ----------------------------------- */
		var gv_rec_id;
		var ACT_REZIPE = new recipe (null, null, null, null, null, null, null);
 
 		document.addEventListener('DOMContentLoaded', DOMContentLoaded);
		function DOMContentLoaded(){
			bakingplan_get_all_recipes();
			menu_main_init();
			menu_elem_init();
		}

// ---------------------- Hauptmenü ----------------------	

		
		function menu_main_init(){
			var menu_items = [
				// [mm_name, mm_function, mm_color]
				["", "", ""],
				["Backplan<br>neu", "me_clicked_new()", "blue"],
				["Backplan<br>löschen", "me_clicked_bakingplan_del()", "red"],
				["Backplan<br>umbenennen", "mm_clicked_bakingplan_rename()", "maroon"],
				["Rezept(e)<br>hinzufügen", "mm_clicked_recipe_add()", "green"],
			];
			menu_main_repaint(menu_items)
			mm_is_open = false;
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
		
/* 
-------------------------------------- HTTP-Requests -------------------------------------
*/

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
		html += " 		<div class=\"rec\" id=\"" + bpr_id + "\" title=\"" + r_id + "\">";
		html += " 			<table class=\"rec_table\" id=\"rec_table_" + bpr_id + "\">";
		html += " 				<tr>";
		html += " 					<td class=\"rec_entity\" style=\"width: 30px;\">";
		html += "";
		html += " 					</td>";
		html += " 					<td class=\"rec_entity rec_name\">";
		html += base64_2_specialchars(r_name) + " (#" + r_id + ")";
		html += " 					</td>";
		html += " 					<td class=\"rec_entity rec_btn\" id=\"rec_btn_" + bpr_id + "\" onclick=\"call_me_show(['" + r_id + "','" + bpr_id + "'])\">";
		html += "<";
		html += " 					</td>";
		html += " 				</tr>";
		html += " 			</table>";
		html += " 		 	<div class=\"rec_details\" id=\"rec_details_" + bpr_id + "\">";
		html += "					<span class=\"flexitem rec_details\" id=\"rec_time_" + bpr_id + "\"><img class=\"img_btn\" id=\"sym_clock\" src=\"../images/sym_clock.svg\" alt=\"sym_clock\" style=\"margin-right: 5px;\">" + base64_2_specialchars(r_bakingtime) + "</span>";
		html += "					<span class=\"flexitem rec_details\" id=\"rec_temp_" + bpr_id + "\"><img id=\"sym_thermo\" src=\"../images/sym_thermo.svg\" alt=\"sym_thermo\" style=\"margin-right: 5px; width:15px; hight:15px\">" + base64_2_specialchars(r_bakingtemperature) + "</span>";
		html += "				</div>";
		html += "	 		</div>";
		html += " 		<div class=\"shaking_dummy\" id=\"dummy_" + bpr_id + "\" onclick=\"bakingplan_paste('" + bpr_id + "')\">";
		html += "[ Klicken zum<br>Einfügen ]";
		html += "	 		</div>";
		return html
	}
	
	function call_me_show(id_array){
		gv_rec_id = id_array[0];
		gv_bpr_id = id_array[1];
		me_show(id_array[1]);
	}
</script>

</html>