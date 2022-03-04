var server_url = "/HomeDashboard/";
var recipe_object = [];
var recipe_info = [];
var recipe_ingredients = [];
var recipe_count;
var act_recipe = 4;
//pdf gen
var imgData;

window.addEventListener('DOMContentLoaded', init());

function init(){
    xhttp_request("get_list_of_recipes","");
    console.log(recipe_object);
}

function get_full_recipe(){
	act_recipe = document.getElementById("php_message").innerHTML % recipe_count;
    if (act_recipe < recipe_count) {
		xhttp_request("get_recipe_data",recipe_object[act_recipe].id);
		xhttp_request("get_recipe_ingredients",recipe_object[act_recipe].id);
    }
}

function act_recipe_to_html(){
    var html = "";
    html += "<div id='container'>";
    html += "<h2 class=\"rec_name\">"+ base64_2_specialchars(recipe_object[act_recipe].name) +"</h1>";
    html += "<span class=\"rec_bakingtime\">"+ base64_2_specialchars(recipe_info[0].bakingtime) + "  <img id='clock' src=\"/HomeDashboard/images/clock_black.svg\"></span>";
    html += "<span class=\"rec_bakingtemperature\">"+ base64_2_specialchars(recipe_info[0].bakingtemperature) +"  <img id='bakingtemperature' src=\"/HomeDashboard/images/sym_thermo_black.svg\"></span><br>";
    html += "<table class=\"ingr_table\"><tr><th>Zutaten</th></tr>";
    for (i=0;i < recipe_ingredients.length;i++){
      	html += "<tr><td>"+ base64_2_specialchars(recipe_ingredients[i].amount) +"</td>";
      	html += "<td>"+base64_2_specialchars(recipe_ingredients[i].name)+"</td></tr>";
    }
    html += "</table>";
    html += "<p class=\"content\">"+ recipe_info[0].preparation +"</p></div>";
    document.getElementById("main").innerHTML = html;
	}

function base64_2_specialchars(string){
	// ersetzt die folgenden in eckige Klammen gehüllten kodierten Sonderzeichen durch den eigentlichen Wert
	// "(WyJd) , &(WyZd) , +(Wytd) , #(WyNd)
	string = string.replace(/WyJd/g, "\"");
	string = string.replace(/WyZd/g, "&");
	string = string.replace(/Wytd/g, "+");
	string = string.replace(/WyNd/g, "#");
	return string;
}

function xhttp_request(request, id) {
    console.log(request +" "+id);
    var url = "";
    url = server_url + "odk_db.php?json={\"request_name\":\"";
    if (request == "get_list_of_recipes")
      	url += "get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
    else if (request == "get_recipe_data" || request == "get_recipe_ingredients")
      	url += request + "\",\"id\":\"" + id + "\"}";
    else if (request == "send_recipe_mail")
   		url += request + "\"}";
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      	if (this.readyState == 4 && this.status == 200){
        	const response = this.responseText;
        	//error catching
        	if (response.substr(0, 8) == "DB_ERROR")
          		alert("DB-Fehler bei Request: " + url);
        	const json_object = JSON.parse(response);
        	//abspeichern in JS arrays
        	switch (request){
          		case "get_list_of_recipes":
            		recipe_object = [];
		           	for (i=0; i < json_object.length; i++)
		              	recipe_object[i] = json_object[i];
		            recipe_count = recipe_object.length;
		            get_full_recipe();//get list of all recepies
		            break;
          		case "get_recipe_data":
		            recipe_info = [];
		            for (i=0; i < json_object.length; i++)
		              recipe_info[i] = json_object[i];
		            act_recipe_to_html();
		            break;
          		case "get_recipe_ingredients":
            		recipe_ingredients = [];
          			console.log(recipe_ingredients);
		            for (i=0; i < json_object.length; i++)
		            	recipe_ingredients[i] = json_object[i];
		            act_recipe_to_html();
		            break;
        	}
      	}
   	}
   	xhttp.open("GET", url, false);
   	xhttp.send();
}

//--------------------------------
//----- PDF Gen ------------------
//--------------------------------

//creates canvas/screenshot object
html2canvas(document.body).then(function(canvas) {
		imgData = canvas.toDataURL("image/jpeg", 1.0);
		json_create_for_pdf(document.getElementById("php_request").innerHTML);
	});

//converts canvas to JPEG to PDF
function get_pdf(bool){
	const { jsPDF } = window.jspdf;
	const pdf_doc = new jsPDF();
	pdf_doc.addImage(imgData, 'JPEG',0 ,0);
	if (bool)
		pdf_doc.save(recipe_object[act_recipe].name);
	else
		return pdf_doc.output();
}

//--------------------------------
//----- JSON Gen -----------------
//--------------------------------

function json_create_for_pdf(request){
	if (request == "download_pdf")
		get_pdf(true);
	else if (request == "print_pdf")
		window.print();
	if (request == "send_mail"){
		var loop = true;
		while(loop){
			var mail = prompt("An welche Email soll das Rezept gesendet werden?","example@mail.com");
			if (mail != undefined && mail != null && mail != ""){
				 var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				// = /\S+@\S+\.\S+/;
	        	if (regex.test(mail) && mail != "example@mail.com")
	        		loop = false;
				else
					alert("Keine valide Mail Adresse!");
			}
			else {
				loop = false;
				break;
			}
		}
		var json_string = '{"name":"'+
				recipe_object[act_recipe].name+
				'","request":"'+request+
				'","bakingtime":"'+
				recipe_info[0].bakingtime+
				'","bakingtemperature":"'+
				recipe_info[0].bakingtemperature+
				'","preparation":"'+
				recipe_info[0].preparation+
				'","pdf":"'+
				btoa(get_pdf(false))+
				'","mail":"'+mail+'"}';
		json_obj = JSON.parse(json_string);
		json_obj["ingredients"] = recipe_ingredients;
		json_string = JSON.stringify(json_obj);

		var data = new FormData();
		data.append("data", json_string);
		var url = server_url + "json_handler_pdf_export.php";//?json=" + json_string;
	    var xhttp = new XMLHttpRequest();
	    xhttp.open( 'post', url, true);
	    xhttp.send(data);
	}
}
