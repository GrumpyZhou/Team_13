<?php
session_start();
require_once('../Model/MoneyTransferHandler.php');

if (isset($_POST['type'])) {
    $id = $_SESSION['iban'];
    $uploadFilePath = $_SERVER['DOCUMENT_ROOT'] . "/Upload/TransactionBatch_" . $id . ".txt";
    $type = $_POST['type'];
    if ($type == 'single') {
        $iban = $_POST['iban'];
        $amount = $_POST['amount'];
        $tid = $_POST['tid'];
        $tan = $_POST['tan'];
        $description = $_POST['description'];
        $rc = MoneyTransferHandler::transferMoney($id, $iban, $amount, $tan, $tid, $description, $uploadFilePath);
        if ($rc != 0) {
            echo "ERROR: Transfer could not be processed! Error Code: $rc";
            return;
        }
    } elseif ($type == 'multiple') {
        $tid = $_POST['tid'];
        $tan = $_POST['tan'];
        $parts = pathinfo($_FILES['batchfile']['name']);
        if($parts['extension'] != "txt") {
            echo "ERROR: Wrong file type!";
            return;
        }
        if (move_uploaded_file($_FILES['batchfile']['tmp_name'], $uploadFilePath)) {
            $rc = MoneyTransferHandler::parseBatchFile($id, $uploadFilePath, $tid, $tan);
            if ($rc != 0) {
                echo "ERROR: Batch file couldn't be processed! Error Code: $rc";
                return;
            }
        } else {
            echo "ERROR: Batch file wasn't uploaded successfully!";
            return;
        }
    }
}

//after the transaction goes back to the account page
header("Location:../View/account.php");

