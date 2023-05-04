<?php
include_once("CustomTestCase.php");

class DeviceTest extends CustomTestCase
{
    protected static array $defaultDevice = array(
        "request_name" => "add_device",
        "device_name" => "testDevice"
    );

    protected function addDefaultDevice()
    {
        return $this->simpleRequest(self::$defaultDevice);
    }

    protected function removeDevice($deviceId)
    {
        return $this->simpleRequest(array(
            "request_name" => "remove_device",
            "device_id" => $deviceId
        ));
    }

    /**
     * @test
     */
    public function addDeviceTest()
    {
        $deviceId = $this->addDefaultDevice();
        $this->assertIsNumeric($deviceId);
        $this->removeDevice($deviceId);
        echo $deviceId;
    }

    /**
     * @test
     */
    public function removeDeviceTest()
    {
        $deviceId = $this->addDefaultDevice();
        $this->assertEquals("OK", $this->removeDevice($deviceId));
    }

    /**
     * @test
     */
    public function setActTempsTest()
    {
        $deviceId = $this->addDefaultDevice();
        $extraDevice = array(
            "request_name" => "add_device",
            "device_name" => "secondDevice"
        );
        $deviceId2 = $this->simpleRequest($extraDevice);
        $jsonResponse = $this->simpleRequest(array(
            "request_name" => "set_act_temps",
            "act_temps" => array(
                self::$defaultDevice["device_name"] => "200",
                $extraDevice["device_name"] => "170"
            )
        ));
        $response1 = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId
        )));
        $response2 = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId2
        )));
        $this->removeDevice($deviceId);
        $this->removeDevice($deviceId2);
        $this->assertEquals("OK", $jsonResponse);
        $this->assertEquals("200", $response1->temp_act);
        $this->assertEquals("170", $response2->temp_act);
    }

    /**
     * @test
     */
    public function setActTempByDeviceIdTest()
    {
        $deviceId = $this->addDefaultDevice();
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "set_act_temp",
                "temp_act" => "220",
                "device_id" => $deviceId
            )
        );
        $response = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $jsonResponse);
        $this->assertEquals("220", $response->temp_act);
    }

    /**
     * @test
     */
    public function setActTempByDeviceNameTest()
    {
        $deviceId = $this->addDefaultDevice();
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "set_act_temp",
                "temp_act" => "180",
                "device_name" => self::$defaultDevice["device_name"]
            )
        );
        $response = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $jsonResponse);
        $this->assertEquals("180", $response->temp_act);
    }

    /**
     * @test
     */
    public function getAllDevicesTest()
    {
        $deviceId = $this->addDefaultDevice();
        $extraDevice = array(
            "request_name" => "add_device",
            "device_name" => "secondDevice"
        );
        $deviceId2 = $this->simpleRequest($extraDevice);
        $jsonResponse = $this->simpleRequest(array("request_name" => "get_all_devices"));

        $this->removeDevice($deviceId);
        $this->removeDevice($deviceId2);
        $this->assertTrue($this->isValidJSON($jsonResponse));
        $response = json_decode($jsonResponse);
        $this->assertCount(2,$response);
        $this->assertEquals($deviceId, $response[0]->id);
        $this->assertEquals($deviceId2, $response[1]->id);
        $this->assertEquals(self::$defaultDevice["device_name"], $response[0]->name);
        $this->assertEquals($extraDevice["device_name"], $response[1]->name);
    }

    /**
     * @test
     */
    public function setMinMaxValuesByIdTest()
    {
        $deviceId = $this->addDefaultDevice();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_minmaxvalues",
            "device_id" => $deviceId,
            "temp_min"=>"20",
            "temp_max"=>"800"
        ));
        $response2 = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_name" => self::$defaultDevice["device_name"]
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $response1);
        $this->assertEquals("20", $response2->temp_min);
        $this->assertEquals("800", $response2->temp_max);
    }

    /**
     * @test
     */
    public function setMinMaxValuesByNameTest()
    {
        $deviceId = $this->addDefaultDevice();
        $response1 = $this->simpleRequest(array(
            "request_name" => "set_minmaxvalues",
            "device_id" => $deviceId,
            "temp_min"=>"20",
            "temp_max"=>"800"
        ));
        $response2 = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $response1);
        $this->assertEquals("20", $response2->temp_min);
        $this->assertEquals("800", $response2->temp_max);
    }

    /**
     * @test
     */
    public function getDeviceValuesByIdTest()
    {
        $deviceId = $this->addDefaultDevice();
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "set_act_temp",
                "temp_act" => "180",
                "device_name" => self::$defaultDevice["device_name"]
            )
        );
        $this->simpleRequest(array(
            "request_name" => "set_minmaxvalues",
            "device_id" => $deviceId,
            "temp_min"=>"10",
            "temp_max"=>"1000"
        ));
        $response = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_id" => $deviceId
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $jsonResponse);
        $this->assertEquals("180", $response->temp_act);
        $this->assertEquals("10", $response->temp_min);
        $this->assertEquals("1000", $response->temp_max);
    }

    /**
     * @test
     */
    public function getDeviceValuesByNameTest()
    {
        $deviceId = $this->addDefaultDevice();
        $jsonResponse = $this->simpleRequest(
            array(
                "request_name" => "set_act_temp",
                "temp_act" => "180",
                "device_id" => $deviceId
            )
        );
        $this->simpleRequest(array(
            "request_name" => "set_minmaxvalues",
            "device_id" => $deviceId,
            "temp_min"=>"10",
            "temp_max"=>"1000"
        ));
        $response = json_decode($this->simpleRequest(array(
            "request_name" => "get_device_values",
            "device_name" => self::$defaultDevice["device_name"]
        )));
        $this->removeDevice($deviceId);
        $this->assertEquals("OK", $jsonResponse);
        $this->assertEquals("180", $response->temp_act);
        $this->assertEquals("10", $response->temp_min);
        $this->assertEquals("1000", $response->temp_max);
    }
}