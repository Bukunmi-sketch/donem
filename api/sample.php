<?php
ini_set("display_errors", 1);
//declare(strict_types=1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type; application/json");

//require "../bulksmsnigeria/vendor/autoload.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
require_once('../vendor/autoload.php');


include '../Models/Login.php';
include '../Models/Auth.php';

$LoginInstance = new Login($conn);
$authInstance = new Auth($conn);

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {

        case 'POST':
                $request = json_decode(file_get_contents('php://input'));
          
                $jwt =$request->jwt;

                if(!empty($jwt)) {

                    try{
                        $secret_Key  = 'xxermuxxusers';

                     //   $decoded= JWT::decode($jwt, $secret_Key, array('HS512'));
                        $decoded = JWT::decode($jwt, new Key($secret_Key, 'HS512'));
    
                        http_response_code(200);
                        $data = ["status" => 200, "message" => "we've got token" , "userdata"=>$decoded ];
                    }catch(Exception $ex){
                        http_response_code(500);
                        $data = ["status" => 500, "message" => $ex->getMessage() ];
                    }

                   
                } else {
                        http_response_code(404);
                        $data = ["status" => 500, "message" => "empty token"];
                }

                echo json_encode($data);
                break;
}


// $secret_Key  = 'xxermuxxusers';
// $iat   =  time();
// //echo $iat;
// $exp= $iat + 10;      
// $nbf = $iat + 10;
// $iss = "localhost";
// $aud   = "xermuxusers";
// $userdata= ["userid" => $logindata['id'], "username" => $logindata['username'], "mobileno" => $logindata['mobile']];          // User name

// $payload_info = [
//         'iat'  => $iat,         // Issued at: time when the token was generated
//         'iss'  => $iss,                       // Issuer
//         'nbf'  => $nbf,         // Not before
//         'exp'  => $exp,
//         'aud' => $aud,                          // Expire
//         "data" => $userdata
// ];

// // set response code
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Origin: *");
// http_response_code(200);
// $jwt = JWT::encode($payload_info, $secret_Key, 'HS512');
// $data = ["status" => 200, "message" => "successful login", "token" => $jwt, "username" =>$logindata['username'],  "mobile" => $logindata['mobile'], "email" =>$logindata['email'], "userid" => $logindata['id'] ];
