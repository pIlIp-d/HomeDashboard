<!DOCTYPE html>
<html>
  <head>
		<link rel="stylesheet" href="style/odk.css">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="apple-mobile-web-app-capable" content="yes">	
    <Title>Rezepte</Title>
  </head>
  
  <body>

<!--
----------------------------------------- Header -----------------------------------------
-->
	<script src="javascript/odk.js"></script>
	<script src="libs/jquery-3.6.0.min.js"></script>
	<script src="libs/ckeditor_classic_25_0_0/ckeditor.js"></script>

 		<header>
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

				<table class="flexitem btn_table" onclick=location.reload(true)><tr><td><b>&#10226;</b></td></tr></table>
  			<span style="position:absolute;left:3.5rem;margin-right:0;padding-right:0;" onclick="window.history.back();">❮❮❮</span>
  			
  			<span class="flexitem" id="header_name" style="font-size: 1.0rem;">Rezeptliste</span>
				<table class="flexitem btn_table"><tr><td></td></tr></table>
				<span class="mm" id="mm"></span>
				<span class="me" id="me" style="right: 50px; top: 50px;"></span>
			</div>
		</header>
<!--
				<span class="flexitem"><img class="img_btn" id="btn_h_home" src="images/btn_dummy.png" alt="home"></span>
------------------------------------------ Main ------------------------------------------
-->
		<dialogs id="dialogs">
		</dialogs>
		
		<main id="main">
		</main>
<!--
----------------------------------------- Footer -----------------------------------------
-->
		<footer>
			<div class="fl_header_footer">
				<span class="flexitem"><img class="img_btn" id="btn_f_cancel" src="images/btn_dummy.png" alt=""></span>
  			<span class="flexitem" id="f_msg" style="font-size: 0.8rem;"></span>
  			<span class="flexitem"><img class="img_btn" id="btn_f_save" src="images/btn_dummy.png" alt=""></span>
			</div>
		</footer>
  </body>

	<script>

/* ------------------------------------------------------------------------------------ */
/* globale Variablen
/* ------------------------------------------------------------------------------------ */
		var prep_editor;
		var ACT_REZIPE = new recipe (null, null, null, null, null, null, null);
		var actHTML = ACT_REZIPE.createHTML();
		const dialog_recipe_edit = new full_dialog("Rezept bearbeiten", "recipe_save()", "&#11153;", "", "", actHTML);
		const dialog_ingredients = new ingredients_dialog("Zutaten bearbeiten", "dialog_ingredients.save_and_hide()", "&#11153;", "dialog_ingredients.add_ingredient()", "&#5161;", "<div id=\"allIngredients\"></div>");
		const dialog_filter = new filter_dialog("Filter definieren", "dialog_filter.save_and_hide()", "&#11153;", "", "", "<div id=\"dialog_filter_content\"></div>");

		ClassicEditor
			.create(document.querySelector("#prep_editor"))
			.then(newEditor =>{
				prep_editor = newEditor;
			})
		.catch(error =>{
				console.error(error);
		});

/* 
-------------------------------------- Eventhandler --------------------------------------
*/

		var gv_rec_id;

		// Elementmenü einklappen, bei "scroll" und "resize"
		window.addEventListener("scroll", me_hide); 
		window.addEventListener("resize", me_hide); 
		
/* ----- Haupt-Menü ------------------------------------------------------------------- */

 		document.addEventListener('DOMContentLoaded', DOMContentLoaded);
		function DOMContentLoaded(){
			GV_FILTERMODE = "none";
			GV_URL = server_root_url + "odk_db.php?json={\"request_name\":\"get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
			reclist_get_all_recipes();
			menu_main_init();
			menu_elem_init();
		}

		// menu_main einklappen, wenn die Fenstergröße geändert wird
		window.addEventListener("resize", menu_main_init)

		function menu_main_init(){
			var menu_items = [
				// [mm_name, mm_function, mm_color]
				["", "", ""],
				["Neues<br>Rezept", "mm_RECIPE_NEW()", "red"],
				["Filter<br>neu", "mm_FILTER_NEW()", "green"],
				["Filter<br>löschen", "mm_FILTER_DEL()", "maroon"],
				["Favoriten", "mm_FAVORITES()", "blue"],
			];
			menu_main_repaint(menu_items)
			mm_is_open = false;
		}

		function mm_RECIPE_NEW(){
			mm_hide();
			me_rec_id = 0;
			//ACT_REZIPE.show(me_rec_id);
			ACT_REZIPE.clear();
			ACT_REZIPE.show();
		}
		
		function mm_FILTER_NEW(){
			mm_hide();
			dialog_filter.createHTML();
			dialog_filter.show();
			//dlg_full_show("Filter");
		}

		function mm_FILTER_DEL(){
			mm_hide();
			GV_FILTERMODE = "none";
			GV_URL = server_root_url + "odk_db.php?json={\"request_name\":\"get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
			reclist_get_all_recipes();
		}

		function mm_FAVORITES(){
			mm_hide();			
			alert("Das muss noch jemand programmieren ...");
		}

