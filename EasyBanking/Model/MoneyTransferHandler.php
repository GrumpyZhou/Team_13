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
        $dbHandler = DatabaseHandler::getInstance();
        $userQuery = "WHERE user_id='" . $user_id . "';";
        $res = $dbHandler->execQuery("SELECT balance FROM accounts " + $userQuery);
        $balance = floatval($res);
        $balance += $amount;
        $dbHandler->execQuery("UPDATE accounts SET balance='" . $balance . "' " . $userQuery);
    }

    // Returns the exit code of the executed command.
    static public function parseBatchFile($senderId, $filePath, $tanId, $tan)
    {
        exec("parseBatchFile $senderId $tanId \"$tan\" \"$filePath\"", $output, $rc);
        return $rc;
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
    static public function transferMoney($source, $receiver, $amount, $tan, $tanId, $filePath)
    {
        $transData = $receiver . " " . $amount . "\n";

        //create batch file
        if(!self::createTransferBatchFile($filePath, $transData))
        {
            return FALSE;
        }
        return self::parseBatchFile($source, $filePath, $tanId, $tan);
    }
}
?>

