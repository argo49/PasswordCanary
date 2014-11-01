<?php

require("twitmon/mysql.php");

$dbh = connect();

$lower = (int)$_GET['i'] * 10;
$upper = $lower + 10;

if ($upper < 500){
	$STH = $dbh->prepare("SELECT * FROM `dumpemails` ORDER BY `timestamp` DESC LIMIT $lower, 10 ");
	$STH->execute();
	$result = $STH->fetchAll();
	echo json_encode(array(
		"result"=>$result,
		"hash"=>md5(var_export($result, true))
		)
	);
}




?>