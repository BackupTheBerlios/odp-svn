<?
/*
#============================================================================
# Program: Report-Networks.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 21/04/05	initial version.
# 31/03/05	decimal IPs refined algorithm
# 20/03/06	new SQL query support
# 04/01/07	minor cosmetic enhancements and loopback indication
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$opr = isset($_GET['opr']) ? $_GET['opr'] : "";
$ipf = isset($_GET['ipf']) ? $_GET['ipf'] : "";
$do = isset($_GET['do']) ? $_GET['do'] : "";
?>
<h1>Network Report</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>" name="netlist">
<table class="content" ><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF']?> >
<img src="img/32/dnet.png" title="Lists Networks, using filter IP[/Prefix] and detects mask inconsistencies">
</a></th>
<th>
IP Address
<SELECT size=1 name="opr">
<OPTION VALUE="=" <?=($opr == "=")?"selected":""?> >equal
<OPTION VALUE="!=" <?=($opr == "!=")?"selected":""?> >not equal
</SELECT>
<input type="text" name="ipf" value="<?=$ipf?>" size="20"> 
</th>
<th width=80>
<input type="submit" name="do" value="Show">
</th>
</tr></table></form><p>
<?
if ($do) {
	$query	= GenQuery('networks','s','*','ip','',array('ip'),array('='),array($ipf) );
	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$res	= @DbQuery($query,$link);
	if ($res) {
		while( ($n = @DbFetchRow($res)) ){
			$n[2]	= ip2long(long2ip($n[2]));								// Hack to fix signing issue for 32bit vars in PHP!
			$n[3]	= ip2long(long2ip($n[3]));
			$dnet	= sprintf("%u",$n[2] & $n[3]);

			if( isset($nets[$dnet]) ){
				if($nets[$dnet] != $n[3]){
					$devs[$dnet][$n[0]]	= "$n[1] <span style=\"color : purple\">" .long2ip($n[3]) . "</span>";
				}else{
					if($devs[$dnet][$n[0]]){
						$devs[$dnet][$n[0]]	= "$n[1] <span class=\"grn\">multiple ok</span>";
					}else{
						$devs[$dnet][$n[0]]	= "$n[1] <span class=\"grn\">ok</span>";
					}
				}
			}else{
				$nets[$dnet] = $n[3];
				$pop[$dnet] = 0;
				$age[$dnet] = 0;
				if($n[3] == -1){
					$devs[$dnet][$n[0]] = "$n[1] <span class=\"prp\">hostroute</span>";
				}else{
					$devs[$dnet][$n[0]] = "$n[1] <span class=\"blu\">mask base</span>";
					$nquery	= GenQuery('nodes','a',"ip & $n[3]",'','lastseen - firstseen',array("ip & $n[3]"),array('='),array($dnet) );
					$nodres	= @DbQuery($nquery,$link);
					$no		= @DbFetchRow($nodres);
					$pop[$dnet]	= $no[1];
					$age[$dnet]	= intval($no[2]/86400);
					@DbFreeResult($nodres);
				}
			}
		}
		@DbFreeResult($res);

		if($nets){
?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2><img src="img/32/net.png"><br>Network</th>
<th width=30%><img src="img/32/dev.png"><br>Devices</th>
<th><img src="img/32/cubs.png"><br>Population</th>
<th><img src="img/32/clock.png"><br>Average Node Age</th>
</tr>
<?
			$row = 0;
			foreach(array_keys($nets) as $dn ){
				if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
				$row++;
				$net	= long2ip($dn);
				list($pfix,$mask,$bmsk)	= Masker($nets[$dn]);
				list($ntimg,$ntit)	= Nettype( $net );
				$pbar = Bar($pop[$dn],110);
				$abar = Bar($age[$dn]);
				$dvs = "";
				foreach( array_keys($devs[$dn]) as $dv ){
					$du = rawurlencode($dv);
					$dvs .= "<a href=Devices-Status.php?dev=$du>$dv</a> ".$devs[$dn][$dv]."<br>\n";
				}
				echo "<tr class=\"$bg\">";
				echo "<th class=\"$bi\" width=20><img src=\"img/16/$ntimg\" title=$ntit></th>\n";
				echo "<td><a href=Topology-Map.php?ina=network&flt=$net%2F$pfix&draw=1>$net/$pfix</a></td>\n";
				echo "<td>$dvs</td><td>$pbar <a href=Nodes-List.php?ina=ip&opa==&sta=$net/$pfix&ord=ip>$pop[$dn]</a></td>\n";
				echo "<td>$abar $age[$dn] days</td>\n";
				echo "</tr>\n";
			}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Networks</td></tr>
</table>
	<?
		}else{
			echo $resmsg;
		}
	}
}
include_once ("inc/footer.php");
?>
