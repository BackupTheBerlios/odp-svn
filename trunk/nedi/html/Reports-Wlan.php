<?
/*
#============================================================================
# Program: Reports-Wlan.php
# Programmer: Remo Rickli
#
# DATE     		COMMENT
# -----------------------------------------------------------
# 20/04/05	initial version.
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");
include_once ('inc/libnod.php');

$_GET = sanitize($_GET);
$ord = isset($_GET['ord']) ? $_GET['ord'] : "";
$all = isset($_GET['all']) ? "checked" : "";
?>
<h1>Wlan Access Points</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/dmobi.png" title="Lists potential APs on ports seeing more than 1 MAC address (show all overrides this).">
</a></th>
<th>Order by
<select name="ord" size=1>
<option value="name" <?=($ord == "name")?"selected":""?> >Name
<option value="ip" <?=($ord == "ip")?"selected":""?> >IP address
<option value="device" <?=($ord == "device")?"selected":""?> >Device

</SELECT>
</th>
<th>
<INPUT type="checkbox" name="all" <?=$all?> > Show all
</th>
<th width=80><input type="submit" value="Show"></th>
</tr></table></form><p>
<?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('wlan');
$res	= @DbQuery($query,$link);
if($res){
	$nwmac = 0;
	while( ($w = @DbFetchRow($res)) ){
		$nwmac++;
		$wlap[] = "$w[0]";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}

?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=4><img src="img/32/mobil.png"><br>Name - IP - MAC Match</th>
<th colspan=3><img src="img/32/dev.png"><br>Device - IF - Nodes</th>
<th colspan=2><img src="img/32/clock.png"><br>First Seen / Last Seen</th>

<?

$query	= GenQuery('nodes');
$res	= @DbQuery($query,$link);
while( ($n = @DbFetchRow($res)) ){
	$macs["$n[6];;$n[7]"]++;
}
	$row = 0;
	$nno = 0;
	$query	= GenQuery('nodes','s','*',$ord);
	$res	= @DbQuery($query,$link);
	while( ($n = @DbFetchRow($res)) ){
		if($macs["$n[6];;$n[7]"] > 1 or $all){
			$m = substr($n[2],0,8);
			if(in_array("$m", $wlap,1) ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$name	= preg_replace("/^(.*?)\.(.*)/","$1", $n[0]);
				$ip	= long2ip($n[1]);
				$img	= Nimg("$n[2];$n[3]");
				$fs	= date("j.M G:i",$n[4]);
				$ls	= date("j.M G:i",$n[5]);
				$pbar	= Bar($macs[$n[6]][$n[7]],5);
				$ud	= rawurlencode($n[6]);
				list($fc,$lc)	= Agecol($n[4],$n[5],$row);
				echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
				echo "<a href=Nodes-Status.php?mac=$n[2]><img src=\"img/oui/$img\" title=\"$n[3] ($n[2])\"></a></th>\n";
				echo "<td>$name</td><td>$ip</td><td>$m</td><td>$n[6]</td><td><a href=Nodes-List.php?ina=device&opa==&sta=$ud&cop=AND&inb=ifname&opb==&stb=$n[7]&>$n[7]</a></td><td>$pbar".$macs["$n[6];;$n[7]"]."</td>\n";
				echo "<td bgcolor=#$fc>$fs</td><td bgcolor=#$lc>$ls</td>";
				echo "</tr>\n";
			}
		}
		$nno++;
	}
?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> out of <?=$nno?> Nodes matching <?=$nwmac?> MAC samples</td></tr>
</table>
<?
include_once ("inc/footer.php");
?>
