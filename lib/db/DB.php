<?php
/**
* @author imavex
* @link http://www.imavex.com/php-pdo-wrapper-class/
*/

/** 
* Arquivo de acesso ao banco de dados editado por Carlos W. Gama
* @author Carlos W. Gama
* @package library
* @subpackage database
* @since 1.0
**/

class DB extends PDO {
	private $erro = "";
	private $sql;
	private $bind;
	private $errorCallbackFunction;
	private $errorMsgFormat;

	public $sqlCreated;

	const PDO_MSSQL = 1;
	const PDO_MYSQL = 2;
	const PDO_POSTGRESQL = 3;
	const PDO_SQLLITE = 4;

	public function __construct($dsn, $user="", $passwd="") {
		$options = array(
			PDO::ATTR_PERSISTENT => true, 
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		try {
			parent::__construct($dsn, $user, $passwd, $options);
			//parent::__construct($dsn);
		} catch (PDOException $e) {
			$this->error = $e->getMessage();
			throw $e;
			
		}
	}

	public static function getDSN($type, $config) {
		switch($type) {
			case SELF::PDO_MSSQL:
			 	return 'odbc:Driver={SQL Server};Server='. $config['host'] . ';Database='. $config['database'] . '; Uid='. $config['user'] . ';Pwd='. $config['password']; break;
			case SELF::PDO_MYSQL:
			 	return 'mysql:host='. $config['host'] . ';dbname='. $config['database'] . ';'; break;
			case SELF::PDO_POSTGRESQL:
			 	return 'pgsql:host='. $config['host'] . ';port='. $config['port'] . ';dbname='. $config['database'] . ';user='. $config['user'] . ';password='. $config['password']; break;
			case SELF::PDO_SQLLITE:
			 	return 'sqlite:' . $config['database']; break;
		}
	}

	private function debug() {
		if(!empty($this->errorCallbackFunction)) {
			$error = array("Error" => $this->error);
			if(!empty($this->sql))
				$error["SQL Statement"] = $this->sql;
			if(!empty($this->bind))
				$error["Bind Parameters"] = trim(print_r($this->bind, true));

			$backtrace = debug_backtrace();
			if(!empty($backtrace)) {
				foreach($backtrace as $info) {
					if($info["file"] != __FILE__)
						$error["Backtrace"] = $info["file"] . " at line " . $info["line"];	
				}		
			}

			$msg = "";
			if($this->errorMsgFormat == "html") {
				if(!empty($error["Bind Parameters"]))
					$error["Bind Parameters"] = "<pre>" . $error["Bind Parameters"] . "</pre>";
				$css = trim(file_get_contents(dirname(__FILE__) . "/error.css"));
				$msg .= '<style type="text/css">' . "\n" . $css . "\n</style>";
				$msg .= "\n" . '<div class="db-error">' . "\n\t<h3>SQL Error</h3>";
				foreach($error as $key => $val)
					$msg .= "\n\t<label>" . $key . ":</label>" . $val;
				$msg .= "\n\t</div>\n</div>";
			}
			elseif($this->errorMsgFormat == "text") {
				$msg .= "SQL Error\n" . str_repeat("-", 50);
				foreach($error as $key => $val)
					$msg .= "\n\n$key:\n$val";
			}

			$func = $this->errorCallbackFunction;
			$func($msg);
		}
	}

	/**
	* Deleta informações da tabela
	* @uses $db->delete("mytable", "Age < 30");
	* @uses $db->delete("mytable", "LName = :lname", array(":lname" => "carlos"));
	*/
	public function delete($table, $where, $bind="") {
		$sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
		$this->run($sql, $bind);
	}

	private function filter($table, $info) {
		$driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
		if($driver == 'sqlite') {
			$sql = "PRAGMA table_info('" . $table . "');";
			$key = "name";
		}
		elseif($driver == 'mysql') {
			$sql = "DESCRIBE " . $table . ";";
			$key = "Field";
		}
		else {	
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
			$key = "column_name";
		}	

		if(false !== ($list = $this->run($sql))) {
			$fields = array();
			foreach($list as $record)
				$fields[] = $record[$key];
			return array_values(array_intersect($fields, array_keys($info)));
		}
		return array();
	}

	private function cleanup($bind) {
		if(!is_array($bind)) {
			if(!empty($bind))
				$bind = array($bind);
			else
				$bind = array();
		}
		return $bind;
	}

	/**
	* Insere dados
	* @uses $insert = array("FName" => "John"); $db->insert("mytable", $insert);
	* @return bool
	*/
	public function insert($table, $info) {
		$fields = $this->filter($table, $info);
		$sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";
		$bind = array();
		foreach($fields as $field)
			$bind[":$field"] = $info[$field];
		return $this->run($sql, $bind);
	}

	/**
	* Busca o ultimo ID inserido
	* @return int
	*/
	public function lastInsertId() {
		return parent::lastInsertId();
	}

	public function run($sql, $bind="") {
		$this->sql = trim($sql);
		$this->bind = $this->cleanup($bind);
		$this->error = "";

		try {
			$pdostmt = $this->prepare($this->sql);
			if($pdostmt->execute($this->bind) !== false) {
				if(preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->sql))
					return $this->toUTF8($pdostmt->fetchAll(PDO::FETCH_ASSOC));
				elseif(preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->sql))
					return $pdostmt->rowCount();
			}	
			$this->sqlCreated = $pdostmt->debugDumpParams();
		} catch (PDOException $e) {
			$this->error = $e->getMessage();	
			$this->debug();
			return false;
		}
	}

	/**
	* Busca vários itens
	* @uses $results = $db->select("mytable");
	* @uses $results = $db->select("mytable", "Gender = 'male'");
	* @uses $bind = array(":search" => "%J"); $results = $db->select("mytable", "FName LIKE :search", $bind);
	* @return array
	*/
	public function select($table, $where="", $bind="", $fields="*") {
		$sql = "SELECT " . $fields . " FROM " . $table;
		if(!empty($where))
			$sql .= " WHERE " . $where;
		$sql .= ";";
		return $this->run($sql, $bind);
	}

	/**
	* Busca um único item
	* @uses $results = $db->selectOne("mytable");
	* @uses $results = $db->selectOne("mytable", "Gender = 'male'");
	* @uses $bind = array(":search" => "%J");
	*		$results = $db->selectOne("mytable", "FName LIKE :search", $bind);
	* @return array
	*/
	public function selectOne($table, $where="", $bind="", $fields="*") {
		$return = $this->select($table, $where, $bind, $fields);
		if (isset($return[0]))
			return $return[0];
	}

	/**
	* Chega se tem pelo menos 1 item
	* @uses $results = $db->hasOne("mytable");
	* @uses $results = $db->hasOne("mytable", "Gender = 'male'");
	* @uses $bind = array(":search" => "%J");
	*		$results = $db->hasOne("mytable", "FName LIKE :search", $bind);
	* @return array
	*/
	public function hasOne($table, $where="", $bind="") {
		$return = $this->select($table, $where, $bind);
		return (sizeof($return) > 0 ? true : false);
	}

	public function setErrorCallbackFunction($errorCallbackFunction, $errorMsgFormat="html") {
		//Variable functions for won't work with language constructs such as echo and print, so these are replaced with print_r.
		if(in_array(strtolower($errorCallbackFunction), array("echo", "print")))
			$errorCallbackFunction = "print_r";

		if(function_exists($errorCallbackFunction)) {
			$this->errorCallbackFunction = $errorCallbackFunction;	
			if(!in_array(strtolower($errorMsgFormat), array("html", "text")))
				$errorMsgFormat = "html";
			$this->errorMsgFormat = $errorMsgFormat;	
		}	
	}

	/**
	* Atualiza uma tabela
	* @uses $db->update("mytable", array("FName" => "Jane", "Gender" => "female"), "FName = 'John'");
	* @uses $bind = array(":fname" => $fname, ":lname" => $lname); 
	*		$db->update("mytable", array("FName" => "Jane", "Gender" => "female"), "FName = :fname AND LName = :lname", $bind);
	* @return bool
	*/
	public function update($table, $info, $where, $bind="") {
		$fields = $this->filter($table, $info);
		$fieldSize = sizeof($fields);

		$sql = "UPDATE " . $table . " SET ";
		for($f = 0; $f < $fieldSize; ++$f) {
			if($f > 0)
				$sql .= ", ";
			$sql .= $fields[$f] . " = :update_" . $fields[$f]; 
		}
		$sql .= " WHERE " . $where . ";";

		$bind = $this->cleanup($bind);
		foreach($fields as $field)
			$bind[":update_$field"] = $info[$field];
		
		return $this->run($sql, $bind);
	}

	public function toUTF8($result) {
		if (is_array($result)) {
			foreach ($result as $key => $value) {
				$key = utf8_encode($key);
				if (is_array($value)) 
					$result[$key] = $this->toUTF8($value);
				else
					$result[$key] = utf8_encode($value);
			}
		} else 
			$result = utf8_encode($result);

		return $result;
	}


}	
?>
