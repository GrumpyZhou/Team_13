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
            <label><? echo $firstname . " " . $lastname; ?></label>
            <form action="../Controller/LogoutCtrl.php" method="post">
                <input type="submit" class="barbtn" value="Log Out"/>
            </form>
        </div>
    </div>

    <div class="menubar">
        <div class="mainmenu">
            <ul>
                <li><a href="./administration.php">Request Administration</a></li>
                <li><a href="../Controller/CustomerDBCtrl.php">Customer DB</a></li>
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
                                   <input type="text" name="startBalance" />
                                   <input type="hidden" name="reqtype" value="registration"/>
                                   <input type="hidden" name="id" value="<?php echo $id;?>"/>
                                   <input class="barbtn" name="action" type="submit" value="Deny"/>
                                   <input class="barbtn" name="action" type="submit" value="Accept"/>
                                   <input type="hidden" name="email" value="<?php $email;?>" />
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
                            <th>Source Name</th>
                            <th>Source IBAN</th>
                            <th>Destination Name</th>
                            <th>Destination IBAN</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Approval</th>
                        </tr>

                        <?php
                        // here get the transaction request list to be handled and iterate and show in the table
                        //false: get transaction requests
                      $registerRequest=RequestHandler::getOpenRequests(false);
                      foreach($registerRequest as $request){
                          $senderId=$request->senderId;
                          $senderName=$request->senderName;
                          $destId=$request->destinationId;
                          $destName=$request->destinationName;
                          $date=$request->date;
                          $amount=$request->amount;
                          $id=$request->transactionId;
                          $desc=$request->description;
?>

                        <tr>
                            <td><?php echo $date;?></td>
                            <td><?php echo $senderName;?></td>
                            <td><?php echo $senderId;?></td>
                            <td><?php echo $destName;?></td>
                            <td><?php echo $destId;?></td>
                            <td><?php echo $amount;?></td>
                            <td><?php echo $desc; ?></td>
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
