<?php
include_once "DatabaseHandler.php";
include_once "Customer.php";
include_once "Employee.php";
abstract class Account {
    public $firstName;
    public $lastName;
    public $email;
    const loginErrorMsg = "ERROR: Wrong password or Account doesn't exist!\n";

    // If login is successfull, an instance of class Customer or Employee is returned.
    // Otherwise, the NULL reference is returned.
    public static function login($email, $password) {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        $row = $res->fetch_assoc();
        if($row == NULL) {
            return self::loginErrorMsg;
        }
        $storedPW = $row['password'];
        $employeeAccount = $row['isEmployee'];
        $accountApproved = $row['approved'];

        if($storedPW != self::calculateHash($password)) {
            return self::loginErrorMsg;
        }

        if($accountApproved == FALSE) {
            return "ERROR: Account not approved yet!\n";
        }

        if($employeeAccount == TRUE) {
            return new Employee($row['first_name'], $row['last_name'], $row['mail_address']);
        } else {
            return new Customer($row['first_name'], $row['last_name'], $row['mail_address'], $row['id']);
        }
    }

    //abstract public function logout(); //TODO: Validate whether this method is necessary

    // Returns TRUE if the registration was successfull
    // Otherwise returns a String containing an error message
    public static function register($email, $firstName, $lastName, $password, $isEmployee) {
        $dbHandler = DatabaseHandler::getInstance();
        $res = $dbHandler->execQuery("SELECT * FROM users WHERE mail_address='" . $email . "';");
        if($res->fetch_assoc() != NULL) {
            return "ERROR: An account with that email has already been created!\n";
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
            return "ERROR: New User couldn't be stored in Database!\n";
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
            return "ERROR: Account entry for new user couldn't be created!\n";
        }

        return TRUE;
    }

    private static function calculateHash($input) {
        return hash("sha256", $input, FALSE);
    }
}
?>
