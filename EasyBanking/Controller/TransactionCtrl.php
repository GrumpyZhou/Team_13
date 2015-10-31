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
        $batchfile = $_POST['batchfile'];  // 'batchfile'  in a file type?
        //call function for dealing
       // parseBatchFile($SESSION_['iban'], $filePath, $tid, $tan)
    }
}

//after the transaction goes back to the account page
header("Location:../View/account.php");
