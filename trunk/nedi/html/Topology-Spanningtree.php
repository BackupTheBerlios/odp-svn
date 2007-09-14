<?
/*
#============================================================================
# Program: Topology-Spanningtree.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 18/04/05	initial version.
# 20/03/06	new SQL query  & graph support
# 25/05/07	Minor SQL and cosmetic improvements
# 31/08/07	implemented CSS scheme
*/

include_once ("inc/header.php");
include_once ('inc/libdev.php');

$_GET = sanitize($_GET);
$dev = isset($_GET['dev']) ? $_GET['dev'] : "";
$shg = isset($_GET['shg']) ? "checked" : "";
$vln = isset($_GET['vln']) ? $_GET['vln'] : "";
?>
<h1>Spanningtree Tool</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>" name="stree">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF']?> >
<img src="img/32/traf.png" title="Select VLAN for VLAN community indexing devices only">
</a></th>
<th>
Switch
<select size=1 name="dev" onchange="document.stree.vln.value=''">
<option value="">---
<?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('devices','s','name,ip,services,community,icon','','',array('services & 2'),array('='),array('2') );
$res	= @DbQuery($query,$link);
if($res){
	while( ($d = @DbFetchRow($res)) ){
		echo "<option value=\"$d[0]\" ";
		if($dev == $d[0]){
			echo "selected";
			$ud = rawurlencode($d[0]);
			$ip	= long2ip($d[1]);
			$sv	= Syssrv($d[2]);
			$comm	= $d[3];
			$img	= $d[4];
		}
		echo " >$d[0]\n";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}
echo "</select>";
if ($dev) {
	$query	= GenQuery('vlans','s','*','','',array('device'),array('='),array($dev) );
	$res	= @DbQuery($query,$link);
	$nvln	= @DbNumRows($res);

	if($res and $nvln){
?>
 Vlan
<select size=1 name="vln">
<option value="">---
<?

		while( ($v = @DbFetchRow($res)) ){
			echo "<OPTION VALUE=\"$v[1]\" ";
			if($vln == $v[1]){echo "selected";}
			echo " >$v[1] $v[2]\n";
		}
		@DbFreeResult($res);
		echo "</select>";
	}
}
echo "</th><th><input type=checkbox name=\"shg\" $shg> graphs</th>";
?>
<th width=80>
<input type="submit" value="Show">
</th>
</tr></table></form>
<?
if ($dev) {
	$query	= GenQuery('interfaces','s','ifidx,ifname,type,alias,comment','','',array('device'),array('='),array($dev) );
	$res	= @DbQuery($query,$link);
	while( ($i = @DbFetchRow($res)) ){
		$ifn[$i[0]] = $i[1];
		$ift[$i[0]] = $i[2];
		$ifi[$i[0]] = "$i[3] $i[4]";
	}
	@DbFreeResult($res);
if('0.0.0.0' == $ip){
	echo "<h4>no IP!</h4>";
	die;
}

?>

<table class="full fixed"><tr><td class="helper">

<h2>General Info</h2>
<table class="content">
<tr><th class="imga" width=80>
<a href=Devices-Status.php?dev=<?=$ud?> ><img src="img/dev/<?=$img?>.png" title="Status"></a>
<br><?=$dev?></th><td class="txta"><a href=telnet://<?=$ip?>><?=$ip?></a></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Services</th><td class="txtb"><?=($sv)?$sv:"&nbsp;"?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">SNMP</th><td class="txta"><?=$comm?></td></tr>
</table>

</td><td class="helper">

<h2>Spanningtree Info <?=($vln)?"for vlan $vln":""?></h2>
<table class="content"><tr>
<th class="<?=$modgroup[$self]?>2">Bridge Address</th><td  class="txta">
<?
	error_reporting(1);
	snmp_set_quick_print(1);

	$braddr	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.1.1.0",($timeout * 1000000) ) );
	if ($braddr){
		echo "$braddr</td></tr>\n";
	}else{
		echo "$toumsg</td></tr></table></th></tr></table>\n";
		include_once ("inc/footer.php");
		die;
	}
?>
<tr><th class="<?=$modgroup[$self]?>2">STP Priority</th><td class="txtb">
<?
	if($vln){$comm = "$comm@$vln";}
	$stppri	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.2.2.0") );
	if($stppri){
		echo "$stppri</td></tr>\n";
	}else{
		echo "$toumsg</td></tr></table></th></tr></table>\n";
		include_once ("inc/footer.php");
		die;
	}
	$laschg	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.2.3.0") );
	sscanf($laschg, "%d:%d:%0d:%0d.%d",$tcd,$tch,$tcm,$tcs,$ticks);
	$tcstr  = sprintf("%d D %d:%02d:%02d",$tcd,$tch,$tcm,$tcs);
	$numchg	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.2.4.0") );

	$droot	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.2.5.0") );
	$rport	= str_replace('"','',snmpget("$ip","$comm",".1.3.6.1.2.1.17.2.7.0") );
?>
<tr><th class="<?=$modgroup[$self]?>2">Topology Changes</th><td class="txta"><?=$numchg?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Topology Changed</th><td class="txtb"><?=$tcstr?></td></tr>
<tr><th class="<?=$modgroup[$self]?>2">Designated Root</th><td class="txta"><?=$droot?></td></tr>
</table>

</td></tr></table>

<h2>Interfaces</h2>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 ><img src="img/32/dumy.png"><br>Name</th>
<th><img src="img/32/say.png"><br>Info</th>
<? if($shg){echo '<th valign=bottom><img src=\"img/32/3d.png\"><br>Traffic/Errors</th>';} ?>
<th colspan=2><img src="img/32/tap.png"><br>State</th>
<th><img src="img/32/star.png"><br>Cost</th>
<?
	if( !is_array($ifn) ){
		echo "</table>\n";
		echo $resmsg;
		echo "<div align=center>$query</dev>";
		include_once ("inc/footer.php");
		die;
	}
	foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.17.1.4.1.2") as $ix => $val){
		$pidx[substr(strrchr($ix, "."), 1 )] = $val;
	}
	foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.17.2.15.1.3") as $ix => $val){
		$pstate[substr(strrchr($ix, "."), 1 )] = $val;
	}
	foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.17.2.15.1.4") as $ix => $val){
		$stpen[substr(strrchr($ix, "."), 1 )] = $val;
	}
	foreach (snmprealwalk("$ip","$comm",".1.3.6.1.2.1.17.2.15.1.5") as $ix => $val){
		$pcost[substr(strrchr($ix, "."), 1 )] = $val;
	}
	asort($pidx);

	$row = 0;
	foreach($pidx as $po => $ix){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$rpimg = "";
		if($rport == $po){$rpimg = "<img src=\"img/16/form.png\" title=Rootport>";}
		if($pstate[$po] == 1){$pst = "<img src=\"img/16/bcnl.png\">disabled";}
		elseif($pstate[$po] == 2){$pst = "<img src=\"img/16/bstp.png\">blocking";}
		elseif($pstate[$po] == 3){$pst = "<img src=\"img/16/bup.png\">listening";}
		elseif($pstate[$po] == 4){$pst = "<img src=\"img/16/brld.png\">learning";}
		elseif($pstate[$po] == 5){$pst = "<img src=\"img/16/brgt.png\">forwarding";}
		else{$pst = "<img src=\"img/16/bcls.png\">broken";}

		if($stpen[$po] == 1){$sten = "<img src=\"img/16/bchk.png\">STP enabled";}
		else{$sten = "<img src=\"img/16/bcnl.png\">STP disabled";}

		list($ifimg,$iftit) = Iftype($ift[$ix]);

		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<img src=\"img/$ifimg\" title=\"$iftit\"></th><th>$ifn[$ix]</th>\n";
		echo "<td>$ifi[$ix] $rpimg</td>\n";
		if($shg){
			if($d = rawurlencode($dev) and $if = rawurlencode($ifn[$ix]) ){
				echo "<td nowrap align=center>\n";
				echo "<a href=Devices-Graph.php?dv=$d&if%5B%5D=$if><img src=inc/drawrrd.php?dv=$d&if%5B%5D=$if&s=s&t=trf>\n";
				echo "<img src=inc/drawrrd.php?dv=$d&if%5B%5D=$if&s=s&t=err></a>\n";
			}else{
				echo "<td></td>";
			}
		}
		echo "<td>$pst</td><td>$sten</td></td><td align=center>$pcost[$po]</td>\n";
		echo "</tr>\n";
	}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Interfaces found</td></tr>
</table>
	<?
}
include_once ("inc/footer.php");
?>
