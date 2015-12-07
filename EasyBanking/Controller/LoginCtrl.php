<?php
session_start();
require_once('../Model/Account.php');
require_once('../Model/PWDSecHandler.php');

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

//    //check whether the user should be lock.
//    $isLocked = PWDSecHandler::isLocked($email);
//
//    if ($isLocked == NULL) {
//        echo 'Invalid email address';
//        echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
//    } else {
//        if (is_string($isLocked)) {
//            echo 'You have been locked until' . $isLocked . 'because of three failed attempt login.';
//            echo "<a href='../View/index.php'>Click here to go back to the HomePage</a>";
//            header("Location:../View/index.php");
//        } else {
            $user = Account::login($email, $password);
//            if (is_string($user)) {
//                echo "Error message: " . $user;
//                PWDSecHandler::incFailedAtmp($email);
//            } else {
//
//                PWDSecHandler::clearLock($email);
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
//            }
//        }
//    }
}


