<?php
if( !defined('ROOT_FOLDER') ) 
  define('ROOT_FOLDER', implode( '/', explode( DIRECTORY_SEPARATOR , dirname(__FILE__), -2)) );
include_once ROOT_FOLDER. '/tests/_objs/dbConn.php';
include_once ROOT_FOLDER. '/tests/_objs/childrens.php';

class Pets extends JDTOExtendedPartnes {
  private $exlog;		
  private $tabela = 'pets';
  private $ID     = 'id';
  private $campos = array(
            'id' => NULL,
            'childid' => NULL,    
            'name' => NULL,
            'type' => NULL
          );

  public function _logSave($message, $level){
    $this->exlog->log_to_file($message, $level);
  }
  
  public function __construct() {
    $DBCon = new DBConn();
    $DBCon = $DBCon->getConnection();
    parent::__construct($DBCon, $this->tabela, $this->ID, $this->campos);
    $this->exlog = new exLog($this->tabela . date('Y-M') . '.log');
  }	

  public function readSingle($id) {
    $this->setField("id", $id );
    $this->SearchFull();
    return $this->getRecordJSON();
  }

  public function read($id=null) {
    $this->setField("id", $id );
    $this->SearchFull();
    if(! $this->getErr() ) {
      $child = new Childs;
      $this->getParents($child, 'childid');
      return $this->getExtendedRecordJSON();
    }
    return null;
  }

  public function search($id=null) {
    $this->setField("id", $id );
    $this->SearchFull();
    return ( $this->getErr() ? NULL : $this->getRecordsJSON() );
  }

  public function delete($id) {
    $this->setField("id", $id );
    $this->exclude();
    return ( $this->getErr() 
             ? null 
             : json_encode( array( 'ReturnCode' => 200, 
                                   'Afected rows' => $this->rowCount() ))
            );
  }

  public function create($person) {
    $this->setRecord($person);
    $id = $this->insert();
    if ( $this->getErr() ) {
      return json_encode( array( 'ReturnCode' => 500,
                                 'Message' => $this->getErrMsg(true)));
    } 
    $this->setField("id", $id);
    $this->SearchByKey();
    return json_encode( array( 'ReturnCode' => 201,
                               'Afected rows' => $this->rowCount(),
                               'Record' => $this->getRecord() 
                              ));
  }

  public function alter($person) {
    $this->setRecord($person);
    $this->update(true);
    if ( $this->getErr() ) {
      return json_encode( array( 'ReturnCode' => 500,
                                 'Message' => $this->getErrMsg(true)));
    } 
    $this->SearchByKey();
    return json_encode( array( 'ReturnCode' => 201,
                               'Afected rows' => $this->rowCount(),
                               'Record' => $this->getRecord() 
                              ));
  }

}
?>