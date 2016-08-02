<?php
/** 
* Webservice com Restful usando Slim
* @author Carlos W. Gama
* @version 1.0
***/

/*********** INIT **************/
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNING));
require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__).'/app/config/config.php');
require_once(dirname(__FILE__).'/app/config/constants.php');

/*********** CONFIG - SLIM **************/
$app = new \Slim\Slim();	//Classe do Framework Slim para tratar as requisições

if (!$WS_CONFIG['is_json'])
	$app->response->header('Content-Type', 'application/json;charset=utf-8');


/*********** WEBSERVICES **************/
$files = array_diff(scandir(DIR_MODULE), array('..', '.'));
foreach ($files as $file) 
	include_once(DIR_MODULE . $file);

$app->run();

