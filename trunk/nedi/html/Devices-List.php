<?
/*
#============================================================================
# Program: Devices-List.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 22/02/05	initial version.
# 04/03/05	Revised backend
# 31/03/05	decimal IPs
# 10/03/06	new SQL query support
# 29/01/07	new Sorting approach
# 30/08/07	implemented CSS scheme
*/

$calendar= 1;

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
$col = isset($_GET['col']) ? $_GET['col'] : array('name','ip','serial','type','location','contact');

$cols = array(	"name"=>"Name",
		"ip"=>"Main IP",
		"origip"=>"Original IP",
		"serial"=>"Serial #",
		"type"=>"Type",
		"services"=>"Services",
		"description"=>"Description",
		"os"=>"OS",
		"bootimage"=>"Bootimage",
		"location"=>"Location",
		"contact"=>"Contact",
		"vtpdomain"=>"VTP Domain",
		"vtpmode"=>"VTP Mode",
		"snmpversion"=>"SNMP Ver",
		"community"=>"Community",
		"cliport"=>"CLI port",
		"login"=>"Login",
		"firstseen"=>"First Seen",
		"lastseen"=>"Last Seen",
		"cpu"=>"CPU Usage",
		"memcpu"=>"Avail CPU Mem",
		"memio"=>"Avail IO Mem",
		"temp"=>"Temperature"
		);

?>
<h1>Device List</h1>
<form method="get" name="list" action="<?=$_SERVER['PHP_SELF']?>">
<table class="content"><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/dev.png" title="Conditions are regexp, IPs can have [/Prefix] to match subnets.">
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
<p><a href="javascript:show_calendar('list.sta');"><img src="img/cal.png"></a>
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
<p><a href="javascript:show_calendar('list.stb');"><img src="img/cal.png"></a>
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
<option value="graphs" <?=(in_array("graphs",$col))?"selected":""?> >Graphs
</SELECT>
</th>
<th width=80><input type="submit" value="Show"></th>
</tr></table></form><p>
<?
if ($ina){
	?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
	<?
	ColHead('name',80);
	foreach($col as $h){
		if($h != 'graphs' and $h != 'name'){
			ColHead($h);
		}
	}
	if( in_array("graphs",$col) ){echo "<th>Graphs</th>";}
	echo "</tr>\n";

	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('devices','s','*',$ord,'',array($ina,$inb),array($opa,$opb),array($sta,$stb),array($cop) );
	$res	= @DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($dev = @DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$ip = long2ip($dev[1]);
			$oi = long2ip($dev[19]);
			$ud = rawurlencode($dev[0]);
			list($fc,$lc) = Agecol($dev[4],$dev[5],$row % 2);
			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			if(in_array("name",$col)){
				echo "<a href=Devices-Status.php?dev=$ud><img src=\"img/dev/$dev[18].png\" title=\"$dev[3]\" vspace=4></a><br>\n";
				}
			echo "<b>$dev[0]</b>\n";
			if(in_array("ip",$col)){
				echo "<td><a href=telnet://$ip>$ip</a></td>";
			}
			if(in_array("origip",$col)){
				echo "<td><a href=telnet://$oi>$oi</a></td>";
			}
			if(in_array("serial",$col)){ echo "<td>$dev[2]</td>";}
			if(in_array("type",$col)){ 
				if( strstr($dev[3],"1.3.6.") ){
					echo "<td><a href=Other-Defgen.php?so=$dev[3]&ip=$ip&co=$dev[15]>$dev[3]</a></td>";
				}else{
					echo "<td>$dev[3]</td>";
				}
			}
			if(in_array("services",$col)){
				$sv = Syssrv($dev[6]);
				echo "<td>$sv ($dev[6])</td>";
			}
			if(in_array("description",$col)){echo "<td>$dev[7]</td>";}
			if(in_array("os",$col))		{echo "<td>$dev[8]</td>";}
			if(in_array("bootimage",$col))	{echo "<td>$dev[9]</td>";}
			if(in_array("location",$col))	{echo "<td>$dev[10]</td>";}
			if(in_array("contact",$col))	{echo "<td>$dev[11]</td>";}
			if(in_array("vtpdomain",$col))	{echo "<td>$dev[12]</td>";}
			if(in_array("vtpmode",$col))	{echo "<td>".VTPmod($dev[13])."</td>";}
			if(in_array("snmpversion",$col)){echo "<td>". ($dev[14] & 127) . (($dev[14] & 128)?"HC":"") ."</td>";}
			if(in_array("community",$col))	{echo "<td>$dev[15]</td>";}
			if(in_array("login",$col))	{echo "<td>$dev[17]</td>";}
			if(in_array("cliport",$col))	{echo "<td>$dev[16]</td>";}
			if( in_array("firstseen",$col) ){
				$fs       = date("j.M G:i:s",$dev[4]);
				echo "<td bgcolor=#$fc>$fs</td>";
			}
			if( in_array("lastseen",$col) ){
				$ls       = date("j.M G:i:s",$dev[5]);
				echo "<td bgcolor=#$lc>$ls</td>";
			}
			if(in_array("cpu",$col))	{echo "<td align=right>$dev[20]</td>";}
			if(in_array("memcpu",$col))	{echo "<td align=right>$dev[21]</td>";}
			if(in_array("memio",$col))	{echo "<td align=right>$dev[22]</td>";}
			if(in_array("temp",$col))	{echo "<td align=right>$dev[23]</td>";}
			if(in_array("graphs",$col)){
				echo "<th><a href=Devices-Graph.php?dv=$ud&cpu=on><img src=inc/drawrrd.php?dv=$ud&t=cpu&s=s title=\"CPU load\">";
				echo "<a href=Devices-Graph.php?dv=$ud&mem=on><img src=inc/drawrrd.php?dv=$ud&t=mem&s=s title=\"Available Memory\">";
				echo "<a href=Devices-Graph.php?dv=$ud&tmp=on><img src=inc/drawrrd.php?dv=$ud&t=tmp&s=s title=\"Temperature\"></th>";
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
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Devices (<?=$query?>)</td></tr>
</table>
	<?
}
include_once ("inc/footer.php");
?>
