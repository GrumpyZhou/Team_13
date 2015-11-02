<?php
include_once "../Model/DatabaseHandler.php";

//get the customers
$dbHandler = DatabaseHandler::getInstance();
$data = $dbHandler->execQuery("SELECT * FROM users INNER JOIN accounts ON users.id = accounts.user_id;");
echo "<table>
<tr>
<th>IBAN</th>
<th>Balance</th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
<th>Registration date</th>
</tr>";

while($row = $data->fetch_assoc())
{
    echo "<tr>";
    echo "<td>" .$row['id']. "</td>";
    echo "<td>" .$row['balance']. "</td>";
    echo "<td>" .$row['first_name']. "</td>";
    echo "<td>" .$row['last_name']. "</td>"
    echo "<td>" .$row['mail_address']. "</td>";
    echo "<td>" .$row['registration_date']. "</td>";
    echo 
    "<td>
        <form action='DownloadHistory.php' method='post'>
            <input type='hidden' name='iban' value=" .$row['id']. " /> 
            <input type='submit' value='Export History'/>
        </form>
    </td>";
}
echo "</table>";
?>
