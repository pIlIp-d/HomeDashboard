<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <style type="text/css">
    




  </style>
</head>
<body>
  <!-- buttons -->
  <div class="nav" id="nav">
    <span onclick="last_recipe();"><</span>
    <span onclick="next_recipe();">></span>
  </div>
  <!-- content -->
  <div class="main" id="main"></div>

<script type="text/javascript">
  var server_url = "http://192.168.115.9/HomeDashboard/";
  var recipe_object = [];
  var recipe_info = [];
  var recipe_ingredients = [];
  var recipe_count;
  var act_recipe = 0;
  window.addEventListener('DOMContentLoaded', init());


  function init(){
    xhttp_request("get_list_of_recipes","");
     console.log(recipe_object);
  }

  function last_recipe(){
    if (act_recipe != 0){
      act_recipe--;
    }
    else {
      act_recipe = recipe_count-1;
    }
    get_full_recipe();
  }

  function next_recipe(){
    if (act_recipe != recipe_count-1){
      act_recipe++;
    }
    else {
      act_recipe = 0;
    }
    get_full_recipe();
  }



  function get_full_recipe(){
    if (act_recipe < recipe_count) {
      xhttp_request("get_recipe_data",recipe_object[act_recipe].id);
      xhttp_request("get_recipe_ingredients",recipe_object[act_recipe].id);
    }
  }

  function act_recipe_to_html(){
    var html = "";
    html += "<h1>"+ base64_2_specialchars(recipe_object[act_recipe].name) +"</h1>";
    html += "<span>Backzeit: "+ base64_2_specialchars(recipe_info[0].bakingtime) +"</span><br>";
    html += "<span>Temperatur: "+ base64_2_specialchars(recipe_info[0].bakingtemperature) +"</span><br>";
    html += "<table><tr><th>Zutaten</th></tr>";
    for (i=0;i < recipe_ingredients.length;i++){ 
      html += "<tr><td>"+ base64_2_specialchars(recipe_ingredients[i].amount) +"</td>";
      html += "<td>"+base64_2_specialchars(recipe_ingredients[i].name)+"</td></tr>";
    }
    html += "</table>";
    html += "";

    html += "<p>"+ recipe_info[0].preparation +"</p>";
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
    if (request == "get_list_of_recipes"){
      url += "get_list_of_recipes\",\"id_list\":\"\",\"count\":\"\",\"filtermode\":\"none\"}";
    }
    else if (request == "get_recipe_data" || request == "get_recipe_ingredients"){
      url += request + "\",\"id\":\"" + id + "\"}";
    }
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200){
        const response = this.responseText;
        //error catching
        if (response.substr(0, 8) == "DB_ERROR"){
          alert("DB-Fehler bei Request: " + url);
        }
        const json_object = JSON.parse(response);
        //abspeichern in JS arrays
        switch (request){
          case "get_list_of_recipes":
            recipe_object = [];
            for (i=0; i < json_object.length; i++){
              recipe_object[i] = json_object[i];
            }
            recipe_count = recipe_object.length; 
            get_full_recipe();//get list of all recepies
            break;
          case "get_recipe_data":
            recipe_info = [];
            for (i=0; i < json_object.length; i++){
              recipe_info[i] = json_object[i];
            }
            act_recipe_to_html();
            break;
          case "get_recipe_ingredients":
            recipe_ingredients = [];
          console.log(recipe_ingredients);
            for (i=0; i < json_object.length; i++){
              recipe_ingredients[i] = json_object[i];
            }
            act_recipe_to_html();
            break;
        }
      }
    }
    xhttp.open("GET", url, false);
    xhttp.send();
  }

</script>

</body>
</html>