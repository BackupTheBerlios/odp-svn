<?
//===============================
// Node related functions.
//===============================

//===================================================================
// Assign an icon to a node.
function Nimg($m) {

	if     (stristr($m,"APPLE"))				{return  "a27";}
	elseif (stristr($m,"000d93"))				{return  "a93";}
	elseif (stristr($m,"000a95"))				{return  "a95";}
	elseif (stristr($m,"ACCTON"))				{return  "acc";}
	elseif (stristr($m,"ACER"))				{return  "acr";}
	elseif (stristr($m,"ADVANTECH"))			{return  "adv";}
	elseif (stristr($m,"ADVANCED DIGITAL INFORMATION"))	{return  "adi";}
	elseif (stristr($m,"ADAPTEC"))				{return  "adt";}
	elseif (stristr($m,"ADVANCED TECHNOLOGY &"))		{return  "adtx";}
	elseif (stristr($m,"AGILENT"))				{return  "agi";}
	elseif (stristr($m,"AMBIT"))				{return  "amb";}
	elseif (stristr($m,"ACTIONTEC"))			{return  "atec";}
	elseif (stristr($m,"ALLEN BRAD"))			{return  "ab";}
	elseif (stristr($m,"ASUS"))				{return  "asu";}
	elseif (stristr($m,"AVM GmbH"))				{return  "avm";}
	elseif (stristr($m,"AXIS"))				{return  "axis";}
	elseif (stristr($m,"BECKHOFF"))				{return  "bek";}
	elseif (stristr($m,"BROADCOM"))				{return  "bcm";}
	elseif (stristr($m,"BROCADE"))				{return  "brc";}
	elseif (stristr($m,"BROTHER INDUSTRIES"))		{return  "bro";}
	elseif (stristr($m,"CANON"))				{return  "can";}
	elseif (stristr($m,"COMPAQ"))				{return  "q";}
	elseif (stristr($m,"COMPAL"))				{return  "cpl";}
	elseif (stristr($m,"DELL"))				{return  "de";}
	elseif (stristr($m,"D-LINK"))				{return  "dli";}
	elseif (stristr($m,"DIGITAL EQUIPMENT"))		{return  "dec";}
	elseif (stristr($m,"ELECTRONICS FOR IMAGING"))		{return  "efi";}
	elseif (stristr($m,"EMULEX"))				{return  "emx";}
	elseif (stristr($m,"ENTRADA"))				{return  "ent";}
	elseif (stristr($m,"EPSON"))				{return  "eps";}
	elseif (stristr($m,"FIRST INTERNAT"))			{return  "fic";}
	elseif (stristr($m,"FUJITSU"))				{return  "fs";}
	elseif (stristr($m,"GIGA-BYTE"))			{return  "gig";}
	elseif (stristr($m,"HEWLETT"))				{return  "hp";}
	elseif (stristr($m,"IBM"))				{return  "ibm";}
	elseif (stristr($m,"INTEL"))				{return  "int";}
	elseif (stristr($m,"INTERFLEX"))			{return  "intr";}
	elseif (stristr($m,"INTERGRAPH"))			{return  "igr";}
	elseif (stristr($m,"IWILL"))				{return  "iwi";}
	elseif (stristr($m,"KINGSTON"))				{return  "ktc";}
	elseif (stristr($m,"KYOCERA"))				{return  "kyo";}
	elseif (stristr($m,"LEXMARK"))				{return  "lex";}
	elseif (stristr($m,"Microsoft Corporation"))		{return  "ms";}
	elseif (stristr($m,"MINOLTA"))				{return  "min";}
	elseif (stristr($m,"LINKSYS"))				{return  "lsy";}
	elseif (stristr($m,"MICRO-STAR"))			{return  "msi";}
	elseif (stristr($m,"LANTRONIX"))			{return  "ltx";}
	elseif (stristr($m,"LANCOM"))				{return  "lac";}
	elseif (stristr($m,"MOTOROLA"))				{return  "mot";}
	elseif (stristr($m,"NATIONAL INSTRUMENTS"))		{return  "ni";}
	elseif (stristr($m,"NETWORK COMP"))			{return  "ncd";}
	elseif (stristr($m,"NETGEAR"))				{return  "ngr";}
	elseif (stristr($m,"NEXT"))				{return  "nxt";}
	elseif (stristr($m,"NOKIA"))				{return  "nok";}
	elseif (stristr($m,"OVERLAND"))				{return  "ovl";}
	elseif (stristr($m,"PLANET"))				{return  "pla";}
	elseif (stristr($m,"PAUL SCHERRER"))			{return  "psi";}
	elseif (stristr($m,"POLYCOM"))				{return  "ply";}
	elseif (stristr($m,"PROXIM"))				{return  "prx";}
	elseif (stristr($m,"QUANTA"))				{return  "qnt";}
	elseif (stristr($m,"RARITAN"))				{return  "rar";}
	elseif (stristr($m,"RAD DATA"))				{return  "rad";}
	elseif (stristr($m,"REALTEK"))				{return  "rtk";}
	elseif (stristr($m,"RICOH"))				{return  "rco";}
	elseif (stristr($m,"RUBY TECH"))			{return  "rub";}
	elseif (stristr($m,"SAMSUNG"))				{return  "sam";}
	elseif (stristr($m,"SILICON GRAPHICS"))			{return  "sgi";}
	elseif (stristr($m,"SHIVA"))				{return  "sva";}
	elseif (stristr($m,"SIEMENS AG"))			{return  "si";}
	elseif (stristr($m,"SNOM"))				{return  "snom";}
	elseif (stristr($m,"SONY"))				{return  "sony";}
	elseif (stristr($m,"STRATUS"))				{return  "sts";}
	elseif (stristr($m,"SUN MICROSYSTEMS"))			{return  "sun";}
	elseif (stristr($m,"SUPERMICRO"))			{return  "sum";}
	elseif (stristr($m,"HUGHES"))				{return  "wsw";}
	elseif (stristr($m,"FOUNDRY"))				{return  "fdry";}
	elseif (stristr($m,"NUCLEAR"))				{return  "atom";}
	elseif (stristr($m,"TOSHIBA"))				{return  "tsa";}
	elseif (stristr($m,"TEKTRONIX"))			{return  "tek";}
	elseif (stristr($m,"TYAN"))				{return  "tya";}
	elseif (stristr($m,"VMWARE"))				{return  "vm";}
	elseif (stristr($m,"WESTERN"))				{return  "wdc";}
	elseif (stristr($m,"WISTRON"))				{return  "wis";}
	elseif (stristr($m,"XYLAN"))				{return  "xylan";}
	elseif (stristr($m,"XEROX"))				{return  "xrx";}
	elseif (stristr($m,"ZYXEL COMMUNICATIONS"))		{return  "zyx";}
	elseif (preg_match("/3\s*COM|MEGAHERTZ/i",$m))		{return  "3com";}
	elseif (preg_match("/AIRONET|CISCO/i",$m))		{return  "cis";}
	elseif (preg_match("/AVAYA|LANNET/i",$m))		{return  "ava";}
	elseif (preg_match("/BAY|NORTEL|NETICS|XYLOGICS/i",$m))	{return  "nort";}
	elseif (preg_match("/SMC Net|STANDARD MICROSYS/i",$m))	{return  "smc";}
	else							{return  "gen";}
}

