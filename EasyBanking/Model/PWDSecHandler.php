<?php
include_once "DatabaseHandler.php";

class PWDSecHandler
{
    static private $tokenLength = 10;
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
            $userID = $row['id'];
            //send the token to the email address
            return self::sendTokenURL($userID, $email, $urlprefix);
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

        //update the token in DB
        $dbHandler->execQuery("UPDATE " . self::$table . " SET token='" . $token . "', valid_until=DATE_ADD(now(), INTERVAL 5 MINUTE)  WHERE user_id='" . $id . "';");
        $mailText = "Hello,\nPlease click the URL to change your password\n\n" . $urlprefix . "/" . id . "/" . $token . "\nIt is valid within 5mins";
        return mail($email, "Token", $mailText, "From: EasyBanking");
    }


    //check whether the user has the valid token
    public static function authenticateToken($id, $token)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $query = "SELECT token FROM " . self::$table . " WHERE id='" . $id . "' and valid_until-now()>0 ;";
        $res = $dbHandler->execQuery($query);
        $row = $res->fetch_assoc();
        $expected = $row['token'];
        return $token == $expected;

    }


    public static function resetPwd($id, $passwd)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $hasedpwd = hash("sha256", $passwd, FALSE);
        $dbHandler->execQuery("UPDATE users SET password='" . $hasedpwd . "' WHERE user_id='" . $id . "';");

    }







    //lock the user after 3 failed login attempt for 10mins
    public static function lock($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET locked_until=DATE_ADD(now(), INTERVAL 10 MINUTE)  WHERE failed_login_attempt>3 and user_id='" . $id . "';");
    }

    public static function unlock($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET failed_login_attempt=0 WHERE user_id='" . $id . "';");
    }

    public static function incFailedAtmp($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET failed_login_attempt=failed_login_attempt+1 WHERE user_id='" . $id . "';");
    }

    //check whether the user has been locked
    public static function isLocked($id)
    {
        $time = DatabaseHandler::getInstance()->execQuery("SELECT locked_until FROM " . self::$table . " WHERE failed_login_attempt>3 and id='" . $id . "' ;");
        return $time - time() > 0 ? true : false;
    }
}

?>

