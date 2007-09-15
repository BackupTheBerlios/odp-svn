<?
/*
#============================================================================
# Program: Reports-Devices.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 21/04/05	initial version.
# 20/03/06	new SQL query support
# 04/09/07	implemented CSS scheme, added vtp mode report
*/

error_reporting(E_ALL ^ E_NOTICE);

include_once ("inc/header.php");
include_once ('inc/libdev.php');

$_GET = sanitize($_GET);
$rep = isset($_GET['rep']) ? $_GET['rep'] : array();
$lim = isset($_GET['lim']) ? $_GET['lim'] : 10;
$ord = isset($_GET['ord']) ? "checked" : "";
?>
<h1>Device Reports</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/dtap.png" title="Device based statistics">
</a></th>
<th>Select Report(s)</th>
<th>
<select multiple name="rep[]" size=4>
<option value="typ" <? if(in_array("typ",$rep)){echo "selected";} ?> >Device Types
<option value="sft" <? if(in_array("sft",$rep)){echo "selected";} ?> >Software
<option value="vtp" <? if(in_array("vtp",$rep)){echo "selected";} ?> >VTP Statistics
<option value="ust" <? if(in_array("ust",$rep)){echo "selected";} ?> >Update Stats 

</select></th>
</th>
<th>Limit
<select size=1 name="lim">
<? selectbox("limit",$lim);?>
</select>
</th>
<th>
<input type="checkbox" name="ord" <?=$ord?> > alternative order
</th>
</select></th>

