<?php
/**
* Opens up a PDO connection to the PasswordCanary DB
* @return Object PDO connection
**/
function connect(){
	/*** mysql hostname ***/
	$hostname = 'localhost';

	/*** mysql username ***/
	$username = 'root';

	/*** mysql password ***/
	$password = '';

	try {
	    $dbh = new PDO("mysql:host=$hostname;dbname=passwordCanary", $username, $password);


	}catch(PDOException $e){
		echo $e->getMessage();
	}
	return $dbh;
}
?>