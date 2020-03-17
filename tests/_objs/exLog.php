<?php
if( !defined('ROOT_FOLDER') ) 
  define('ROOT_FOLDER', implode( '/', explode( DIRECTORY_SEPARATOR , dirname(__FILE__), -2)) );

	class exLog {
    private $log_file;
		private const default_path = ROOT_FOLDER . '/tests/_logs/';
		public function log_to_file($log_msg, $level){
	
    switch( gettype($log_msg) ){
      case "NULL": 
        $log_msg = "(null)";
        break;
      case "boolean": 
        $log_msg = $log_msg ? "(true)" : "(false)";
        break;
      case "integer": 
      case "double": 
      case "string": 
        break;
      case "array": 
      case "object": 
        $log_msg = json_encode($log_msg);
        break;
      case "resource": 
      case "unknown type": 
      default:
        $log_msg = json_encode($log_msg);
        break;
    }
      
    $fp_log = fopen($this->log_file, 'a');
    $curr_date = date('Y-m-d H:i:s');
    
    $log_line = '[' . $curr_date . ']:[' . $level .'] ' . $log_msg . PHP_EOL;
    fwrite($fp_log, $log_line);
  }

	public function getUserIP() {
		$ip = @$_SERVER['HTTP_CLIENT_IP'];
		if(filter_var($ip, FILTER_VALIDATE_IP)) 
			return $ip;

		$ip = @$_SERVER['HTTP_X_FORWARDED_FOR'];
		if(filter_var($ip, FILTER_VALIDATE_IP)) 
			return $ip;

		$ip = @$_SERVER['REMOTE_ADDR'];
			return $ip;
	}
 
	public function __construct($log_file){
			$this->log_file = self::default_path . $log_file;
		}
	}
?>
