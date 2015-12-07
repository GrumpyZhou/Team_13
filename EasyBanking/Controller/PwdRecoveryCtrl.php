<?php
session_start();
require_once('../Model/PWDSecHandler.php');


//  url: e.g "http://localhost/Controller/PwdRecoveryCtrl.php/id/token";

$uri=parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$param=explode("/",$uri);

//step 1. get user email and send token
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $urlprefix="http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    echo 'current url: '.$urlprefix;
    if (PWDSecHandler::handlePWDRecovery($email,$urlprefix)) {
        echo 'Token has been successfully sent to your email address, please check it.';
        echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
    }else{
        echo 'Failed to handle the password recovery';
    }
}

//step 2. check the token and redirect to the reset page
if(sizeof($param)>3){
    if(PWDSecHandler::authenticateToken($param[3],$param[4])){
        //if the user is authenticated
        $_SESSION['resetpwd_user']=$param[3];
        echo 'userid: '.$param[3]." token ".$param[4];
        header("Location:../View/pwdreset.php");
    }else{
        $_SESSION['resetpwd_result']='Fail to recover the password!!';
        header("Location:../View/index.php");
    }
}

//step 3. reset the password
if(isset($_POST['newpwd'])){
    $newpwd=$_POST['newpwd'];
    PWDSecHandler::resetPwd($_POST['uid'],$newpwd);
    echo 'password has been updated successfully!';
    echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
}




