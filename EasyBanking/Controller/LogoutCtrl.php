<?php
session_start();

if(!$_SESSION['isEmployee']){
	unset( $_SESSION['iban']);
unset( $_SESSION['balance']);

}
unset( $_SESSION['email']);
unset( $_SESSION['firstname']);
unset( $_SESSION['lastname']);
unset( $_SESSION['isEmployee']);

header("Location:../View/index.php");
exit();
