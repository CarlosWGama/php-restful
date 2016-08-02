<?php
/**********************
* Arquivo com as configurações usadas no webservice
* @package modules
* @subpackage geral 
*******************/

/**
* Definição do modulo qualquer
*/

/**
******************************
* Usando get
* @uses http://localhost/webservice/informacao/10/15
******************************
*/
$app->get('/informacao/:dado1/:dado2', function($dado1, $dado2) use ($app) {
	echo json_encode(array($dado1, $dado2));
});


/**
******************************
* Usando post
* @uses http://localhost/webservice/informacao
******************************
*/
$app->post('/informacao/:dado3', function($dado3) use ($app) {
	$dado1 = $app->request->post('dado1');
	$dado2 = $app->request->post('dado2');
	echo json_encode(array($dado1, $dado2, $dado3));
});

/**
******************************
* Usando put
* @uses http://localhost/webservice/informacao
******************************
*/
$app->put('/informacao', function() use ($app) {
	$dado1 = $app->request->post('dado1');
	$dado2 = $app->request->post('dado2');
	echo json_encode(array($dado1, $dado2));
});

/**
******************************
* Usando delete
* @uses http://localhost/webservice/informacao
******************************
*/
$app->delete('/informacao', function() use ($app) {
	$dado1 = $app->request->post('dado1');
	$dado2 = $app->request->post('dado2');
	echo json_encode(array($dado1, $dado2));
});


