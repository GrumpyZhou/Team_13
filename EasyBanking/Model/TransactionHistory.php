<?php
include_once "DatabaseHandler.php";
include_once "3rd Party/fpdf.php";

class Transaction
{
    public $date;
    public $sourceName;
    public $sourceIBAN;
    public $receiverName;
    public $receiverIBAN;
    public $amount;
    public $description;

    private static $cellWidth = 24;
    private static $cellHeight = 4;

    function __construct($date, $sourceName, $sourceIBAN, $receiverName, $receiverIBAN, $amount, $description)
    {
        $this->date = $date;
        $this->sourceName = $sourceName;
        $this->sourceIBAN = $sourceIBAN;
        $this->receiverName = $receiverName;
        $this->receiverIBAN = $receiverIBAN;
        $this->amount = $amount;
        $this->description = $description;
    }

    public static function WriteElement($width, $height,$data, $pdf)
    {
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->MultiCell($width, $height, $data);
        $pdf->SetXY($x + $width, $y);
    }

    public static function WriteData($date, $sourceName, $sourceIBAN, $receiverName, $receiverIBAN, $amount, $description, $pdf)
    {
        $pdf->SetXY(0, $pdf->GetY() + self::$cellHeight*4);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $date, $pdf);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $sourceName,$pdf);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $sourceIBAN,$pdf);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $receiverName,$pdf);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $receiverIBAN,$pdf);
        self::WriteElement(self::$cellWidth,self::$cellHeight, $amount,$pdf);
        self::WriteElement(self::$cellWidth * 2.8,self::$cellHeight, $description,$pdf);
    }
}

class TransactionHistory {
    function __construct(){
    }

    private static $transactionsPerPage = 13;
    //returns firstname lastname from user as string
    public static function GetAccountName($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $user = $dbHandler->execQuery("SELECT * FROM users WHERE id='" . $userId . "';")->fetch_assoc();
        return $user['first_name'] . " " . $user['last_name'];
    }

    public static function GetTransactionHistory($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $history = $dbHandler->execQuery("SELECT * FROM transactions WHERE (sender_id='" . $userId . "' OR receiver_id='" . $userId . "') AND approved='1';");

        $dataArray = array();
        while($row = $history->fetch_assoc())
        {
            $amount = $row['amount'];
            $sourceIBAN = $row['sender_id'];
            $receiverIBAN = $row['receiver_id'];
            //if send by the user the amount will be negative
            if($sourceIBAN == $userId)
            {
                $amount *= -1.0;
            }
            $dataArray[] = new Transaction($row['transaction_date'], self::GetAccountName($sourceIBAN) , $sourceIBAN , self::GetAccountName($receiverIBAN), $receiverIBAN, $amount, $row['description']);
        }
        return $dataArray;
    }

    //Returns the path to the created pdf file
    public static function ExportPDF($userId, $outputFilepath)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);

        Transaction::WriteData("Transaction Date", "Source Name", "Source IBAN", "Receiver Name", "Receiver IBAN", "Amount", "Description", $pdf);
        $dataArray = self::GetTransactionHistory($userId);
        $count = 0;
        foreach($dataArray as &$element)
        {
            Transaction::WriteData($element->date, $element->sourceName, $element->sourceIBAN, $element->receiverName, $element->receiverIBAN, $element->amount, $element->description, $pdf);
            if($count > self::$transactionsPerPage)
            {
                $pdf->AddPage();
                $count = 0;
            }
            $count = $count + 1;
        }

        $pdf->Output($outputFilepath);
        return $outputFilepath;
    }
}
?>
