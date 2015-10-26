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
    <div class="login">
        <form action="../Controller/LoginCtrl.php" method="post">
            <label>Email</label><input type="text" name="email" required/>
            <label>Password</label><input type="text" name="password" required/>
            <input type="submit" class="barbtn" value="Log In"/>
        </form>
        <form action="./administration.html">
            <input type="submit" class="barbtn" value="(Adm)LogIn"/>
        </form>
    </div>
</div>

<div class="clear"/>

<div class="mainpart">
    <div class="banner"><h1>Manage your bank account with EasyBanking!</h1>
        <span>Convenient access, Easy operation, Clean layout </span>
    </div>
    <div class="descript">No Accout Yet? Register Now!</div>
    <div class="widw">
        <div class="titbg"><span>Transfer Money</span></div>
        <form class="register" action="../Controller/RegisterCtrl.php" method="post">
            <table>
                <tr class="tt">
                    <td>First Name</td>
                    <td>Last Name</td>
                </tr>
                <tr>
                    <td><input type="text" name="fname" required/></td>
                    <td><input type="text" name="lname" required/></td>
                <tr>
                    <td>Email Address</td>
                    <td>Password</td>
                </tr>
                <tr>
                    <td><input type="text" name="email" required/></td>
                    <td><input type="text" name="password" required/></td>
                </tr>
                <tr>
                    <td>Are you an employee?</td>
                    <td id="checkbox">
                        <input type="checkbox" name="yes"/><label>Yes</label>
                        <input type="checkbox" name="no"/><label>No</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input class="barbtn" type="submit" value="Register"/></td>
                </tr>
            </table>
        </form>
    </div>
</div>


</body>
</html>

