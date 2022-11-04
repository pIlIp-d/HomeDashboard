<?php
include_once("CustomTestCase.php");

class IngredientTest extends CustomTestCase
{
    /**
     * @test
     */
    public function addValidIngredientTest(): void
    {
        $ingrId = $this->addIngredient(self::$defaultIngredient);
        $this->assertIsNumeric($ingrId);

        $this->removeIngredient($ingrId);
    }

    /**
     * @test
     */
    public function validRemoveIngredientTest(): void
    {
        $ingrId = $this->addDefaultIngredient();

        $this->removeIngredient($ingrId);
        $recipeData = $this->getIngredientsData($ingrId);
        $this->assertEquals("false", $recipeData);
    }

    /**
     * @test
     */
    public function getAllIngredientsTest(): void
    {
        $ingrId1 = $this->addDefaultIngredient();
        $customIngredientData = array(
            'request_name' => 'add_ingredient',
            'ingr_name' => 'anotherIngredient'
        );
        $ingrId2 = $this->addIngredient($customIngredientData);

        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_all_ingredients'
        ));
        $response = json_decode($jsonResponse);
        $this->removeIngredient($ingrId1);
        $this->removeIngredient($ingrId2);

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
    public function getIngredientTest(): void
    {
        $ingrId1 = $this->addDefaultIngredient();

        $jsonResponse = $this->simpleRequest(array(
            'request_name' => 'get_ingredient',
            'i_id' => $ingrId1
        ));
        $response = json_decode($jsonResponse);

        $this->removeIngredient($ingrId1);
        $this->assertTrue($this->isValidJSON($jsonResponse));

        $this->assertTrue(isset($response->name));
        $this->assertEquals(self::$defaultIngredient["ingr_name"], $response->name);

    }

}