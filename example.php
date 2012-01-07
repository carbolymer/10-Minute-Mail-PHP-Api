#!/usr/bin/php
<?php
error_reporting(E_ALL);
include 'TenMinuteMail.api.php';
$oServ = new \TenMinuteMail\Service();
// close the connection
unset($oServ);
?>
