<?php 
/* version 0.7
 * class JDTOTable
 * class JDTOBasic 
 * abstract class JDTOExtended
 * abstract class JDTOExtendedPartnes
 */
 
function is_empty($var) {
  if( is_null($var) )   return true;
  if( $var == "" )      return true;
  if(trim($var) == "")  return true;
  return false;
}


abstract class JDTOExtendedPartnes extends JDTOExtended {
  private $extendedRecord;

  public function getExtendedRecord()             { return $this->extendedRecord;                }
  public function getExtendedRecordJSON()         { return json_encode( $this->extendedRecord ); }
  
  protected function getChildrens($object, $parentKey){
    $recs   = empty($this->extendedRecord) ? parent::getRecords() : $this->extendedRecord;
    $recs   = $recs[parent::getTableName()];
    $arrTmp = array();
    if(isset($recs)) {
      foreach ($recs as $rec) {
        $object->setInitial();
        if( !empty($rec) ) {
          $filter = parent::valueTosql($rec[$this->getTable()->getkeysNames()]);
          if( !is_empty($filter) ) {
            $filter = "$parentKey = $filter";
            $object->SearchFull($filter);
          }
        }
        if($object->rowCount() > 0)
          $rec = array_merge( $rec, $object->getRecords() );
        else 
          $rec[$object->getTable()->getTableName()] = array();
           
        $arrTmp[] = $rec;
      }
      $this->extendedRecord[parent::getTableName()] = $arrTmp; 
    }
    return json_encode( $this->extendedRecord );
  }

  protected function getParents($object, $parentKey){
    $recs   = empty($this->extendedRecord) ? parent::getRecords() : $this->extendedRecord;
    $recs   = $recs[parent::getTableName()];
    $arrTmp = array();
    if(isset($recs)) {
      foreach ($recs as $rec) {
        $object->setInitial();
        if( !empty($rec) ) {
          $filter = parent::valueTosql($rec[$parentKey]);
          if( !is_empty($filter) ) {        
            $filter = $object->getTable()->getkeysNames() ." = $filter";
            $object->SearchFull($filter);
          }
        }
        if($object->rowCount() > 0)
          $rec = array_merge( $rec, $object->getRecords() );
        else 
          $rec[$object->getTable()->getTableName()] = array();
        $arrTmp[] = $rec;
      }
      $this->extendedRecord[parent::getTableName()] = $arrTmp; 
    }
    return json_encode( $this->extendedRecord );
  }

}  // *********** End JDTOExtendedPartnes class

// ================================================================================================
abstract class JDTOExtended {    
  private $table;
  private $records;
  private $recordsIDX;
  private $conADO;
  private $iniRecord;
  abstract function _logSave($message, $level);

  public function getRecord()                  { return $this->table->getRecord();              }
  public function setRecord($arrayRecord)      { return $this->table->setRecord($arrayRecord);  }
  public function getField($fieldName)         { return $this->table->getField($fieldName);     }
  public function setField($fieldName, $value) { $this->table->setField($fieldName, $value);    }
  public function rowCount()                   { return $this->conADO->rowCount();              }
  public function getTable()                   { return $this->table;                           }
  public function getTableName()               { return $this->table->getTableName();           }
  public function getkeysNames()               { return $this->table->getkeysNames();           }
  public function getRecordJSON()              { return $this->table->getRecordJSON();          }
  public function setRecordJSON($jsonString)   { $this->table->setRecordJSON($jsonString);      }
  public function getRecordsJSON($idx=null)    { return json_encode( self::getRecords($idx) );  }
  public function getErr()                     { return $this->conADO->getErr();                }
  public function getIndex()                   { return $this->recordsIDX;                      }
  public function getLastID()                  { return $this->conADO->getLastID();             }
  protected function valueTosql($var)          { return $this->conADO->valueTosql($var);        }


  public function setInitial() {
    $this->table->setRecord($this->iniRecord);
    $this->recordsIDX = -1;
    $this->conADO->setInitial();
  }

  public function getErrMsg($sql = false) {
    if ($sql) {
      return array('ErrMsg' => $this->conADO->getErrMsg(), 
                   'SQL' => $this->conADO->getSQL() );
    }
    return $this->conADO->getErrMsg();
  }

  public function recordNext() {
    if(isset($this->records)) {
      if( $this->recordsIDX < (count($this->records) - 1) ) {
        $this->recordsIDX++;
        $this->table->setRecord($this->records[$this->recordsIDX]);
      } else {
        $this->table->setRecord($this->iniRecord);
      }
    }
  }

  public function recordBefore() {
    if(isset($this->records)) {
      if( $this->recordsIDX > 0) {
        $this->recordsIDX--;
        $this->table->setRecord($this->records[$this->recordsIDX]);
      } else {
        $this->table->setRecord($this->iniRecord);
      }
    }
  }

