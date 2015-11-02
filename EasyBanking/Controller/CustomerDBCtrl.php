<?php
include_once "../Model/DatabaseHandler.php";

//get the customers
$dbHandler = DatabaseHandler::getInstance();
$data = $dbHandler->execQuery("SELECT * FROM users WHERE isEmployee='0';");
echo "<table>
<tr>
<th>IBAN</th>
<th>First Name</th>
<th>Last Name</th>
<th>Email</th>
<th>Registration date</th>
</tr>";

while($row = $data->fetch_assoc())
{
    echo "<tr>";
    echo "<td>" .$row['id']. "</td>";
    echo "<td>" .$row['first_name']. "</td>";
    echo "<td>" .$row['last_name']. "</td>";
    echo "<td>" .$row['mail_address']. "</td>";
    echo "<td>" .$row['registration_date']. "</td>";
}
echo "</table>";
?>
