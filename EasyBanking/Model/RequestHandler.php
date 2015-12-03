<?php
include_once "DatabaseHandler.php";
include_once "MoneyTransferHandler.php";

class TransactionRequest
{
    public $date;
    public $senderId;
    public $amount;
    public $transactionId;
    function __construct($date, $sender, $amount, $transactionId)
    {
        $this->date = $date;
        $this->senderId = $sender;
        $this->amount = $amount;
        $this->transactionId = $transactionId;
    }
}

class AccountRequest
{
    public $date;
    public $mail;
    public $id;
    function __construct($date, $mail, $id)
    {
        $this->date = $date;
        $this->mail = $mail;
        $this->id = $id;
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

    //$id = user id
    private function createTans($id)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        $tans = array();
        for($iTan = 0; $iTan < self::$tanCount; $iTan++)
        {
            $tans[$iTan] = "";
            for($i = 0; $i < self::$tanLength; $i++)
            {
                $tans[$iTan] .= $characters[mt_rand(0, strlen($characters)-1)];
            }
            //TODO check for unqiueness
            $dbHandler->execQuery("INSERT INTO tans (tan_id, user_id, tan, used)
                VALUES ('" .$iTan. "','" .$id. "','" .$tans[$iTan]. "','0');");
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

        $pending = $dbHandler->execQuery("SELECT * FROM " . $table . " WHERE approved='0';");
        $dataArray = array();
        if($accounts)
        {
            while($row = $pending->fetch_assoc())
            {
                $dataArray[] = new AccountRequest($row['registration_date'], $row['mail_address'], $row['id']);
            }
        }
        else
        {
            while($row = $pending->fetch_assoc())
            {
                $dataArray[] = new TransactionRequest($row['transaction_date'], $row['sender_id'],$row['amount'], $row['id']);
            }
        }
        return $dataArray;
    }

    // $tans -> Array containing the tans
    // $email -> E-Mail address of the customer as String
    private function mailTans($tans, $email) {
        $mailText = "Hello,\nwith this E-Mail we send you your Tans,\nwhich you can use in the future to authenticate yourself,\nin order to perform money transactions:\n\n";
        for($i = 0; $i < self::$tanCount; $i++) {
            $mailText .= $i . ": " . $tans[$i] . "\n";
        }
        mail($email, "Your personal TAN numbers", $mailText, "From: EasyBanking");
    }

    // $id: the user id or the id of the transaction
    // $transaction: Boolean
    // -> If True, the function approves the transaction with id $id
    // -> If False, the function approves the registration request with user-id $id
    // $startBalance: if not specified zero
    public function approveRequest($id, $transaction, $startBalance=0.0)
    {
        $table = "users";
        if($transaction)
        {
            $table = "transactions";
        }

        //check if already approved
        $dbHandler = DatabaseHandler::getInstance();
        $aprroved = $dbHandler->execQuery("SELECT approved FROM " . $table . " WHERE id='" . $id . "';");
        if($aprroved == '1')
        {
            echo "ERROR: Already approved!\n";
            return NULL;
        }

        //change the value
        $dbHandler->execQuery("UPDATE " . $table . " SET approved='1' WHERE id='" . $id . "';");

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
            self::mailTans($tans, $email);

            $balance = floatval($startBalance);
            $dbHandler->execQuery("UPDATE accounts SET balance='" . $balance . "' WHERE user_id='" . $id . "';"); 
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
        if(!$transaction)
        {
            $dbHandler->execQuery("DELETE FROM accounts WHERE user_id='" .$id. "';");
        }
    }
}
?>

