<?
/*
#============================================================================
# Program: Reports-Incidents.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 27/07/06	initial version.
# 05/01/07	improved calendar, new agent reports.
# 04/09/07	implemented CSS scheme
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");
include_once ('inc/libdev.php');
include_once ('inc/libmon.php');

$_GET = sanitize($_GET);
$rep = isset($_GET['rep']) ? $_GET['rep'] : array();
$lim = isset($_GET['lim']) ? $_GET['lim'] : 10;
$alt = isset($_GET['alt']) ? "checked" : "";

$cpos = strpos($locformat, "c");
$bpos = strpos($locformat, "b");
?>
<h1>Incident Reports</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/dbmb.png" title="Increase Limit to see older calendars and check order for details">
</a></th>
<th>Select Report(s)</th>
<th>
<select multiple name="rep[]" size=4>
<OPTION value="dev" <? if(in_array("dev",$rep)){echo "selected";} ?> >Devices
<OPTION value="cat" <? if(in_array("cat",$rep)){echo "selected";} ?> >Categories
<OPTION value="agt" <? if(in_array("agt",$rep)){echo "selected";} ?> >Agents
<OPTION value="cal" <? if(in_array("cal",$rep)){echo "selected";} ?> >Calendar
</SELECT></th>
</th>
<th>Limit
<SELECT size=1 name="lim">
<? selectbox("limit",$lim);?>
</SELECT>
</th>
<th>
<INPUT type="checkbox" name="alt" <?=$alt?> > alternative
</th>
</SELECT></th>

<th width=80><input type="submit" name="do" value="Show"></th>
</tr></table></form><p>
<?
if($rep){
$now = getdate();
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('incidents');
$res	= @DbQuery($query,$link);
if($res){
	$tinc = 0;
	$numdv	= array();
	$numcat	= array();
	$insta	= array();
	while( ($i = @DbFetchRow($res)) ){
		$numdv[$i[2]]++;
		$numcat[$i[8]]++;
		$indev[$i[0]] = $i[2];
		$insta[$i[0]] = $i[4];
		$incat[$i[0]] = $i[8];
		if($i[5]){
		
			$inend[$i[0]] = $i[5];
		}else{
			$inend[$i[0]] = $now[0];
		}
		if($i[6]){
			$agdly[$i[6]] += $i[7] - $i[4];
			$aginc[$i[6]]++;
		}
		$tinc++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}
$query	= GenQuery('devices');
$res	= @DbQuery($query,$link);
if($res){
	$tdev = 0;
	while( ($d = @DbFetchRow($res)) ){
		$dip[$d[0]]  = long2ip($d[1]);
		$dtyp[$d[0]] = $d[3];
		$dos[$d[0]]  = $d[8];
		$dcon[$d[0]] = $d[11];
		$dico[$d[0]] = $d[18];
		$tdev++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}

if ( in_array("dev",$rep) ){
?>
<h3>Devices</h3><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=3><img src="img/32/dev.png"><br>Device</th>
<th><img src="img/32/umgr.png"><br>Contact</th>
<th width=50%><img src="img/32/bomb.png"><br>Incidents</th>
</tr>
<?
	if($alt){
		asort($numdv);
	}else{
		arsort($numdv);
	}
	$row = 0;
	foreach($numdv as $dv => $ndi){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$img = $dico[$dv];
		$ud  = rawurlencode($dv);
		$ibar = Bar($ndi,3);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<a href=Devices-Status.php?dev=$ud><img src=\"img/dev/$img.png\" title=\"$dtyp[$dv]\"><p></a>$dv</th>\n";
		echo "<td><a href=telnet://$dip[$dv]>$dip[$dv]</td><td>$dos[$dv]</td><td>$dcon[$dv]</td><td>$ibar $ndi</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> of <?=$tdev?> devices with incidents</td></tr>
</table>
	<?
}

if ( in_array("cat",$rep) ){
?>
<h3>Categories</h3><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2><img src="img/32/find.png"><br>Category</th>
<th><img src="img/32/eyes.png"><br>Seen</th>
</tr>
<?
	if($alt){
		ksort($numcat);
	}else{
		arsort($numcat);
	}
	$row = 0;
	foreach($numcat as $c => $nc){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$cimg = Cimg($c);
		$cbar = Bar($nc,3);
		echo "<tr class=\"$bg\"><th class=\"$bi\" width=80>\n";
		echo "<img src=\"img/32/$cimg.png\"></th>";
		echo "<td><a href=Monitoring-Incidents.php?cat=$c>$icat[$c]</a></td><td>$cbar $nc times</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> categories</td></tr>
</table>
	<?
}

if ( in_array("agt",$rep) ){
?>
<h3>Agents</h3><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th><img src="img/32/smil.png"><br>Name</th>
<th width=50%><img src="img/32/bomb.png"><br>Acknowledged Incidents</th>
<th><img src="img/32/clock.png"><br>Average Response Time</th>
</tr>
<?
	if($alt){
		asort($aginc);
	}else{
		arsort($aginc);
	}
	$row = 0;
	foreach($aginc as $na => $nainc){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$ibar = Bar($nainc);
		$adly = intval($agdly[$na] / $nainc / 3600);
		$dbar = Bar($adly,24);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<img src=\"".Smilie($na)."\" title=\"Hello I'm $na\"'><p>$na</th>\n";
		echo "<td>$ibar $nainc</td><td>$dbar $adly hours</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> agents</td></tr>
</table>
	<?
}

if ( in_array("cal",$rep) ){

$heuer = $now['year'];
if($lim == 20){$heuer -= 1;}
elseif($lim == 50){$heuer -= 2;}
elseif($lim == 100){$heuer -= 3;}
elseif($lim == 500){$heuer -= 4;}
elseif($lim == 0){$heuer -= 5;}
?>
<h3>Incidents Since<?=$heuer?></h3><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th></th>
<?
	for($d=1;$d < 32;$d++){
		echo "<th>$d</th>";
	}
	$row = 0;
	$prevm = "";
	for($t = strtotime(date("1/1/$heuer"));$t < $now[0];$t += 86400){
		$then = getdate($t);
		if($prevm != $then['month']){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			echo "</tr>\n<tr class=\"$bg\"><th class=\"$modgroup[$self]2\">". substr($then[month],0,3)." $then[year]</th>";
		}
		asort($insta);
		foreach($insta as $id => $st){
			if($st < ($t + 86400) ){
				if($inend[$id] < $t){
					unset($insta[$id]);
					unset($inend[$id]);
				}else{
					$curi[$t][] = $id;
				}
			}
		}
		if($then['wday'] == 0 or $then['wday'] == 6){
			$cl = "red";
		}else{
			$cl = "blu";
		}
		echo "<th class=\"$cl\">";
		if(isset($curi[$t]) ){
			if($alt){
				$ni = 0;
				foreach($curi[$t] as $id){
					$ni++;
					$cimg = Cimg($incat[$id]);
					$tit  = $indev[$id] . " had " .$icat[$incat[$id]] . " incident on $then[weekday]";
					echo "<a href=Monitoring-Incidents.php?id=$id>";
					echo "<img src=\"img/16/$cimg.png\" title=\"$tit\">";
					if ($ni == 4){echo "<br>";$ni = 0;}
					echo "</a>";
				}
			}else{
				$ninc = count($curi[$t]);
				if($ninc == 1){
					echo "<img src=\"img/16/bomb.png\" title=\"Only 1 incident on $then[weekday]\"></a>";
				}elseif($ninc < 10){
					echo "<img src=\"img/16/impt.png\" title=\"$ninc incidents on $then[weekday]\"></a>";
				}else{
					echo "<img src=\"img/16/bstp.png\" title=\"$ninc incidents on $then[weekday]!\"></a>";
				}
			}
		}else{
			echo substr($then['weekday'],0,1);
		}
		echo "</td>";
		$prevm = $then['month'];
	}
	echo "</table>";
}

}
include_once ("inc/footer.php");
?>
