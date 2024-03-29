<?php

ini_set("display_erros", 1);

class Initializer
{
    public function __construct(array $highPrivUser, string $dbname, bool $createExamples = true, bool $deleteCurrentEntries = false)
    {
        include_once "db_handler.php";
        $conn = connect_database(
            $highPrivUser["username"],
            $highPrivUser["password"],
            $dbname
        );
        $tables = array("bakingplans", "bakingplans_recipes", "ingredients", "recipes", "recipes_ingredients", "presets", "devices", "timers");
        $this->create_tables($conn, $tables);
        if ($deleteCurrentEntries)
            $this->clear_tables($conn, $tables);

        if ($createExamples)
            $this->create_examples();
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
        foreach ($json_strings as $key => $json) {
            $json_decoded = json_decode($json);
            make_request($json_decoded->request_name, $json_decoded);
        }
        echo "<br>";
        echo str_replace(ob_get_clean(), "OK", "");
        echo "<br>created example data";
    }

    private function create_tables(PDO $conn, array $table_list): void
    {
        foreach ($table_list as $key => $table)
            $this->create_table($conn, $table);
    }

    private function create_table(PDO $conn, string $table_name): void
    {
        $sql = "";
        switch ($table_name) {
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
                active BIT NOT NULL DEFAULT 0
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
                temp_act INT NOT NULL DEFAULT 0,
                temp_min INT NOT NULL DEFAULT 0,
                temp_max INT NOT NULL DEFAULT 0,
                timecode long NOT NULL DEFAULT CURRENT_TIMESTAMP,
                timestring VARCHAR(20) NOT NULL DEFAULT CURRENT_TIMESTAMP
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
        $stmt = $conn->prepare($sql);
        try {
            $stmt->execute();
            echo "<br> created: " . $table_name;
        } catch (PDOException $e) {
            echo "Something went wrong. check if the base tables already exist.<br>";
            echo $e->getMessage();
        }
    }

    private function clear_tables(PDO $conn, array $tables): void
    {
        foreach ($tables as $table) {
            $stmt = $conn->prepare("TRUNCATE TABLE $table;");
            $stmt->execute();
            echo "deleted $table.<br>";
        }
    }

}

ini_set('display_errors', '1');

function showForm($errorMessage = ""): void
{
    echo <<< FORM
            <!DOCTYPE html>
            <html>
                <h1>Create DB tables with admin rights</h1>
                <div style="color:red;">$errorMessage</div>
                <form action="./initializer.php" method="POST">
                    <label for="username">SQL-Admin Username</label><br>
                    <input type="text" name="username" value="root">
                    <br>
                    <label for="password">SQL-Admin Password</label><br>
                    <input type="password" name="password"><br>
                    <label for="delete_entries">Delete all current entries.</label>
                    <input type="checkbox" name="delete_entries" value="true"><br>
                    <label for="create_entries">Create Example entries</label>
                    <input type="checkbox" name="create_entries" value="true"><br><br>
                    <input type="submit" name="submit" value="Create Tables">
                </form>
            </html>
        FORM;
}

if (count(get_included_files()) == 1) {
    if (isset($_POST["submit"])) {
        try {
            new Initializer(
                array("username" => $_POST["username"], "password" => $_POST["password"]),
                getenv("MYSQL_DATABASE"),
                $_POST["create_entries"] ?? false,
                $_POST["delete_entries"] ?? false
            );
        } catch (PDOException $e) {
            echo $e;
            showForm("Wrong Admin/root-credentials or drivers not working/ PDOException. Try again");
        }
    } else {
        showForm();
    }
}