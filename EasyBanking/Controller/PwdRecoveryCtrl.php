<?php
session_start();
require_once('../Model/Account.php');

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    //check whether email exists if so do
    //generate token and update db
    //send token via email
    //check token and update pwd
    //else
    //do nothing ! no error ms in case the user account enumeration
}


