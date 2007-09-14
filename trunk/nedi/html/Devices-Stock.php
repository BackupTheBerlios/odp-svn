<?

/*
#============================================================================
# Program: Devices-Stock.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -------- ------------------------------------------------------------------
# 22/02/05	initial version.
# 04/03/05	revised backend
# 10/03/05	revised authentication
# 07/03/06	renamed time field, added icons
# 17/03/06	new SQL query support
# 05/03/07	location field
# 04/09/07	implemented CSS scheme, adjusted layout
*/

$cico['10']  = "star";
$cico['100'] = "brld";
$cico['150'] = "impt";
$cico['200'] = "bstp";

include_once ("inc/header.php");
include_once ('inc/libdev.php');
$_GET = sanitize($_GET);
?>
<h1>Stock Management</h1>
<form method="get" action="<?=$_SERVER['PHP_SELF']?>" name="add">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/pkg.png" title="Devices and modules will be removed if SN# is found in discovery">
</a></th>

<th>Serial# <input type="text" name="ser" size="20" OnFocus="select();"></th>
<th>Type/Model<input type="text" name="typ" size="20" OnFocus="select();"></th>
<th>Location <input type="text" name="loc" size="12" OnFocus="select();"></th>
<th>Condition <select size=1 name="con">
<?
foreach (array_keys($stco) as $c){
	echo "<option value=\"$c\" ";
	echo ">$stco[$c]\n";
}
?>
</select></th>
<th width=80><input type="submit" value="Add" name="add"></th></tr>
</table></form>
<script type="text/javascript">
document.add.ser.focus();
</script>
<p>
<?
$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);

if( isset($_GET['add']) or isset($_GET['del']) ){
	if( preg_match("/adm/",$_SESSION['group']) ){
		if( isset($_GET['del']) ){
			$query	= GenQuery('stock','d','','','',array('serial'),array('='),array($_GET['del']) );
			if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Device $_GET[del] $delokmsg</h3>";}
		}elseif ($_GET['add'] and $_GET['ser'] and $_GET['typ'] and $_GET['loc'] and $_GET['con']){
			$now = time();
			$query	= GenQuery('stock','i','','','',array('serial','type','user','time','location','state'),'',array($_GET['ser'],$_GET['typ'],$_SESSION['user'],$now,$_GET['loc'],$_GET['con'] ) );
			if( !@DbQuery($query,$link) ){echo "<h4>".DbError($link)."</h4>";}else{echo "<h3>Device $_GET[ser] $upokmsg</h3>";}
		}
	}else{
		echo $nokmsg;
	}
}
?>

<table class="full fixed"><tr><td class="helper">

<h2>Available Items</h2>
<table class="content">
<tr class="<?=$modgroup[$self]?>2">
<th><img src="img/32/fiap.png"><br>Type/Model</th>
<th><img src="img/32/dev.png"><br>Quantity</th>

<?
$query	= GenQuery('stock','g','type');
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( ($dev = @DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$stbar = Bar($dev[1],0);
		echo "<tr class=\"$bg\">\n";
		echo "<td>$dev[0]</td><td>$stbar $dev[1]</td></tr>\n";
	}
}
?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Types/Models (<?=$query?>)</td></tr>
</table>

</td><td class="helper">

<h2>Location Distribution</h2>
<table class="content">
<tr class="<?=$modgroup[$self]?>2">
<th><img src="img/32/glob.png"><br>Location</th>
<th><img src="img/32/dev.png"><br>Quantity</th>

<?
$query	= GenQuery('stock','g','location');
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( ($dev = @DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$stbar = Bar($dev[1],0);
		echo "<tr class=\"$bg\">\n";
		echo "<td>$dev[0]</td><td>$stbar $dev[1]</td></tr>\n";
	}
}
?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Locations (<?=$query?>)</td></tr>
</table>

</td></tr></table>

<h2>Detailed Inventory</h2>
<table class="content">
<tr class="<?=$modgroup[$self]?>2">
<th colspan=2><img src="img/32/key.png"><br>Serial #</th>
<th><img src="img/32/fiap.png"><br>Type/Model</th>
<th><img src="img/32/smil.png"><br>Added by</th>
<th><img src="img/32/clock.png"><br>Added on</th>
<th><img src="img/32/glob.png"><br>Location</th>
<th><img src="img/32/idea.png"><br>Action</th></tr>

<?
$query	= GenQuery('stock','s','*','type');
$res	= @DbQuery($query,$link);
if($res){
	$row = 0;
	while( ($dev = @DbFetchRow($res)) ){
		if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
		$row++;
		$img = "genh.png";
		$ud  = rawurlencode($dev[0]);
		$da  = date("j.M (G:i)",$dev[3]);
		list($a1c,$a2c) = Agecol($dev[3],$dev[3],$row % 2);
		echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
		echo "<img src=\"img/16/" . $cico[$dev[5]] . ".png\" title=" . $stco[$dev[5]] . "></th><td>$dev[0]</td>\n";
		echo "<td>$dev[1]</td><td>$dev[2]</td><td bgcolor=#$a1c>$da</td><td>$dev[4]</td>\n";
		echo "<td align=center><a href=$_SERVER[PHP_SELF]?del=$ud><img src=\"img/16/bcnl.png\" onclick=\"return confirm('Delete $dev[0] from stock?')\" title=\"Delete this device!\"></a></td>\n";
		echo "</tr>\n";
	}
	@DbFreeResult($res);
}else{
	print @DbError($link);
}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Devices (<?=$query?>)</td></tr>
</table>
	<?

include_once ("inc/footer.php");
?>
