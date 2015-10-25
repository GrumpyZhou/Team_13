<?php
include_once "RequestHandler.php";
include_once "DatabaseHandler.php";

class MoneyTransferHandler {
    static private $parserPath = "../Parser/";
    static private $filePath = "../transaction.txt";
    static private $uploadPath = "../Uploads/";

	function __construct(){
    }

	static private function createTransferBatchFile($data)
	{
		$batchFile = fopen($filePath, "w") or die("Unable to open file");
		fwrite($batchFile, $data);
		fclose($batchFile);
	}

	static private function changeBalance($amount, $user_id)
	{
		$dbHandler = DatabaseHandler::getInstance();
		$userQuery = "WHERE user_id='" . $user_id . "';";
		$res = $dbHandler->execQuery("SELECT balance FROM accounts " + userQuery);
		$balance = floatval($res);
		$balance += $amount;
		$dbHanlder->execQuery("UPDATE accounts SET balance='" . $balance . "' " + userQuery);
	}

    static public function performTransaction($id)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM transactions WHERE id='" . $id . "';");
        $row = $res->fetch_assoc();
        $amount = $row['amount'];
        $sender = $row['sender_id'];
        $receiver = $row['receiver_id'];

        changeBalance(-amount, sender);
        changeBalance(amount, receiver);
    }

	static public function transferMoney($source, $receiver, $amount, $tan)
	{
	    $data = "" . $source . " " . $receiver . " " . $amount . " " . $tan . "";

        createTransferBatchFile($data);

        uploadBatch(intval($source));

		return TRUE;
	}

	static public function uploadBatch($senderId, $file)
    {
	    exec("$parserPath $senderId $filePath");
	}
}
?>
