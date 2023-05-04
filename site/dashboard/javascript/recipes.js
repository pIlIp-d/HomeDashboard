
var recipe_object = [];
var recipe_info = [];
var recipe_ingredients = [];
var recipe_count;
var act_recipe = 4;

window.addEventListener('DOMContentLoaded',  xhttp_request("get_list_of_recipes",""));

//Share Menu
var expanded = false;
function share_menu(){
  var table = document.getElementById("share_table");
   if (!expanded) {
    table.style.display = "block";
    expanded = true;
    document.getElementById("share_button").style.opacity = "70%";
  }
  else {
    table.style.display = "none";
    expanded = false;
    document.getElementById("share_button").style.opacity = "100%";
  }
}
function last_recipe(){
  if (act_recipe != 0)
    act_recipe--;
  else
    act_recipe = recipe_count-1;
  get_full_recipe();
}

function next_recipe(){
  if (act_recipe != recipe_count-1)
    act_recipe++;
  else
    act_recipe = 0;
  get_full_recipe();
}

function getSVG(path){
    var xhttp = new XMLHttpRequest();
    var response = "";
    xhttp.onreadystatechange = function(me = this) {
        if (this.readyState == 4 && this.status == 200)
            response = this.responseText;
        else {
            if (this.status > 399)
                console.log("error or wrong response code");
        }
    }
    xhttp.open("GET", path, false);
    xhttp.send();
    return response;
}

function act_recipe_to_html(){
    console.log(recipe_object);
  var html = "";
  html += "<h2 class=\"rec_name\">"+ base64_2_specialchars(recipe_object[act_recipe % recipe_count].name) +"</h1>";
  html += "<div id='bakingtime_container'><span class=\"rec_bakingtime\">"+ base64_2_specialchars(recipe_info[0].bakingtime) + getSVG('/images/clock_black.svg')+"</div>";
  html += "<div id='bakingtemparature_container'><span class=\"rec_bakingtemperature\">"+ base64_2_specialchars(recipe_info[0].bakingtemperature) + getSVG('/images/sym_thermo_black.svg')+"</div>";
  html += "<table class=\"ingr_table\"><tr><th>Zutaten</th></tr>";
  for (i=0;i < recipe_ingredients.length;i++){
    html += "<tr><td>"+ base64_2_specialchars(recipe_ingredients[i].amount) +"</td>";
    html += "<td>"+base64_2_specialchars(recipe_ingredients[i].name)+"</td></tr>";
  }
  html += "</table>";
  html += "";
  html += "<p class=\"content \">"+ recipe_info[0].preparation +"</p>";
  document.getElementById("main").innerHTML = html;
}

function get_full_recipe(){
    xhttp_request("get_recipe_data",recipe_object[act_recipe % recipe_count].id);
    xhttp_request("get_recipe_ingredients",recipe_object[act_recipe % recipe_count].id);
}

function xhttp_request(request, id) {
  console.log(request +" "+id);
  var url = "";
  url = DB_URL + "?json={\"request_name\":\"";
  //if (request == "get_list_of_recipes")
  //  url += "bakingplan_basic_get_all_recipes\"}";
  if (request == "get_list_of_recipes")
    url += "get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
  if (request == "get_recipe_data" || request == "get_recipe_ingredients")
    url += request + "\",\"id\":\"" + id + "\"}";
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

function exportpdf(request){
  const doc = document.getElementById('hidden_canvas');
  doc.innerHTML = "";
  doc.innerHTML = "<iframe id='hidden_canvas' src='/dashboard/export.php?recipe="+act_recipe+"&request="+request+" '></iframe>";
}
