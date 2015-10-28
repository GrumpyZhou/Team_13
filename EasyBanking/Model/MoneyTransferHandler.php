<?php
include_once "RequestHandler.php";
include_once "DatabaseHandler.php";

class MoneyTransferHandler {
    static private $parserPath = "../Parser/";
    static private $uploadPath = "../uploads/";

    function __construct(){
    }

    static private function createTransferBatchFile($fileName, $tanData, $transData)
    {
        $batchFile = fopen(fileName, "w");
        if($batchFile == FALSE)
        {
            echo "Error: Failed creating batch file!\n";
            return FALSE;
        }
        fwrite($batchFile, $tanData);
        fwrite($batchFile, $transData);
        fclose($batchFile);
        return TRUE;
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

    static private function parseBatchFile($senderId, $filePath)
    {
        exec("self::$parserPath $senderId $filePath");
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

    static public function transferMoney($source, $receiver, $amount, $tan, $tanId)
    {
        $tanData = $tanId . " " . $tan . "\n";
        $transData = $receiver . " " . $amount . "\n";

        //create batch file
        $fileName = self::uploadPath . $source;
        if(!createTransferBatchFile($fileName, $tanData, $transData))
        {
            return FALSE;
        }
        parseBatchFile(intval($source), $fileName);

        return TRUE;
    }

    static public function uploadBatch($senderId)
    {
        $targetDir = $uploadPath . $senderId;
        //TODO check if file is correct
        $uploadOK = 1;

        if(!move_uploaded_file($_FILES["batchfile"]["name"], $targetDir))
        {
            echo "Error: Uploading batch file!\n";
            return NULL;
        }

        parseBatchFile($senderId, $targetDir);
    }
}
?>
