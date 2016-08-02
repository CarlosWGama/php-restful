<?php
/**
* Arquivo com as configurações usadas no webservice
* @author Carlos W. Gama
* @package configuration
**/
/** CONFIGURAÇÕES DE ACESSO AO SQL SERVER **/
$DB_SQLSERVER_CONFIG = array(
	'host'		=> 'localhost',
	'port'		=> '1433',
	'database'	=> 'database',
	'user' 		=> 'root',	
	'password'	=> '',	
	'typeDB'	=> '1',			//SQL SERVER
);

/** CONFIGURAÇÕES DE ACESSO MYSQL **/
$DB_MYSQL_CONFIG = array(
	'host'		=> 'localhost',
	'port'		=> '3306',
	'database'	=> 'database',
	'user' 		=> 'root',	
	'password'	=> '',	
	'typeDB'	=> '2',			//MySQL
);

/*
PDO_MSSQL = 1;		//SQL Server
PDO_MYSQL = 2;		//Mysql
PDO_POSTGRESQL = 3; //Postgres
PDO_SQLLITE = 4;	//SQLite
*/