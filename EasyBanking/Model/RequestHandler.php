<?php
include_once "DatabaseHandler.php";
include_once "MoneyTransferHandler.php";
include_once "3rd Party/fpdf_protection.php";

class TransactionRequest
{
    public $date;
    public $senderName;
    public $senderId;
    public $destinationName;
    public $destinationId;
    public $amount;
    public $transactionId;
    public $description;
    function __construct($date, $senderName, $senderId, $destinationName, $destinationId, $amount, $transactionId, $description)
    {
        $this->date = $date;
        $this->senderName = $senderName;
        $this->senderId = $senderId;
        $this->destinationName = $destinationName;
        $this->destinationId = $destinationId;
        $this->amount = $amount;
        $this->transactionId = $transactionId;
        $this->description = $description;
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
    static private $outputPathAbs = "/Upload/TanPDF_";

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

    private function GetUserName($id)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $row = $dbHandler->execQuery("SELECT * FROM users WHERE id='" . $id . "';")->fetch_assoc();
        $name = $row['first_name'] . " " . $row['last_name'];
        return $name;
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
                $senderId = $row['sender_id'];
                $senderName = self::GetUserName($senderId);
                $destId = $row['receiver_id'];
                $destName = self::GetUserName($destId);
                $dataArray[] = new TransactionRequest($row['transaction_date'], $senderName, $senderId, $destName, $destId, $row['amount'], $row['id'], $row['description']);
            }
        }
        return $dataArray;
    }

    private function CreateTanPDF($tans, $id, $password)
    {
        $outputPath = $_SERVER['DOCUMENT_ROOT'] . self::$outputPathAbs . $id . ".pdf";
        //TODO: change the password
        $currPassword = "password";
        $pdf = new FPDF_Protection();
        $pdf->SetProtection(array(), $currPassword, $currPassword);
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        for($i = 0; $i < self::$tanCount; $i++)
        {
            $text = $i . ": " . $tans[$i] . "\n";
            $pdf->Cell(0, 4, $text, 0, 1);
        }
        $pdf->Output($outputPath);

        return $outputPath;
    }
    // $tans -> Array containing the tans
    // $email -> E-Mail address of the customer as String
    private function mailTans($tanFile, $email) {
        $mailText = "Hello,\nwith this E-Mail we send you your Tans,\nwhich you can use in the future to authenticate yourself,\nin order to perform money transactions:\n\n";
        $subject = "Your personal TAN numbers";
        $randomHash = md5(date('r', time()));
        $fileName = "tanFile.pdf";

        $eol = PHP_EOL;
        //header
        $headers = "From: EasyBanking" . $eol;
        $headers .= "MIME-Version: 1.0" . $eol;
        $headers .= "Content-Type: multipart/mixed; boundary=\"" . $randomHash . "\"" . $eol . $eol;
        $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
        $headers .= "MIME encoded message." . $eol . $eol;

        //message
        $headers .= "--" . $randomHash . $eol;
        $headers .= "Content-Type: text/html; charset=\"iso-8859-1\"" . $eol;
        $headers .= "Content-Transfer-Encoding: 8bit" . $eol . $eol;
        $headers .= $mailText .$eol . $eol;

        //read attachement , encode and split it
        $file = fopen($tanFile, 'rb');
        $data = chunk_split(base64_encode(fread($file, filesize($tanFile))));
        fclose($file);

        //attachement
        $headers .= "--" . $randomHash . $eol;
        $headers .= "Content-Type: application/octet-stream; name=\"" . $fileName . "\"" . $eol;
        $headers .= "Content-Transfer-Encoding: base64" . $eol;
        $headers .= "Content-Disposition: attachemenet" . $eol . $eol;
        $headers .= $data .$eol .$eol;
        $headers .= "--" . $randomHash . "--";

        mail($email, $subject, "", $headers);
    }

    // $email -> E-Mail address of the customer as String
    private function mailSCS($email) {
        $mailText = "Hello,\nyou have been approved at EasyBanking.\nYou can download the SCS at our home page\nin order to perform money transactions.\n\n";
        mail($email, "Your account at EasyBanking", $mailText, "From: EasyBanking");
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
            $res = $dbHandler->execQuery("SELECT * FROM " . $table . " WHERE id='" . $id . "';");
            $row = $res->fetch_assoc();
            $email = $row['mail_address'];
            $usesSCS = $row['uses_scs'];
            if($usesSCS)
            {
				self::mailSCS($email);
			}
			else
			{
				$tanFile = self::CreateTanPDF($tans, $id, $row['password']);
				self::mailTans($tanFile, $email);
			}

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
            $dbHandler->execQuery("DELETE FROM scs WHERE user_id='" .$id. "';");
        }
    }
}
?>