  public function getRecords($idx=null) {
    if($idx === null) 
      return array(self::getTableName() => $this->records);
    else 
      return (isset($this->records[$idx]) 
              ? array(self::getTableName() => [ $this->records[$idx] ] ) 
              : array(self::getTableName() => [ $this->iniRecord ] ) );
  }

  protected function setRecords($records) {
    $this->recordsIDX = 0;
    if($records === null)
      $records = [$this->iniRecord];
      
    if(is_array($records)) {
      $this->records = $records;
      if(isset($this->records[0]))
        $this->table->setRecord($this->records[0]);
      else 
        $this->table->setRecord($this->records);
    }
  }

  public function recordsConvertToSub(){
    if(!is_array($this->records)) return null;
    try {
      $arrTmp = array();
      foreach ($this->records as $rec) {
        $arrTmp[$rec[self::getkeysNames()]] = $rec;
      }
      $this->records = $arrTmp;
    } catch(Exception $e) {
      // sem alteração
    }
  }

  // funções basicas 
  public function insert() {
    $this->conADO->Insert($this->table);
    if( $this->conADO->getErr()) {
      $this->_logSave( $this->conADO->getErrMsg() ." - ".  $this->conADO->getSQL(), 'ERRO');
      return -1;
    } 
    return $this->conADO->getLastID();
  }  

  public function exclude() {
    $this->conADO->Exclude($this->table);
    if($this->conADO->getErr()) {
      $this->_logSave($this->conADO->getErrMsg() ." - ". $this->conADO->getSQL(), 'ERRO');
      return false;
    } 
    return true;
  }  

  public function update($ignoreNull=false) {
    $this->conADO->Update($this->table, $ignoreNull);
    if( $this->conADO->getErr()) {
      $this->_logSave( $this->conADO->getErrMsg() ." - ".  $this->conADO->getSQL(), 'ERRO');
      return false;
    } 
    return true;
  }  

  public function SearchByKey() {
    $rtrnVl = $this->conADO->SearchByKey($this->table);
    if($this->conADO->getErr()) {
      self::setRecords(null);
      $this->_logSave($this->conADO->getErrMsg() ." - ". $this->conADO->getSQL(), 'ERRO');
    } elseif((is_null($rtrnVl)) or ($this->conADO->rowCount()  < 1 )) { 
      self::setRecords(null);
    } else {
      self::setRecords($rtrnVl);
    }
  }  

  public function SearchFull($filter=null) {
    $rtrnVl = $this->conADO->SearchFull($this->table, $filter);
    if( $this->conADO->getErr() ) {
      self::setRecords(null);
      $this->_logSave($this->conADO->getErrMsg() ." - ". $this->conADO->getSQL(), 'ERRO');
    } elseif((is_null($rtrnVl)) or ($this->conADO->rowCount() < 1 )) { 
      self::setRecords(null);
    } else {
      self::setRecords($rtrnVl);
    }
  }  
  
  public function __construct($PDOConnection, $tableName, $keysNames, $arrayRecord) {
    $this->iniRecord = $arrayRecord;
    $this->conADO    = new JDTOBasic($PDOConnection);
    $this->table     = new JDTOTable($tableName, $keysNames, $arrayRecord);
  }
} // *********** End JDTOExtended class

// ================================================================================================
class JDTOBasic {
  private $connect;
  private $err;
  private $errMsg;
  private $sql;
  private $affectedRows;
  private $errMsgLst = array(
            'exclude'  => "Não foi possível excluir o registro.",
            'insert'   => "Não foi possível inserir o registro.",
            'update'   => "Não foi possível alterar o registro.",
            'search'   => "Não foi possível recuperar o registro.",
            'nextKey'  => "Não foi possível obter o próximo valor.",
            'curKey'   => "Não foi possível obter o valor."
            );

  public function getErr()        { return $this->err;           }
  public function getErrMsg()     { return $this->errMsg;        }
  public function getSQL()        { return $this->sql;           }
  public function rowCount()      { return $this->affectedRows;  }
  public function getConnection() { return $this->connect;       }

  public function setInitial($clearRows=false) {
    $this->err    = false;
    $this->errMsg = "";
    if($clearRows)
      $this->affectedRows = 0;
  }

  // identificar a próxima chave primaria para a tabela 
  public function getNextID($JDTOtable) {
    self::setInitial(false);
    try {
      $this->sql = "SELECT Auto_increment FROM information_schema.tables WHERE table_name='".
                    $JDTOtable->getTableName() ."'";
      $result = self::getConnection()->query($this->sql);
      $rtrnVl = $result->fetch(PDO::FETCH_ASSOC);
      return $rtrnVl['Auto_increment'];
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['nextKey']. " [". $JDTOtable->getTableName() ."-getNextID] ".
                      $e->getCode().": ".$e->getMessage();

      return null;
    }
  }

