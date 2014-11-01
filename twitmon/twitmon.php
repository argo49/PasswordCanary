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
	$password = 'kMfnBRZD1f';

	try {
	    $dbh = new PDO("mysql:host=$hostname;dbname=passwordCanary", $username, $password);


	}catch(PDOException $e){
		echo $e->getMessage();
	}
	return $dbh;
}

/**
* Saves Dump Email
* @param  Array   Associative array $data, array(email, dumpid)
* @return Boolean returns true on successful transactions
**/

function saveDumpEmail($data){
	$dbh = connect();
	$STH = $dbh->prepare("INSERT INTO `dumpemails` ( email, dumpid ) values ( ?, ? )");
	if ($STH->execute($data)){
		return true;
	}else{
		return false;
	}
}

function dumpidExists($dumpid){
	$dbh = connect();
	$STH = $dbh->prepare("SELECT * FROM `dumpids` WHERE `dumpid` = ?");
	$STH->bindParam(1, $dumpid);
	$STH->execute();
	return $STH->fetchColumn();
	//return $dumpid;
}
function checkTweets(){
	require("libs/TwitterAPIExchange.php");
	$settings = array(
	    'oauth_access_token' => "[]",
	    'oauth_access_token_secret' => "[]",
	    'consumer_key' => "[]",
	    'consumer_secret' => "[]"
	);
	$twitter = new TwitterAPIExchange($settings);
	$url = 'https://api.twitter.com/1.1/search/tweets.json';
	$getfield = '?q=from%3Adumpmon%20emails';
	$requestMethod = 'GET';
	$result = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
	
	$dbh = connect();
	foreach ($result->statuses as $tweet){
		$output ="";
		preg_match("/(.*) Emails: (.*)/", $tweet->text, $output);
		$dumpid = explode("/",$output[1])[3];
		if (!dumpidExists($dumpid)){
			$STH = $dbh->prepare("INSERT INTO `dumpids` ( dumpid ) values ( ? )");
			$STH->bindParam(1, $dumpid);
			$STH->execute();
			$string = file_get_contents("http://t.co/".$dumpid); // Load text file contents
			if ($string){

				$matches = array(); //create array
				$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; //regex for pattern of e-mail address
				preg_match_all($pattern, $string, $matches); //find matching pattern
				$STH = $dbh->prepare("INSERT INTO `dumpemails` ( email, dumpid ) values ( ?, ? )");
				foreach($matches[0] as $email){
					$STH->execute(array($email, $dumpid));
				}
				
			}
			
		}

	}
}
echo "<pre>";
checkTweets();
echo "</pre>";

?>