<?php
session_start();
require_once('../Model/TransactionHistory.php');

if(isset($_POST['iban'])) {
    $id = $_POST['iban'];
} else {
    $id = $_SESSION['iban'];
}
$outputPathAbs = "/Upload/TransactionHistory_" . $id . ".pdf";
$outputPathRel = ".." . $outputPathAbs;
$outputPathAbs = $_SERVER['DOCUMENT_ROOT'] . $outputPathAbs;

TransactionHistory::ExportPDF($id, $outputPathAbs);

header("Location:" . $outputPathRel);
?>
