<?
/*
#============================================================================
# Program: Devices-Status.php
# Programmer: Remo Rickli
#
# DATE	COMMENT
# --------------------------------------------------------------------------
# 14/04/05	initial version.
# 1/03/06	offline functions, traffic graphs
# 16/03/06	new SQL query support
# 17/01/07	Minor improvements.
# 17/05/07	Relative Errors
# 30/08/07	implemented CSS scheme
*/

include_once ("inc/header.php");
include_once ('inc/libdev.php');

$_GET = sanitize($_GET);
$shd = isset($_GET['dev']) ? $_GET['dev'] : "";
$rtl = isset($_GET['rtl']) ? 1:0;
$dld = isset($_GET['del']) ? $_GET['del'] : "";
$shg = isset($_GET['shg']) ? $_GET['shg'] : "";
$shp = isset($_GET['shp']) ? $_GET['shp'] : "";

?>
<h1>Device Status</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/hwif.png" title="Only works properly, when interfaces in db are up to date!">
</a></th>
<th>
Device <SELECT size=1 name="dev">
<OPTION VALUE="">------------
<?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('devices','s','name','name');
$res	= @DbQuery($query,$link);
if($res){
	while( $d = @DbFetchRow($res) ){
		echo "<option value=\"$d[0]\"";
		if($shd == $d[0]){echo "selected";}
		echo ">$d[0]\n";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}
?>
</SELECT>
</th>
<th>
<input type="checkbox" name="shg" <?=($shg)?"checked":""?>> IF Graphs
</th>
<th>
<input type="checkbox" name="shp" <?=($shp)?"checked":""?>> Population
</th>
<th width=80>
<input type="submit" value="Show" name="show">
</th>
</tr></table></form><p>
<?
if ($shd){
if ($rtl){
	if(preg_match("/adm/",$_SESSION['group']) ){
		$query	= GenQuery('devices','u','name',$shd,'',array('cliport'),array('='),array('0') );
		if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h3>";}else{echo "<h3>$shd's cliport $upokmsg</h3>";}
	}else{
		echo $nokmsg;
	}
}
$query	= GenQuery('devices','s','*','','',array('name'),array('='),array($shd) );
$res	= @DbQuery($query,$link);
$ndev	= @DbNumRows($res);
if ($ndev != 1) {
	echo "<h4>$_GET[dev] $n1rmsg ($ndev)</h4>";
	@DbFreeResult($res);
	die;
}
$dev	= @DbFetchRow($res);
@DbFreeResult($res);

$query	= GenQuery('networks','s','*','','',array('device'),array('='),array($shd) );
$res	= @DbQuery($query,$link);
while( $n = @DbFetchRow($res) ){
	$net[$n[1]][$n[2]] = ip2long(long2ip($n[3]));		// thanks again PHP (for increased grey hair count to fix netmask)!
}
@DbFreeResult($res);

$ud		= rawurlencode($dev[0]);
$ip		= ($dev[1]) ? long2ip($dev[1]) : 0;
$oi		= ($dev[19]) ? long2ip($dev[19]) : 0;
$img		= $dev[18];
list($fc,$lc)	= Agecol($dev[4],$dev[5],0);
$fs		= date($datfmt,$dev[4]);
$ls		= date($datfmt,$dev[5]);

$sv		= Syssrv($dev[6]);
$os		= $dev[8];
$comm		= $dev[15];
$ver		= $dev[14] & 127;
?>

<table class="full fixed"><tr><td class="helper">

<h2>General Info</h2><p>
<table class="content"><tr>
<th class="imga" width=150><a href=?dev=<?=$ud?> ><img src="img/dev/<?=$img?>.png" title="<?=$dev[3]?>" vspace=4></a><br><?=$dev[0]?></th>
<th class="<?=$modgroup[$self]?>2">

<div style="float:left">

<a href=Monitoring-Messages.php?ina=source&opa==&sta=<?=$ud?>><img src="img/16/say.png" title="Messages"></a>
<a href=Devices-Config.php?shc=<?=$ud?> ><img src="img/16/cfg2.png" title="Config of device"></a>
<a href=Nodes-List.php?ina=device&opa==&sta=<?=$ud?>&ord=ifname><img src="img/16/cubs.png" title="Nodes on device"></a>
<a href=Devices-Interfaces.php?ina=device&opa==&sta=<?=$ud?>&ord=ifname><img src="img/16/dumy.png" title="Interfaces of device"></a>
<a href=Devices-Modules.php?ina=device&opa==&sta=<?=$ud?>&ord=slot><img src="img/16/cog.png" title="Interfaces of device"></a>

</div><div style="float:left">

<?
if ($ver){
	if($dev[6] > 3){
?><a href=Topology-Routes.php?rtr=<?=$ud?> ><img src="img/16/rout.png" title="Routes on device"></a>
<a href=Topology-Multicast.php?rtr=<?=$ud?> ><img src="img/16/cam.png" title="Multicast routes on device"></a><?
	}
	if($dev[6] & 2){
?><a href=Devices-Vlans.php?ina=device&opa==&sta=<?=$ud?>><img src="img/16/stat.png" title="Vlans on switch"></a><?
?><a href=Topology-Spanningtree.php?dev=<?=$ud?> ><img src="img/16/traf.png" title="Spanningtree info on switch"></a><?
	}
}
if( preg_match("/adm/",$_SESSION['group']) ){
	if( preg_match("/^(IOS|Ironware|ProCurve)/",$os) ){
		$shlog = "sh log";
	}elseif($os == "CatOS"){
		$shlog = "sh logg buf";
	}else{
		$shlog = "";
	}
	if($dev['17'] and $shlog){
?>

</div><div style="float:left;margin:2px 16px">

<form method="post" action="Devices-Write.php">
<input type="hidden" name="sta" value="<?=$dev[0]?>">
<input type="hidden" name="cmd" value="<?=$shlog?>">
<input type="hidden" name="ina" value="name">
<input type="hidden" name="opa" value="=">
<input type="hidden" name="scm" value="1">
<input type="image" src="img/16/wrte.png""  value="Submit" title="Show log">
</form>
<?	}?>

</div><div style="float:right">

<a href=Topology-Linked.php?dv=<?=$ud?> ><img src="img/16/wglb.png" title="Edit Links"></a>
<a href=?dev=<?=$ud?>&rtl="1"><img src="img/16/kons.png" title="Retry login on next discovery"></a>
<a href=<?=$_SERVER['PHP_SELF']?>?del=<?=$ud?>><img src="img/16/bcnl.png" onclick="return confirm('Schedule for deletion?')"></a>
<?
}
?>

</div>

</th></tr>
<tr><th class="<?=$modgroup[$self]?>2">Main IP</th>		<td class="txtb">
<a href=https://<?=$ip?> target=window><img src="img/16/glok.png" align=right title="HTTPS"></a>
<a href=http://<?=$ip?> target=window><img src="img/16/glob.png" align=right  title="HTTP"></a>
<a href=ssh://<?=$ip?>><img src="img/16/lokc.png" align=right  title="SSH"></a>
<a href=telnet://<?=$ip?>><img src="img/16/loko.png" align=right title="Telnet"></a><?=$ip?>
</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Original IP</th>		<td class="txta">
<a href=https://<?=$oi?> target=window><img src="img/16/glok.png" align=right title="HTTPS"></a>
<a href=http://<?=$oi?> target=window><img src="img/16/glob.png" align=right  title="HTTP"></a>
<a href=ssh://<?=$oi?>><img src="img/16/lokc.png" align=right  title="SSH"></a>
<a href=telnet://<?=$oi?>><img src="img/16/loko.png" align=right title="Telnet"></a><?=$oi?>
</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Services</th>	<td class="txtb"><?=$sv?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Bootimage</th>	<td class="txta"><?=$dev['9']?> (<?=$os?>)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Serial #</th>	<td class="txtb"><?=$dev['2']?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Description</th>	<td class="txta"><b><?=$dev[3]?></b> <?=$dev['7']?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Location</th>	<td class="txtb"><?=$dev['10']?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Contact</th>		<td class="txta"><?=$dev['11']?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">VTP Info</th>	<td class="txtb">Domain:<?=$dev['12']?> <?=VTPmod($dev['13'])?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">SNMP Access</th>	<td class="txta"><?=($dev['14'] and $dev['15'])?"<img src=\"img/bulbg.png\">":"<img src=\"img/bulbr.png\">"?> <?=$dev['15']?> (Version <?=($ver  . (($dev[14] & 128)?" using HC-counters":""));?>)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">CLI Access</th>	<td class="txta"><?=($dev['16'] and $dev['17'])?"<img src=\"img/bulbg.png\">":"<img src=\"img/bulbr.png\">"?> <?=$dev['17']?> (Port <?=($dev['16'])?$dev['16']:"-"?>)</td></tr>
<tr><th class="<?=$modgroup[$self]?>2">First Seen</td>	<td bgcolor=#<?=$fc?>><?=$fs?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Last Seen</td>	<td bgcolor=#<?=$lc?>><?=$ls?></td></tr>
<?
if($ver){
	echo "<tr><th class=\"$modgroup[$self]2\">System</th><th class=\"txtb\">";
	echo "<a href=Devices-Graph.php?dv=$ud&cpu=on><img src=inc/drawrrd.php?dv=$ud&t=cpu&s=s title=\"CPU load\">";
	echo "<a href=Devices-Graph.php?dv=$ud&mem=on><img src=inc/drawrrd.php?dv=$ud&t=mem&s=s title=\"Available Memory\">";
	echo "<a href=Devices-Graph.php?dv=$ud&tmp=on><img src=inc/drawrrd.php?dv=$ud&t=tmp&s=s title=\"Temperature\">";
	echo "</th></tr>";
}

flush();
if ($ver){
	echo "<tr><th class=\"$modgroup[$self]2\">Uptime</th><th class=\"txta\">";
	error_reporting(1);
	snmp_set_quick_print(1);
	$uptime	= snmpget("$ip","$comm",".1.3.6.1.2.1.1.3.0",($timeout * 100000) );
	if ($uptime){
		sscanf($uptime, "%d:%d:%d:%d.%d",$upd,$uph,$upm,$ups,$ticks);
		$upmin	= $upm + 60 * $uph + 1440 * $upd;
		if ($upd  < 1) {echo "<img src=\"img/16/impt.png\"> ";} else { echo "<img src=\"img/16/bchk.png\"> ";}
		echo sprintf("%d D %d:%02d:%02d",$upd,$uph,$upm,$ups)."</th></tr>\n";
	}else{
		echo $toumsg;
		echo "</th></tr>\n";
	}
}
flush();
?>
</table>

<h2>Vlans</h2>
<table class="content" ><tr class="<?=$modgroup[$self]?>2">
<th valign=bottom width=150><img src="img/32/stat.png"><br>Vlan</th>
<th valign=bottom><img src="img/32/say.png"><br>Name</th></tr>
<?
if($dev['13'] == 1){
	echo "<tr class=\"txta\"><th colspan=2>Not shown on VTP clients</th></tr>\n";
}else{
	$query	= GenQuery('vlans','s','*','vlanid','',array('device'),array('='),array($shd) );
	$res	= @DbQuery($query,$link); 
	$row  = 0;
	while( $v = @DbFetchRow($res) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$v[1]</th><td>$v[2]</td></tr>\n";
		$nvlan++;
	}
	@DbFreeResult($res);
	if(!$row){
		echo "<tr class=\"txta\"><th colspan=2>$resmsg</th></tr>\n";
	}
}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Vlans (<?=$query?>)</td></tr>
</table>
	<?
flush();
?>

</td><td class="helper">

<h2>Modules</h2>
<table class="content" ><tr class="<?=$modgroup[$self]?>2">
<th valign=bottom colspan=3><img src="img/32/cog.png" title="Slot, Model and Description"><br>Module</th>
<th valign=bottom><img src="img/32/key.png"><br>Serial</th>
<th valign=bottom colspan=3 title="HW / FW / SW"><img src="img/32/dsw.png"><br>Version</th>
</tr>
<?
$query	= GenQuery('modules','s','*','slot','',array('device'),array('='),array($shd) );
$res	= @DbQuery($query,$link); 
$row  = 0;
while( $m = @DbFetchRow($res) ){
	if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
	$row++;
	echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
	echo "$m[1]</th><th>$m[2]</th><td>$m[3]</td><td>$m[4]</td><td>$m[5]</td><td>$m[6]</td><td>$m[7]</td></tr>\n";
}
@DbFreeResult($res);
if(!$row){
	echo "<tr class=\"txta\"><th colspan=7>$resmsg</th></tr>\n";
}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Modules (<?=$query?>)</td></tr>
</table>
	<?
flush();
?>
<h2>Links</h2>
<table class="content" ><tr class="<?=$modgroup[$self]?>2">
<th valign=bottom><img src="img/32/dumy.png"><br>Interface</th>
<th valign=bottom><img src="img/32/dev.png"><br>Neighbour</th>
<th><img src="img/32/tap.png"><br>Bandwidth</th>
<th><img src="img/32/powr.png" title="PoE consumption in mW"><br>Power</th>
<th><img src="img/32/fiap.png" title="C=CDP,M=Mac,O=Oui,V=VoIP,L=LLDP,S=static"><br>Type</th></tr>
<?
$query	= GenQuery('links','s','*','ifname','',array('device'),array('='),array($shd) );
$res	= @DbQuery($query,$link);
$row  = 0;
$tpow = 0;							# China in your hand ;-)
while( $l = @DbFetchRow($res) ){
	if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
	$row++;
	$tpow += $l[7];
	$ul = rawurlencode($l[3]);
	echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
	echo "$l[2]</th><td><a href=$_SERVER[PHP_SELF]?dev=$ul>$l[3]</a> on $l[4] (Vlan$l[9] $l[8])</td>";
	echo "<td align=right>" . Zfix($l[5]) . "</td><td align=right>$l[7]</td>";
	echo "<td align=\"center\">$l[6]</td></tr>\n";
}
@DbFreeResult($res);
if(!$row){
	echo "<tr class=\"$bg\"><th colspan=5>$resmsg</th></tr>\n";
}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Links drawing <?=($tpow / 1000)?> W total</td></tr>
</table>

</td></tr></table>

<?
flush();
if ($ver){
	$query	= GenQuery('interfaces','s','*','ifidx','',array('device'),array('='),array($shd) );
	$res	= @DbQuery($query,$link);
	while( $i = @DbFetchRow($res) ){
		$ifn[$i[2]] = $i[1];
		$ift[$i[2]] = $i[4];
		$ifa[$i[2]] = $i[8];
		$ifs[$i[2]] = ZFix($i[9]);
		$ifd[$i[2]] = $i[10];
		$ifi[$i[2]] = "$i[6] <i>$i[7]</i> <b>$i[20]</b>";
		$ifv[$i[2]] = $i[11];
		$ifm[$i[2]] = $i[5];
		$ino[$i[2]] = $i[12];
		$oto[$i[2]] = $i[14];
		$dio[$i[2]] = $i[16];
		$die[$i[2]] = $i[17];
		$doo[$i[2]] = $i[18];
		$doe[$i[2]] = $i[19];
	}
	@DbFreeResult($res);
	if( !count($ifn) ){
		echo $resmsg;
		echo "<div align=\"center\">$query</dev>";
		include_once ("inc/footer.php");
		die;
	}
?>
<h2>Interfaces</h2><p>
<table class="content" ><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 valign=bottom><img src="img/16/dumy.png" title="Realtime, operational status"><br>Name</th>
<th valign=bottom><img src="img/16/stat.png" title="Vlanid from DB"><br>Vlan</th>
<th valign=bottom><img src="img/16/find.png" title="Descriptions from DB"><br>Info</th>
<th valign=bottom><img src="img/spd.png" title="Speed from DB"><br>Speed</th>
<th valign=bottom><img src="img/dpx.png" title="- n/a, ? unknown"><br>Duplex</th>
<th valign=bottom><img src="img/cal.png" title="Realtime, last status change"><br>Last Chg</th>
<?
	if($shp){
		echo '<th valign=bottom><img src="img/16/cubs.png"><br>Pop</th>';

		$query	= GenQuery('nodes','g','ifname','','',array('device'),array('='),array($shd) );
		$res	= @DbQuery($query,$link);
		if($res){
			while( ($nc = @DbFetchRow($res)) ){
				$ncount[$nc[0]] = $nc[1];
			}
		}
		$query	= GenQuery('nodiflog','s','*','','',array('device'),array('='),array($shd) );
		$res	= @DbQuery($query,$link);
		if($res){
			while( ($nl = @DbFetchRow($res)) ){
				$niflog[$nl[3]] = $nl[1];
			}
		}
	}
	if($shg){
		echo '<th valign=bottom><img src="img/16/3d.png"><br>Traffic/Errors</th>';
	}else{
 ?>
<th valign=bottom><img src="img/16/bbup.png" title="Bytes within last <?=$rrdstep?>s"><br>In Octets</th>
<th valign=bottom><img src="img/16/bbdn.png" title="Blue field indicates traffic was seen at all"><br>Out Octets</th>
<th valign=bottom><img src="img/16/rbup.png" title="Errors within last <?=$rrdstep?>s"><br>In Err</th>
<th valign=bottom><img src="img/16/rbdn.png" title="Blue field indicates errors were seen at all"><br>Out Err</th>
<?
	}
?>
<th valign=bottom><img src="img/netg.png" title="HW and IP address"><br>Address</th>
<?
	if($uptime){
		foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.2.2.1.8") as $ix => $val){
			$ifost[substr(strrchr($ix, "."), 1 )] = $val;
		}
		foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.2.2.1.9") as $ix => $val){
			$iflac[substr(strrchr($ix, "."), 1 )] = $val;
		}
	}
	$row = 0;
	foreach ( $ifn as $i => $in){
		if ($row % 2){$bg = "txta"; $bi = "imga";$off=200;}else{$bg = "txtb"; $bi = "imgb";$off=185;}
		$row++;
		$bg3= sprintf("%02x",$off);
		$bio= $bie = $boo = $boe = $rs = $gs = $bg3;
		$ui = rawurlencode($in);
		#if ($ifa[$i] == "1"){$gs = sprintf("%02x",55 + $off);}
		#if ($ifost[$i] == "2" or $ifost[$i] == "down"){$rs = sprintf("%02x",55 + $off);}
		if ($ifa[$i] == "1"){
			if ($ifost[$i] == "2" or $ifost[$i] == "down"){
				$ifstat = "warn";
			}else{
				$ifstat = "good";
			}
		}else{
			if ($ifost[$i] == "2" or $ifost[$i] == "down"){
				$ifstat = "crit";
			}else{
				$ifstat = "alrm";
			}
		}

		if ($ino[$i] > 70){									// Ignore the first 70  packets...
			$bio = sprintf("%02x","40" + $off);
			$ier = $die[$i] * $die[$i]/($dio[$i])?$dio[$i]:1 + $off;			// Relative inerr^2 with fix for / by 0
			if ($ier > 255){$ier = 255;}
			$bie = sprintf("%02x", $ier);
		}
		if ($oto[$i] > 70){									// ...since some always see that.
			$boo = sprintf("%02x","40" + $off);
			$oer = $doe[$i] * $doe[$i]/($doo[$i])?$doo[$i]:1 + $off;
			if ($oer > 255){$oer = 255;}
			$boe = sprintf("%02x", $oer);
		}
		sscanf($iflac[$i], "%d:%d:%d:%d.%d",$lcd,$lch,$lcm,$lcs,$ticks);
		$il		= $upmin - ($lcm + 60 * $lch + 1440 * $lcd);
		if($il <= 0){
			$iflch	= "-";
			$blc	= $bg3;
		}else{
			$ild	= intval($il / 1440);
			$ilh	= intval(($il - $ild * 1440)/60);
			$ilm	= intval($il - $ild * 1440 - $ilh * 60);
			$iflch	= sprintf("%d D %d:%02d",$ild,$ilh,$ilm);
			$rblcm	= $off + 1000/($il + 1);
			if($rblcm > 255){$rblcm = 255;}
			$blc	= sprintf("%02x",$rblcm );
		}
		list($ifimg,$iftit)	= Iftype($ift[$i]);

		echo "<tr class=\"$bg\">";
		echo "<th class=\"$ifstat\"><img src=\"img/$ifimg\" title=\"$i - $iftit\"></th>\n";
		echo "<td><b>$in</b></td>\n";
		echo "<td align=\"center\">$ifv[$i]</td><td>$ifi[$i]</td>\n";
		echo "<td align=right>$ifs[$i]</td><td align=\"center\">$ifd[$i]</td>\n";
		echo "<td align=right bgcolor=#$blc$bg3$bg3>$iflch</td>\n";

		if($shp){
			if($niflog[$in]){
				$bnl = sprintf("%02x","40" + $off);
				echo "<td bgcolor=#$bg3$bg3$bnl title=\"Last node tracked ". date($datfmt,$niflog[$in]) ."\" nowrap>";
			}else{
				echo "<td nowrap>";
			}

			if($ncount[$in]){
				echo Bar($ncount[$in],8) . " <a href=Nodes-List.php?ina=device&opa==&sta=$ud&cop=AND&inb=ifname&opb==&stb=$ui title=\"$ud-$in Nodes-List\">$ncount[$in]</a>\n";
			}
			echo "</td>\n";
		}
		if($shg){
			if($ud and $ui ){				// Still needed?
				echo "<td nowrap align=\"center\">\n";
				echo "<a href=Devices-Graph.php?dv=$ud&if%5B%5D=$ui title=\"$in Devices-Graph\">\n";
				echo "<img src=inc/drawrrd.php?dv=$ud&if%5B%5D=$ui&s=s&t=trf>\n";
				echo "<img src=inc/drawrrd.php?dv=$ud&if%5B%5D=$ui&s=s&t=err></a>\n";
			}else{
				echo "<td>Tell Remo, if you see this!!!</td>";
			}
		}else{
			echo "<td bgcolor=#$bg3$bg3$bio align=right>".$dio[$i]."</td>\n";
			echo "<td bgcolor=#$bg3$bg3$boo align=right>".$doo[$i]."</td>\n";
			echo "<td bgcolor=#$bie$bg3$bg3 align=right>".$die[$i]."</td>\n";
			echo "<td bgcolor=#$boe$bg3$bg3 align=right>".$doe[$i]."</td>\n";
		}
		echo "<td>";
		if($ifm[$i]){echo "<a href=Nodes-Status.php?mac=$ifm[$i]>$ifm[$i]</a><br>";}
		foreach ($net[$in] as $ip => $dmsk){
			list($pfix,$msk,$bmsk)	= Masker($dmsk);
			$dnet = long2ip($ip);
			echo "<a href=Reports-Networks.php?ipf=$dnet%2F$pfix&do=Show title=\"$dnet/$pfix Report-Networks\">$dnet</a>/$pfix ";
		}
		echo "</td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Interfaces</td></tr>
</table>
	<?
}
}elseif ($dld){
	if(preg_match("/adm/",$_SESSION['group']) ){
		$now = time();
		$query	= GenQuery('devdel','i','','','',array('device','user','time'),'',array($dld,$_SESSION['user'],$now) );
		if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Device $_GET[del] $upokmsg</h3>";}
?>
<script language="JavaScript"><!--
setTimeout("history.go(-2)",2000);
//--></script>
<?
	}else{
		echo $nokmsg;
	}
}
include_once ("inc/footer.php");
