<?php
session_start();
require_once('../Model/PWDSecHandler.php');


if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $urlprefix="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    echo 'current url: '.$urlprefix;
    if (PWDSecHandler::handlePWDRecovery($email,$urlprefix)) {

       echo 'Failed to handle the password recovery';
    }else{
        echo 'Email has been successfully sent!';
    }
}

//  uri :  /id/token
$uri=basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if(isset($uri)){
    $param=explode("/",$uri);
    if(PWDSecHandler::authenticateToken($param[0],$param[1])){
        $_SESSION['resetpwd_user']=$param[0];
        header("Location:../View/pwdreset.php");
    }else{
        header("Location:../View/administration.php");
    }
}

