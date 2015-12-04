<?php
session_start();
require_once('../Model/TransactionHistory.php');

if(isset($_SESSION['isEmployee'])) {
	$id = "";
	if($_SESSION['isEmployee'] == false) {
		$id = $_SESSION['iban'];
	} else {
		if(isset($_POST['iban'])) {
			$id = $_POST['iban'];
		} else {
			exit;
		}
	}
	$outputPathAbs = "/tmp/TransactionHistory_" . $id . ".pdf";

	TransactionHistory::ExportPDF($id, $outputPathAbs);
	if(file_exists($outputPathAbs)) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($outputPathAbs).'"');
		header('Expires: 0');
		header('Cache-Control: private, max-age=0, no-cache');
		header('Content-Length: '.filesize($outputPathAbs));
		readfile($outputPathAbs);
		exit;
	}
}
?>
