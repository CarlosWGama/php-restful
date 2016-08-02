<?php
require_once(dirname(__FILE__).'/Curl.php');

class Modules extends PHPUnit_Framework_TestCase {
	
	protected $curl;
    protected $urlBase = "/modulo";

    public function __construct() {
        parent::__construct();
        $this->curl = new Curl();

        require(dirname(__FILE__).'/../../app/config/config.php');

        if (isset($WS_CONFIG['url']))
            $this->urlBase = $WS_CONFIG['url'] . $this->urlBase;
    }
}