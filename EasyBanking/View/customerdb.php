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
        <label>Employee Zhou</label>
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
        <h1>Customer Search Engine</h1>
    </div>
<div class="search">
<form>
    <input type="text" name="email" placeholder="IBAN" required/>
    <input class="barbtn" type="submit" value="Search">
</form>
    </div>
    <div class="transhis">
        <div class="widw">
            <div class="titbg"><span>Account & Transaction Information</span></div>
    <pre>
        <span>Zhou Qunjie</span>
        IBAN: <span>xxxx xxxx</span>
        CurrentBalance: <span>xxxx.xx</span>
    </pre>
            <table>

                <tr>
                    <th>Transaction Date</th>
                    <th>IBAN</th>
                    <th>Amount</th>

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


            <form class="viewtrans" action="transhistory.html" target="_blank"><input class="barbtn" type="submit"
                                                                                      value="View/Export"/>
            </form>
        </div>
    </div>
</div>

</body>
</html>