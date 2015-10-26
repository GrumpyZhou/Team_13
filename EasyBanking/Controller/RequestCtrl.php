<?php

if (isset($_POST['reqtype'])) {
    $reqtype = $_POST['reqtype'];
    echo $reqtype;

    if ($reqtype == 'registration') {
        $email = $_POST['email'];
        echo  "approve registration of  ".$email;

        // .... call function for dealing approval

    } elseif ($reqtype == 'transaction') {

        //$trid = $_POST['trid']; //not implemented yet
        echo  "approve transaction of  ?? we need a transaction id";

        //call function for dealing  transaction
    }
}

//return to the administration page
header("Location:../View/administration.php");