// ---------------------- Rezeptmenü ----------------------	

		function menu_elem_init(){
			var menu_items = [
				// [mm_name, mm_function, mm_color]
				["", "", ""],
				["Bearbeiten", "me_RECIPE_EDIT()", "red"],
				["Löschen", "me_RECIPE_DELETE()", "green"],
				["Zum<br>Backplan", "me_RECIPE_TO_BAKINGPLAN()", "blue"],
			];
			me_init(menu_items)
			me_is_open = false;
		}

		function me_RECIPE_EDIT(){
			me_hide();
			//ACT_REZIPE.show(me_rec_id);
			ACT_REZIPE.show();
		}
		
		function me_RECIPE_DELETE(){
			me_hide();
			if (confirm("Rezept #" + ACT_REZIPE.id + " wirklich löschen?") == true) {
				recipe_delete(ACT_REZIPE.id);
				reclist_get_all_recipes();
			}
			else{
				reclist_get_all_recipes();
			}
		}
		
		function me_RECIPE_TO_BAKINGPLAN(){
			me_hide();
			alert("Das muss noch jemand programmieren ...");
		}

/* ------------------------------------------------------------------------------------ */
/* Rezept-Dialog
/* ------------------------------------------------------------------------------------ */


		function ingredients_edit(){
			//window.location.href = "_recipe_ingredients.php?data=" + me_rec_id;
			dialog_ingredients.createHTML();
			dialog_ingredients.show();		
		}
		
		function recipe_save(){
			dialog_recipe_edit.hide();
			ACT_REZIPE.read_data_from_GUI();
			ACT_REZIPE.write_data_to_db()
			reclist_get_all_recipes();
		}		

		function prep_editor_add(){
			ClassicEditor
				.create(document.querySelector("#prep_editor"))
				.then(newEditor =>{
					prep_editor = newEditor;
				})
			.catch(error =>{
					console.error(error);
			});
		}

