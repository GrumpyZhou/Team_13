<?php
include_once "DatabaseHandler.php";
require_once('3rd Party\fpdf.php');

class Transaction
{
    public $date;
    public $receiverIBAN;
    public $amount;

    private static $cellWidth = 60;
    private static $cellHeight = 8;

    function __construct($date, $IBAN, $amount)
    {
        $this->date = $date;
        $this->receiverIBAN = $IBAN;
        $this->amount = $amount;
    }

    public static function WriteData($date, $iban, $amount, $pdf)
    {
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $date,0, 0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $iban,0,0);
       $pdf->Cell(self::$cellWidth,self::$cellHeight, $amount, 0,1);
    }
}

class TransactionHistory {
    private static $fileName = "../Upload/TransactionHistory_";

    function __construct(){
    }

    public static function GetTransactionHistory($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $history = $dbHandler->execQuery("SELECT * FROM transactions WHERE sender_id='" . $userId . "' OR receiver_id='" . $userId . "';");

        $dataArray = array();
        while($row = $history->fetch_assoc())
        {
            $amount = $row['amount'];
            //if send by the user the amount will be negative
            if($row['sender_id'] == $userId)
            {
                $amount *= -1.0;
            }
            $dataArray[] = new Transaction($row['transaction_date'], $row['receiver_id'], $amount);
        }

        return $dataArray;
    }

    //Returns the path to the created pdf file
    public static function ExportPDF($userId)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        Transaction::WriteData("Transaction Date", "IBAN", "Amount", $pdf);
        $dataArray = self::GetTransactionHistory($userId);
        foreach($dataArray as &$element)
        {
            Transaction::WriteData($element->date, $element->receiverIBAN, $element->amount, $pdf);
        }

        $file = self::$fileName . $userId . ".pdf";
        $pdf->Output($file);

        return $file;
    }
}
?>
