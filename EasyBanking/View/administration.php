<?php
session_start();
require_once('../Model/RequestHandler.php');

if (!isset($_SESSION['isEmployee'])||!$_SESSION['isEmployee']) {
   header("Location:../View/index.php");
} else {
    //echo "admin page";
//can not get these fields !!
$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
echo $firstname;
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
            <label><? echo "????".$firstname . " " . $lastname; ?></label>
            <form action="../Controller/LogoutCtrl.php" method="post">
                <input type="submit" class="barbtn" value="Log Out"/>
            </form>
        </div>
    </div>

    <div class="menubar">
        <div class="mainmenu">
            <ul>
                <li><a href="./administration.php">Request Administration</a></li>
                <li><a href="./customerdb.php">Customer DB</a></li>
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
                        <tr>
                            <th>Registeration Date</th>
                            <th>Email</th>
                            <th>Approval</th>
                        </tr>
                        <?php
                        // here get the registration request list to be handled and iterate and show in the table
                      //true: get registration requests
                      $registerRequest=RequestHandler::getOpenRequests(true);
                      foreach($registerRequest as $request){
                          $email=$request->mail;
                          $date=$request->date;
                          $id=$request->id;
                        ?>
                        <tr>
                            <td><?php echo $date;?></td>
                            <td><?php echo $email;?></td>
                            <td>
                                <form action="../Controller/RequestCtrl.php" method="post">
                                   <input type="hidden" name="reqtype" value="registration"/>
                                   <input type="hidden" name="id" value="<?php echo $id;?>"/>
                                   <input class="barbtn" name="action" type="submit" value="Deny"/>
                                   <input class="barbtn" name="action" type="submit" value="Accept"/>                                  <input type="hidden" name="email" value="<?php $email;?>" />
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                </table>
            </div>
        </div>
        <br>


        <div class="transreq">
            <div class="widw">
                <div class="titbg"><span>Transaction Requests</span></div>
                <table>
                        <tr>
                            <th>Transaction Date</th>
                            <th>IBAN</th>
                            <th>Amount</th>
                            <th>Approval</th>
                        </tr>

                        <?php
                        // here get the transaction request list to be handled and iterate and show in the table
                        //false: get transaction requests
                      $registerRequest=RequestHandler::getOpenRequests(false);
                      foreach($registerRequest as $request){
                          $sender=$request->senderId;
                          $date=$request->date;
                          $amount=$request->amount;
                          $id=$request->transactionId;
?>

                        <tr>
                            <td><?php echo $date;?></td>
                            <td><?php echo $sender;?></td>
                            <td><?php echo $amount;?></td>
                            <td>
                                <form action="../Controller/RequestCtrl.php" method="post">
                                   <input type="hidden" name="reqtype" value="transaction"/>
                                   <input type="hidden" name="id" value="<?php echo $id;?>"/>
                                   <input class="barbtn" name="action" type="submit" value="Deny"/>
                                   <input class="barbtn" name="action" type="submit" value="Accept"/>
                                   <input type="hidden" name="sender" value="<?php $sender;?>" />
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                </table>
            </div>
        </div>
    </div>

    </body>
    </html>


<?php
}
?>