//===================================================================
// Emulate good old nbtstat on port 137
function NbtStat($ip) {

	$nbts	= pack('C50',129,98,00,00,00,01,00,00,00,00,00,00,32,67,75,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,65,00,00,33,00,01);
	$fp		= @fsockopen("udp://$ip", 137, $errno, $errstr);
	if (!$fp) {
		return "ERROR! $errno $errstr";
	}else {
		fwrite($fp, "$nbts");
		stream_set_timeout($fp, 0, 1000000 );
		$data =  fread($fp, 400);
		fclose($fp);

		if (preg_match("/AAAAAAAAAA/",$data) ){
			$nna = unpack('cnam',substr($data,56,1));  							# Get number of names
			$out = substr($data,57);                							# get rid of WINS header

			for ($i = 0; $i < $nna['nam'];$i++){
				$nam = preg_replace("/ +/","",substr($out,18*$i,15));
				$id = unpack('cid',substr($out,18*$i+15,1));
				$fl = unpack('cfl',substr($out,18*$i+16,1));
				$na = "";
				$gr = "";
				$co = "";
				if ($fl['fl'] > 0){
					if ($id['id'] == "3"){
						if ($na == ""){
							$na = $nam;
						}else{
							$co = $nam;
						}
					}
				}else{
					if ($na == ""){
						$gr = $nam;
					}
				}
			}
			return "<img src=\"img/16/bchk.png\"> $na $gr $co";
		}else{
			return "<img src=\"img/16/bstp.png\"> No response";
		}
	}
}

//===================================================================
// Check for open port and return server information, if possible.
function CheckTCP ($ip, $p,$d){

	if ($ip == "0.0.0.0") {
		return "<img src=\"img/16/bcls.png\"> No IP!";
	}else{
		$fp = @fsockopen($ip, $p, $errno, $errstr, 1 );

		flush();
		if (!$fp) {
			return "<img src=\"img/16/bstp.png\"> $errstr";
		} else {
			fwrite($fp,$d);
			stream_set_timeout($fp, 0, 100000 );
			$ans = fread($fp, 255);
			$ans .= fread($fp, 255);
			fclose($fp);
			if( preg_match("/<address>(.*)<\/address>/i",$ans,$mstr) ){
				return "<img src=\"img/16/bchk.png\"> " . $mstr[1];
			}elseif( preg_match("/Server:(.*)/i",$ans,$mstr) ){
				return "<img src=\"img/16/bchk.png\"> " . $mstr[1];
			}elseif( preg_match("/CONTENT=\"(.*)\">/i",$ans,$mstr) ){
				return "<img src=img/16/bchk.png\"> " . $mstr[1];
			}else{
				$mstr = preg_replace("/[^\x20-\x7e]|<!|!>/",'',$ans);
				return "<img src=\"img/16/bchk.png\"> $mstr";
			}
		}
	}
}

//===================================================================
// Create and send magic packet (copied from the PHP webiste)
function wake($ip, $mac, $port){
	$nic = fsockopen("udp://" . $ip, $port);
	if($nic){
		$packet = "";
		for($i = 0; $i < 6; $i++)
			$packet .= chr(0xFF);
		for($j = 0; $j < 16; $j++){
			for($k = 0; $k < 6; $k++){
				$str = substr($mac, $k * 2, 2);
				$dec = hexdec($str);
				$packet .= chr($dec);
			}
		}
		$ret = fwrite($nic, $packet);
		fclose($nic);
		if($ret)
			return true;
	}
	return false;
} 
?>