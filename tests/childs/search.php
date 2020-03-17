<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: access');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

$lastname = isset($_GET['lastname']) ? $_GET['lastname'] : die();

if( !defined('ROOT_FOLDER') ) 
  define('ROOT_FOLDER', implode( '/', explode( DIRECTORY_SEPARATOR , dirname(__FILE__), -2)) );
include_once ROOT_FOLDER. '/tests/_objs/childrens.php';

$child = new Childs;
$stmt = $child->search($lastname);


if ($stmt){
  http_response_code(200);
  echo $stmt;
} else {
  http_response_code(500);
  echo json_encode( array( 'ReturnCode' => 500,
                           'Message' => $child->getErrMsg(true)));
}

