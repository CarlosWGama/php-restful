<?php
/**
**************************************************
* Arquivo com acesso aos dados do usuario
* @author Carlos W. Gama
* @package Model
* @subpackage usuarios 
*******************/
require(dirname(__FILE__).'/Model.php');

class Usuario extends Model{
		

	/**
	* Realiza o login
	* @access public
	* @param $matricula int
	* @param $senha string
	* @return array
	*/
	public function login($matricula, $senha) {
		$dados = array();
		if ($this->DBSQLSERVER->hasOne("vw_usuario", "num_matricula=:matricula AND num_senha = :senha", array(":matricula" => $matricula, ":senha" => $senha)));
			$dados = $this->getDados($matricula);

		if (empty($dados)) return ;

		$this->criaToken($dados);
		return $dados;
	}

	/**
	* Busca dados do usuário
	* @access public
	* @param $matricula int
	* @return array
	*/
	public function getDados($matricula) {
		$dados = $this->DBSQLSERVER->selectOne("vw_usuario", "num_matricula=:matricula", array(":matricula" => $matricula));
		$dados['num_matricula'] = $this->formataMatricula($dados['num_matricula']);
		$dados['dsc_pessoa']	= $dados['num_pessoa'];
		unset($dados['num_pessoa']);
		return $dados;
	}
}