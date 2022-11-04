<?php

include_once(__DIR__ . "/../initializer.php");

use PHPUnit\Framework\TestCase;

# TODO remove credFile and find a better solution via environment variables

class CustomTestCase extends TestCase
{
    protected static string $testDB = "testDB";
    protected static string $mainDB;
    protected static string $credFile;

    protected static array $defaultRecipeData = array(
        'request_name' => 'add_recipe',
        'rec_name' => 'testRecipe',
        'rec_bakingtime' => '100',
        'rec_bakingtemperature' => '160',
        'rec_preparation' => 'This is the Preparation Text'
    );

    function removeRecipe(int $recipeID)
    {
        $send_data = http_build_query(array("json" => "{\"request_name\":\"remove_recipe\",\"rec_id\":\"$recipeID\"}"));
        if ("OK" != $this->getResponse($send_data))
        {
            $this->fail("Removing Recipe $recipeID failed.");
        }
    }

    protected function addDefaultRecipe(): int
    {
        return $this->addRecipe(self::$defaultRecipeData);
    }

    protected function addRecipe(array $recipeData): int
    {
        return $this->simpleRequest($recipeData);
    }

    protected function getRecipeData(int $recipeId): string
    {
        $recipeData = array(
            'request_name' => 'get_recipe_data',
            'id' => $recipeId
        );
        $send_data = http_build_query(array("json" => json_encode($recipeData)));
        return $this->getResponse($send_data);
    }

    protected static array $defaultIngredient = array(
        'request_name' => 'add_ingredient',
        'ingr_name' => 'testIngredient'
    );

    protected function addDefaultIngredient(): int
    {
        return $this->simpleRequest(self::$defaultIngredient);
    }

    protected function addIngredient(array $recipeData): int
    {
        return $this->simpleRequest($recipeData);
    }

    function removeIngredient(int $ingredientID)
    {
        $send_data = http_build_query(array("json" => "{\"request_name\":\"remove_ingredient\",\"i_id\":\"$ingredientID\"}"));
        if ("OK" != $this->getResponse($send_data))
        {
            $this->fail("Removing Ingredient $ingredientID failed.");
        }
    }

    function removeIngredients(array $ingredientIDs)
    {
        $str = "{";
        foreach ($ingredientIDs as $key => $id)
        {
            if ($key === 0)
                $str .= $id;
            else
                $str .= ", " . $id;
        }
        $str = "}";
        $send_data = http_build_query(array("json" => "{\"request_name\":\"remove_ingredient\",\"rec_id\":$str}"));
        if ("OK" != $this->getResponse($send_data))
        {
            $this->fail("Removing Ingredients failed.");
        }
    }

    protected function getIngredientsData(int $ingredientId): string
    {
        $recipeData = array(
            'request_name' => 'get_recipe_data',
            'id' => $ingredientId
        );
        $send_data = http_build_query(array("json" => json_encode($recipeData)));
        return $this->getResponse($send_data);
    }


    public static function setUpBeforeClass(): void
    {
        self::$credFile = realpath(__DIR__ . "\..\cred.json");
        $rawCreds = file_get_contents(self::$credFile);
        $creds = json_decode($rawCreds);

        self::$mainDB = $creds->db_cred->db_name;
        $creds->db_cred->db_name = self::$testDB;

        file_put_contents(self::$credFile, json_encode($creds));
        ob_start();
        new Initializer(array("username" => "root", "password" => ""), false);
        ob_clean();
    }

    public function setUp(): void
    {
        $read_db_name = json_decode(file_get_contents(self::$credFile))->db_cred->db_name;
        if (self::$testDB !== $read_db_name)
            $this->fail("Testing DB needs to be the active DB.");
    }

    protected function getResponse($data): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/HomeDashboard/db_handler.php?" . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);
        curl_close($ch);
        return $server_output;
    }

    protected function isValidJSON($jsonString): bool
    {
        if (!empty($jsonString))
        {
            @json_decode($jsonString);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    protected function simpleRequest(array $requestData): string|int
    {
        $send_data = http_build_query(array("json" => json_encode($requestData)));
        return $this->getResponse($send_data);
    }

    public static function tearDownAfterClass(): void
    {
        $rawCreds = file_get_contents(self::$credFile);
        $creds = json_decode($rawCreds);
        $creds->db_cred->db_name = self::$mainDB;
        file_put_contents(self::$credFile, json_encode($creds));
    }

}