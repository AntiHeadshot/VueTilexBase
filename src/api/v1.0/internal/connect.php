<?php 
    if(false && isset($_SESSION['dbconfig']) && $_SESSION['dbconfig'] != null)
        $dbconfig = $_SESSION['dbconfig'];
    else{
        $dbconfig = parse_ini_file("../../../config/db.ini",true);
        $_SESSION['dbconfig'] = $dbconfig;
    }

    $pdo = new PDO($dbconfig["login"]["connection"], $dbconfig["login"]["user"], $dbconfig["login"]["password"]);
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
?>