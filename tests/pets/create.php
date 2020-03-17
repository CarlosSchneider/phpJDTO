<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Max-Age: 3600');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data) ||
    empty($data['childid']) || 
    empty($data['name'])) {
  http_response_code(400);
  die();
}

if( !defined('ROOT_FOLDER') ) 
  define('ROOT_FOLDER', implode( '/', explode( DIRECTORY_SEPARATOR , dirname(__FILE__), -2)) );
include_once ROOT_FOLDER. '/tests/_objs/pets.php';

$pet = new Pets;
$stmt = $pet->create($data);

if ($stmt){
  http_response_code(201);
  echo $stmt;
} else {
  http_response_code(500);
  echo  json_encode( array( 'ReturnCode' => 500,
                            'Message' => $pet->getErrMsg(true)));
}

