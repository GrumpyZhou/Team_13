<?php
session_start();
require_once('../Model/RequestHandler.php');

if (isset($_SESSION['isEmployee'])) {
    if($_SESSION['isEmployee'] == false) {
        exit;
    }
} else {
    exit;
}

if (isset($_POST['reqtype'])) {
    $reqtype = $_POST['reqtype'];
  //echo $reqtype;
    if ($reqtype == 'registration') {
        $id=$_POST['id'];
        $action=$_POST['action'];
        if($action=='Accept'){
             // $transaction: Boolean
            $startBalance= htmlentities( strip_tags ($_POST['startBalance']));
            RequestHandler::approveRequest($id, false, $startBalance);
        }else{
            RequestHandler::denyRequest($id,false);
        }
    } elseif ($reqtype == 'transaction') {
        $id=$_POST['id'];
        $action=$_POST['action'];
        if($action=='Accept'){
             // $transaction: Boolean
            RequestHandler::approveRequest($id, true);
        }else{
            RequestHandler::denyRequest($id,true);
        }
    }
}
header("Location:../View/administration.php");
