<?php
include_once "DatabaseHandler.php";

class Transaction
{
    public $date;
    public $receiverIBAN;
    public $amount;
    function __construct($date, $IBAN, $amount)
    {
        $this->date = $date;
        $this->receiverIBAN = $IBAN;
        $this->amount = $amount;
    }
}

class TransactionHistory {
    function __construct(){
    }

    public static function GetTransactionHistory($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $history = $dbHandler->execQuery("SELECT * FROM transactions WHERE sender_id='" . $userId . "';");

        $dataArray = array();
        while($row = $history->fetch_assoc())
        {
            $dataArray[] = new Transaction($row['transaction_date'], $row['receiver_id'], $row['amount']);
        }

        return dataArray;
    }
}
?>
