<?php
include_once "DatabaseHandler.php";

class TransactionHistory {
    function __construct(){
    }

    public static function GetTransactionHistory($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $history = $dbHandler->execQuery("SELECT * FROM transactions WHERE sender_id='" . $userId . "';");
        while($row = $history->fetch_assoc())
        {
            echo "<tr>";
            echo "<td>" . $row['transaction_date'] . "</td>";
            echo "<td>" . $row['receiver_id'] . "</td>";
            echo "<td>" . $row['amount'] . "</td>";
            echo "</tr>";
        }
    }
}
?>
