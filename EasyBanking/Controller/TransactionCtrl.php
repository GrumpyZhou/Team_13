<?php
session_start();

if (isset($_POST['type'])) {
    $type = $_POST['type'];
    if ($type == 'single') {
        $iban = $_POST['iban'];
        $amount = $_POST['amount'];
        $tid = $_POST['tid'];
        $tan = $_POST['tan'];

        // .... call function for dealing

       // echo $iban." ".$amount." ".$tid." ".$tan;

    } elseif ($type == 'multiple') {

        $tid = $_POST['tid'];
        $tan = $_POST['tan'];
        $batchfile = $_POST['batchfile'];  // 'batchfile'  in a file type?

        //call function for dealing
    }
}

//after the transaction goes back to the account page
header("Location:../View/account.html");
