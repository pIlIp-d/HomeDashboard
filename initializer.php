<?php

ini_set("display_erros", 1);

//TODO UI

//TODO cleanup / drop / truncate all tables if exist, for testing properly

class Initializer{
    private stdClass $credentials;

    public function __construct(array $highPrivUser, bool $createExamples = true){
        $this->credentials = $this->getCredentials();
        $conn = $this->connect_database(
            $this->credentials->db_cred->db_host,
            $highPrivUser["username"],
            $highPrivUser["password"]
        );
        $conn = $this->create_database(
            $conn,
            $this->credentials->db_cred->db_name,
            $this->credentials->db_cred->db_host,
            $highPrivUser["username"],
            $highPrivUser["password"]
        );

        $tables = array("bakingplans", "bakingplans_recipes", "ingredients", "recipes", "recipes_ingredients", "presets", "devices", "timers");
        $this->create_tables($conn, $tables);
        $this->clear_tables($conn, $tables);
        if ($createExamples)
            $this->create_examples();
        $conn->close();
    }
    private function getCredentials(): stdClass
    {
        $credPath = __DIR__ . "/cred.json";
        if (!file_exists($credPath))
        {
            $defaultCredentials = array(
                "username" => "sql",
                "password" => "your_password",
                "dbname" => "datenbankname",
                "servername" => "localhost"
            );
            file_put_contents($credPath, "{\"db_Cred\":".json_encode($defaultCredentials).",\"message_cred\": {\"user\": \"u78ezbqvvy8kiq64pmn86473xgeszc\",\"api_key\": \"apd459753w7ykqejabrbboed7vt2u1\"}}");
        }
        return json_decode(file_get_contents($credPath));
    }


    private function create_examples(): void
    {
        $json_strings = array("{\"request_name\":\"add_bakingplan\",\"bp_name\":\"Beispiel Backplan\"}", "{\"request_name\":\"set_active_bakingplan\",\"bp_id\":\"1\"}",
            "{\"request_name\":\"add_bakingplan\",\"bp_name\":\"Beispiel Backplan 2\"}",
            "{\"request_name\":\"add_recipe\",\"rec_name\":\"Beispiel Rezept\",\"rec_bakingtime\":\"15\",\"rec_bakingtemperature\":\"200\",\"rec_preparation\":\"Backen, Backen, Backen.\"}", "{\"request_name\":\"set_active_recipe\",\"recipe_id\":\"1\"}",
            "{\"request_name\":\"add_recipe\",\"rec_name\":\"Beispiel Rezept 2\",\"rec_bakingtime\":\"15\",\"rec_bakingtemperature\":\"200\",\"rec_preparation\":\"Backen, Backen, Backen.\"}",
            "{\"request_name\":\"paste_bakingplan_rec\",\"rec_id\":\"1\",\"bp_id\":\"1\",\"order_no\":\"0\"}",
            "{\"request_name\":\"paste_bakingplan_rec\",\"rec_id\":\"2\",\"bp_id\":\"1\",\"order_no\":\"10\"}",
            "{\"request_name\":\"add_ingredient\",\"ingr_name\":\"Beispiel Zutat\"}",
            "{\"request_name\":\"add_ingredient\",\"ingr_name\":\"Beispiel Zutat 2\"}",
            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"1\",\"i_id\":\"2\"}",
            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"1\",\"i_id\":\"1\"}",
            "{\"request_name\":\"add_ingredient_to_recipe\",\"rec_id\":\"2\",\"i_id\":\"2\"}",
            "{\"request_name\":\"add_preset\",\"preset_name\":\"empty\",
                                \"grid_object_v\":[{\"type\":\"settings\",\"size\":\"11\",\"pos\":0,\"start\":0,\"stop\":0,\"display\":0}],
                                \"grid_object_h\":[{\"type\":\"settings\",\"size\":\"11\",\"pos\":0,\"start\":0,\"stop\":0,\"display\":0}]
                            }",
            "{\"request_name\":\"add_device\",\"device_name\":\"wfo_top\"}",
            "{\"request_name\":\"add_device\",\"device_name\":\"wfo_bottom\"}",
            "{\"request_name\":\"add_device\",\"device_name\":\"grill_left\"}",
            "{\"request_name\":\"add_device\",\"device_name\":\"grill_right\"}",
            "{\"request_name\":\"add_device\",\"device_name\":\"meat\"}"

        );
        ob_start();//echo/output buffer -> buffers echos of db_handler.php
        foreach ($json_strings as $key => $json)
        {

            $send_data = http_build_query(array("json" => json_encode($json)));
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://localhost/HomeDashboard/db_handler.php" . $send_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            curl_close($ch);
        }
        echo str_replace(ob_get_clean(), "OK", "");
        echo "<br>created example data";
    }
    private function create_database(mysqli $conn, string $dbname, string $servername, string $user, string $password): mysqli
    {
        $sql = "CREATE DATABASE $dbname";
        if ($conn->query($sql) === TRUE)//=== to exclude error when no bool is returned
            echo "<br>Database '$dbname' created successfully";
        else
            echo "<br>Error creating database: <br>" . $conn->error;
        return new mysqli($servername, $user, $password, $dbname);
    }

    private function connect_database(string $servername, string $user, string $password): mysqli
    {
        $conn = new mysqli($servername, $user, $password);
        if ($conn->connect_error)//check connection
            die("Connection failed: " . $conn->connect_error);
        return $conn;
    }

    private function create_tables(mysqli $conn, array $table_list): void
    {
        foreach ($table_list as $key => $table)
            $this->create_table($conn, $table);
    }

    private function create_table(mysqli $conn, string $table_name): void
    {
        echo $table_name;
        $sql = "";
        switch ($table_name)
        {
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
        if (mysqli_query($conn, $sql))
            echo "<br>Table '$table_name' created successfully.";
        else
        {
            if (mysqli_error($conn) == "OK")
                echo "<br>" . $table_name . "was created successfully.";
            else
                echo "<br>" . mysqli_error($conn);
        }
    }

    private function clear_tables(mysqli $conn, array $tables): void
    {
        foreach ($tables as $table){
            $sql = "TRUNCATE TABLE $table";
            $conn->query($sql);
        }
    }
}