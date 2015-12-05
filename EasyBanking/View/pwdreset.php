<?php
session_start();
if (!isset($_SESSION['resetpwd_user'])){
    header("Location:../View/index.php");
} else {
    $id = $_SESSION['resetpwd_user'];

}
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">

    <script type="text/javascript"
            src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
    <link rel="stylesheet" type="text/css" href="./main.css">
    <title>EasyBanking-Welcome</title>

<body>


<div class="topbar">
    <div class="logo">EasyBanking</div>
</div>
<div class="clear"/>
<div class="mainpart">
    <div class="banner"><h1>Manage your bank account with EasyBanking!</h1>
        <span>Convenient access, Easy operation, Clean layout </span>
    </div>
    <div class="clear"/>

    <div class="widw">
        <div class="titbg"><span>Reset Password</span></div>
        <form class="pwdrec" action="../Controller/PwdRecoveryCtrl.php" method="post">
            <label id="llong">New Password</label> <input class='pwdinput' type="password" name="npwd" required/>
            <input id='pwdbtn'class="barbtn" type="submit" value="Confirm"/>
            <input type="hidden" name="uid" value="<? echo $id; ?>"/>
        </form>
    </div>
</div>

<br>
<br>

</body>
</html>

