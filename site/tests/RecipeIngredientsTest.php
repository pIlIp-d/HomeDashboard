<?php

include_once("CustomTestCase.php");

class RecipeIngredientsTest extends CustomTestCase
{

    /**
     * @test
     */
    public function addIngredientsToRecipeTest(): void
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
    public function updateIngredientsToRecipeTest(): void
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
    public function getRecipeIngredientTest(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId = $this->addDefaultIngredient();
        $recipeIngredientId = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId,
        ));

        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredient',
            'ri_id' => $recipeIngredientId
        ));
        $response = json_decode($jsonResponse);

        $this->removeIngredient($ingrId);
        $this->removeRecipe($recId);
        $this->assertTrue($this->isValidJSON($jsonResponse));

        $this->assertTrue(isset($response->id));
        $this->assertEquals($recipeIngredientId, $response->id);
        $this->assertTrue(isset($response->recipes_id));
        $this->assertEquals($recId, $response->recipes_id);
        $this->assertTrue(isset($response->ingredients_id));
        $this->assertEquals($ingrId, $response->ingredients_id);
    }

    /**
     * @test
     */
    public function getRecipeIngredientsTest(): void
    {
        $recId = $this->addDefaultRecipe();
        $ingrId1 = $this->addDefaultIngredient();
        $ingrId2 = $this->addDefaultIngredient();
        $recipeIngredientId1 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId1,
        ));
        $recipeIngredientId2 = $this->simpleRequest(array(
            'request_name' => 'add_ingredient_to_recipe',
            'rec_id' => $recId,
            'i_id' => $ingrId2,
        ));

        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_recipe_ingredients',
            'rec_id' => $recId,
        ));
        $response = json_decode($jsonResponse);

        $this->removeIngredient($ingrId1);
        $this->removeIngredient($ingrId2);
        $this->removeRecipe($recId);
        $this->assertTrue($this->isValidJSON($jsonResponse));

        $this->assertCount(2, $response);

        $this->assertTrue(isset($response[0]->name));
        $this->assertEquals(self::$defaultIngredient["ingr_name"], $response[0]->name);
        $this->assertTrue(isset($response[1]->name));
        $this->assertEquals(self::$defaultIngredient["ingr_name"], $response[1]->name);

        $this->assertTrue(isset($response[0]->ri_id));
        $this->assertEquals($recipeIngredientId1, $response[0]->ri_id);
        $this->assertTrue(isset($response[1]->ri_id));
        $this->assertEquals($recipeIngredientId2, $response[1]->ri_id);

        $this->assertTrue(isset($response[0]->i_id));
        $this->assertEquals($ingrId1, $response[0]->i_id);
        $this->assertTrue(isset($response[1]->i_id));
        $this->assertEquals($ingrId2, $response[1]->i_id);

        $this->assertTrue(isset($response[0]->amount));
        $this->assertEquals("", $response[0]->amount);
        $this->assertTrue(isset($response[1]->amount));
        $this->assertEquals("", $response[1]->amount);
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


}