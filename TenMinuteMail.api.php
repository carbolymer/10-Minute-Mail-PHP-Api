<?php
namespace TenMinuteMail;
class Service
{
	// current email count
	private $_iEmailCount = 0;
	private $_hConn = null;
	public function __construct()
	{
	// initialize curl
	echo "CONSTRUCTION\n";
	}
	public function __destruct()
	{
	// close curl
	echo "DESTRUCTION!\n";
	}
	public function getNewAddress()
	{
	// get new address, optionally delete old
	}
	public function getRemainingTime()
	{
	// returns remaining time in minutes
	}
	public function renew()
	{
	// restore address lasting time to 10 minutes
	}
	public function getEmails()
	{
	// return email addresses of type Email
	}
	public function countEmails()
	{
	// return 
	}
	public function countNewEmails()
	{
	// return 
	}
};

class Email
{
	
};
?>
