<?php
// require './Includes/db.inc.php';
require './db/db.php';
/* The code you provided is registering an autoloader function using the `spl_autoload_register`
function. This function allows you to register multiple functions that will be called when a class
or interface is being used and has not been defined yet. */
spl_autoload_register(function($className) {
             $file ='./Models/'.$className.'.php';
            if (file_exists($file)) {
                 // echo "$file included\n";
                  include $file;
            } else {
                echo 'trash unable to' .$className;
            throw new Exception("Unable to load $className.");
           }
        });

        
try {
  
    $authInstance= new Auth($conn);
    $userInstance= new Users($conn);
    $loginInstance= new Login($conn);
    $registerInstance= new Register($conn);
    $chatbotInstance= new Chatbot($conn);
    $settingInstance= new Setting($conn);
    $dashboardInstance= new Dashboard($conn);
    $subscriptionInstance= new Subscription($conn);
    
} catch (Exception $e) {
echo $e->getMessage(), "\n";
}  

$dirfile="./Images/signup_img/dp--";
$chatbotdirfile="./Images/chatbot_img/";
?>