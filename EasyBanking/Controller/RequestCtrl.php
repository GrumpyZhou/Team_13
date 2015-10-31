<?php
require_once('../Model/RequestHandler.php');

if (isset($_POST['reqtype'])) {
    $reqtype = $_POST['reqtype'];
  //echo $reqtype;
    if ($reqtype == 'registration') {
        $id=$_POST['id']; 
        $action=$_POST['action'];
        if($action=='Accept'){
			 // $transaction: Boolean
			RequestHandler::approveRequest($id, false);
		echo  "approve registration of  ".$id;
		}else{
			RequestHandler::denyRequest($id,false);
		}
    } elseif ($reqtype == 'transaction') {
        $id=$_POST['id'];  
        $action=$_POST['action'];
        if($action=='Accept'){
			 // $transaction: Boolean
			RequestHandler::approveRequest($id, true);
		echo  "approve transaction of  ".$id;
		}else{
			RequestHandler::denyRequest($id,true);
		}
    }
}

//return to the administration page
header("Location:../View/administration.php");
