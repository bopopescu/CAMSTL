<?php

/*
 *	pptp client implement file.
 */
require_once "db_sqlite3.inc";
require_once "util.inc";
require_once "cidr.inc";

class pptpclient
{
	var $pptpenable = false;
	var	$user;
	var	$password;
	var $subnet;
	var $devicename;

	function pptpConfigure($serverip)
	{
		$dt = new db_sqlite3();
		$conn = $dt->connect_sqlite('data.db');

		$sql="select * from pptpClient where ServerIp=".escapeshellarg($serverip);
		$current = $dt->query_sqlite($conn, $sql);

		foreach($current as $configrow)
		{
			if($configrow['pptpClientEnable']==1)
			{
				$this->pptpenable=true;
				$this->user=$configrow['UserName'];
				$this->password= $configrow['Password'];

				$cidr = new CIDR();
				$this->subnet = $cidr->alignedCIDR($configrow["RemoteNetwork"] ,$configrow["RemoteNetmask"])."\n";
			}
		}

		if($this->pptpenable)
		{
			$ar = array();
			$cmd = '/usr/sbin/pptp '.$serverip.' noauth '.' require-mppe-128 user '.$this->user.' password '.$this->password;
			echo $cmd;
			atsexec($cmd, $ar);
		}
	}

	//route map configure.
	private function pptpNetworkConfigure($subnet, $deviceName)
	{
		$ar = array();
		$cmd = '/sbin/route add -net '.$subnet.' dev '.$deviceName;
		atsexec($cmd, $ar);
	}

	private function pptpGetpppDevicename($subnet)
	{
		//we know the remotenetwork ip, run ip ro to get pptp device name
		$ar = array();
		$cmd = '/sbin/ip ro';
		atsexec($cmd, $ar);

		$reg= '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(|\/\d{0,2}) dev ([a-zA-Z]+\d+) /';
		$cidr = new CIDR();
		foreach($ar as $line)
		{
			if(preg_match($reg, $line, $arMatches))
			{
				if($cidr->IPisWithinCIDR($arMatches[1], $subnet))
					$this->devicename = ($arMatches[3]);
			}
		}
	}
}
?>
