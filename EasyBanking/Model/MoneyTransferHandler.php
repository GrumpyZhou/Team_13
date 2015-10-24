<?php
include_once "RequestHandler.php";
include_once "DatabaseHandler.php";

class MoneyTransferHandler {
    private $senderId;
	private $receiverId;	
	private $amount;
	private $tan;
	
	static private $approveLimit = 10000.0;
	
	function __construct(){
    }
	
	private function createRequest()
	{
		$requestHandler = RequestHandler::getInstance();
		$requestHandler->addRequest();
	}
	
	//Not needed ?
	private function createTransferBatchFile($data)
	{
		//create the batch file 
		$batchFile = fopen("transaction.txt", "w") or die("Unable to open file");
		fwrite($batchFile, $data);
		fclose($batchFile);
	}
	
	private function checkTan()
	{
		$dbHandler = DatabaseHandler::getInstance();
		$res = $dbHandler->execQuery("SELECT * FROM tans 
			WHERE user_id='" . self::$senderId . "' 
			AND tan='" . self::$tan . "';");
		if($res->num_rows == 0)
		{
			return FALSE;	
		}
		//TODO:check status of tan, update status of tan
		
		return TRUE;
	}
	
	private function changeBalance($amount, $user_id)
	{
		$dbHandler = DatabaseHandler::getInstance();
		$userQuery = "WHERE user_id='" . $user_id . "';";
		$res = $dbHandler->execQuery("SELECT balance FROM accounts " + userQuery);
		$balance = floatval($res);
		$balance += $amount;
		$dbHanlder->execQuery("UPDATE accounts SET balance='" . $balance . "' " + userQuery); 
	}
	
	private function addHistory()
	{
		$dbHandler = DatabaseHandler::getInstance();
		//TODO: status for the transaction
		$status = 0;
		$dbHandler->execQuery("INSERT INTO transactions 
			VALUES ('" . self::$senderId . "','" . self::$receiverId . "','" 
			. self::$amount . "','" . $status . "';");
	}
	
	public function transferMoney($source, $receiver, $amount, $tan)
	{
		self::$senderId = intval($source);
		self::$receiverId = intval($receiver);
		self::$amount = floatval($amount);
		self::$tan = $tan;
		
		if(!checkTan)
		{
			echo "Invalid Tan";
			return FALSE;
		}
		
		if(self::$amount > $approveLimit)
		{
			createRequest();	
		}
		else
		{
			changeBalance(-self::$amount, self::$senderId);
			changeBalance(self::$amount, self::$receiverId);
			addHistory();
		}
		
		return TRUE;
	}
	
	public function uploadBatch()
	{
		//TODO: parse with c program
		
		transferMoney();
	}
	
	public function __toString()
	{
		$data = (string)self::$senderId + " " + (string)self::$receiverId + " " 
			+ (string)self::$amount + " " + (string)self::$tan;
		return $data;
	}
}
?>
