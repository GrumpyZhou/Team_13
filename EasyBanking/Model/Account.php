<?php
include_once "DatabaseHandler.php";
include_once "Customer.php";
include_once "Employee.php";

abstract class Account
{
    public $firstName;
    public $lastName;
    public $email;
    const loginErrorMsg = "ERROR: Wrong password or Account doesn't exist!\n";

    // If login is successfull, an instance of class Customer or Employee is returned.
    // Otherwise, the NULL reference is returned.
    public static function login($email, $password)
    {
        $email=mysql_real_escape_string($email);
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        $row = $res->fetch_assoc();
        if ($row == NULL) {
            return self::loginErrorMsg;
        }
        $storedPW = $row['password'];
        $employeeAccount = $row['isEmployee'];
        $accountApproved = $row['approved'];

        if ($storedPW != self::calculateHash($password)) {
            return self::loginErrorMsg;
        }

        if ($accountApproved == FALSE) {
            return "ERROR: Account not approved yet!\n";
        }

        if ($employeeAccount) {
            return new Employee($row['first_name'], $row['last_name'], $row['mail_address']);
        } else {
            $res2 = $dbHandler->execQuery("SELECT * FROM accounts WHERE user_id='" . $row['id'] . "';");
            $row2 = $res2->fetch_assoc();
            return new Customer($row['first_name'], $row['last_name'], $row['mail_address'], $row['id'], $row2['balance']);
        }
    }

    //abstract public function logout(); //TODO: Validate whether this method is necessary

	public static function CalcPDFPassword($hashedPassword)
	{
		$doubleHash = self::calculateHash($hashedPassword);
		return substr($doubleHash, 0, 10);
	}

    // Returns TRUE if the registration was successfull
    // Otherwise returns a String containing an error message
    public static function register($email, $firstName, $lastName, $password, $isEmployee, $usesSCS)
    {
        $email=mysql_real_escape_string($email);
        $firstName=mysql_real_escape_string($firstName);
        $lastName=mysql_real_escape_string($lastName);

        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        if ($res->fetch_assoc() != NULL) {
            return "ERROR: An account with that email has already been created!\n";
        }

        $query = "INSERT INTO users (first_name, last_name, isEmployee, approved, mail_address, password, uses_scs)";
        $query .= " VALUES ('" . $firstName . "', '" . $lastName . "', ";
        if ($isEmployee) {
            $query .= "TRUE, FALSE, ";
        } else {
            $query .= "FALSE, FALSE, ";
        }
        $query .= "'" . $email . "', ";
        $query .= "'" . self::calculateHash($password) . "', ";

        if ($usesSCS) {
            $query .= "TRUE" . ");";
        } else {
            $query .= "FALSE" . ");";
        }

        $rc = $dbHandler->execQuery($query);
        if ($rc != TRUE) {
            return "ERROR: New User couldn't be stored in Database!\n";
        }

        // Get id of newly created user
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        $userID = $row['id'];

        //Add an entry for the user in passwdsec table

        $query = "INSERT INTO passwdsec VALUES (" . $userID . ", DEFAULT , DEFAULT ,0, DEFAULT)";
        if ($dbHandler->execQuery($query) != TRUE) {
            return "ERROR: New entry in passwdsec for new user couldn't be created!\n";
        }

        // If the new Account is for an employee, we are already done here.
        if ($isEmployee) {
            return TRUE;
        }

        // Add new account for the customer
        $query = "INSERT INTO accounts VALUES (" . $userID . ", 0);";
        if ($dbHandler->execQuery($query) != TRUE) {
            return "ERROR: Account entry for new user couldn't be created!\n";
        }


        //Add SCS row if user chose SCS
        if ($usesSCS) {
            $pin = mt_rand(100000, 999999);
            $pin_string = (string)$pin;
            $query = "INSERT INTO scs VALUES (" . $userID . ", '" . $pin_string . "', 0);";
            if ($dbHandler->execQuery($query) != TRUE) {
                return "ERROR: Account entry for new user couldn't be created!\n";
            }
            return "You have registered successfully!<br><br>Your SCS PIN is <b>" . $pin_string . "</b>!<br>Please remember or save it somewhere <b>NOW</b>. It will not be shown again!";
        }
        else
        {
			return "You have registered successfully!<br>Your PDF password is <b>" . self::CalcPDFPassword(self::calculateHash($password)) . "</b>! Please remember it or save it somewhere <b>NOW</b>. It will not be shown again!";
		}
        return TRUE;
    }

    private static function calculateHash($input)
    {
        return hash("sha256", $input, FALSE);
    }
}

?>

