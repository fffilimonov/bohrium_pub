<?php
namespace Codeception\Module;
class ScreenShotModule extends \Codeception\Module  {

    private $redis = null;
    private $sid = null;

    public function _initialize() {
        $this->sid = getenv('SID');
        $this->redis = new \Redis();
        $this->redis->connect('redis-scens', 6379);
    }

    public function _afterStep(\Codeception\Step $step) {
        $name = $step->toString(64);
        if ($name !== "") {
            $imgData = $this->getModule('WebDriver')->webDriver->takeScreenshot();
            $base64 = 'data:image/png;base64,' . base64_encode($imgData);
            $data = array($name => $base64);
            $this->redis->append($this->sid, "," . json_encode($data));
        }
    }
}