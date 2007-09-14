<?
/*
#============================================================================
# Program: Devices-Vlans.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 19/07/07	initial version.
# 30/08/07	implemented CSS scheme
*/

include_once ("inc/header.php");
include_once ('inc/libdev.php');

$_GET = sanitize($_GET);
$sta = isset($_GET['sta']) ? $_GET['sta'] : "";
$stb = isset($_GET['stb']) ? $_GET['stb'] : "";
$ina = isset($_GET['ina']) ? $_GET['ina'] : "";
$inb = isset($_GET['inb']) ? $_GET['inb'] : "";
$opa = isset($_GET['opa']) ? $_GET['opa'] : "";
$opb = isset($_GET['opb']) ? $_GET['opb'] : "";
$cop = isset($_GET['cop']) ? $_GET['cop'] : "";
$ord = isset($_GET['ord']) ? $_GET['ord'] : "";
$col = isset($_GET['col']) ? $_GET['col'] : array('device','vlanid','vlanname');

$cols = array(	"device"=>"Device",
		"vlanid"=>"Vlan ID",
		"vlanname"=>"Vlan Name",
		);

?>
<h1>Vlan List</h1>
<form method="get" name="list" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/stat.png" title="Search Vlan table.">
</a></th>
<th valign=top>Condition A<p>
<SELECT size=1 name="ina">
<?
foreach ($cols as $k => $v){
       $selopt = ($ina == $k)?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
</SELECT>
<SELECT size=1 name="opa">
<? selectbox("oper",$opa);?>
</SELECT>
<p>
<input type="text" name="sta" value="<?=$sta?>" size="25">
</th>
<th valign=top>Operation<p>
<SELECT size=1 name="cop">
<? selectbox("comop",$cop);?>
</SELECT>
</th>
<th valign=top>Condition B<p>
<SELECT size=1 name="inb">
<?
foreach ($cols as $k => $v){
       $selopt = ($inb == $k)?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
</SELECT>
<SELECT size=1 name="opb">
<? selectbox("oper",$opb);?>
</SELECT>
<p>
<input type="text" name="stb" value="<?=$stb?>" size="25">
</th>
<th valign=top>Display<p>
<SELECT MULTIPLE name="col[]" size=4>
<?
foreach ($cols as $k => $v){
       $selopt = (in_array($k,$col))?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
<OPTION VALUE="pop" <?=(in_array("pop",$col))?"selected":""?> >Population
</SELECT>
</th>
<th width=80><input type="submit" value="Show"></th>
</tr></table></form><p>
<?
if ($ina){
	?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
	<?
	foreach($col as $h){
		if($h != 'pop'){
			ColHead($h);
		}
	}
	if( in_array("pop",$col) ){echo "<th>Population</th>";}
	echo "</tr>\n";

	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('vlans','s','*',$ord,'',array($ina,$inb),array($opa,$opb),array($sta,$stb),array($cop) );
	$res	= @DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($v = @DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ud = rawurlencode($v[0]);
			echo "<tr class=\"$bg\">";
			if(in_array("device",$col)){
				echo "<td><a href=Devices-Status.php?dev=$ud>$v[0]</a></td>\n";
			}
			if(in_array("vlanid",$col)){echo "<td>$v[1]</td>";}
			if(in_array("vlanname",$col)){echo "<td>$v[2]</td>";}
			if(in_array("pop",$col)){
				$nquery	= GenQuery('nodes','g','vlanid','','',array('device','vlanid'),array('=','='),array($v[0],$v[1]),array('AND') );
				$np  = @DbQuery($nquery,$link);
				$nnp = @DbNumRows($np);
				if ($nnp == 1) {
					$vpop = @DbFetchRow($np);
					$pbar = Bar($vpop[1],110);
					echo "<td>$pbar <a href=Nodes-List.php?ina=device&opa==&sta=$v[0]&inb=vlanid&opb==&stb=$v[1]&cop=AND>$vpop[1]</td>";
				}else{
					echo "<td></td>";
				}
				@DbFreeResult($np);
			}
			echo "</tr>\n";
		}
		@DbFreeResult($res);
	}else{
		print @DbError($link);
	}
	?>
</table>
<table class="content">
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Vlans (<?=$query?>)</td></tr>
</table>
	<?
}
include_once ("inc/footer.php");
?>
