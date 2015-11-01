<?php
include_once "DatabaseHandler.php";
include_once "MoneyTransferHandler.php";

class TransactionRequest
{
    public $date;
    public $senderId;
    public $amount;
    function __construct($date, $sender, $amount)
    {
        $this->date = $date;
        $this->senderId = $sender;
        $this->amount = $amount;
    }
}

class AccountRequest
{
    public $date;
    public $mail;
    function __construct($date, $mail)
    {
        $this->date = $date;
        $this->mail = $mail;
    }
}

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
        for($iTan = 0; $iTan < self::$tanCount; $iTan++)
        {
            $tans[$iTan] = "";
            for($i = 0; $i < self::$tanLength; $i++)
            {
                $tans[$iTan] .= $characters[mt_rand(0, strlen($characters)-1)];
            }
            //TODO check for unqiueness
            $dbHandler->execQuery("INSERT INTO tans VALUES ('" . $id . "','" . $tans[$iTan] . "','FALSE');");
        }
        return $tans;
    }

    // $account: Boolean
    // -> If True, the function returns pending registration requests
    // -> If False, the function returns pending transaction requests
    public function getOpenRequests($accounts)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $table = "transactions";
        if($accounts)
        {
            $table = "users";
        }

        $pending = $dbHandler->execQuery("SELECT * FROM " . $table . " WHERE approved='FALSE';");
        $dataArray = array();
        if($accounts)
        {
            while($row = $pending->fetch_assoc())
            {
                $dataArray[] = new AccountRequest($row['registration_date'], $row['mail_address']);
            }
        }
        else
        {
            while($row = $pending->fetch_assoc())
            {
                $dataArray[] = new TransactionRequest($row['transaction_date'], $row['sender_id'],$row['amount']);
            }
        }
        return $dataArray;
    }

    // $tans -> Array containing the tans
    // $email -> E-Mail address of the customer as String
    private function mailTans($tans, $email) {
        $mailText = "Hello,\nwith this E-Mail we send you your Tans,\nwhich you can use in the future to authenticate yourself,\nin order to perform money transactions:\n\n";
        for($i = 0; $i < self::$tanLength; $i++) {
            $mailText .= $i . ": " . $tans[$i] . "\n";
        }
        mail($email, "Your personal TAN numbers", $mailText, "From: EasyBanking");
    }

    // $id: the user id or the id of the transaction
    // $transaction: Boolean
    // -> If True, the function approves the transaction with id $id
    // -> If False, the function approves the registration request with user-id $id
    public function approveRequest($id, $transaction)
    {
        $table = "users";
        if($transaction)
        {
            $table = "transactions";
        }

        //check if already approved
        $dbHandler = DatabaseHandler::getInstance();
        $aprroved = $dbHandler->execQuery("SELECT approved FROM " . $table . " WHERE id='" . $id . "';");
        if($aprroved)
        {
            echo "ERROR: Already approved!\n";
            return NULL;
        }

        //change the value
        $dbHandler = DatabaseHandler::getInstance();
        $dbHandler->execQuery("UPDATE " . $table . " SET approved='TRUE' WHERE id='" . $id . "';");

        //TODO perform action
        if($transaction)
        {
            MoneyTransferHandler::performTransaction($id);
        }
        else
        {
            $tans = self::createTans($id);
            $res = $dbHandler->execQuery("SELECT * FROM " . $table . " WHERE id='" . $id . "';");
            $row = $res->fetch_assoc();
            $email = $row['mail_address'];
            mailTans($tans, $email);
        }
    }

    // $id: the user id or the id of the transaction
    // $transaction: Boolean
    // -> If True, the function denies the transaction with id $id
    // -> If False, the function denies the registration request for user-id $id
    public function denyRequest($id, $transaction)
    {
        $table = "users";
        if($transaction)
        {
            $table = "transactions";
        }

        $dbHandler = DatabaseHandler::getInstance();
        $dbHandler->execQuery("DELETE FROM " . $table . " WHERE id='" . $id . "';");
        //TODO: In case of denied user registration, also delete corresponding
        //      columns in the other tables (e.g. accounts table).
    }
}
?>
