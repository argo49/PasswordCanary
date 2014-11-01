<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

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
	    'oauth_access_token' => "",
	    'oauth_access_token_secret' => "",
	    'consumer_key' => "",
	    'consumer_secret' => ""
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
			echo "New dump found $dumpid \n";
			$STH = $dbh->prepare("INSERT INTO `dumpids` ( dumpid ) values ( ? )");
			$STH->bindParam(1, $dumpid);
			$STH->execute();
			$string = file_get_contents("http://t.co/".$dumpid); // Load text file contents
			if ($string){

				$matches = array(); //create array
				$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; //regex for pattern of e-mail address
				preg_match_all($pattern, $string, $matches); //find matching pattern
				//$STH = $dbh->prepare("INSERT INTO `dumpemails` ( email, dumpid ) values ( ?, ? )");
				foreach($matches[0] as $email){
					//$STH->execute(array($email, $dumpid));
					$STH = $dbh->prepare("SELECT * FROM `subscribers` WHERE email = ?");
					$STH->execute(array($email));
					//echo "$email\n";
					$lastnotif = $STH->fetchAll();
					//var_dump($lastnotif);
					//echo "\n";
					

					if(count($lastnotif) > 0){
						$lastnotif = (int)$lastnotif[0]["lastnotif"];
						//echo "lastnotif $lastnotif \n";
						//echo "24 hours ".(60*60*24)."\n";
						//echo "delta".(time() - $lastnotif)."\n";
						if ((time() - $lastnotif) > (60*60*24) ){
							notifAction($email, $dumpid);
						}
					}
				}
				
			}
			
		}

	}
}
function notifAction($email, $dumpid){
	

	require("libs/mailgun/autoload.php");



	$mg = new Mailgun\Mailgun("");
	$domain = "sandbox256.mailgun.org";

$tags = array("{**EMAIL**}","{**DUMP**}");
$dump = "http://t.com/".$dumpid;
$replace = array($email, $dump);

	# Now, compose and send your message.
	$mg->sendMessage($domain, array('from'    => 'passwordcanary@sandbox256.mailgun.org', 
	                                'to'      =>  $email, 
	                                'subject' => '[ACTION REQUIRED] Your Password Might be Compromised', 
	                                'text'    => str_replace($tags, $replace, file_get_contents("detectEmail.txt")),
	                                'html'	  => str_replace($tags, $replace, file_get_contents("detectEmail.html"))	));
	
	$dbh = connect();
	$STH = $dbh->prepare("UPDATE `subscribers` SET `lastnotif` = ? WHERE `email` = ?");
 	$STH->execute(array(time(), $email));
	echo "watch out $email";



}
echo "<pre>";
checkTweets();
echo "</pre>";
//saveDumpEmail(array("asd","asdasdas"));

?>