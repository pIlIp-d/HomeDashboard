<?php

ini_set('display_errors',1);
//User for DB
$username = "sql";
$password = "your_password";

$dbname =  "datenbankname";
$servername = "localhost";

function connect_database($servername, $user, $password){
    $conn = new mysqli($servername, $user, $password);
    if ($conn->connect_error)//check connection
      die("Connection failed: " . $conn->connect_error);
    return $conn;
}
function create_database($conn, $dbname, $servername, $user, $password){
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE)//=== to exclude error when no bool is returned
        echo "<br>Database '$dbname' created successfully";
    else
        echo "<br>Error creating database: <br>".$conn->error;
    return new mysqli($servername, $high_priviledge_user, $high_priviledge_user_password, $dbname);
}

function create_tables($conn, $table_list){
    foreach($table_list as $table)
        create_table($conn, $table);
}

function create_table($conn, $table_name){
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
            $sql = "CREATE TABLE presets (
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
                timecode long NOT NULL,
                timestring VARCHAR(20) NOT NULL
            )";
            break;
        case "timers":
            $sql = "CREATE TABLE timers(
                id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                preset_id INT NOT NULL,
                timer_id INT NOT NULL,
                time LONG NOT NULL
            )";
            break;

    }
    if(mysqli_query($conn, $sql))
        echo "<br>Table '$table_name' created successfully.";
    else {
        if (mysqli_error($conn) == "OK")
            echo "<br>".$table_name."was created successfully.";
        else
            echo "<br>".mysqli_error($conn);
    }
}

function safe_credentials($dbname, $username, $password){
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
                            }",
                            "{\"request_name\":\"insert_device\",\"device_name\":\"wfo_top\"}",
                            "{\"request_name\":\"insert_device\",\"device_name\":\"wfo_bottom\"}",
                            "{\"request_name\":\"insert_device\",\"device_name\":\"grill_left\"}",
                            "{\"request_name\":\"insert_device\",\"device_name\":\"grill_right\"}",
                            "{\"request_name\":\"insert_device\",\"device_name\":\"meat\"}"

                        );
    ob_start();//echo/output buffer -> buffers echos of odk_db.php
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
    echo str_replace(ob_get_clean(),"OK","");
    echo "<br>created example data";
}

//TODO UI

//user for creating /initialising tables
$high_priviledge_user = "root";
$high_priviledge_user_password = "";

$conn = connect_database($servername, $high_priviledge_user, $high_priviledge_user_password);
$conn = create_database($conn, $dbname, $servername, $high_priviledge_user, $high_priviledge_user_password));

create_table($conn, array("bakingplans","bakingplans_recipes","ingredients","recipes","recipes_ingredients","presets","devices","timers"););
safe_credentials($dbname, $username, $password);
create_examples();

$conn->close();

?>
