<?php
session_start();
//require_once('../Model/TransactionHandler.php'); // lead to server error
if (!isset($_SESSION['isEmployee'])||$_SESSION['isEmployee']) {
   header("Location:../View/index.php");
} else {
	
$firstname =  $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$iban =$_SESSION['iban'];
$balance = "123.12";
$email = $_SESSION['email']; 
?>
    <!DOCTYPE html>
    <html>
    <head lang="en">
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="./main.css">
        <title>EasyBanking-Account</title>
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
                        <th>IBAN</th>
                        <th>Amount</th>

                    </tr>
                    <?php
                 $dataArray=array("date"=>"20.01.2015","iban"=>"9","amount"=>"+123");
                     //$data=TransactionHandler::GetTransactionHistory() ;//where to get the user_Id??
                    // To be implemented: get the transaction history of this user. iterate the data and show in the table.  
                   
                    ?>
                    <tr>
                        <td><?php echo $dataArray["date"]; ?></td>
                        <td><?php echo $dataArray["iban"]; ?></td>
                        <td><?php echo $dataArray["amount"]; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo $dataArray["date"]; ?></td>
                        <td><?php echo $dataArray["iban"]; ?></td>
                        <td><?php echo $dataArray["amount"]; ?></td>
                    </tr>
                </table>
                <!-- The creation can be either by js or by php, to be determined-->
                <form class="viewtrans"  action="./transhistory.html"><input class="barbtn" type="submit"
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
