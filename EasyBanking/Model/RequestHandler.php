<?php
include_once "DatabaseHandler.php";
include_once "MoneyTransferHandler.php";

class RequestHandler {
    static private $instance = null;

    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}

    public function getOpenRequests()
    {
        $dbHandler = DatabaseHandler::getInstance();
        $pendingAccounts = $dbHandler->execQuery("SELECT * FROM users WHERE approved='FALSE';");
        $pendingTransfers = $dbHandler->execQuery("SELECT * FROM transactions WHERE approved='FALSE';");
    }

    public function approveRequest($id, $transaction)
    {
        $table = "users";
        if($transaction)
        {
            $table = "transactions";
        }
        //TODO check if already approved

        //change the value
        $dbHandler = DatabaseHandler::getInstance();
        $dbHandler->execQuery("UPDATE " . $table . " SET approved='TRUE' WHERE id='" . $id . "';");

        //TODO perform action
        if($transaction)
        {
            MoneyTansferHandler::performTransaction($id);
        }
    }

    public function denyRequest($id, $transaction)
    {
        //TODO: delete the request ?
    }
}
?>
