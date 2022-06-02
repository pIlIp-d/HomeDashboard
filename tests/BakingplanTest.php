<?php

include_once("CustomTestCase.php");

class BakingplanTest extends CustomTestCase
{
    protected static array $defaultBakingplan = array(
        "request_name" => "add_bakingplan",
        "bp_name" => "testBakingplan"
    );

    protected function addDefaultBakignplan()
    {
        return $this->simpleRequest(self::$defaultBakingplan);
    }

    protected function removeBakignplan($bpId)
    {
        return $this->simpleRequest(array(
            "request_name" => "delete_bakingplan",
            "bp_id" => $bpId
        ));
    }
//TODO fail if remove doesnt work
    protected function removeBakignplanRecipe($bprId)
    {
        return $this->simpleRequest(array(
            "request_name" => "remove_bakingplan_recipe",
            "bp_id" => $bprId
        ));
    }

    /**
     * @test
     */
    public function insertBakingplanTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $this->removeBakignplan($bpId);
        $this->assertIsNumeric($bpId);
    }

    /**
     * @test
     */
    public function renameBakingplanTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $newBakingplanData = array(
            "request_name" => "rename_bakingplan",
            "bp_id" => $bpId,
            "bp_name" => "new Name"
        );
        $response = $this->simpleRequest($newBakingplanData);
        $newBakingplanJson = $this->simpleRequest(array(
            "request_name" => "get_bakingplan",
            "bp_id" => $bpId
        ));
        $this->removeBakignplan($bpId);

        $this->assertEquals("OK", $response);

        $this->assertTrue($this->isValidJSON($newBakingplanJson));
        $newBakingplanResponse = json_decode($newBakingplanJson);
        $this->assertTrue(isset($newBakingplanResponse->name));
        $this->assertEquals($newBakingplanData["bp_name"], $newBakingplanResponse->name);
    }

    /**
     * @test
     */
    public function getBakingplanTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $newBakingplanJson = $this->simpleRequest(array(
            "request_name" => "get_bakingplan",
            "bp_id" => $bpId
        ));
        $this->removeBakignplan($bpId);
        $this->assertTrue($this->isValidJSON($newBakingplanJson));

        $newBakingplanData = json_decode($newBakingplanJson);

        $this->assertTrue(isset($newBakingplanData->id));
        $this->assertEquals($bpId, $newBakingplanData->id);

        $this->assertTrue(isset($newBakingplanData->name));
        $this->assertEquals(self::$defaultBakingplan["bp_name"], $newBakingplanData->name);
    }

    /**
     * @test
     */
    public function deleteBakingplanTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $this->removeBakignplan($bpId);
        $response = $this->simpleRequest(array(
            "request_name" => "get_bakingplan",
            "bp_id" => $bpId
        ));
        $this->assertEquals("false", $response);
    }

    /**
     * @test
     */
    public function activeBakingplanTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $response = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $activeRecipeJson = $this->simpleRequest(array(
            "request_name" => "get_active_bakingplan",
            "bp_id" => $bpId
        ));
        $this->removeBakignplan($bpId);

        $this->assertTrue($this->isValidJSON($activeRecipeJson));
        $activeRecipeData = json_decode($activeRecipeJson);

        $this->assertTrue(isset($activeRecipeData->id));
        $this->assertEquals($bpId, $activeRecipeData->id);

        $this->assertTrue(isset($activeRecipeData->name));
        $this->assertEquals(self::$defaultBakingplan["bp_name"], $activeRecipeData->name);
    }

    /**
     * @test
     */
    public function pasteBakingplanRecipeTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();

        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId = $this->simpleRequest($bpData);
        $recipesJson = $this->simpleRequest(array(
            "request_name" => "get_all_bakingplan_recipes"
        ));

        //TODO remove_bakingplan_recipe
        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);

        $this->assertEquals("OK", $response1);
        $this->assertTrue($this->isValidJSON($recipesJson));

        $recipes = json_decode($recipesJson);
        $this->assertCount(1, $recipes);
        $recipes = $recipes[0];

        //r_id
        $this->assertTrue(isset($recipes->r_id));
        $this->assertEquals($recId, $recipes->r_id);

        //r_name
        $this->assertTrue(isset($recipes->r_name));
        $this->assertEquals(self::$defaultRecipeData["rec_name"], $recipes->r_name);

        //r_bakingtime
        $this->assertTrue(isset($recipes->r_bakingtime));
        $this->assertEquals(self::$defaultRecipeData["rec_bakingtime"], $recipes->r_bakingtime);

        //r_bakingtemperature
        $this->assertTrue(isset($recipes->r_bakingtemperature));
        $this->assertEquals(self::$defaultRecipeData["rec_bakingtemperature"], $recipes->r_bakingtemperature);

        //bpr_orderno
        $this->assertTrue(isset($recipes->bpr_orderno));
        $this->assertEquals($bpData["order_no"], $recipes->bpr_orderno);

        //bpr_id
        $this->assertTrue(isset($recipes->bpr_id));
        $this->assertEquals($bprId, $recipes->bpr_id);

        //bp_name
        $this->assertTrue(isset($recipes->bp_name));
        $this->assertEquals(self::$defaultBakingplan["bp_name"], $recipes->bp_name);
    }


    /**
     * @test
     */
    public function bakingplanRemoveRecipeTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();

        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId = $this->simpleRequest($bpData);
        $response2 = $this->simpleRequest(array(
            "request_name" => "remove_bakingplan_recipe",
            "bpr_id"=>$bprId
        ));

        $recipeJson = $this->simpleRequest(array(
            "request_name" => "get_all_bakingplan_recipes",
            "bpr_id"=>$bprId
        ));

        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);
        $this->removeBakignplanRecipe($bprId);

        $this->assertEquals("OK", $response1);
        $this->assertEquals("OK", $response2);

        $this->assertEquals("[]",$recipeJson);
    }

    /**
     * @test
     */
    public function getAllBakingplansTest(): void
    {
        $bpId1 = $this->addDefaultBakignplan();
        $bpId2 = $this->addDefaultBakignplan();
        $response = $this->simpleRequest(array("request_name" => "get_all_bakingplans"));

        $this->removeBakignplan($bpId1);
        $this->removeBakignplan($bpId2);

        $this->assertTrue($this->isValidJSON($response));

        $recipes = json_decode($response);
        $this->assertCount(2, $recipes);

        $this->assertTrue(isset($recipes[0]->id));
        $this->assertEquals($bpId1, $recipes[0]->id);

        $this->assertTrue(isset($recipes[1]->id));
        $this->assertEquals($bpId2, $recipes[1]->id);
    }
    /**
     * @test
     */
    public function getAllBakingplanRecipesTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId1 = $this->simpleRequest($bpData);
        $bprId2 = $this->simpleRequest($bpData);
        $recipesJson = $this->simpleRequest(array(
            "request_name" => "get_all_bakingplan_recipes"
        ));

        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);
        $this->removeBakignplanRecipe($bprId1);
        $this->removeBakignplanRecipe($bprId2);

        $this->assertEquals("OK", $response1);
        $this->assertTrue($this->isValidJSON($recipesJson));

        $recipes = json_decode($recipesJson);

        /**values are tested inside @link{$this->pasteBakingplanRecipeTest} */
        $this->assertCount(2, $recipes);
    }

    /**
     * @test
     */
    public function bakingplanGetRecipeIdTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId = $this->simpleRequest($bpData);
        $response2 = $this->simpleRequest(array(
            "request_name" => "get_bakingplan_rec_id",
            "bpr_id" => $bprId
        ));

        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);
        $this->removeBakignplanRecipe($bprId);

        $this->assertTrue($this->isValidJSON($response2));

        $responseId = json_decode($response2);
        $this->assertCount(1, $responseId);
        $this->assertEquals($recId, $responseId[0]->r_id);
    }

    /**
     * @test
     */
    public function getBakingplanRecipeOrderNoTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId = $this->simpleRequest($bpData);
        $response2 = $this->simpleRequest(array(
            "request_name" => "get_bakingplan_recipe_order_no",
            "bpr_id" => $bprId
        ));

        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);
        $this->removeBakignplanRecipe($bprId);

        $this->assertTrue($this->isValidJSON($response2));

        $responseId = json_decode($response2);
        $this->assertEquals($bpData["order_no"], $responseId->order_no);
    }

    /**
     * @test
     */
    public function bakingplanGetAllRecipesTest(): void
    {
        $bpId1 = $this->addDefaultBakignplan();
        $bpId2 = $this->addDefaultBakignplan();

        $bakingplansJson = $this->simpleRequest(array("request_name" => "get_all_bakingplans"));

        $this->assertTrue($this->isValidJSON($bakingplansJson));

        $bakingplans = json_decode($bakingplansJson);
        $this->assertCount(2, $bakingplans);

        $this->assertTrue(isset($bakingplans[0]->id));
        $this->assertEquals($bpId1, $bakingplans[0]->id);

        $this->assertTrue(isset($bakingplans[1]->id));
        $this->assertEquals($bpId2, $bakingplans[1]->id);
    }

    /**
     * @test
     */
    public function bakingplanSetOrderNoTest(): void
    {
        $bpId = $this->addDefaultBakignplan();
        $recId = $this->addDefaultRecipe();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_active_bakingplan",
            "bp_id" => $bpId
        ));
        $bpData = array(
            "request_name" => "paste_bakingplan_rec",
            "bp_id" => $bpId,
            "rec_id" => $recId,
            "order_no" => "10"
        );
        $bprId = $this->simpleRequest($bpData);
        $setResponse = $this->simpleRequest(array(
            "request_name" => "bakingplan_set_order_no",
            "bpr_id" => $bprId,
            "order_no"=>"20"
        ));

        $this->removeBakignplan($bpId);
        $this->removeRecipe($recId);
        $this->removeBakignplanRecipe($bprId);

        $response3 = $this->simpleRequest(array(
            "request_name" => "get_bakingplan_recipe_order_no",
            "bpr_id" => $bprId
        ));

        $this->assertEquals("OK", $response1);
        $this->assertEquals("OK", $setResponse);

        $this->assertTrue($this->isValidJSON($response3));

        $responseId = json_decode($response3);
        $this->assertEquals(20, $responseId->order_no);
    }

}