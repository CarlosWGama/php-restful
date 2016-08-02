<?php
require_once(dirname(__FILE__).'/../../lib/tests/Modules.php');

class UsuarioTest extends Modules {
    
    /**
    * Define a url base do modulo
    * @access protected
    * @var string
    */
    protected $urlBase = "/usuario";

    /**
    * Matricula do usuario usada no teste
    * @access private
    * @var int
    */
    private $matricula = 99999999;

    /**
    * Senha do usuario usada no teste
    * @access private
    * @var int
    */
    private $senha = 123456;

    /**
    * Verifica se realiza o login com sucesso e falha
    */
    public function testLogin() {
        $dados = array('matricula' => $this->matricula, 'senha' => $this->senha);
        $dadosF = array('matricula' => $this->matricula, 'senha' => '12333333');
    	$sucesso = $this->curl->post($this->urlBase.'/login', $dados);
    	$falha = $this->curl->post($this->urlBase.'/login', $dadosF);
    	
    	$this->assertEquals(200, $sucesso['http_code']);
    	$this->assertFalse(empty($sucesso['response']));
    	$this->assertEquals(403, $falha['http_code']);
    }

    /**
    * Verifica se retorna algo no token
    */
    public function testGetToken() {
       $this->assertFalse(empty($this->getToken())); 
    }

    /**
    * Verifica se busca os dados do usuário
    */
    public function testDadosUsuario() {
        $token = $this->getToken();

        $dados = $this->curl->get($this->urlBase.'/dados/'.$token);
        $dados['response'] = json_decode($dados['response'], true);

        //Login ok
        $this->assertEquals(200, $dados['http_code']);

        //Dados correto
        $this->assertEquals($this->matricula, $dados['response']['num_matricula']);
    }

    /**
    * Busca um Token de acesso
    * @access private
    * @param $matricula string
    * @param $senha string
    * @return token string
    */
    private function getToken($matricula = "", $senha = "") {
        $dados['matricula'] = (empty($matricula) ? $this->matricula : $matricula);
        $dados['senha'] = (empty($senha) ? $this->senha : $senha);
        
        $login = $this->curl->post($this->urlBase.'/login', $dados);
        $dados = json_decode($login['response'], true);
        
        if (isset($dados['token']))
            return $dados['token'];

    }

}
?>