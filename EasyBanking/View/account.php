<?php
session_start();
require_once('../Model/TransactionHistory.php');
if (!isset($_SESSION['isEmployee'])||$_SESSION['isEmployee']) {
   header("Location:../View/index.php");
} else {

$firstname =  $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$iban =$_SESSION['iban'];
$balance = $_SESSION['balance'];
$email = $_SESSION['email'];
?>
    <!DOCTYPE html>
    <html>
    <head lang="en">
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="./main.css">
        <title>EasyBanking-Account</title>

        <style id="antiClickjack">body{display:none !important;}</style>
        <script type="text/javascript">
            if (self === top)
            {
                var antiClickjack = document.getElementById("antiClickjack");
                antiClickjack.parentNode.removeChild(antiClickjack);
            }
            else
            {
                top.location = self.location;
            }
        </script>
    </head>
    <body>


    <div class="topbar">
        <div class="logo">EasyBanking</div>
        <div id="logout">
            <label><? echo $firstname . " " . $lastname ?></label>

            <form action="../Controller/LogoutCtrl.php" method="post">
                <input type="submit" class="barbtn" value="Log Out"/>
            </form>
        </div>
    </div>

    <div class="menubar">
        <div class="mainmenu">

            <ul>
                <li><a href="./account.php"/>Personal Bank Account</a></li>
                <li><a href="./transaction.php">Online Transaction</a></li>
            </ul>
        </div>
    </div>

    <div class="mainpart">

        <div class="banner">
            <h1>Welcome  <? echo $firstname . " " . $lastname; ?>!</h1>
        </div>

        <div class="accinfo">
            <div class="widw">
                <div class="titbg"><span>Bank Account Overview</span></div>
                <table>

                    <tr>
                        <td>First Name:</td>
                        <td><? echo $firstname; ?></td>
                    </tr>
                    <tr>
                        <td>Last Name:</td>
                        <td><? echo $lastname; ?></td>
                    </tr>
                    <tr>
                        <td>IBAN:</td>
                        <td><? echo $iban; ?></td>
                    </tr>
                    <tr>
                        <td>Balance:</td>
                        <td><? echo $balance; ?></td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td><? echo $email; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <br>


        <div id="pdf" class="transhis">
            <div class="widw">
                <div class="titbg"><span>Transaction History</span></div>
    <pre>
        <span><? echo $firstname . " " . $lastname; ?></span>
        IBAN: <span><? echo $iban; ?></span>
        CurrentBalance: <span><? echo $balance; ?></span>
    </pre>
                <table>

                    <tr>
                        <th>Transaction Date</th>
                        <th>Source Name</th>
                        <th>Source IBAN</th>
                        <th>Destination Name</th>
                        <th>Destination IBAN</th>
                        <th>Amount</th>
                        <th>Description</th>
                    </tr>
                    <?php
                    $dataArray=TransactionHistory::GetTransactionHistory($iban) ;
                    foreach($dataArray as &$element)
                    {
                    ?>
                    <tr>
                        <td><?php echo $element->date; ?>ate</td>
                        <td><?php echo $element->sourceName; ?></td>
                        <td><?php echo $element->sourceIBAN; ?></td>
                        <td><?php echo $element->receiverName; ?></td>
                        <td><?php echo $element->receiverIBAN; ?></td>
                        <td><?php echo $element->amount; ?></td>
                        <td><?php echo $element->description; ?></td>
                    </tr>
                    <?php
                    } ?>
                </table>
                <!-- Don't know how to use it ? And the form didn't appear!-->
                <form class="viewtrans"  action="../Controller/DownloadHistory.php"><input class="barbtn" type="submit"
                                               value="View/Export"/>
                </form>
            </div>
        </div>
    </div>

    </body>
    </html>

<?php
}

?>
