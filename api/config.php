<?php
// declare(strict_types=1);

// ini_set("display_errors", 1);

// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: *");
// header("Access-Control-Allow-Methods: POST");
// header("Content-Type: application/json; charset=UTF-8");
// //header("Access-Control-Allow-Headers: Content-Type; application/json");

// //require "../bulksmsnigeria/vendor/autoload.php";
// use Firebase\JWT\JWT;
// require_once('../vendor/autoload.php');


// include '../Models/Register.php';
// include '../Models/Auth.php';

// $registerInstance = new Register($conn);
// $authInstance = new Auth($conn);

// $method = $_SERVER['REQUEST_METHOD'];
// switch ($method) {

//         case 'POST':
//                 $request = json_decode(file_get_contents('php://input'));

//                 $userid = 2 + rand(0, time());
//                 $username =  $authInstance->validate($request->username);
//                 $mobileno =  $authInstance->validate($request->mobileno);
//                 $country =  $authInstance->validate($request->country);
//                 $email ="";
//                 $password = $authInstance->validate($request->password);
//                 $confirmpass = $authInstance->validate($request->confirmpass);
//                 $verficationbadgeStatus = $request->verficationbadge_status;
//                 $accountStatus = $request->account_status;
//                 $date = date("y:m:d h:i:sa");
//                 $otp = mt_rand(100000,999999);
//           //      $usertoken = mt_rand(100000,999999);
//             //    register($userid, $username, $email, $mobile, $country, $status, $otp, $usertoken, $password, $date)
//                 if (!empty($username) && !empty($mobileno) && !empty($country) && !empty($password) && !empty($confirmpass)) {          
//                        if($registerInstance->registerCheckUsername($username)){
//                         if($registerInstance->registerCheckMobile($mobileno)){
//                                 if ($authInstance->phoneNolength($mobileno)) {
//                                         if ($authInstance->matchpassword($password, $confirmpass)){
//                                                 if($registerInstance->register($userid, $username, $email, $mobileno, $country, $accountStatus, $otp, $password, $date)){
                                        
//                                                         $secret_Key  = '68V0zWFrS72GbpPreidkQFLfj4v9m3Ti+DXc8OB0gcM=';
//                                                         $iat   =  time();
//                                                         $expire_at  = $iat + 30;      // Add 60 seconds
//                                                         $nbf - $iat + 10;
//                                                         $iss = "localhost";
//                                                         $aud   = "xermuxusers";   
//                                                         $userdat =[ "id" => $userid, "username" => $request->username, "mobileno" => $request->mobileno ] ;          // User name
                                                        
//                                                         $payload_info = [
//                                                             'iat'  => $iat,         // Issued at: time when the token was generated
//                                                             'iss'  => $iss,                       // Issuer
//                                                             'nbf'  => $nbf,         // Not before
//                                                             'exp'  => $expire_at, 
//                                                             'aud' => $aud,                          // Expire
//                                                             "data" => $userdata
//                                                         ];
                                                    
//                                                          // set response code
//                                                        http_response_code(200);
//                                                       $jwt=JWT::encode( $payload_data, $secret_Key, 'HS512' );
//                                                       $data = ["status" => 200, "message" => "successful login", "token"=>$jwt, "username" => $username, "mobile" => $mobileno, "userid" => $userid];


//                                                 }else{
//                                                         http_response_code(401);
//                                                         $data = ["status" => 500, "message" => "an error occurred while creating user"];
//                                                 }
//                                         } else{
//                                                         $data = ["status" => 500, "message" => "password does not match"]; 
//                                         }
//                                 } else {
//                                         $data = ["status" => 500, "message" => "The Phone No length is not appropriate"];
//                                 }
//                         }else{
//                                 $data = ["status" => 500, "message" => "The mobile no has been used"];
//                         }
//                        }else{
//                         $data = ["status" => 500, "message" => "this username has been taken"];
//                        }
                        
//                 } else {
//                         $data = ["status" => 500, "message" => "all fields are required to be filled"];
//                 }


//                 echo json_encode($data);
//                 break;
// }
