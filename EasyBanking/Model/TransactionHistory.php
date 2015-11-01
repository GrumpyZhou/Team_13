<?php
include_once "DatabaseHandler.php";
include_once "3rd Party/fpdf.php";

class Transaction
{
    public $date;
    public $IBAN;
    public $amount;

    private static $cellWidth = 60;
    private static $cellHeight = 8;

    function __construct($date, $IBAN, $amount)
    {
        $this->date = $date;
        $this->IBAN = $IBAN;
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
    function __construct(){
    }

    public static function GetTransactionHistory($userId)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $history = $dbHandler->execQuery("SELECT * FROM transactions WHERE sender_id='" . $userId . "' OR receiver_id='" . $userId . "';");

        $dataArray = array();
        while($row = $history->fetch_assoc())
        {
            $amount = row['amount'];
            $iban;
            //if send by the user the amount will be negative and iban will be the receiver
            if($row['sender_id'] == $userId)
            {
                $iban = $row['receiver_id'];
                $amount *= -1.0;
            }
            else
            {
                $iban = $row['sender_id'];
            }

            $dataArray[] = new Transaction($row['transaction_date'], $iban, $amount);
        }

        return $dataArray;
    }

    //Returns the path to the created pdf file
    public static function ExportPDF($userId, $outputFilepath)
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        Transaction::WriteData("Transaction Date", "IBAN", "Amount", $pdf);
        $dataArray = self::GetTransactionHistory($userId);
        foreach($dataArray as &$element)
        {
            Transaction::WriteData($element->date, $element->IBAN, $element->amount, $pdf);
        }

        $pdf->Output($outputFilepath);
        return $outputFilepath;
    }
}
?>
