<?php
include_once "DatabaseHandler.php";
include_once "Customer.php";
include_once "Employee.php";
abstract class Account {
    protected $firstName;
    protected $lastName;
    protected $email;
    const loginErrorMsg = "ERROR: Wrong password or Account doesn't exist!\n";

    // If login is successfull, an instance of class Customer or Employee is returned.
    // Otherwise, the NULL reference is returned.
    public static function login($email, $password) {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        $row = $res->fetch_assoc();
        if($row == NULL) {
            echo self::loginErrorMsg;
            return NULL;
        }
        $storedPW = $row['password'];
        $employeeAccount = $row['isEmployee'];
        $accountApproved = $row['approved'];

        if($accountApproved == FALSE) {
            echo "ERROR: Account not approved yet!\n";
            return NULL;
        }

        if($storedPW != self::calculateHash($password)) {
            echo self::loginErrorMsg;
            return NULL;
        }

        if($employeeAccount == TRUE) {
            return new Employee($row['first_name'], $row['last_name'], $row['mail_address']);
        } else {
            return new Customer($row['first_name'], $row['last_name'], $row['mail_address']);
        }
    }

    //abstract public function logout(); //TODO: Validate whether this method is necessary

    // Returns a BOOL indicating wheter the registration was successfull (TRUE) or not (FALSE)
    public static function register($email, $firstName, $lastName, $password, $isEmployee) {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        if($res->fetch_assoc() != NULL) {
            echo "ERROR: An account with that email has already been created!\n";
            return FALSE;
        }

        $query = "INSERT INTO users (first_name, last_name, isEmployee, approved, mail_address, password)";
        $query .= " VALUES ('" . $firstName . "', '" . $lastName . "', ";
        if($isEmployee) {
            $query .= "TRUE, FALSE, ";
        } else {
            $query .= "FALSE, FALSE, ";
        }
        $query .= "'" . $email . "', ";
        $query .= "'" . self::calculateHash($password) . "');";

        $rc = $dbHandler->execQuery($query);
        if($rc != TRUE) {
            echo "ERROR: New User couldn't be stored in Database!\n";
            return FALSE;
        }
        // If the new Account is for an employee, we are already done here.
        if($isEmployee) {
            return TRUE;
        }

        // Get id of newly created user
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        $userID = $row['id'];

        // Add new account for the customer
        $query = "INSERT INTO accounts VALUES (" . $userID . ", 0);";
        if($dbHandler->execQuery($query) != TRUE) {
            echo "ERROR: Account entry for new user couldn't be created!\n";
            return FALSE;
        }

        return TRUE;
    }

    private static function calculateHash($input) {
	echo "HASH: " . hash("sha256", $input, FALSE) . "\n";
        return hash("sha256", $input, FALSE);
    }
}
?>
