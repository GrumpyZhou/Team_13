<?php
session_start();
require_once('../Model/MoneyTransferHandler.php');

if (isset($_POST['type'])) {
    $type = $_POST['type'];
    if ($type == 'single') {
        $iban = $_POST['iban'];
        $amount = $_POST['amount'];
        $tid = $_POST['tid'];
        $tan = $_POST['tan'];
        // .... call function for dealing and any returned message?
        //MoneyTransferHandler::transferMoney($SESSION_['iban'], $iban, $amount, $tan, $tid);
        
       // echo $iban." ".$amount." ".$tid." ".$tan;

    } elseif ($type == 'multiple') {
        $tid = $_POST['tid'];
        $tan = $_POST['tan'];
        $id = $_SESSION['iban'];
        $uploadFilePath = $_SERVER['DOCUMENT_ROOT'] . "/Upload/TransactionBatch_" . $id . ".txt";
        if(move_uploaded_file($_FILES['batchfile']['tmp_name'], $uploadFilePath)) {
            echo "SUCCESS!";
            return;
            //TODO: Process batch file
        } else {
            echo "ERROR: Batch file wasn't uploaded successfully!";
            return;
        }
    }
}

//after the transaction goes back to the account page
header("Location:../View/account.php");
