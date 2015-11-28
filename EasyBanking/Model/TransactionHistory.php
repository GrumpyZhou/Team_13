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

    private static $cellWidth = 40;
    private static $cellHeight = 8;

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

    public static function WriteData($date, $sourceName, $sourceIBAN, $receiverName, $receiverIBAN, $amount, $description, $pdf)
    {
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $date,0, 0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $sourceName, 0,0);
       $pdf->Cell(self::$cellWidth/2,self::$cellHeight, $sourceIBAN, 0,0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $receiverName, 0,0);
       $pdf->Cell(self::$cellWidth/2,self::$cellHeight, $receiverIBAN, 0,0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $amount, 0,0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $description, 0,1);
    }
}

class TransactionHistory {
    function __construct(){
    }
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
        foreach($dataArray as &$element)
        {
            Transaction::WriteData($element->date, $element->sourceName, $element->sourceIBAN, $element->receiverName, $element->receiverIBAN, $element->amount, $element->description, $pdf);
        }

        $pdf->Output($outputFilepath);
        return $outputFilepath;
    }
}
?>
