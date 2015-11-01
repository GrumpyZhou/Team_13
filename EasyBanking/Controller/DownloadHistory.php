<?php
session_start();
require_once('../Model/TransactionHistory.php');

$id = $_SESSION['iban'];
$outputPathAbs = "/Upload/TransactionHistory_" . $id . ".pdf";
$outputPathRel = ".." . $outputPathAbs;
$outputPathAbs = $_SERVER['DOCUMENT_ROOT'] . $outputPathAbs;

TransactionHistory::ExportPDF($id, $outputPathAbs);

header("Location:" . $outputPathRel);
?>
