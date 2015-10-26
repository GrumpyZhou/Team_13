<?php
session_start();
//require_once('../Model/Customer.php');

//Commented during the test...
//
//if (!isset($_SESSION['currentUser'])) {
//
//    header("Location:../View/index.php");
//} else {
//    $user = $_SESSION['currentUser'];


$firstname = "Qunjie";//$user->getFirstName();
$lastname = "Zhou";//$user->getLastName();
$iban = "xxx xxx";
$balance = "123.123";
$email = "zhou@123.com";

?>
    <!DOCTYPE html>
    <html>
    <head lang="en">
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="./main.css">
        <script type="text/javascript" src="./jspdf/jspdf.js"></script>
        <script type="text/javascript" src="./jspdf/plugins/split_text_to_size.js"></script>
        <script type="text/javascript" src="./jspdf/plugins/from_html.js"></script>
        <script type="text/javascript" src="./jspdf/plugins/standard_fonts_metrics.js"></script>
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
                <li><a href="toBeDeleted/account.html">Personal Bank Account</a></li>
                <li><a href="toBeDeleted/transaction.html">Online Transaction</a></li>
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

                    // To be implemented: get the transaction history of this user. iterate the data and show in the table.
                    ?>
                    <tr>
                        <td>20.01.2015</td>
                        <td>xxxx xxxx</td>
                        <td>+xxx.xxx</td>
                    </tr>
                    <tr>
                        <td>20.01.2015</td>
                        <td>xxxx xxxx</td>
                        <td>+xxx.xxx</td>
                    </tr>
                    <tr>
                        <td>20.01.2015</td>
                        <td>xxxx xxxx</td>
                        <td>+xxx.xxx</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
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

//}

?>