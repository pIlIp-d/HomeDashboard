<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <style type="text/css">
    
    a { 
      text-decoration: none; 
      color: black;
    }
    /*share*/
    #share_table{
      display: none;
      position: absolute;
      top: calc(6px + 2rem);
      right: 6px;
      font-family: Arial;
      z-index: 100;
    }
    #share_button{
      position: absolute;
      width: 1.8rem;
      height: 1.8rem;
      padding:  10px;
      top: 6px;
      right: 6px;
    }
    #hidden_canvas {
      position: fixed;
      visibility: hidden;
    }
    .table_item{
      position: absolute;
      border-width: 1px;
      border-style: solid;
      right: 6px;
      padding: 0.5rem;
      background-color: #ffffff;

      width: 6.8rem;
      height: 1rem;
    }
    .table_item:hover{
      background-color: #f4f4f4;
    }
   
    #share_1{
      top: 0.1rem;
    }
    #share_2{
      top: 2.4rem;
    }
    #share_3{
      top: 4.7rem;
    }

    /*-- Arrows --*/  
    #last_rec {
      transform: rotate(-90deg);
      width: 1rem;
      height: 1rem;
      margin: 0;
      padding: 10px;
      top: 15px;
      left: 5%;
    }
    #next_rec {
      transform: rotate(90deg);
      width: 1rem;
      height: 1rem;
      margin: 0;
      padding: 10px;
      top: 15px;
      left: 5%;
    }
    #config{
      width: 1rem;
      height: 1rem;
      margin: 0;
      padding: 10px;
      top: 15px;
      left: 5%;
    }
    /* content */
    .rec_bakingtime {
      position: relative;
      left: 1%;
      top: 11%;
    }
    .rec_bakingtemperature {
      position: relative;
      left: 16%;
      top: 11%;
    }
    #clock{
      position:  relative;
      top: 3px;
      height: 1.2rem;
      width:  1.2rem;
    }
    #bakingtemperature{
      position:  relative;
      top: 3px;
      left: -4px;
      height: 1.2rem;
      width:  1.2rem;
    }
    .main {
      z-index: 0;
      pointer-events:none;
      font-family: Arial;
      top:  30px;
      left:  4%;
      position: absolute;
      height: auto;
    }
    

  </style>
</head>
<body>
  <!-- buttons -->
  <div class="nav" id="nav">
    <img src="/HomeDashboard/images/arrow_up.svg" id="last_rec" onclick="last_recipe();">
    <img src="/HomeDashboard/images/arrow_up.svg" id="next_rec" onclick="next_recipe();">
    <a href="/HomeDashboard/odk_recipies.php"><img src="/HomeDashboard/images/sym_pencil.svg" id="config"></a>
  </div>
  <!-- content -->
  <div class="main" id="main"></div>
  <div id="hidden_canvas"></div>
  <!--share-->
  <div class="share-container">
    <img src="/HomeDashboard/images/share_bold.svg" id="share_button" onclick="share_menu();">
        <div id="share_table">
          <span class="table_item" id="share_1" onclick='exportpdf("download_pdf");'>Download PDF</span>
          <span class="table_item" id="share_2" onclick='exportpdf("print_pdf");'>Print PDF</span>
          <span class="table_item" id="share_3" onclick='exportpdf("send_mail");'>Send via Mail</span>
        </span>
  </div>
<script type="text/javascript">
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

  function exportpdf(request){
    const doc = document.getElementById('hidden_canvas');
    doc.innerHTML = "";
    doc.innerHTML = "<iframe id='hidden_canvas' src='/HomeDashboard/dashboard/export.php?recipe="+act_recipe+"&request="+request+" '></iframe>";
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

//---------------------------
//------ HTML ---------------
//---------------------------

  function get_full_recipe(){
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

  function base64_2_specialchars(string){
  // ersetzt die folgenden in eckige Klammen gehüllten kodierten Sonderzeichen durch den eigentlichen Wert
  // "(WyJd) , &(WyZd) , +(Wytd) , #(WyNd)
    string = string.replace(/WyJd/g, "\"");
    string = string.replace(/WyZd/g, "&");
    string = string.replace(/Wytd/g, "+");
    string = string.replace(/WyNd/g, "#");
    return string;
  }

//---------------------------
//------ Request ------------
//---------------------------

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