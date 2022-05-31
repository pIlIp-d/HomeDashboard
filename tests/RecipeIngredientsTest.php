<?php

include_once("CustomTestCase.php");

class RecipeIngredientsTest extends CustomTestCase
{
    protected static array $defaultIngredient = array(
        'request_name' => 'insert_ingredient',
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

    function removeIngredient(int $ingredientID){
        $send_data = http_build_query(array("json" => "{\"request_name\":\"remove_ingredient\",\"i_id\":\"$ingredientID\"}"));
        if ( "OK" != $this->getResponse($send_data)){
            $this->fail("Removing Ingredient $ingredientID failed.");
        }
    }

    function removeIngredients(array $ingredientIDs){
        $str = "{";
        foreach($ingredientIDs as $key => $id)
        {
            if ($key === 0)
                $str .= $id;
            else
                $str .= ", ".$id;
        }
        $str = "}";
        $send_data = http_build_query(array("json" => "{\"request_name\":\"remove_ingredient\",\"rec_id\":$str}"));
        if ( "OK" != $this->getResponse($send_data)){
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

    /**
     * @test
     */
    public function validInsertIngredientTest(): void
    {
        $ingrId = $this->addIngredient(self::$defaultIngredient);
        $this->assertIsNumeric($ingrId);

        $this->removeIngredient($ingrId);
    }

    /**
     * @test
     */
    public function validRemoveIngredientsTest(): void
    {
        $ingrId = $this->addDefaultIngredient();

        $this->removeIngredient($ingrId);
        $recipeData = $this->getIngredientsData($ingrId);
        $this->assertEquals("false", $recipeData);
    }

    /**
     * @test
     */
    public function addIngredientsToRecipe(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();
        $addIngredientData = array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId
        );
        $response = $this->simpleRequest($addIngredientData);

        $this->removeRecipe($recId);
        $this->removeIngredient($ingrId);

        $this->assertIsNumeric($response);
    }

    /**
     * @test
     */
    public function updateIngredientsToRecipe(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();

        $recipeIngredientId = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
            "i_amount" => "60 g"
        ));
        $amount = "100 g";
        $updateResponse = $this->simpleRequest(array(
            'request_name' => 'update_recipe_ingredient',
            'ri_id' => $recipeIngredientId,
            'ri_amount' => $amount
        ));
        $recipe = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredient',
            'ri_id' => $recipeIngredientId,
        ));
        $this->removeRecipe($recId);
        $this->removeIngredient($ingrId);

        $this->assertIsNumeric($recipeIngredientId);
        $this->assertEquals("OK", $updateResponse);
        $this->isValidJSON($recipe);

        $recipeData = json_decode($recipe);

        $this->assertTrue(isset($recipeData->amount));
        $this->assertEquals($amount ,$recipeData->amount);
    }

    /**
     * @test
     */
    public function removeIngredientFromRecipeTest(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();

        $recipeIngredientId = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $deleteResponse = $this->simpleRequest(array(
            'request_name' => 'remove_ingredient_from_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId
        ));

        $response = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredients',
            'id' => $recId
        ));

        $this->removeRecipe($recId);
        $this->removeIngredient($ingrId);

        $this->assertIsNumeric($recipeIngredientId);
        $this->assertEquals("OK", $deleteResponse);
        $this->assertEquals("[]", $response);
    }

    /**
     * @test
     */
    public function getCountOfIngredientRecipesTest(): void
    {
        $recId1 = $this->addDefaultRecipe();
        $recId2 = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();

        $recipe1IngredientId = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId1,
            'i_id' => $ingrId,
        ));
        $recipe2IngredientId = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId2,
            'i_id' => $ingrId,
        ));

        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_count_of_ingredient_recipes',
            'i_id' => $ingrId
        ));

        $response = json_decode($jsonResponse);

        $this->removeRecipe($recId1);
        $this->removeRecipe($recId2);
        $this->removeIngredient($ingrId);

        $this->assertTrue(isset($response->count));
        $this->assertEquals(2 ,$response->count);

    }

    /**
     * @test
     */
    public function getRecipeIngredientId(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();

        $recipeIngredientId1 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $recipeIngredientId2 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $recipeIngredientId3 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredients',
            'rec_id' => $recId
        ));
        $response = json_decode($jsonResponse);

        $this->removeRecipe($recId);
        $this->removeIngredient($ingrId);

        $this->assertCount(3, $response);
    }

    /**
    * @test
    */
    public function removeAllIngredientsFromRecipeTest(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();

        $recipeIngredientId1 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $recipeIngredientId2 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $recipeIngredientId3 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));
        $response = $this->simpleRequest(array(
            'request_name' => 'remove_all_ingredients_from_recipe',
            'rec_id' => $recId
        ));

        $response1 = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredient',
            'ri_id' => $recipeIngredientId1
        ));
        $response2 = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredient',
            'ri_id' => $recipeIngredientId2
        ));
        $response3 = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredient',
            'ri_id' => $recipeIngredientId3
        ));

        $this->removeRecipe($recId);
        $this->removeIngredient($ingrId);

        $this->assertEquals("OK" ,$response);
        $this->assertEquals("false", $response1);
        $this->assertEquals("false", $response2);
        $this->assertEquals("false", $response3);
    }

    /**
     * @test
     */
    public function getAllIngredientsTest(): void
    {
        $ingrId1 = $this->addDefaultIngredient();
        $customIngredientData = array(
            'request_name' => 'insert_ingredient',
            'ingr_name' => 'anotherIngredient'
        );
        $ingrId2 = $this->addIngredient($customIngredientData);

        $jsonResponse = $this->simpleRequest(array(
                'request_name' => 'get_all_ingredients'
        ));
        $response = json_decode($jsonResponse);

        $this->assertCount(2, $response);

        $this->assertTrue(isset($response[0]->name));
        $this->assertEquals(self::$defaultIngredient["ingr_name"], $response[0]->name);

        $this->assertTrue(isset($response[0]->id));
        $this->assertEquals($ingrId1, $response[0]->id);

        $this->assertTrue(isset($response[1]->name));
        $this->assertEquals($customIngredientData["ingr_name"], $response[1]->name);

        $this->assertTrue(isset($response[1]->id));
        $this->assertEquals($ingrId2, $response[1]->id);
    }

    /**
     * @test
     */
    public function deleteIngredientTest(): void
    {
        $ingrId = $this->addDefaultIngredient();
        $response = $this->simpleRequest(array('request_name' => 'remove_ingredient', "i_id"=>$ingrId));
        $this->assertEquals("OK", $response);

        $response = $this->simpleRequest(array('request_name' => 'get_ingredient', "i_id"=>$ingrId));
        $this->assertEquals("false", $response);
    }

    /**
     * @test
     */
    public function getIngredientsByName(): void
    {
        $this->fail("Not implemented, yet.");//TODO
    }

}