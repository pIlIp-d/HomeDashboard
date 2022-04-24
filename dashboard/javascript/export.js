
window.addEventListener('DOMContentLoaded', init_export());

function init_export(){

    replaceSVGs();
    json_create_for_pdf(document.getElementById("php_request").innerHTML);
}

function xhttp_request_export(request, id) {
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

function json_create_for_pdf(request){
	if (request == "download_pdf")
		get_pdf(true);
	else if (request == "print_pdf") {
        setPrintCSS();
        window.print();
    }
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
				recipe_object[act_recipe % recipe_count].name+
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

function setPrintCSS(){
    document.getElementById("main").classList.add("main-print");
}

function replaceSVGs(){
    document.getElementById("bakingtemperature").remove();
    document.getElementById("clock").remove();
    let time_container = document.getElementById("bakingtime_container");
    time_container.innerHTML = "<span id='clock_text'>Backzeit:</span>" + time_container.innerHTML;
    let temp_container = document.getElementById("bakingtemparature_container");
    temp_container.innerHTML = "<span id='temp_text'>Backtemperatur:</span>" + temp_container.innerHTML;
}

//creates canvas/screenshot object and converts it to pdf
function get_pdf(bool){
    console.log(recipe_object);
    const { jsPDF } = window.jspdf;
	const pdf_doc = new jsPDF();
    pdf_doc.html(document.body, {
        callback: function (pdf_doc) {
            if (bool)
                pdf_doc.save(recipe_object[act_recipe % recipe_count].name);
            else
                return pdf_doc.output();
        },
        unit: 'pt',
        format: 'letter',
        orientation: 'portrait',
        margin: [7,7]
    });
}
