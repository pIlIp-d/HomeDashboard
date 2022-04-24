var server_url = "http://"+location.host+"/HomeDashboard/";
var recipe_object = [];
var recipe_info = [];
var recipe_ingredients = [];
var recipe_count;
var act_recipe = 4;

  //init
  window.addEventListener('DOMContentLoaded',  xhttp_request("get_list_of_recipes",""));

//---------------------------
//------ Buttons -------------
//---------------------------
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

//---------------------------
//------ HTML ---------------
//---------------------------
    function toogleBakingplan(){
      bakingplan_is_active = bakingplan_is_active ^ 1;
      if (bakingplan_is_active)
        document.getElementById("btn_bakingplan").innerHTML = "Bakingplan: on";
      else
        document.getElementById("btn_bakingplan").innerHTML = "Bakingplan: off";
      xhttp_request("get_list_of_recipes","");
    }

  function get_full_recipe(){
      console.log("get_full_recipe");
    if (act_recipe < recipe_count) {
      xhttp_request("get_recipe_data",recipe_object[act_recipe].id);
      xhttp_request("get_recipe_ingredients",recipe_object[act_recipe].id);
    }
  }

  function act_recipe_to_html(){
    var html = "";
    html += "<h2 class=\"rec_name\">"+ base64_2_specialchars(recipe_object[act_recipe].name) +"</h1>";
    html += "<span class=\"rec_bakingtime\">"+ base64_2_specialchars(recipe_info[0].bakingtime) + "  <img id='clock' src=\"/HomeDashboard/images/clock_black.svg\"></span>";
    html += "<span class=\"rec_bakingtemperature\">"+ base64_2_specialchars(recipe_info[0].bakingtemperature) +"  <img id='bakingtemperature' src=\"/HomeDashboard/images/sym_thermo_black.svg\"></span><br>";
    html += "<table class=\"ingr_table\"><tr><th>Zutaten</th></tr>";
    for (i=0;i < recipe_ingredients.length;i++){
      html += "<tr><td>"+ base64_2_specialchars(recipe_ingredients[i].amount) +"</td>";
      html += "<td>"+base64_2_specialchars(recipe_ingredients[i].name)+"</td></tr>";
    }
    html += "</table>";
    html += "";
    html += "<p class=\"content\">"+ recipe_info[0].preparation +"</p>";
    document.getElementById("main").innerHTML = html;
  }

//---------------------------
//------ Request ------------
//---------------------------

  function xhttp_request(request, id) {
    console.log(request +" "+id);
    var url = "";
    url = server_url + "odk_db.php?json={\"request_name\":\"";
    if (bakingplan_is_active && request == "get_list_of_recipes")
      url += "bakingplan_basic_get_all_recipes\"}";
    else if (request == "get_list_of_recipes")
      url += "get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
    else if (request == "get_recipe_data" || request == "get_recipe_ingredients")
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
            if (!bakingplan_is_active){
              recipe_object = [];
              for (i=0; i < json_object.length; i++)
                recipe_object[i] = json_object[i];
              recipe_count = recipe_object.length;
            }
            else{
            }
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