<th width=80><input type="submit" name="do" value="Show"></th>
</tr></table></form><p>
<?
if($rep){
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
$query	= GenQuery('devices');
$res	= @DbQuery($query,$link);
if($res){
	$ndev = 0;
	while( ($d = @DbFetchRow($res)) ){
		$dtyp[$d[3]]++;
		$dico[$d[3]] = $d[18];
		$fseen[$d[4]]++;
		$lseen[$d[5]]++;
		$dops[$d[8]]++;
		$dbim[$d[9]]++;
		$vtpd[$d[12]]++;
		$vtpm[$d[13]]++;
		$ndev++;
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
	die;
}

if ( in_array("typ",$rep) ){
?>
<h2>Device Types</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th colspan=2 width=33%><img src="img/32/fiap.png"><br>Type</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	$ntyp = 0;
	if($ord){
		arsort($dtyp);
	}else{
		ksort($dtyp);
	}
	$row = 0;
	foreach ($dtyp as $typ => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$img	= $dico[$typ];
		$tbar	= Bar($n,0);
		$utyp	= rawurlencode($typ);
		echo "<tr class=\"$bg\"><th class=\"$bi\" width=10%>\n";
		echo "<a href=Devices-List.php?ina=type&opa==&sta=$utyp><img src=\"img/dev/$img.png\" title=\"$typ\"></a></th>\n";
		echo "<td><a href=Reports-Modules.php?rep[]=inv&flt=$utyp>$typ</a></td>\n";
		echo "<td>$tbar $n</td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> device types of <?=$ndev?> devices in total</td></tr>
</table>
	<?
}

if ( in_array("sft",$rep) ){
?>

<table class="full fixed"><tr><td class="helper">

<h2>Operating Systems</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th><img src="img/32/os.png"><br>OS</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	ksort($dops);
	$row = 0;
	foreach ($dops as $ops => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		if(!$ops){$ops="^$";}
		$tbar = Bar($n,0);
		$uops = rawurlencode($ops);
		echo "<tr class=\"$bg\">\n";
		echo "<td><a href=Devices-List.php?ina=os&opa==&sta=$uops>$ops</a></td>\n";
		echo "<td>$tbar $n</td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Operating systems of <?=$ndev?> devices in total</td></tr>
</table>

</td><td class="helper">

<h2>Boot Images</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th><img src="img/32/foto.png"><br>Boot Image</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	ksort($dbim);
	$row = 0;
	foreach ($dbim as $bim => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$o = "=";
		if(!$bim){$bim="^$";$o="regexp";}
		$tbar = Bar($n,0);
		$ubim = rawurlencode($bim);
		echo "<tr class=\"$bg\">\n";
		echo "<td><a href=Devices-List.php?ina=bootimage&opa=$o&sta=$ubim>$bim</a></td>\n";
		echo "<td>$tbar $n</td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Boot images of <?=$ndev?> devices in total</td></tr>
</table>

</td></tr></table>

	<?
}

if ( in_array("vtp",$rep) ){
?>

<table class="full fixed"><tr><td class="helper">

<h2>VTP Domains</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=33%><img src="img/32/stat.png"><br>VTP Domain</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	if($ord){
		arsort($vtpd);
	}else{
		ksort($vtpd);
	}
	$row = 0;
	foreach ($vtpd as $vtp => $n){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$op="=";
		if(!$vtp){$vtp="^$";$op="regexp";}
		$tbar = Bar($n,0);
		$uvtp = rawurlencode($vtp);
		echo "<tr class=\"$bg\">\n";
		echo "<td><b>$vtp</b></td><td>$tbar <a href=Devices-List.php?ina=vtpdomain&opa=$op&sta=$vtp>$n</a></td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> VTP domains of <?=$ndev?> devices in total</td></tr>
</table>

</td><td class="helper">

<h2>VTP Modes</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=33%><img src="img/32/stat.png"><br>VTP Mode</th>
<th><img src="img/32/dev.png"><br>Devices</th>
<?
	if($ord){
		arsort($vtpm);
	}else{
		ksort($vtpm);
	}
	$row = 0;
	foreach ($vtpm as $vtp => $m){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$tbar = Bar($m,0);
		$uvtp = rawurlencode($vtp);
		echo "<tr class=\"$bg\">\n";
		echo "<th>".VTPmod($vtp)."</th><td>$tbar <a href=Devices-List.php?ina=vtpmode&opa==&sta=$vtp>$m</a></td></tr>\n";
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> VTP domains of <?=$ndev?> devices in total</td></tr>
</table>

</td></tr></table>

	<?
}

if ( in_array("ust",$rep) ){
?>
<h2>Devices Update Statistic</h2><p>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<th width=33%><img src="img/32/clock.png"><br>Timestamp</th>
<th><img src="img/32/eyes.png"><br>Events</th>
<?

	foreach($fseen as $k => $v){
		$devup[$k]['fs'] = $v;
	}
	foreach($lseen as $k => $v){
		$devup[$k]['ls'] = $v;
	}
	if($ord){
		ksort ($devup);
	}else{
		krsort ($devup);
	}
	$row = 0;
	foreach ( array_keys($devup) as $d ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$devup[$d]['fs'] = isset($devup[$d]['fs']) ? $devup[$d]['fs'] : 0;
		$devup[$d]['ls'] = isset($devup[$d]['ls']) ? $devup[$d]['ls'] : 0;
		if(!$devup[$d]['fs']){$devup[$d]['fs'] = 0;}
		if(!$devup[$d]['ls']){$devup[$d]['ls'] = 0;}
		$fbar = Bar($devup[$d]['fs'],100000);
		$lbar = Bar($devup[$d]['ls'],1);
		$fd   = rawurlencode(date("m/d/Y H:i:s",$d));
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo date($datfmt,$d)."</th>\n";
		echo "<td>$fbar <a href=Devices-List.php?ina=firstseen&opa==&sta=$fd>".$devup[$d]['fs']."</a> first seen<br>\n";
		echo "$lbar <a href=Devices-List.php?ina=lastseen&opa==&sta=$fd>".$devup[$d]['ls']."</a> last seen</td></tr>\n";
		if($row == $lim){break;}
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Updates from <?=$ndev?> devices in total</td></tr>
</table>
	<?
}

}
include_once ("inc/footer.php");
?>
