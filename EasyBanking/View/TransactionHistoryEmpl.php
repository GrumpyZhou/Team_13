<?php
include_once "../Model/TransactionHistory.php";

$id = $_POST['iban'];
$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$balance = $_POST['balance'];
?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="./main.css">
    <title>EasyBanking-Transaction History</title>
</head>
<body>
<div class="transhis">
    <div class="widw">
        <div class="titbg"><span>Transaction History</span></div>
    <pre>
        <span><?php echo $firstName. " " . $lastName; ?></span>
        IBAN: <span><?php echo $id; ?></span>
        CurrentBalance: <span><?php echo $balance; ?></span>
    </pre>
    <table>

        <tr>
            <th>Transaction Date</th>
            <th>IBAN</th>
            <th>Amount</th>

        </tr>
        <?php 
        $dataArray=TransactionHistory::GetTransactionHistory($id);
        foreach($dataArray as &$element)
        {
        ?>
        <tr>
            <td><?php echo $element->date; ?></td>
            <td><?php echo $element->IBAN; ?></td>
            <td><?php echo $element->amount; ?></td>
        </tr>
        <?php
        }
        ?>
    </table>

</div>
</div>
</div>

</body>
</html>
