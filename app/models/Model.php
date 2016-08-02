<?php
require_once(DIR_LIB.'db/DB.php');
/**
**************************************************
* Arquivo com acesso aos dados do aluno
* @author Carlos W. Gama
* @package Model
*******************/

/**
* Classe global para todos os models
* @abstract 
*/
abstract class Model {
	
	/** 
	* Variavel para acessar o banco SQL SERVER
	* @access protected
	* @var DB
	*/
	protected $DBSQLSERVER = null;

	/** 
	* Variavel para acessar o banco MYSQL
	* @access protected
	* @var DB
	*/
	protected $DBMYSQL = null;

	/**
	* Tempo de duração do token ativo
	* @access protected
	* @var int
	*/
	protected $duracaoToken = 20; //20 minutos

	private $appSlim;

	public function __construct() {

		$this->appSlim = \Slim\Slim::getInstance();
		try {
			//**** DB *****//
			require(CONFIG_DB);
			//SQL SERVER
			$dsn = DB::getDSN($DB_SQLSERVER_CONFIG['typeDB'], $DB_SQLSERVER_CONFIG);
			$this->DBSQLSERVER = new DB($dsn, $DB_SQLSERVER_CONFIG['user'], $DB_SQLSERVER_CONFIG['password']);

			//MYSQL
			$dsn = DB::getDSN($DB_MYSQL_CONFIG['typeDB'], $DB_MYSQL_CONFIG);
			$this->DBMYSQL = new DB($dsn, $DB_MYSQL_CONFIG['user'], $DB_MYSQL_CONFIG['password']);
		} catch (Exception $e) {
			echo $e->getMessage();
			die;
		}
	}

	/**
	* Cria o login de acesso aos usuários
	* @access protected
	* @param $dados array
	* @return $dados array com token
	*/
	protected function criaToken(&$dados) {
		//Exemplo de classe que salva o token no banco de dados

		//Deleta tokens antigos
		$where = "matricula = :matricula";
		$bind = array(":matricula" => $dados['num_matricula']);
		$this->DBMYSQL->delete('login_ws', $where, $bind);

		//Cria novo token
		$dados['token'] = md5($dados['num_matricula'] . date('Y-m-d H:i:s'));

		$this->DBMYSQL->insert('login_ws', array(
			'token'			=> $dados['token'],
			'matricula'		=> $dados['num_matricula'],
			'ip'			=> $this->appSlim->request->getIP(),
			'expira'		=> $this->getExpira()
		));

		return $dados;
	}

	/**
	* Busca a matricula e atualiza o token
	* @access protected
	* @param $token string
	* @return int (matricula)
	*/
	public function getMatriculaByToken($token) {
		//Busca o ID pelo token

		$where = 'ip = :ip AND token = :token';
		$bind = array(':ip' => $this->appSlim->request->getIP(), ':token' => $token);
		$login = $this->DBWS->selectOne('login_ws', $where, $bind, 'matricula, expira');
		
		if (!empty($login)) {

			if ($login['expira'] < date('Y-m-d H:i:s')) {
				$this->appSlim->halt(403, "Acesso expirado"); //Acesso negado				
			} else {
				$this->DBWS->update('login_ws', array('expira' => $this->getExpira()), 'token = :token', array(':token' => $token));
				return $login['matricula'];	
			}
		}

		$this->appSlim->halt(403, "Acesso negado"); //Acesso negado
	}

	/**
	* Retorna o novo tempo que o token irá expirar
	* @access private
	* @return datetime
	*/
	private function getExpira() {
		return date('Y-m-d H:i:s', mktime(date('H'), date('i')+$this->duracaoToken, date('s'), date('m'), date('d'), date('Y')));
	}

	/**
	* Deleta o token
	* @access public
	*/
	public function logout($token) {
		$where = "token = :token AND ip = :ip";
		$bind = array(":token" => $token, ':ip' => $this->appSlim->request->getIP());
		$this->DBWS->delete('login_ws', $where, $bind);
	}
}