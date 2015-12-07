<?php
include_once "DatabaseHandler.php";

class PWDSecHandler
{
    static private $table = "passwdsec";

    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    /*
     * Section 1: functions for handle the password recovery
     */

    /**
     * @param $email
     * @param $urlprefix
     * @return bool
     */
    public static function  handlePWDRecovery($email, $urlprefix)
    {

        //check whether the user email address exists
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        if ($row == NULL) {
            echo "Wrong User email address.";
            return false;
        } else {
            $id = $row['id'];
            //send the token to the email address
            self::sendTokenURL($id, $email, $urlprefix);
            return true;
        }

    }

    /**
     * @param $id
     * @param $email
     * @param $urlprefix
     * @return bool
     */
    private function sendTokenURL($id, $email, $urlprefix)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $token = md5(uniqid(1, true)) . md5(time());

        //update the token in DB and give it 5-min valid duration
        $dbHandler->execQuery("UPDATE " . self::$table . " SET token='" . $token . "', valid_until=DATE_ADD(now(), INTERVAL 5 MINUTE)  WHERE id='" . $id . "';");
        $mailText = "Hello,\nPlease click the URL to change your password\n\n" . $urlprefix . "/" . $id . "/" . $token . "\nNote it is valid ONLY within 5mins!";
        return mail($email, "Token", $mailText, "From: EasyBanking");
    }

    /**
     * @param $id
     * @param $token
     * @return bool
     */
    public static function authenticateToken($id, $token)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT token FROM " . self::$table . " WHERE id='" . $id . "' and valid_until-now()>0 ;";  //the token is still valid
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        $expected = $row['token'];
        return $token == $expected;
    }


    /**
     * @param $id
     * @param $passwd
     */
    public static function resetPwd($id, $passwd)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $hasedpwd = hash("sha256", $passwd, FALSE);
        $dbHandler->execQuery("UPDATE users SET password='" . $hasedpwd . "' WHERE id='" . $id . "';");
    }


    /*
     * Section 2: functions for handle the login lock out
     */

    
    /**
     * @param $email
     * @return bool|null
     */
    public static function isLocked($email)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        if ($row == NULL) {
            echo "Wrong User email address.";
            return NULL;
        } else {
            $id = $row['id'];
            //check whether she is still locked
            $res = $dbHandler->execQuery("SELECT locked_until FROM " . self::$table . " WHERE locked_until-now()>0 and id='" . $id . "' ;");
            $row = $res->fetch_assoc();
            if ($row == NULL) {
                return false;
            } else {
                $locked_until = $row['locked_until'];
                return $locked_until;
            }
        }
    }

    /**
     * @param $email
     * @return string
     */
    public static function incFailedAtmp($email)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        $id = $row['id'];

        //check whether the user has exceed the login limit but not locked yet
        $res = $dbHandler->execQuery("SELECT * FROM " . self::$table . " WHERE failed_login_attempt>=2 and  locked_until-now()<0 and id='" . $id . "' ;");
        $row = $res->fetch_assoc();

        if ($row != NULL) {
            //lock the user and recount the failed attempt
            $dbHandler->execQuery("UPDATE " . self::$table . " SET failed_login_attempt=0, locked_until=DATE_ADD(now(), INTERVAL 10 MINUTE)  WHERE failed_login_attempt>=2 and id='" . $id . "';");
            return 'You have failed to login for 3 times. You are now locked!';
        } else {
            //just increase the failed_login_attempt
            $dbHandler->execQuery("UPDATE " . self::$table . " SET failed_login_attempt=failed_login_attempt+1 WHERE id='" . $id . "';");//increase failed attempt
            return 'Login Failed.';
        }
    }

    /**
     * @param $email
     */
    public static function clearLock($email)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        if ($row != NULL) {
            $id = $row['id'];
            $dbHandler->execQuery("UPDATE " . self::$table . " SET failed_login_attempt=0 WHERE id='" . $id . "';");
        }
    }
}

?>

