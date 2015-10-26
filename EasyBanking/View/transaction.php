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
    <title>EasyBanking-Transaction</title>
</head>
<body>


<div class="topbar">
    <div class="logo">EasyBanking</div>
    <div id="logout">
        <label><? echo $firstname . " " . $lastname ?></label>

        <form action="./index.html">
            <input type="submit" class="barbtn" value="Log Out"/>
        </form>
    </div>
</div>


<div class="menubar">
    <div class="mainmenu">

        <ul>
            <li><a href="./account.html">Personal Bank Account</a></li>
            <li><a href="./transaction.html">Online Transaction</a></li>
        </ul>
    </div>
</div>

<div class="mainpart">
    <div class="banner">
        <h1>Welcome  <? echo $firstname . " " . $lastname ?>!</h1>
    </div>
    <div class="widw">
        <div class="titbg"><span>Single Transaction</span></div>
        <!-- Single Transaction Form -->
        <form class="transfer" action="../Controller/TransactionCtrl.php" method="post">
            <ul>
                <li><label>IBAN</label><br>
                    <input type="text" name="iban" required/></li>
                <li><label>Amount</label><br>
                    <input type="text" name="amount" required/>
                </li>
                <li><label>TAN ID</label><br>
                    <input type="text" name="tid" required/></li>
                <li>
                <li><label>TAN</label><br>
                    <input type="text" name="tan" required/></li>
            </ul>
            <input type="hidden" name="type" value="single">
            <input class="barbtn" type="submit" value="Submit"/>
        </form>
    </div>


    <div class="widw">
        <div class="titbg"><span>Multiple Transaction</span></div>
        <!-- Multiple Transaction Form not implemented yet! -->
        <form class="transfer" action="../Controller/TransactionCtrl.php" method="post">
            <ul>
                <li><label>TAN ID</label><br>
                    <input type="text" name="tid" required/></li>
                <li>
                <li><label>TAN</label><br>
                    <input type="text" name="tan" required/></li>
                <li>
                    <label>Upload your TAN batch file</label><br>
                    <!-- see class fileToUpload usage in php in http://www.w3schools.com/php/php_file_upload.asp  -->
                    <input class="fileToUpload" type="file" name="batchfile" required/>
                </li>
            </ul>
            <input type="hidden" name="type" value="multiple">

            <input class="barbtn" type="submit" value="Submit"/>
        </form>

    </div>
</div>
</body>
</html>


<?php

//}

?>