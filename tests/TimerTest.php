<?php
include_once("CustomTestCase.php");

class TimerTest extends CustomTestCase
{

    private int $presetId = 0;
    protected function removeTimer($timerId)
    {
        return $this->simpleRequest(array(
            "request_name" => "remove_timer",
            "timer_id" => $timerId,
            "preset_id" => $this->presetId
        ));
    }

    /**
     * @test
     */
    public function setTimerTest()
    {
        $resp1 = $this->simpleRequest(array(
            "request_name" => "set_timer",
            "timer_id" => 0,
            "preset_id" => $this->presetId,
            "time" => time()
        ));
        $resp2 = $this->simpleRequest(array(
            "request_name" => "set_timer",
            "timer_id" => 1,
            "preset_id" => $this->presetId,
            "time" => time()
        ));
        $this->removeTimer(0);
        $this->assertEquals("OK", $resp1);
        $this->assertEquals("OK", $resp2);
    }

    /**
     * @test
     */
    public function getTimerTest()
    {
        $currentTime = time();
        $this->simpleRequest(array(
            "request_name" => "set_timer",
            "timer_id" => 0,
            "preset_id" => $this->presetId,
            "time" => $currentTime
        ));
        $response = $this->simpleRequest(array(
            "request_name" => "get_timer",
            "timer_id" => 0,
            "preset_id" => $this->presetId
        ));
        $this->removeTimer(0);
        $this->assertEquals($currentTime, $response);
    }

    /**
     * @test
     */
    public function removeTimerTest()
    {
        $this->simpleRequest(array(
            "request_name" => "set_timer",
            "timer_id" => 0,
            "preset_id" => $this->presetId,
            "time" => time()
        ));
        $this->assertEquals("OK", $this->removeTimer(0));
    }
}