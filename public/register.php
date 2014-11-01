<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
	require("includes/mysql.php");
	
	$csrf_real = $_SESSION['csrf'];
	$csrf_submit = $_POST['csrf'];

	$db = connect();

	$email = $_POST['email'];

	if (filter_var(($email), FILTER_VALIDATE_EMAIL)){

		if ($csrf_real == $csrf_submit){


			$STH = $db->prepare("SELECT * FROM `subscribers` WHERE `email` = ?");
			$STH->execute(array($email));
			if(!$STH->fetchColumn()){
				$STH = $db->prepare("INSERT INTO `subscribers` (email) VALUES (?)");
			 	$STH->execute(array($email));
				echo json_encode(array("status"=>"success","message"=>"Success!"));
			}else{
				echo json_encode(array("status"=>"error","message"=>"Email Address alread Subscribed"));
			}

			


		}else{
			echo json_encode(array("status"=>"error","message"=>"An unexpected error occured. Please try again. (CSRF Failed)"));
		}

		

	}else{
		echo json_encode(array("status"=>"error","message"=>"The e-mail provided is not valid."));
	}

?>