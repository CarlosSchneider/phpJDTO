<?php
if( !defined('ROOT_FOLDER') ) 
  define('ROOT_FOLDER', implode( '/', explode( DIRECTORY_SEPARATOR , dirname(__FILE__), -2)) );
include_once ROOT_FOLDER. '/tests/_objs/exLog.php';
include_once ROOT_FOLDER. '/scr/objs/jdtodb.php';

class DBConn {		
  private $host      = "localhost" ;
  private $db_name   = "phptest";
  private $username  = "phpusr";
  private $password  = "9PmOuDmCbH7EpVmG";
  private $conn;
  private $exlog;

  public function getConnection(){
    try {
      $this->conn = null;
      $this->conn = new PDO('mysql:host='.$this->host.';dbname='.$this->db_name, $this->username, $this->password,
                        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_PERSISTENT => true ));
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      return $this->conn;
    } catch (PDOException $e){
      $this->exlog->log_to_file($e->getMessage(), 'ERRO');
      return null;
    }
  }

  public function __construct(){
    $this->exlog = new exLog("dbConn" . date('Y-M') . '.log');
  }
}
?>