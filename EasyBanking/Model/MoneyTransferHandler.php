<?php
include_once "RequestHandler.php";
include_once "DatabaseHandler.php";

class MoneyTransferHandler {

    function __construct(){
    }

    static private function createTransferBatchFile($fileName, $transData)
    {
        $batchFile = fopen($fileName, "w");
        if($batchFile == FALSE)
        {
            echo "Error: Failed creating batch file!\n";
            return FALSE;
        }
        fwrite($batchFile, $transData);
        fclose($batchFile);
        return TRUE;
    }

    static private function changeBalance($amount, $user_id)
    {
		$amount=mysql_real_escape_string($amount);
		$user_id=mysql_real_escape_string($user_id);


		$dbHandler = DatabaseHandler::getInstance();
        $userQuery = "WHERE user_id='" . $user_id . "';";
        $res = $dbHandler->execQuery("SELECT * FROM accounts " .$userQuery);
        $row = $res->fetch_assoc();
        $balance = $row['balance'];
        $balance += $amount;
        $dbHandler->execQuery("UPDATE accounts SET balance='" . $balance . "' " . $userQuery);
    }

    // Returns the exit code of the executed command.
    static public function parseBatchFile($senderId, $filePath, $tanId, $tan)
    {

		if(self::checkTAN($filePath, $senderId, $tan, $tanId) == FALSE)
		{
			return "Invalid TAN";
		}
		$senderId = escapeshellarg($senderId);
		$tanId = escapeshellarg($tanId);
		$tan = escapeshellarg($tan);
		$filePath = escapeshellarg($filePath);
		error_log("Parser command: " . "parseBatchFile $senderId $tanId $tan $filePath");
        exec("parseBatchFile $senderId $tanId $tan $filePath", $output, $rc);
        return $rc;
    }
    
    static public function checkTAN($batchFilePath, $senderId, $tan, $tanId)
    {

		$senderId=mysql_real_escape_string($senderId);
		$tan=mysql_real_escape_string($tan);
		$tanId=mysql_real_escape_string($tanId);
		$batchFilePath=mysql_real_escape_string($batchFilePath);

		$dbHandler = DatabaseHandler::getInstance();
		$res = $dbHandler->execQuery("SELECT * FROM users WHERE id='" . $senderId . "';");
		$row = $res->fetch_assoc();
		$usesSCS = $row['uses_scs'];
		
		if($usesSCS) {
			$tan_array = explode('+', $tan);
			$tan_hash = $tan_array[1];
			$tan_counter = $tan_array[0];
			//get SCS info
			
			$res = $dbHandler->execQuery("SELECT * FROM scs WHERE user_id='" . $senderId . "';");
			$row = $res->fetch_assoc();
			$pin = $row['pin'];
			$counter = $row['counter'];
			$counter += 1;
			//TAN no longer valid?
			if ($tan_counter < $counter)
			{
				error_log("TAN checking problem: counter invalid.");
				return FALSE;
			}
			
			$counter = $tan_counter;
			$hashedPin = $pin;
			for($i = 0; $i < $counter; $i++)
			{
				$hashedPin = hash('sha256', $hashedPin);
				error_log("Hashed PIN in loop: " . $hashedPin);
			}
			
			$finalStringToHash = $hashedPin;
			
			//parse batch file
			$txt_file    = file_get_contents($batchFilePath);
			$rows        = explode("\n", $txt_file);

			foreach($rows as $row => $data)
			{
				//get row data
				$row_data = explode(' ', $data);
				$finalStringToHash .= $row_data[0];
				//$finalStringToHash .= $row_data[1];
			}
			$finalStringToHash .= date("Ymd");
			$finalHash = hash('sha256', $finalStringToHash);
			if($finalHash == $tan_hash)
			{
				$res = $dbHandler->execQuery("UPDATE scs SET counter = '" . $counter . "' WHERE user_id='" . $senderId . "';");
				return TRUE;
			}
			else
			{
				error_log("Hash comparison not successful. " . $finalHash . "from us vs. " . $tan_hash);
				return FALSE;
			}
		}
		
		else {
			$res = $dbHandler->execQuery("SELECT * FROM tans WHERE user_id = '" . $senderId . "' AND tan = '" . $tan . "' AND tan_id = '" . $tanId . "' AND used = '0';");
			if($res->fetch_assoc() == NULL) {
				return FALSE;
			}
			$res = $dbHandler->execQuery("UPDATE tans SET used='1' WHERE user_id = '" . $senderId . "' AND tan = '" . $tan . "' AND tan_id = '" . $tanId . "';");
			return TRUE;
		}
	}

    static public function performTransaction($id)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM transactions WHERE id='" . $id . "';");
        $row = $res->fetch_assoc();
        $amount = $row['amount'];
        $sender = $row['sender_id'];
        $receiver = $row['receiver_id'];

        self::changeBalance(-$amount, $sender);
        self::changeBalance($amount, $receiver);
    }

    //$source: user id of the sender
    //$receiver: user id of the receiver
    static public function transferMoney($source, $receiver, $amount, $tan, $tanId, $description, $filePath)
    {
		$receiver=mysql_real_escape_string($receiver);
		$amount=mysql_real_escape_string($amount);
		$description=mysql_real_escape_string($description);
		$filePath=mysql_real_escape_string($filePath);


		$transData = "<" . $receiver . "> <" . $amount . "> <" . $description .">\n";
        //create batch file
        if(!self::createTransferBatchFile($filePath, $transData))
        {
            return FALSE;
        }
        return self::parseBatchFile($source, $filePath, $tanId, $tan);
    }
}
?>