  // ****************************************************
  public function getLastID() {
    self::setInitial(false);
    try {
      $this->sql = "SELECT LAST_INSERT_ID()";
      $p_sql  = self::getConnection()->query($this->sql);
      $rtrnVl = $p_sql->fetch(PDO::FETCH_ASSOC);
      $rtrnVl = $rtrnVl['LAST_INSERT_ID()'];      //or $rtrnVl = $p_sql->lastInsertId(); 
      return $rtrnVl;
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['nextKey']. " [". $this->tableName ."-getLastID] ".
                      $e->getCode().": ".$e->getMessage();
      return null;
    }
  }

  // ****************************************************
  public function Insert($JDTOtable) {
    self::setInitial();
    try {
      $fields=""; 
      $values="";
      foreach ($JDTOtable->getRecord() as $field=>$value){
        $field=trim($field);
        if( $value !== null ) {
          if($fields == "") {
            $fields=$field;
            $values=":". preg_replace('/[^A-Za-z0-9]/i', '_', $field);
          } else {
            $fields.=", ".$field;
            $values.=", :". preg_replace('/[^A-Za-z0-9]/i', '_', $field);
          }
        }
      }
      $this->sql = "INSERT INTO ". $JDTOtable->getTableName() ." ($fields) VALUES ($values);";
      $p_sql = self::getConnection()->prepare($this->sql);
      $fields = explode(",", $fields);
      foreach($fields as $field){
        $field=trim($field);
        $p_sql->bindValue(":".preg_replace('/[^A-Za-z0-9]/i', '_', $field), $JDTOtable->getField($field));
      }
      $rtrnVl = $p_sql->execute();
      $this->affectedRows = $p_sql->rowCount();
      return $rtrnVl;

    } catch (Exception  $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['insert']." [".$JDTOtable->getTableName() ."-insert] ". 
                      $e->getCode().": ".$e->getMessage();
      $this->sql    = self::PDOdebugQuery($p_sql)[1];
      return null;
   }
  }

  // ****************************************************
  public function Exclude($JDTOtable) {
    self::setInitial();
    try {
      $keys="";
      foreach ($JDTOtable->getRecord() as $field=>$value){
        if( (in_array($field, (array)$JDTOtable->getkeysNames())) ) {
          if($keys == "") {
            $keys="($field = :$field)";
          } else {
            $keys.="AND ($field = :$field)";
          }
        }          
      }
      $this->sql = "DELETE FROM ". $JDTOtable->getTableName() ." WHERE ($keys);";
      $p_sql = self::getConnection()->prepare($this->sql);

      foreach ($JDTOtable->getRecord() as $field=>$value){
        if( (in_array($field, (array)$JDTOtable->getkeysNames())) ) {
          $p_sql->bindValue(":$field", $value);
        }
      }
      $rtrnVl = $p_sql->execute();
      $this->affectedRows = $p_sql->rowCount();
      return $rtrnVl;
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['exclude']." [".$JDTOtable->getTableName()."-delete] ".
                      $e->getCode().": ".$e->getMessage();
      $this->sql    = self::PDOdebugQuery($p_sql)[1];
      return null;
    }
  }

  // ****************************************************
  public function Update($JDTOtable, $ignoreNull=false) {
    self::setInitial();
    try {
      $fields ="";
      $keys   ="";
      $validField = array();
      foreach ($JDTOtable->getRecord() as $field=>$value){
        if( (in_array($field, (array)$JDTOtable->getkeysNames())) ) {
          if($keys == "") {
            $keys="($field = :$field)";
          } else {
            $keys.="AND ($field = :$field)";
          }
          $validField[] = $field;
        } else {
          if(!($ignoreNull and is_empty($value))) {
            if($fields == "") {
              $fields="$field = :$field";
            } else {
              $fields.=", $field = :$field";
            }
            $validField[] = $field;
          }
        }
      }
      $this->sql = "UPDATE ". $JDTOtable->getTableName() ." SET $fields  WHERE ($keys);";
      $p_sql = self::getConnection()->prepare($this->sql);

      foreach($validField as $field){
        $field=trim($field);
        $p_sql->bindValue(":$field", $JDTOtable->getField($field));
      }

      $p_sql->execute();
      $this->affectedRows = $p_sql->rowCount(); 
      return $this->affectedRows;
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['update']. " [". $JDTOtable->getTableName()."-update] ".
                      $e->getCode().": ".$e->getMessage();
      $this->sql    = self::PDOdebugQuery($p_sql)[1];
                      
      return null;
    }
  }
  
