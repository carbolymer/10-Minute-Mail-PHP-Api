<?php
namespace TenMinuteMail;
class Service
{
	private static $_sURL = 'http://10minutemail.com/10MinuteMail/index.html';
	private $_sRenewURL = null;
	private $_iEmailCount = 0;
	private $_aEmails = array();
	private $_hConn = null;
	private $_sCookies = null;
	private $_sEmail = null;
	private $_iRemainingTime = null;
	public function __construct()
	{
		$this->_hConn = curl_init();
		
//		$this->_sCookies = file_get_contents('./cookie');

		$aOpts = array( 
			CURLOPT_URL				=> self::$_sURL,
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_HEADER			=> true,
			CURLINFO_HEADER_OUT		=> true,
			CURLOPT_FOLLOWLOCATION	=> true,
/*			CURLOPT_COOKIESESSION	=> true,
			CURLOPT_COOKIEFILE		=> './jar',
			CURLOPT_COOKIEJAR		=> './jar',
			CURLOPT_COOKIE			=> $this->_sCookies*/
		);
		
		curl_setopt_array($this->_hConn, $aOpts);
	}
	public function __destruct()
	{
		curl_close($this->_hConn);
		file_put_contents('./cookie',$this->_sCookies);
	}
	// obtains new email address
	public function getNewAddress()
	{
		curl_setopt($this->_hConn, CURLOPT_COOKIE, '');
		curl_setopt($this->_hConn, CURLOPT_URL, self::$_sURL);
		$sResponse = curl_exec($this->_hConn);
		$iFound = preg_match_all("#Set-Cookie: (.*?);#Umi",$sResponse,$aMatches);
		if($iFound > 1)
			$this->_sCookies = implode(";",$aMatches[1]);
		elseif($iFound == 1)
			$this->_sCookies = $aMatches[1][0];
		curl_setopt($this->_hConn, CURLOPT_COOKIE, $this->_sCookies);
		
		preg_match("#<input id=\"addyForm:addressSelect\" type=\"text\" name=\"addyForm:addressSelect\" value=\"(.*?)\" size=#mi",$sResponse,$aMatches);
		$this->_sEmail = $aMatches[1];
		$this->_refreshRenewURL($sResponse);
		$this->_aEmails = array();
	}
	// returns email address
	public function getAddress()
	{
		return $this->_sEmail;
	}
	// returns remaining time in minutes
	public function getRemainingTime()
	{
		return $this->_iRemainingTime;
	}
	// gives 10 minutes more to email address
	public function renew()
	{
		curl_setopt($this->_hConn, CURLOPT_URL, $this->_sRenewURL);
		$this->_refreshRenewURL(curl_exec($this->_hConn));
	}
	// returns all emails, newest is on the bottom
	public function getEmails()
	{
		return $this->_aEmails;
	}
	// returns email count
	public function countEmails()
	{
		return $this->_iEmailCount;
	}
	
	// returns new emails count
	// function can be used to check for new mail
	public function countNewEmails()
	{
		$iCount = $this->_iEmailCount;
		$this->check();
		return $this->_iEmailCount - $iCount;
	}
	
	// connects to service and gathers all info
	// returns nothing
	public function check()
	{
		curl_setopt($this->_hConn, CURLOPT_URL, self::$_sURL);
		$sResponse = curl_exec($this->_hConn);
		$this->_refreshRenewURL($sResponse);
		
		preg_match("#<span id=\"expirationTime\">Your e-mail address will expire in (\d+) minutes.</span>#Umi",$sResponse,$aMatches);
		$this->_iRemainingTime = intval($aMatches[1]);
		
		$iFound = preg_match("#<table id=\"emailTable\" width=\"700px\">
<thead>(.*?)</thead>
<tbody>(.*?)</tbody>
</table>#si",$sResponse,$aMatches);
		if($iFound == 0)
			return $this->_aEmails;
		preg_match_all("#<tr>
<td><input type=\"checkbox\" name=\"emailTable:(\d+):j_id29\"(.*?)disabled=\"disabled\" /></td>
<td>(.*?)</td>
<td><a href=\"(.*?)\" id=\"(.*?)\">(.*?)</a></td>
<td>(.*?)</td>
<td>(.*?)</td>
</tr>#si",$aMatches[2],$aMatches);
		
		$this->_iEmailCount = count($aMatches[3]);
		for($i = 0; $i < $this->_iEmailCount ; $i++)
		{
			$this->_aEmails[$i] =  new Email($i);
			$this->_aEmails[$i]->sender = trim($aMatches[3][$i]);
			$this->_aEmails[$i]->url = 'http://10minutemail.com'.urldecode(str_replace('&amp;','&',trim($aMatches[4][$i])));
			$this->_aEmails[$i]->subject = trim($aMatches[6][$i]);
			$this->_aEmails[$i]->date = strtotime(trim($aMatches[8][$i]));
			$this->_aEmails[$i] = $this->_parseEmail($this->_aEmails[$i]);
		}
	}
	
	private function _refreshRenewURL($sResponse)
	{
		preg_match("#Give me <a href=\"(.*?)\" id=\"j_id20\">10 more#mi",$sResponse,$aMatches);
		$aMatches[1] = str_replace('&amp;','&',$aMatches[1]);
		$this->_sRenewURL = 'http://10minutemail.com'.str_replace('index.html','index.html;'.$this->_sCookies,urldecode($aMatches[1]));
	}
	
	private function _parseEmail(Email $oEmail)
	{
		curl_setopt($this->_hConn, CURLOPT_URL, $oEmail->url);
		$sResponse = curl_exec($this->_hConn);
		preg_match("#<strong>(.*?)</strong>(.*?)<strong>(.*?)</strong>(.*?)<strong>(.*?)</strong>(.*?)<br />(.*?)<div style=\"clear:both\"></div>(.*?)<div id=\"j_id22\" style=\"font-size: 0px;\">#si",$sResponse,$aMatches);
		$oEmail->subject = trim($aMatches[6]);
		$oEmail->message = trim(str_replace('<br />
		<br />
		<br />
		<br />
','',strstr($aMatches[8], '<!--', true)));
		return $oEmail;
	}


	private function dee($sResponse)
	{	// TODO wyjebac
		preg_match("#<input id=\"addyForm:addressSelect\" type=\"text\" name=\"addyForm:addressSelect\" value=\"(.*?)\" size=#mi",$sResponse,$aMatches);
		var_dump($aMatches[1]);
	}
};

class Email
{
	private $_iID = null;
	public $sender = null;
	public $subject = null;
	public $message = null;
	public $date = null;
	public $url = null;
	// -1 means that this is response
	public function __construct($iID = -1)
	{
		$this->_iID = $iID;
	}
	
	public function reply($sMessage)
	{
		// TODO
		;
	}
};
?>
