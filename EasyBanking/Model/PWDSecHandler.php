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
            return self::sendTokenURL($id, $email, $urlprefix);
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
        $mailText = "Hello,\nPlease click the URL to change your password\n\n" . $urlprefix . "/" . id . "/" . $token . "\nNote it is valid ONLY within 5mins!";
        echo 'Mail content: ' . $mailText;
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
            //check whether should the account been unlocked ???
            $dbHandler->execQuery("UPDATE ".self::$table." SET failed_login_attempt=0 WHERE failed_login_attempt>3 and locked_until-now()<0 and id='" . $id . "';");
            $res = $dbHandler->execQuery("SELECT locked_until FROM " . self::$table . " WHERE failed_login_attempt>3 and locked_until-now()>0 and id='" . $id . "' ;");
            $row = $res->fetch_assoc();
            if ($row == NULL) {
                echo "Not Locked";
                return false;
            } else {
                $locked_until = $row['locked_until'];
                return $locked_until;
            }
        }
    }


    /**
     * @param $email
     */
    public static function incFailedAtmp($email)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT id FROM users WHERE mail_address='" . $email . "';";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        if ($row == NULL) {
            echo "Wrong User email address.";
        } else {
            $id = $row['id'];
            $dbHandler->execQuery("UPDATE ".self::$table." SET failed_login_attempt=failed_login_attempt+1 WHERE id='" . $id . "';");

            //check whether the user has exceed the login limit but not locked yet
            $res = $dbHandler->execQuery("SELECT * FROM " . self::$table . " WHERE failed_login_attempt>3 and  locked_until-now()<0 and id='" . $id . "' ;");
            $row = $res->fetch_assoc();
            if ($row != NULL) {
                echo 'locking user ' . $id . ' who has failed login  ' . $row['failed_login_attempt'] . 'times';
                $dbHandler->execQuery("UPDATE ".self::$table." SET locked_until=DATE_ADD(now(), INTERVAL 10 MINUTE)  WHERE failed_login_attempt>3 and id='" . $id . "';");

            }

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
            $dbHandler->execQuery("UPDATE ".self::$table." SET failed_login_attempt=0 WHERE id='" . $id . "';");
        }
    }
}

?>

