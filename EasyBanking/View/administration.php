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
                <li><a href="toBeDeleted/administration.html">Request Administration</a></li>
                <li><a href="./customerdb.html">Customer DB</a></li>
            </ul>
        </div>
    </div>

    <div class="mainpart">

        <div class="banner">
            <h1>Requests To Be Handled!</h1>
        </div>
        <div class="regireq">
            <div class="widw">
                <div class="titbg"><span>Registration Requests</span></div>
                <table>
                    <form action="../Controller/RequestCtrl.php" method="post">
                        <input type="hidden" name="reqtype" value="registration">
                        <tr>
                            <th>Registeration Date</th>
                            <th>Email</th>
                            <th>Approval</th>
                        </tr>
                        <?php
                        // here get the registration request list to be handled and iterate and show in the table
                        ?>
                        <tr>
                            <td>20.01.2015</td>
                            <td>222@gmail.com</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="email" value="">
                        </tr>
                        <tr>
                            <td>20.01.2015</td>
                            <td>222@gmail.com</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="email" value="">
                        </tr>
                        <tr>
                            <td>20.01.2015</td>
                            <td>222@gmail.com</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="email" value="">
                        </tr>
                        <form>
                </table>
            </div>
        </div>
        <br>


        <div class="transreq">
            <div class="widw">
                <div class="titbg"><span>Transaction Requests</span></div>
                <table>
                    <form action="../Controller/RequestCtrl.php" method="post">
                        <input type="hidden" name="reqtype" value="transaction"/>
                        <tr>
                            <th>Transaction Date</th>
                            <th>IBAN</th>
                            <th>Amount</th>
                            <th>Approval</th>
                        </tr>

                        <?php
                        // here get the registration request list to be handled and iterate and show in the table
                        ?>
                        <tr>
                            <td>20.01.2015</td>
                            <td>xxxx xxxx</td>
                            <td>+xxx.xxx</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="iban" value="">
                        </tr>
                        <tr>
                            <td>20.01.2015</td>
                            <td>xxxx xxxx</td>
                            <td>+xxx.xxx</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="iban" value="">
                        </tr>
                        <tr>
                            <td>20.01.2015</td>
                            <td>xxxx xxxx</td>
                            <td>+xxx.xxx</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="iban" value="">
                        </tr>
                        <tr>
                            <td>20.01.2015</td>
                            <td>xxxx xxxx</td>
                            <td>+xxx.xxx</td>
                            <td><input class="barbtn" type="submit" value="confirm"/></td>
                            <input type="hidden" name="iban" value="">
                        </tr>
                    </form>
                </table>
            </div>
        </div>
    </div>

    </body>
    </html>


<?php
//}
?>