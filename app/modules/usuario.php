<?php
/**
**************************************************
* Arquivo com as configurações usadas no webservice
* @package modules
* @subpackage usuarios 
*******************/

/**
* Definição do modulo
*/
$app->group('/usuario', function () use ($app) {


   /**
   * Model de usuario
   * @var Usuario
   */
   require_once(DIR_MODEL.'Usuario.php');
   $usuario = new Usuario();


	/**
	* Condição para usar Semestre e Ano
	* @var array
	*/
	$ANO_SEMESTRE = array('ano' => '(19|20)[0-9][0-9]', 'semestre' => '(1|2)');

	/**
	**********************************************
	* Realiza o Login do Usuario
	* @uses localhost/webservice/usuario/login
	**********************************************
	*/
	$app->post('/login', function () use ($app, $usuario) {
		$matricula 	= $app->request->post('matricula');
		$senha 		= $app->request->post('senha');
		//Faz o login
      	$dados = $usuario->login($matricula, $senha);
		if (empty($dados))
			$app->halt(403, "Matricula ou Senha incorreta");	//Acesso negado
		
		//Envia os dados
		echo json_encode($dados);
	});

	/**
	**********************************************
	* Realiza o Logout do usuario
	* @uses localhost/webservice/usuario/logout/5af269fbc41eec93964e39511fc33062
	**********************************************
	*/
	$app->get('/logout/:token', function ($token) use ($app, $usuario) {
		//Realiza o logout
      	$usuario->logout($token);
		//Envia os dados
		echo json_encode(array("success" => true));
	});

	/**
	**********************************************
	* Busca informação do usuario
	* @uses localhost/webservice/usuario/dados/b2t0Lo~BPJeY1pxCrhY4MFEz5.gK7xmB49lVFy3doIU-
	**********************************************
	*/
	$app->get('/dados/:token', function ($token) use ($usuario) {
		$matricula = $usuario->getMatriculaByToken($token);
      	$dados = $usuario->getDados($matricula);
   		echo json_encode($dados);
	});

   	/**
   	*********************************************
   	* Busca informação com filtro por ano e mes
   	* @uses localhost/webservice/usuario/informacao/2015/1/c23afd199de59f667ef51b552ce12cfd
   	********************************************
   	*/
   	$app->get('/informacao/:ano/:semestre/:token', function ($ano, $semestre, $token) use ($usuario) {
		

	})->conditions($ANO_SEMESTRE);

});




