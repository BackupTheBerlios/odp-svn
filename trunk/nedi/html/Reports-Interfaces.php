<?
/*
#============================================================================
# Program: Reports-Interfaces.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 21/04/05	initial version.
# 20/03/06	new SQL query support
# 22/03/07	relative counter support, new option scheme
# 25/07/07	Chart cleanup
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");

$_GET = sanitize($_GET);
$rep = isset($_GET['rep']) ? $_GET['rep'] : array();
$lim = isset($_GET['lim']) ? $_GET['lim'] : 10;
$alt = isset($_GET['alt']) ? "checked" : "";
$opt = isset($_GET['opt']) ? "checked" : "";
?>
<h1>Interface Reports</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/ddum.png" title="Use alternative and optimize for more specific details">
</a></th>
<th>Select Report(s)</th>
<th>
<SELECT MULTIPLE name="rep[]" size=4>
<OPTION VALUE="uif" <? if(in_array("uif",$rep)){echo "selected";} ?> >Active Interfaces
<OPTION VALUE="dif" <? if(in_array("dif",$rep)){echo "selected";} ?> >Disabled Interfaces
<OPTION VALUE="itr" <? if(in_array("itr",$rep)){echo "selected";} ?> >Traffic
<OPTION VALUE="lmi" <? if(in_array("lmi",$rep)){echo "selected";} ?> >Link Mismatch
</SELECT>

</th>
<th>Limit
<SELECT size=1 name="lim">
<? selectbox("limit",$lim);?>
</SELECT>
</th>
<th>
<INPUT type="checkbox" name="opt"  <?=$opt?> title="Make traffic relative to IF speed and errors relative to traffic"> optimize
</th>
<th>
<INPUT type="checkbox" name="alt"  <?=$alt?> title="Show ethernet only on Active IFs, incomplete link mismatches and absolute traffic counters"> alternative
</th>
</SELECT></th>
<th width=80><input type="submit" name="do" value="Show"></th>
</tr></table></form><p>
<?
if($rep){

$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('interfaces');
$res	= @DbQuery($query,$link);
if($res){
	$nif = 0;
	$ndif = 0;
	$nummo	= array();
	while( ($i = @DbFetchRow($res)) ){
		if($alt){
			if($i[4] == 6){								# alternatively only show ethernet IF or absolute counters
				$numif[$i[0]]++;
				if($i[12] > 70){$nactif[$i[0]]++;}
			}
			if($opt){
				if($i[12]){$topier["$i[0];;$i[1]"] = $i[13]/$i[12];}		# Using a flat array for  value based sorting
				if($i[14]){$topoer["$i[0];;$i[1]"] = $i[15]/$i[14];}		# Normalize errors to traffic...
				if($i[9]){							# ...and traffic to IF speed if available
					$topino["$i[0];;$i[1]"] = intval($i[12]/$i[9]);
					$topoto["$i[0];;$i[1]"] = intval($i[14]/$i[9]);
				}
			}else{
				$topino["$i[0];;$i[1]"] = $i[12];
				$topier["$i[0];;$i[1]"] = $i[13];
				$topoto["$i[0];;$i[1]"] = $i[14];
				$topoer["$i[0];;$i[1]"] = $i[15];
			}
		}else{
			$numif[$i[0]]++;
			if($i[12] > 70){$nactif[$i[0]]++;}
			if($opt){
				if($i[16]){$topier["$i[0];;$i[1]"] = $i[17]/$i[16];}
				if($i[18]){$topoer["$i[0];;$i[1]"] = $i[19]/$i[18];}
				if($i[9]){
					$topino["$i[0];;$i[1]"] = sprintf("%1.2f",$i[16]*800/$i[9]/$rrdstep);
					$topoto["$i[0];;$i[1]"] = sprintf("%1.2f",$i[18]*800/$i[9]/$rrdstep);
				}
			}else{
				$topino["$i[0];;$i[1]"] = intval($i[16]/$rrdstep);
				$topier["$i[0];;$i[1]"] = intval($i[17]/$rrdstep);
				$topoto["$i[0];;$i[1]"] = $i[18];
				$topoer["$i[0];;$i[1]"] = $i[19];
			}
		}
		$ifal["$i[0];;$i[1]"] = $i[7];
		$ifsp["$i[0];;$i[1]"] = $i[9];
		$ifdu["$i[0];;$i[1]"] = $i[10];
		$ifvl["$i[0];;$i[1]"] = $i[11];
		if($i[8] == 2){$ndif++;$disif[$i[0]] .= "$i[1] ";}
		$nif++;
	}
	@DbFreeResult($res);
}else{
	echo @DbError($link);
	die;
}

if ( in_array("uif",$rep) ){
	if($alt){$ifty = "ethernet";}else{$ifty = "all";}
	foreach ($numif as $dv => $ni){
		$ainorm[$dv] = intval(100 * $nactif[$dv] / $ni);
	}
	arsort($ainorm);

?>

<table class="full fixed"><tr><td class="helper">

<h2>Most Used Devices</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Total Interfaces</th>
<th><img src="img/32/cnic.png"><br>Used Interfaces</th>
<?
	$row = 0;
	foreach ($ainorm as $dv => $up){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ubar	= Bar($up,48);
		$ud	= rawurlencode($dv);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$row</th><td><a href=Devices-Status.php?dev=$ud&shp=on>$dv</a></td>\n";
		echo "<td align=center>".$numif[$dv]."</td><td>$ubar $up % (".$nactif[$dv].")</td></tr>\n";
		if($row == $_GET['lim']){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Most used devices by <?=$ifty?> interfaces</td></tr>
</table>
	<?
?>
</td><td class="helper">

<h2>Least Used Devices</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Total Interfaces</th>
<th><img src="img/32/cnic.png"><br>Used Interfaces</th>
<?
	asort($ainorm);
	$row = 0;
	foreach ($ainorm as $dv => $up){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ubar	= Bar($up,48);
		$ud	= rawurlencode($dv);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$row</th><td><a href=Devices-Status.php?dev=$ud&shp=on>$dv</a></td>\n";
		echo "<td align=center>".$numif[$dv]."</td><td>$ubar $up % (".$nactif[$dv].")</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Least used devices by <?=$ifty?> interfaces</td></tr>
</table>

</td></tr></table>

	<?
}

if ( in_array("dif",$rep) ){
?>
<h2>Disabled Interfaces</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/bstp.png"><br>Disabled Interfaces</th>
<?
	if($ndif){
		if($alt){
			krsort($disif);
		}else{
			ksort($disif);
		}
		$row = 0;
		foreach ($disif as $dv => $di){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud	= rawurlencode($dv);
			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			echo "$row</th><td><a href=Devices-Status.php?dev=$ud>$dv</a></td>\n";
			echo "<td>$di</td></tr>\n";
		}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$ndif?> Disabled interfaces on <?=$row?> devices</td></tr>
</table>
	<?
}

if ( in_array("itr",$rep) ){
	if($opt){$relt = "relative";}else{$relt = "absolute";}
	if($alt){$tott = "in total";}else{$tott = "for ${rrdstep}s";}

?>
<table class="full fixed"><tr><td class="helper">

<h2>Inbound Traffic</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Interface</th>
<th><img src="img/32/bup.png"><br>Octets</th></tr>
<? TrafficCharts($topino,'trf');?>
</table>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<td>Top <?=$lim?> Interfaces by <?=$relt?> traffic <?=$tott?></td></tr></table>

</td><td class="helper">

<h2>Inbound Errors</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Interface</th>
<th><img src="img/32/rbup.png"><br>Errors</th>
<? TrafficCharts($topier,'err');?>
</table>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<td>Top <?=$lim?> Interfaces by <?=$relt?> errors <?=$tott?></td></tr></table>

</td></tr><tr><td class="helper">


<h2>Outbound Traffic</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Interface</th>
<th><img src="img/32/bdwn.png"><br>Octets</th>
<? TrafficCharts($topoto,'trf');?>
</table>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<td>Top <?=$lim?> Interfaces by <?=$relt?> traffic <?=$tott?></td></tr></table>

</td><td class="helper">

<h2>Outbound Errors</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=25%><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/dumy.png"><br>Interface</th>
<th><img src="img/32/rbdn.png"><br>Errors</th>
<? TrafficCharts($topoer,'err');?>
</table>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<td>Top <?=$lim?> Interfaces by <?=$relt?> errors <?=$tott?></td></tr></table>

</td></tr></table>
<?
}
if ( in_array("lmi",$rep) ){
?>
<h2>Link Mismatch</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=80><img src="img/32/fiqu.png" title="Speed,Vlan or Duplex are tested"><br>Mismatch</th>
<th><img src="img/32/dev.png" title="Check alt-order to show incomplete entries as well"><br>Device</th>
<th colspan=2><img src="img/32/dumy.png" title="Bold value is link related and tested here..."><br>Interface</th>
<th width=60><img src="img/32/fiap.png" title="C=CDP,M=Mac,O=Oui,V=VoIP,L=LLDP,S=static"><br>Type</th>
<th><img src="img/32/dev.png"><br>Device</th>
<th colspan=2><img src="img/32/dumy.png" title="...IF related value in () just for reference"><br>Interface</th>
<?
	$libw	= array();
	$query	= GenQuery('links');
	$res	= @DbQuery($query,$link);
	$nli    = @DbNumRows($res);
	if($res){
		$row = 0;
		while( ($l = @DbFetchRow($res)) ){
			$libw[$l[1]][$l[2]][$l[3]][$l[4]] = $l[5];			# Bandwidth is the only value, which is constructed from local IF in SNMP::CDP/LLDP
			$lity[$l[1]][$l[2]][$l[3]][$l[4]] = $l[6];
			$lidu[$l[1]][$l[2]][$l[3]][$l[4]] = $l[8];			# Duplex and Vlan are read via CDP from remote side
			$livl[$l[1]][$l[2]][$l[3]][$l[4]] = $l[9];
		}
		@DbFreeResult($res);
	}else{
		echo @DbError($link);
		die;
	}
	$row = 0;
	foreach(array_keys($libw) as $dv){
		foreach(array_keys($libw[$dv]) as $if){
			foreach(array_keys($libw[$dv][$if]) as $nb){
				foreach(array_keys($libw[$dv][$if][$nb]) as $ni){
					$ud = rawurlencode($dv);
					$un = rawurlencode($nb);
					if($alt or $libw[$dv][$if][$nb][$ni] and $libw[$nb][$ni][$dv][$if]){
						if($libw[$dv][$if][$nb][$ni] != $libw[$nb][$ni][$dv][$if]){
							if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
							$row++;
							echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
							echo "<img src=\"img/spd.png\" title=\"bandwidth\"></th>\n";
							echo "<td><a href=Devices-Status.php?dev=$ud>$dv</a></td><td>$if (".Zfix($ifsp["$dv;;$if"]).")</td>\n";
							echo "<th class=\"$bi\">".Zfix($libw[$dv][$if][$nb][$ni])."</th>\n";
							echo "<th>".$lity[$dv][$if][$nb][$ni]."</th>\n";
							echo "<td><a href=Devices-Status.php?dev=$un>$nb</a></td><td>$ni (".Zfix($ifsp["$nb;;$ni"]).")</td>\n";
							echo "<th class=\"$bi\">".Zfix($libw[$nb][$ni][$dv][$if])."</th></tr>\n";
						}
					}
					if ($alt or strlen($lidu[$dv][$if][$nb][$ni]) == 2 and strlen($lidu[$nb][$ni][$dv][$if]) == 2){ 
						if($lidu[$dv][$if][$nb][$ni] != $lidu[$nb][$ni][$dv][$if]){
							if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
							$row++;
							echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
							echo "<img src=\"img/dpx.png\" title=\"duplex\"></th>\n";
							echo "<td><a href=Devices-Status.php?dev=$ud>$dv</a></td><td>$if (".$ifdu["$dv;;$if"].")</td>\n";
							echo "<th class=\"$bi\">".$lidu[$dv][$if][$nb][$ni]."</th>\n";
							echo "<th>".$lity[$dv][$if][$nb][$ni]."</th>\n";
							echo "<td><a href=Devices-Status.php?dev=$un>$nb</a></td><td>$ni (".$ifdu["$nb;;$ni"].")</td>\n";
							echo "<th class=\"$bi\">".$lidu[$nb][$ni][$dv][$if]."</th></tr>\n";
						}
					}
					if($alt or $livl[$dv][$if][$nb][$ni] and $livl[$nb][$ni][$dv][$if]){
						if($livl[$dv][$if][$nb][$ni] != $livl[$nb][$ni][$dv][$if]){
							if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
							$row++;
							echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
							echo "<img src=\"img/16/stat.png\" title=\"vlan\"></th>\n";
							echo "<td><a href=Devices-Status.php?dev=$ud>$dv</a></td><td>$if (Vlan".$ifvl["$dv;;$if"].")</td>\n";
							echo "<th class=\"$bi\">Vlan".$livl[$dv][$if][$nb][$ni]."</th>\n";
							echo "<th>".$lity[$dv][$if][$nb][$ni]."</th>\n";
							echo "<td><a href=Devices-Status.php?dev=$un>$nb</a></td><td>$ni (Vlan".$ifvl["$nb;;$ni"].")</td>\n";
							echo "<th class=\"$bi\">Vlan".$livl[$nb][$ni][$dv][$if]."</th></tr>\n";
						}
					}
					if($row == $lim){break;}
				}
				if($row == $lim){break;}
			}
			if($row == $lim){break;}
		}
		if($row == $lim){break;}
	}
	if($alt){$nli .= " <b>incomplete</b>";}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Mismatches of <?=$nli?> links in total</td></tr>
</table>
	<?
}
}	# End if($rep)

include_once ("inc/footer.php");

function TrafficCharts($traffic,$typ){

global $lim,$ifal,$ifsp;

	if($typ == "trf"){
		$unit = "octets";
	}else{
		$unit = "errors";
	}
	arsort($traffic);
	$row = 0;
	foreach ($traffic as $di => $tr){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$d = explode(';;', $di);
		$ud = rawurlencode($d[0]);
		$ui = rawurlencode($d[1]);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "$row</th><td><a href=Devices-Status.php?dev=$ud>$d[0]</a></td>\n";
		echo "<td><a href=Nodes-List.php?ina=device&opa==&sta=$ud&cop=AND&inb=ifname&opb==&stb=$ui>$d[1]</a> $ifal[$di] <i>";
		echo Zfix($ifsp[$di]) ."</i></td>\n";
		echo "<td align=center>\n";
		echo "<a href=Devices-Graph.php?dv=$ud&if%5B%5D=$ui>";
		echo "<img src=inc/drawrrd.php?dv=$ud&if%5B%5D=$ui&s=s&t=$typ title=\"$tr $unit\"></a>\n";
		if($row == $lim){break;}
	}
}

?>