  // ****************************************************
  public function SearchByKey($JDTOtable) {
    self::setInitial();
    try {
      $keys="";
      foreach ($JDTOtable->getRecord() as $field=>$value){
        if( (in_array($field, (array)$JDTOtable->getkeysNames())) ) {
          if($keys == "") {
            $keys="($field = :$field)";
          } else {
            $keys.="AND ($field = :$field)";
          }
        }          
      }
      $this->sql = "SELECT * FROM ". $JDTOtable->getTableName() ." WHERE ($keys);";
      $p_sql = self::getConnection()->prepare($this->sql);

      foreach ($JDTOtable->getRecord() as $field=>$value){
        if( (in_array($field, (array)$JDTOtable->getkeysNames())) ) {
          $p_sql->bindValue(":$field", $value);
        }
      }
      $p_sql->execute();
      $this->affectedRows = $p_sql->rowCount();
      return $p_sql->fetch(PDO::FETCH_ASSOC);
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['search']. " [". $JDTOtable->getTableName()."-select] ".
                      $e->getCode().": ".$e->getMessage();
      $this->sql    = self::PDOdebugQuery($p_sql)[1];
      return null;
    }
  }

  // ****************************************************
  public function SearchFull($JDTOtable, $filter=null ) {
    self::setInitial();
    try {
      $this->sql = "SELECT * FROM ". $JDTOtable->getTableName();
      if(is_null($filter) ) {
        $filter="";
        foreach ($JDTOtable->getRecord() as $field=>$value){
          if( !is_empty( $value ) ){
            if($filter == "") 
              $filter="($field = :$field)";
            else
              $filter.="AND ($field = :$field)";
          }          
        }
      }
      $this->sql .= is_empty( $filter ) ? ";" : " WHERE ($filter);";
      $p_sql = self::getConnection()->prepare($this->sql);

      if( !is_empty( $filter ) ) {
        foreach ($JDTOtable->getRecord() as $field=>$value){
          if( !is_empty( $value ) )
            $p_sql->bindValue(":$field", $value);
        }
      }
      $p_sql->execute();
      $this->affectedRows = $p_sql->rowCount();
     
      return $p_sql->fetchAll(PDO::FETCH_ASSOC);
    
    } catch (Exception $e) {
      $this->err    = true;
      $this->errMsg = $this->errMsgLst['search']. " [". $JDTOtable->getTableName()."-select] ".
                      $e->getCode().": ".$e->getMessage();
      $this->sql    = self::PDOdebugQuery($p_sql)[1];
      return null;
    }
  }  
  
  public function valueTosql($var) {                    // TODO: evoluir
    if( is_null($var) ) 
      return null;
    switch( gettype($var) ){
        case "NULL": 
        case "boolean": 
        case "integer": 
        case "double": 
            return $var;
        case "string": 
            return "'".quotemeta($var)."'"; //PDO::quote($var) 
        case "array": 
        case "object": 
        case "resource": 
        case "unknown type": 
            return gettype($var);
        default:
            return null;
    }
  }  
  
  protected function PDOdebugQuery($pdo) {
    ob_start();
    $pdo->debugDumpParams();
    $r = ob_get_contents();
    ob_end_clean();
    return explode("\n", $r);
  }

  public function __construct($instanseOfConnection) {
    $this->connect = $instanseOfConnection;
  }
  
  public function __destruct() {
    if (!isset($this->connect)) {     
      try {
        $this->connect = null; 
      } catch (PDOException $e) {
      }
    }
  }  
} // *********** End JDTOBasic class

// ================================================================================================
class JDTOTable {
  private $tableName;
  private $keysNames;
  private $record;

  public function getTableName()             { return $this->tableName;                         }
  public function getkeysNames()             { return $this->keysNames;                         }
  public function getRecord()                { return $this->record;                            }
  public function getField($fieldName)       { return $this->record[$fieldName];                }
  public function getRecordJSON()            { return json_encode( $this->record );             }
  public function setRecordJSON($jsonString, $ignoreNull=false ) { 
                                               self::setRecord(json_decode($jsonString, true), $ignoreNull); }

  public function setField($fieldName, $value) {
    if( array_key_exists($fieldName, $this->record) )
      $this->record[$fieldName] = $value;
  }

  public function setRecord($arrayRecord, $ignoreNull=false) {
    if(is_array($arrayRecord)){
      foreach ($arrayRecord as $fieldName => $value){
        if(!($ignoreNull and is_empty($value))) 
          self::setField($fieldName, $value);
      }
    } else 
      throw new Exception("Value must be array, like base record.");
  }

  public function __construct($tableName, $keysNames, $arrayRecord) {
    $this->tableName = $tableName;
    $this->keysNames = $keysNames;
    $this->record    = $arrayRecord;
  }
} // *********** FIM class JDTOtable
// FIM
?>