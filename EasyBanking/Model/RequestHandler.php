<?php
include_once "DatabaseHandler.php";
include_once "MoneyTransferHandler.php";

class RequestHandler {
    static private $instance = null;
    static private $tanLength = 15;
    static private $tanCount = 100;

    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct(){}

    private function createTans($id)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        $tans = array();
        for($iTan = 0; $iTan < $tanCount; $iTan++)
        {
            $tans[$iTan] = "";
            for($i = 0; $i < $tanLength; $i++)
            {
                $tans[$iTan] .= $characters[mt_rand(0, strlen($characters)-1)];
            }
            //TODO check for unqiueness
            $dbHandler->execQuery("INSERT INTO tans VALUES ('" . $id . "','" . $tans[$iTan] . "','FALSE');");
        }
        return $tans;
    }

    public function getOpenRequests()
    {
        $dbHandler = DatabaseHandler::getInstance();
        $pendingAccounts = $dbHandler->execQuery("SELECT * FROM users WHERE approved='FALSE';");
        $pendingTransfers = $dbHandler->execQuery("SELECT * FROM transactions WHERE approved='FALSE';");
    }

    private function mailTans($tans, $email) {
        $mailText = "Hello,\nwith this E-Mail we send you your Tans,\nwhich you can use in the future to authenticate yourself,\nin order to perform money transactions:\n\n";
        for($i = 0; $i < self::$tanLength; $i++) {
            $mailText .= $i . ": " . $tans[$i] . "\n";
        }
        mail($email, "Your personal TAN numbers", $mailText);
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
        else
        {
            $tans = createTans($id);
            $res = $dbHandler->execQuery("SELECT * FROM " . $table . " WHERE id='" . $id . "';");
            $row = $res->fetch_assoc();
            $email = $row['mail_address'];
            mailTans($tans, $email);
        }
    }

    public function denyRequest($id, $transaction)
    {
        //TODO: delete the request ?
    }
}
?>
