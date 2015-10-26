<?php
session_start();
unset( $_SESSION['currentUser']);
header("Location:../View/index.php");
exit();
