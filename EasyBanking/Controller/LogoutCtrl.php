<?php
session_start();
session_destroy();
session_unset();
session_start();
session_regenerate_id(TRUE); 

header("Location:../View/index.php");
exit();
