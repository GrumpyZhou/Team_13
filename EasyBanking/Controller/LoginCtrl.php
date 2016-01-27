<?php
session_start();
session_regenerate_id(TRUE);
require_once('../Model/Account.php');
require_once('../Model/PWDSecHandler.php');


if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = htmlentities( strip_tags ($_POST['email']));
    $password = htmlentities( strip_tags ($_POST['password']));
    $isLocked = PWDSecHandler::isLocked($email);

    if ($isLocked === NULL) {
        echo 'Invalid E-Mail Address or Password.';
        echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
    } else {
        if (is_string($isLocked)) {
            //if the user has been locked and the locked_until is returned.
            echo 'You have been locked until ' . $isLocked . '!<br>';
            echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
        } else {
            $user = Account::login($email, $password);
            if (is_string($user)) {
                echo 'Invalid E-Mail Address or Password.';
                echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
                PWDSecHandler::incFailedAtmp($email);
            } else {
                PWDSecHandler::clearLock($email);
                $_SESSION['email'] = $user->email;
                $_SESSION['firstname'] = $user->firstName;
                $_SESSION['lastname'] = $user->lastName;
                if ($user instanceof Customer) {
                    $_SESSION['isEmployee'] = false;
                    $_SESSION['iban'] = $user->IBAN;
                    $_SESSION['balance'] = $user->balance;
                    header("Location:../View/account.php");
                } else {
                    $_SESSION['isEmployee'] = true;
                    header("Location:../View/administration.php");
                }
            }
        }
    }
}
