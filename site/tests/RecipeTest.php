<?php

//TODO variations of test data and parameters

include_once("CustomTestCase.php");

class RecipeTest extends CustomTestCase
{

    /**
     * @test
     */
    public function validAddRecipeTest(): void
    {
        $recipeId = $this->addDefaultRecipe();
        $this->assertIsNumeric($recipeId);
        $this->removeRecipe($recipeId);
    }

    /**
     * @test
     */
    public function validRemoveRecipeTest(): void
    {
        $recId = $this->addDefaultRecipe();

        $this->removeRecipe($recId);
        $recipeData = $this->getRecipeData($recId);
        $this->assertEquals("false", $recipeData);
    }

    /**
     * @test
     */
    public function validGetRecipeDataTest(): void
    {
        $recId = $this->addDefaultRecipe();

        $recipeData = $this->getRecipeData($recId);

        $this->removeRecipe($recId);

        $this->assertTrue($this->isValidJSON($recipeData));

        $recipeData = json_decode($recipeData);

        $this->assertTrue(isset($recipeData->id));
        $this->assertEquals($recId, $recipeData->id);

        $this->assertTrue(isset($recipeData->name));
        $this->assertEquals(self::$defaultRecipeData["rec_name"], $recipeData->name);

        $this->assertTrue(isset($recipeData->bakingtime));
        $this->assertEquals(self::$defaultRecipeData["rec_bakingtime"], $recipeData->bakingtime);

        $this->assertTrue(isset($recipeData->bakingtemperature));
        $this->assertEquals(self::$defaultRecipeData["rec_bakingtemperature"], $recipeData->bakingtemperature);

        $this->assertTrue(isset($recipeData->preparation));
        $this->assertEquals(self::$defaultRecipeData["rec_preparation"], base64_decode($recipeData->preparation));
    }

    /**
     * @test
     */
    public function validUpdateRecipeDataTest(): void
    {
        $recId = $this->addDefaultRecipe();

        $updatedRecipeData = array(
            'request_name' => 'update_recipe',
            'rec_id' => $recId,
            'rec_name' => 'newRecipeName',
            'rec_bakingtime' => '150',
            'rec_bakingtemperature' => '200',
            'rec_preparation' => 'This is the new Preparation Text'
        );
        $response = $this->simpleRequest($updatedRecipeData);

        $recipeData = $this->getRecipeData($recId);
        $this->removeRecipe($recId);

        $this->assertEquals("OK", $response);

        $this->assertTrue($this->isValidJSON($recipeData));

        $recipeData = json_decode($recipeData);
        $this->assertTrue(isset($recipeData->id));
        $this->assertEquals($updatedRecipeData["rec_id"], $recipeData->id);

        $this->assertTrue(isset($recipeData->name));
        $this->assertEquals($updatedRecipeData["rec_name"], $recipeData->name);

        $this->assertTrue(isset($recipeData->bakingtime));
        $this->assertEquals($updatedRecipeData["rec_bakingtime"], $recipeData->bakingtime);

        $this->assertTrue(isset($recipeData->bakingtemperature));
        $this->assertEquals($updatedRecipeData["rec_bakingtemperature"], $recipeData->bakingtemperature);

        $this->assertTrue(isset($recipeData->preparation));
        $this->assertEquals($updatedRecipeData["rec_preparation"], base64_decode($recipeData->preparation));
    }

    /**
     * @test
     */
    public function validGetAllRecipesTest(): void
    {
        $recId1 = $this->addDefaultRecipe();

        $recipeData2 = array(
            'request_name' => 'add_recipe',
            'rec_name' => 'newRecipeName',
            'rec_bakingtime' => '150',
            'rec_bakingtemperature' => '200',
            'rec_preparation' => 'This is the new Preparation Text'
        );
        $recId2 = $this->addRecipe($recipeData2);

        $response = $this->getResponse("json={\"request_name\":\"get_all_recipes\"}");

        $this->assertTrue($this->isValidJSON($response));

        $responseData = json_decode($response);

        $this->removeRecipe($recId1);
        $this->removeRecipe($recId2);

        $this->assertCount(2, $responseData);

        $this->assertTrue(isset($responseData[0]->id));
        $this->assertEquals($recId1, $responseData[0]->id);
        $this->assertTrue(isset($responseData[1]->id));
        $this->assertEquals($recId2, $responseData[1]->id);

        $this->assertTrue(isset($responseData[0]->name));
        $this->assertEquals(self::$defaultRecipeData["rec_name"], $responseData[0]->name);
        $this->assertTrue(isset($responseData[1]->name));
        $this->assertEquals($recipeData2["rec_name"], $responseData[1]->name);
    }

    /**
     * @test
     */
    public function setActiveRecipeTest(): void
    {
        $recId = $this->addDefaultRecipe();
        $response = $this->getResponse("json="
            .json_encode(
                array(
                    "request_name"=>"set_active_recipe",
                    "recipe_id"=> $recId
                )
            )
        );
        $actRecipeId = $this->getResponse("json={\"request_name\":\"get_active_recipe\"}");
        $this->removeRecipe($recId);

        $this->assertEquals("OK", $response);

        $actRecipeId = json_decode($actRecipeId);

        $this->assertTrue(isset($actRecipeId->id));
        $this->assertEquals($recId, $actRecipeId->id);
    }

}