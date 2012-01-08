#!/usr/bin/php
<?php
error_reporting(E_ALL);
include 'TenMinuteMail.php';
$oServ = new \TenMinuteMail\Service();

$oServ->getNewAddress();
echo "\n-> Your Address is:\t".$oServ->getAddress()."\n";
while(true)
{
	$oServ->check();
	echo "\n*---------------------------------------------------------*\n";
	echo "Your Addres will expire in ".$oServ->getRemainingTime()." minutes."."\n";
	if($oServ->getRemainingTime() <= 2)
		$oServ->renew();
	$o = $oServ->getEmails();
	echo "You have ".count($o)." e-mails.\n";
	if(count($o) > 0)
		var_dump($o);
	sleep(20);
}

// close the connection
unset($oServ);
?>
