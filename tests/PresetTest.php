<?php
include_once("CustomTestCase.php");

class PresetTest extends CustomTestCase
{
    protected static array $defaultPreset = array(
        "request_name" => "add_preset",
        "preset_name" => "testPreset",
        "grid_object_v" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]',
        "grid_object_h" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]'
    );

    protected function addDefaultPreset()
    {
        return $this->simpleRequest(self::$defaultPreset);
    }

    protected function removePreset($presetId)
    {
        return $this->simpleRequest(array(
            "request_name" => "remove_preset",
            "preset_id" => $presetId
        ));
    }

    /**
     * @test
     */
    public function setPresetTest()
    {
        $resp = $this->addDefaultPreset();
        $this->removePreset($resp);
        $this->assertIsNumeric($resp);
    }

    /**
     * @test
     */
    public function getAllPresetIdsTest()
    {
        $presetId = $this->addDefaultPreset();
        $presetId2 = $this->simpleRequest(array(
            "request_name" => "add_preset",
            "preset_name" => "secondPreset",
            "grid_object_v" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]',
            "grid_object_h" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]'
        ));
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "get_preset_ids"
        ));
        $this->removePreset($presetId);
        $this->removePreset($presetId2);
        $this->assertTrue($this->isValidJSON($jsonResponse));
        $response = json_decode($jsonResponse);
        $this->assertCount(2, $response);
    }


    /**
     * @test
     */
    public function getAllPresetsTest()
    {
        $presetId = $this->addDefaultPreset();
        $presetId2 = $this->simpleRequest(array(
            "request_name" => "add_preset",
            "preset_name" => "secondPreset",
            "grid_object_v" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}, {"type":"timer","size":"11","pos":1,"start":1,"stop":1,"display":0}]',
            "grid_object_h" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]'
        ));
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "get_all_presets"
            ));
        $this->removePreset($presetId);
        $this->removePreset($presetId2);
        $this->assertTrue($this->isValidJSON($jsonResponse));
        $response = json_decode($jsonResponse);
        $this->assertCount(2, $response);
        $this->assertEquals(self::$defaultPreset["preset_name"], $response[0]->name);
        $this->assertEquals(self::$defaultPreset["grid_object_v"], $response[0]->grid_object_v);
        $this->assertEquals(self::$defaultPreset["grid_object_h"], $response[0]->grid_object_h);
    }

    /**
     * @test
     */
    public function getPresetTest(){
        $presetId = $this->addDefaultPreset();
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "get_preset",
                "preset_id" => $presetId
            ));
        $this->removePreset($presetId);
        $this->assertTrue($this->isValidJSON($jsonResponse));
        $response = json_decode($jsonResponse);
        $this->assertEquals($presetId, $response->id);
        $this->assertEquals(self::$defaultPreset["preset_name"], $response->name);
        $this->assertEquals(self::$defaultPreset["grid_object_v"], $response->grid_object_v);
        $this->assertEquals(self::$defaultPreset["grid_object_h"], $response->grid_object_h);
    }

    /**
     * @test
     */
    public function savePresetTest(){
        $presetId = $this->addDefaultPreset();
        $presetData =  array(
            "request_name" => "save_preset",
            "preset_id" => $presetId,
            "preset_name" => "secondPreset",
            "grid_object_v" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0},{"type":"timer","size":"11","pos":1,"start":1,"stop":1,"display":0}]',
            "grid_object_h" => '[{"type":"settings","size":"11","pos":0,"start":0,"stop":0,"display":0}]'
        );
        $saveResponse = $this->simpleRequest($presetData);
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "get_preset",
                "preset_id" => $presetId
            ));
        $this->removePreset($presetId);
        $this->assertEquals("OK", $saveResponse);
        $this->assertTrue($this->isValidJSON($jsonResponse));
        $response = json_decode($jsonResponse);
        $this->assertEquals($presetId, $response->id);
        $this->assertEquals($presetData["preset_name"], $response->name);
        $this->assertEquals($presetData["grid_object_v"], json_encode($response->grid_object_v));
        $this->assertEquals($presetData["grid_object_h"], json_encode($response->grid_object_h));
    }

}