/* 
-------------------------------------- HTTP-Requests -------------------------------------
*/

	function reclist_get_all_recipes(){
		if (GV_FILTERMODE = "none"){
			document.getElementById("header_name").innerHTML = "Rezeptliste"
		}
		else{
			document.getElementById("header_name").innerHTML = "Rezeptliste (gefiltert)"
		}
		const url = GV_URL;
		//alert("url:\n" + url);
		const xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res = this.responseText;
				//alert("res:\n" + res);
				if (res.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url);
				}
				const json_object = JSON.parse(res);
		  	var html = "";
		  	console.log(json_object);
		  	for (i = 0; i < json_object.length; ++i) {
					html += recipe_createHTML(json_object[i]);
				}
				document.getElementById("main").innerHTML = html;
			}
		}
		xhttp.open("GET", url, false);  
		xhttp.send();		
	}

	function recipe_expand_collapse(id){
		// Menüs schliessen, wenn geöffnet
		if (mm_is_open){
			menu_main_collapse();
		}
		if (me_is_open){
			me_hide();
		}
		// blendet Zutaten und Zubereitung für das angeklickte Rezept ein/aus
		gv_rec_id = id;
		// ausblenden wenn Details schon eingeblendet sind
    if (document.getElementById("ingr_table_" + id)) {
			// Darstellung anpassen
			document.getElementById(id).style.backgroundColor = "#f0f0f0";
			document.getElementById("rec_btn_" + id).style.borderRadius = "0px 5px 5px 0px";
			document.getElementById("rec_details_" + id).style.borderTop = 0;
			// Details ausblenden
			document.getElementById("rec_details_" + id).innerHTML = "";
			document.getElementById("rec_ingr_" + id).innerHTML = "";
			document.getElementById("rec_prep_" + id).innerHTML = "";
			document.getElementById("ingr_table_" + id)
		}
		// einblenden wenn Details ausgeblendet sind
		else{
			// Darstellung anpassen
			document.getElementById(id).style.backgroundColor = "white";
			document.getElementById("rec_btn_" + id).style.borderRadius = "0px 5px 0px 0px";
			document.getElementById("rec_details_" + id).style.borderTop = "thin solid lightgray";
			// Details einblenden
			const url = server_root_url + "odk_db.php?json={\"request_name\":\"get_recipe_data\",\"id\":\"" + id + "\"}";
			const xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200){
					const res = this.responseText;
					if (res.substr(0, 8) == "DB_ERROR"){
						alert("DB-Fehler bei Request: " + url);
					}
					const json_object = JSON.parse(res);
					console.log(json_object);
					var html = "";
					// Backzeit und Backtemperatur
					html += "<span class=\"flexitem rec_details\" id=\"rec_time_" + json_object[0].id + "\"><img class=\"img_btn\" id=\"sym_clock\" src=\"images/sym_clock.svg\" alt=\"sym_clock\" style=\"margin-right: 5px;\">" + base64_2_specialchars(json_object[0].bakingtime) + "</span>";
					html += "<span class=\"flexitem rec_details\" id=\"rec_temp_" + json_object[0].id + "\"><img id=\"sym_thermo\" src=\"images/sym_thermo.svg\" alt=\"sym_thermo\" style=\"margin-right: 5px; width:15px; hight:15px\">" + base64_2_specialchars(json_object[0].bakingtemperature) + "</span>";
					document.getElementById("rec_details_" + id).innerHTML = html;
					html = "";
					// Zubereitung
					html += "<div class=\"rec_header\">Zubereitung:</div>";
					html += "<div class=\"rec_prep\">" + base64_2_specialchars(json_object[0].preparation) + "</div>";
					html = "";
					document.getElementById("rec_prep_" + id).innerHTML = html;
					// Zutaten

					const url2 = server_root_url + "odk_db.php?json={\"request_name\":\"get_recipe_ingredients\",\"id\":\"" + id + "\"}";
					const xhttp2 = new XMLHttpRequest();
					xhttp2.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200){
							const res2 = this.responseText;
							if (res2.substr(0, 8) == "DB_ERROR"){
								alert("DB-Fehler bei Request: " + url2);
							}
							const json_object2 = JSON.parse(res2);

							html = "<div class=\"rec_header\">Zutaten:</div>";
							html += "<table class=\"ingr_table\" id=\"ingr_table_" + id + "\">";
							for (i = 0; i < json_object2.length; ++i){
								html += "<tr>";
								html += "	<td id=\"rec_amount_" + id + "_" + json_object2[i].i_id + "\">" + base64_2_specialchars(json_object2[i].amount) + "</td>";
								html += "	<td id=\"rec_ingr_" + id + "_" + json_object2[i].i_id + "\">" + base64_2_specialchars(json_object2[i].name) + "</td>";
								html += "</tr>";
							}
							html += "</table>";
							document.getElementById("rec_ingr_" + id).innerHTML = html;
						}
					}
					xhttp2.open("GET", url2, false);  
					xhttp2.send();		

				}
			}
			xhttp.open("GET", url, false);  
			xhttp.send();		
			document.getElementById("f_msg").innerText = "Rezept #" + id;
		}
	}

	function recipe_delete(id){
		// Zutatenzuordnung in Tabelle "recipes_ingredients" löschen
		const url1 = server_root_url + "odk_db.php?json={\"request_name\":\"remove_all_ingredients_from_recipe\",\"rec_id\":\"" + id + "\"}";
		const xhttp1 = new XMLHttpRequest();
		xhttp1.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200){
				const res1 = this.responseText;
				if (res1.substr(0, 8) == "DB_ERROR"){
					alert("DB-Fehler bei Request: " + url1);
				}

				const url2 = server_root_url + "odk_db.php?json={\"request_name\":\"delete_recipe\",\"rec_id\":\"" + id + "\"}";
				const xhttp2 = new XMLHttpRequest();
				xhttp2.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200){
						const res2 = this.responseText;
						if (res2.substr(0, 8) == "DB_ERROR"){
							alert("DB-Fehler bei Request: " + url2);
						}
					}
				}
				xhttp2.open("GET", url2, false);  
				xhttp2.send();		

			}
		}
		xhttp1.open("GET", url1, false);  
		xhttp1.send();		
	}

	function recipe_createHTML(rec_obj){
		// Neues Rezept anlegen
		var html = ""
		// HTML-String erzeugen					
		html += " 		<div class=\"rec\" id=\"" + rec_obj.id + "\">";
		html += " 			<table class=\"rec_table\" id=\"rec_table_" + rec_obj.id + "\">";
		html += " 				<tr>";
		html += " 					<td class=\"rec_entity\" style=\"width: 30px;\" onclick=\"recipe_expand_collapse(" + rec_obj.id + ")\">";
		html += "";
		html += " 					</td>";
		html += " 					<td class=\"rec_entity rec_name\" onclick=\"recipe_expand_collapse(" + rec_obj.id + ")\">";
		html += base64_2_specialchars(rec_obj.name);
		html += " 					</td>";
		html += " 					<td class=\"rec_entity rec_btn\" id=\"rec_btn_" + rec_obj.id + "\" onclick=\"me_show(" + rec_obj.id + ")\">";
		html += "<";
		html += " 					</td>";
		html += " 				</tr>";
		html += " 			</table>";
		html += " 		 	<div class=\"rec_details\" id=\"rec_details_" + rec_obj.id + "\" onclick=\"recipe_expand_collapse(" + rec_obj.id + ")\">";
		html += "				</div>";
		html += "				<div class=\"rec_ingr\" id=\"rec_ingr_" + rec_obj.id + "\" onclick=\"recipe_expand_collapse(" + rec_obj.id + ")\">";
		html += "				</div>";
		html += "				<div class=\"rec_prep\" id=\"rec_prep_" + rec_obj.id + "\" onclick=\"recipe_expand_collapse(" + rec_obj.id + ")\">";
		html += "	 			</div>";
		html += "	 		</div>";
		return html
	}

	</script>
</html>

