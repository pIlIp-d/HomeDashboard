<?php

function connect_database(){
    global $conn, $servername, $high_priviledge_user, $high_priviledge_user_password;
    $conn = new mysqli($servername, $high_priviledge_user, $high_priviledge_user_password);
    if ($conn->connect_error)//check connection
      die("Connection failed: " . $conn->connect_error);
}
function create_database(){
    global $conn, $dbname, $servername, $high_priviledge_user, $high_priviledge_user_password;
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE)
        echo "<br>Database '$dbname' created successfully";
    else
        echo "<br>Error creating database: <br>".$conn->error;
    $conn = new mysqli($servername, $high_priviledge_user, $high_priviledge_user_password, $dbname);
}

function create_table($table_name){
    global $conn;
    switch($table_name){
        case "bakingplans":
            $sql = "CREATE TABLE bakingplans(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                type VARCHAR(10) DEFAULT NULL
            )";
            break;
        case "bakingplans_recipes":
            $sql = "CREATE TABLE bakingplans_recipes(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                recipes_id INT NOT NULL,
                bakingplans_id INT NOT NULL,
                order_no INT NOT NULL
            )";
            break;
        case "ingredients":
            $sql = "CREATE TABLE ingredients(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(70) NOT NULL
            )";
            break;
        case "recipes":
            $sql = "CREATE TABLE recipes(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                preparation TEXT NOT NULL,
                bakingtime INT NOT NULL,
                bakingtemperature INT NOT NULL,
                active BIT NOT NULL
            )";
            break;
        case "recipes_ingredients":
            $sql = "CREATE TABLE recipes_ingredients(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                ingredients_id INT NOT NULL,
                recipes_id INT NOT NULL,
                amount VARCHAR(20) NOT NULL
            )";
            break;
        case "presets":
            $sql = "CREATE TABLE presets(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                grid_object_v JSON NOT NULL,
                grid_object_h JSON NOT NULL
            )";
            break;
        case "devices":
            $sql = "CREATE TABLE devices(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                temp_act INT NOT NULL,
                temp_min INT NOT NULL,
                temp_max INT NOT NULL,
                timecode int NOT NULL,
                timestring VARCHAR(20) NOT NULL
            )";
            break;
    }
    if(mysqli_query($conn, $sql))
        echo "<br>Table '$table_name' created successfully.";
    else
        echo "<br>" . mysqli_error($conn);

}

function safe_credentials(){
    global $username, $password, $dbname;
    $filename = "cred.json";
    if (file_exists($filename))
        return;
    $json_cred = "
    {
      \"db_cred\": {
        \"username\": \"$username\",
        \"password\": \"$password\",
        \"db_name\": \"$dbname\"
      },
      \"mail_cred\": {
          \"username\": \"\",
          \"password\": \"\",
          \"server\": \"\"
      }
    }
    ";
    file_put_contents($filename,$json_cred);
}

function create_examples(){
    $json_strings = array("{\"request_name\":\"insert_bakingplan\",\"bp_name\":\"Beispiel Backplan\"}","{\"request_name\":\"bakingplan_activate\",\"bp_id\":\"1\"}",
                            "{\"request_name\":\"insert_bakingplan\",\"bp_name\":\"Beispiel Backplan 2\"}",
                            "{\"request_name\":\"add_recipe\",\"rec_name\":\"Beispiel Rezept\",\"rec_bakingtime\":\"15\",\"rec_bakingtemperature\":\"200\",\"rec_preparation\":\"Backen, Backen, Backen.\"}","{\"request_name\":\"set_active_recipe\",\"recipe_id\":\"1\"}",
                            "{\"request_name\":\"add_recipe\",\"rec_name\":\"Beispiel Rezept 2\",\"rec_bakingtime\":\"15\",\"rec_bakingtemperature\":\"200\",\"rec_preparation\":\"Backen, Backen, Backen.\"}",
                            "{\"request_name\":\"bakingplan_paste_rec\",\"rec_id\":\"1\",\"bp_id\":\"1\",\"order_no\":\"0\"}",
                            "{\"request_name\":\"bakingplan_paste_rec\",\"rec_id\":\"2\",\"bp_id\":\"1\",\"order_no\":\"10\"}",
                            "{\"request_name\":\"insert_ingredient\",\"ingr_name\":\"Beispiel Zutat\"}",
                            "{\"request_name\":\"insert_ingredient\",\"ingr_name\":\"Beispiel Zutat 2\"}",
                            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"1\",\"i_id\":\"2\"}",
                            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"1\",\"i_id\":\"1\"}",
                            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"2\",\"i_id\":\"2\"}",
                            "{\"request_name\":\"set_new_preset\",\"preset_name\":\"empty\",
                                \"grid_object_v\":[{\"type\":\"settings\",\"size\":\"11\",\"pos\":0,\"start\":0,\"stop\":0,\"display\":0}],
                                \"grid_object_h\":[{\"type\":\"settings\",\"size\":\"11\",\"pos\":0,\"start\":0,\"stop\":0,\"display\":0}]
                            }"
                        );
    foreach ($json_strings as $key => $json){
        if ($key == 0){
            $_GET["json"] = $json;
            include 'odk_db.php';
        }
        else {
            $json_decoded = json_decode($json);
            make_request($json_decoded->request_name);
        }
    }
}

//TODO UI

//User for DB
$credentials = json_decode(file_get_contents("cred.json"))->db_cred;//To be changed to html UI
$username = $credentials->username;
$password =  $credentials->password;

$dbname =  "DatenbankTest";
$servername = "localhost";

//user for creating /initialising tables
$high_priviledge_user = "root";
$high_priviledge_user_password = "";

connect_database();
create_database();

create_table("bakingplans");
create_table("bakingplans_recipes");
create_table("ingredients");
create_table("recipes");
create_table("recipes_ingredients");
create_table("presets");
safe_credentials();
create_examples();



$conn->close();


?>
