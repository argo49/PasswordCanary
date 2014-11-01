<?php

require("includes/mysql.php");

$dbh = connect();


$STH = $dbh->prepare("SELECT * FROM `counts`");
$STH->execute();
$result = $STH->fetchAll();
echo json_encode($result);





?>