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

    //$id = user id
    private function sendToken($id, $email)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $token = md5(uniqid($id, true));

        //update the token in DB
        $dbHandler->execQuery("UPDATE " . self::$table . " SET token='" . $token . "', valid_until=DATE_ADD(now(), INTERVAL 5 MINUTE)  WHERE user_id='" . $id . "';");
        $mailText = "Hello,\nPlease use this token to change your password\n\n" . $token . "\nIt is valid within 5mins";
        mail($email, "Token", $mailText, "From: EasyBanking");
    }

    //check whether the user has the valid token
    public function authenticateToken($id, $token)
    {
        $expected = DatabaseHandler::getInstance()->execQuery("SELECT token FROM " . self::$table . " WHERE id='" . $id . "' and valid_until-now()>0 ;");
        return $token == $expected ? true : false;
    }


    public function resetPwd($id, $passwd)
    {
        $dbHandler = DatabaseHandler::getInstance();
        $hasedpwd = hash("sha256", $passwd, FALSE);
        $dbHandler->execQuery("UPDATE users SET password='" . $hasedpwd . "' WHERE user_id='" . $id . "';");

    }

    //lock the user after 3 failed login attempt for 10mins
    public function lock($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET locked_until=DATE_ADD(now(), INTERVAL 10 MINUTE)  WHERE failed_login_attempt>3 and user_id='" . $id . "';");
    }

    public function unlock($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET failed_login_attempt=0 WHERE user_id='" . $id . "';");
    }

    public function incFailedAtmp($id)
    {
        DatabaseHandler::getInstance()->execQuery("UPDATE users SET failed_login_attempt=failed_login_attempt+1 WHERE user_id='" . $id . "';");
    }

    //check whether the user has been locked
    public function isLocked($id)
    {
        $time = DatabaseHandler::getInstance()->execQuery("SELECT locked_until FROM " . self::$table . " WHERE failed_login_attempt>3 and id='" . $id . "' ;");
        return $time - time() > 0 ? true : false;
    }
}

?>

