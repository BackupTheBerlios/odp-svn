<?
/*
#============================================================================
# Program: Nodes-List.php
# Programmer: Remo Rickli
#
# DATE		COMMENT
# -----------------------------------------------------------
# 25/02/05	initial version
# 04/03/05	Revised backend
# 31/03/05	decimal IPs
# 17/03/06	new SQL query support
# 29/01/07	new Sorting approach
# 12/04/07	Minor GUI fixes
# 30/08/07	implemented CSS scheme
*/

$calendar= 1;

include_once ("inc/header.php");
include_once ('inc/libnod.php');

$_GET = sanitize($_GET);
$sta = isset($_GET['sta']) ? $_GET['sta'] : "";
$stb = isset($_GET['stb']) ? $_GET['stb'] : "";
$ina = isset($_GET['ina']) ? $_GET['ina'] : "";
$inb = isset($_GET['inb']) ? $_GET['inb'] : "";
$opa = isset($_GET['opa']) ? $_GET['opa'] : "";
$opb = isset($_GET['opb']) ? $_GET['opb'] : "";
$cop = isset($_GET['cop']) ? $_GET['cop'] : "";
$ord = isset($_GET['ord']) ? $_GET['ord'] : "";
$col = isset($_GET['col']) ? $_GET['col'] : array('name','ip','ifname','vlanid','firstseen','lastseen');

$cols = array(	"name"=>"Name",
		"ip"=>"IP Address",
		"ipupdate"=>"IP Update",
		"ipchanges"=>"IP Chg",
		"iplost"=>"IP Lost",
		"mac"=>"MAC Address",
		"oui"=>"OUI Vendor",
		"firstseen"=>"Firstseen",
		"lastseen"=>"Lastseen",
		"device"=>"Device",
		"vlanid"=>"Vlan",
		"ifname"=>"Ifname",
		"ifmetric"=>"IF Metric",
		"ifupdate"=>"IF Update",
		"ifchanges"=>"IF Chg",
		);

?>
<h1>Node List</h1>
<form method="get" name="list" action="<?=$_SERVER['PHP_SELF']?>" name="list">
<table class="content" ><tr class="<?=$modgroup[$self]?>1">
<th width=80><a href=<?=$_SERVER['PHP_SELF'] ?>>
<img src="img/32/cubs.png" title="List those nodes...">
</a></th>
<th valign=top>Condition A<p>
<select size=1 name="ina">
<?
foreach ($cols as $k => $v){
       $selopt = ($ina == $k)?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
</select>
<select size=1 name="opa">
<? selectbox("oper",$opa);?>
</select>
<p><a href="javascript:show_calendar('list.sta');"><img src="img/cal.png"></a>
<input type="text" name="sta" value="<?=$sta?>" size="25">
</th>
<th valign=top>Combination<p>
<select size=1 name="cop">
<? selectbox("comop",$cop);?>
</select>
</th>
<th valign=top>Condition B<p>
<select size=1 name="inb">
<?
foreach ($cols as $k => $v){
       $selopt = ($inb == $k)?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
</select>
<select size=1 name="opb">
<? selectbox("oper",$opb);?>
</select>
<p><a href="javascript:show_calendar('list.stb');"><img src="img/cal.png"></a>
<input type="text" name="stb" value="<?=$stb?>" size="25">
</th>
<th valign=top>Display<p>
<select multiple name="col[]" size=4>
<?
foreach ($cols as $k => $v){
       $selopt = (in_array($k,$col))?"selected":"";
       echo "<option value=\"$k\" $selopt >$v\n";
}
?>
<option value="ifdet" <?=(in_array("ifdet",$col))?"selected":""?> >IF Details
<option value="graph" <?=(in_array("graph",$col))?"selected":""?> >Graphs
<option value="ssh" <?=(in_array("ssh",$col))?"selected":""?> >SSH Server
<option value="tel" <?=(in_array("tel",$col))?"selected":""?> >Telnet Server
<option value="www" <?=(in_array("www",$col))?"selected":""?> >Web Server
<option value="nbt" <?=(in_array("nbt",$col))?"selected":""?> >Netbios
</select>
</th>
<th width=80><input type="submit" value="Show"></th>
</tr></table></form><p>
<?

if ($ina){
	?>
<table class="content"><tr class="<?=$modgroup[$self]?>2">
<td width=80></td>
	<?
	if( in_array("name",$col) )	{ColHead('name');}
	if( in_array("ip",$col) )	{ColHead('ip');}
	if( in_array("ipupdate",$col) )	{ColHead('ipupdate');}
	if( in_array("ipchanges",$col) ){ColHead('ipchanges');}
	if( in_array("iplost",$col) )	{ColHead('iplost');}
	if( in_array("mac",$col) )	{ColHead('mac');}
	if( in_array("oui",$col) )	{ColHead('oui');}
	if( in_array("ifname",$col) )	{ColHead('ifname');}
	if( in_array("vlanid",$col) )	{ColHead('vlanid');}
	if( in_array("ifmetric",$col) )	{ColHead('ifmetric');}
	if( in_array("ifupdate",$col) )	{ColHead('ifupdate');}
	if( in_array("ifchanges",$col) ){ColHead('ifchanges');}
	if(in_array("ifdet",$col))	{echo "<th>IF Details</th>";}
	if(in_array("graph",$col))	{echo "<th>Traffic / Errors</th>";}
	if( in_array("firstseen",$col) ){ColHead('firstseen');}
	if( in_array("lastseen",$col) )	{ColHead('lastseen');}
	if( in_array("ssh",$col) )	{echo "<th>SSH server</th>";}
	if( in_array("tel",$col) )	{echo "<th>Telnet server</th>";}
	if( in_array("www",$col) )	{echo "<th>Web server</th>";}
	if( in_array("nbt",$col) )	{echo "<th>Netbios</th>";}
	echo "</tr>\n";

	$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
	$query	= GenQuery('nodes','s','*',$ord,'',array($ina,$inb),array($opa,$opb),array($sta,$stb),array($cop));
	$res	= @DbQuery($query,$link);
	if($res){
		$row = 0;
		while( ($n = @DbFetchRow($res)) ){
			if ($row % 2){$bg = "txta"; $bi = "imga";}else{$bg = "txtb"; $bi = "imgb";}
			$row++;
			$name		= preg_replace("/^(.*?)\.(.*)/","$1", $n[0]);
			$ip		= long2ip($n[1]);
			$img		= Nimg("$n[2];$n[3]");
			list($fc,$lc)	= Agecol($n[4],$n[5],$row % 2);
			$ud = rawurlencode($n[6]);
			$ui = rawurlencode($n[7]);

			echo "<tr class=\"$bg\"><th class=\"$bi\">\n";
			echo "<a href=Nodes-Status.php?mac=$n[2]><img src=\"img/oui/$img.png\" title=\"$n[3] ($n[2])\"></a></th>\n";
			if(in_array("name",$col)){ echo "<td><b>$n[0]</b></td>";}
			if(in_array("ip",$col)){
				echo "<td><a href=?ina=ip&opa==&sta=$ip>$ip</a></td>";
			}
			if(in_array("ipupdate",$col)){
				$au      	= date("j.M G:i:s",$n[12]);
				list($a1c,$a2c) = Agecol($n[12],$n[12],$row % 2);
				echo "<td bgcolor=#$a1c>$au</td>";
			}
			if(in_array("ipchanges",$col))	{echo "<td align=right>$n[13]</td>";}
			if(in_array("iplost",$col))	{echo "<td align=right>$n[14]</td>";}
			if(in_array("mac",$col))	{echo "<td class=drd>$n[2]</td>";}
			if(in_array("oui",$col))	{echo "<td><a href=http://www.google.com/search?q=".urlencode($n[3])."&btnI=1>$n[3]</a></td>";}
			if(in_array("ifname",$col)){
				echo "<td><a href=?ina=device&opa==&sta=$ud&ord=ifname>$n[6]</a>";
				echo " - <a href=?ina=device&opa==&inb=ifname&opb==&sta=$ud&cop=AND&stb=$ui>$n[7]</a></td>";
			}
			if(in_array("vlanid",$col))	{echo "<td>$n[8]</td>";}
			if(in_array("ifmetric",$col))	{echo "<td align=right>$n[9]</td>";}
			if(in_array("ifupdate",$col)){
				$iu       = date("j.M G:i:s",$n[10]);
				list($i1c,$i2c) = Agecol($n[10],$n[10],$row % 2);
				echo "<td bgcolor=#$i1c>$iu</td>";
			}
			if(in_array("ifchanges",$col))	{echo "<td align=right>$n[11]</td>";}
			if(in_array("ifdet",$col)){
				$link	= @DbConnect($dbhost,$dbuser,$dbpass,$dbname);
				$iquery	= GenQuery('interfaces','s','*','','',array('device','ifname'),array('=','='),array($n[6],$n[7]),array('AND') );
				$ires	= @DbQuery($iquery,$link);
				$nif	= @DbNumRows($ires);
				if ($nif == 1) {
					$if	= @DbFetchRow($ires);		
					if ($if[8] == "2"){
						$ifimg	= "<img src=\"img/bulbr.png\" title=\"Disabled!\">";
					}else{
						$ifimg = "<img src=\"img/bulbg.png\" title=\"Enabled\">";
					}
					echo "<td> $ifimg ".Zfix($if[9])."-$if[10] <i>$if[7] $if[20]</i></td>";
				}else{
					echo "<td>-</td>";
				}
				@DbFreeResult($ires);
			}
			if(in_array("graph",$col)){
				echo "<td nowrap align=center>\n";
				echo "<a href=Devices-Graph.php?dv=$ud&if%5B%5D=$ui><img src=\"inc/drawrrd.php?dv=$ud&if%5B%5D=$ui&s=s&t=trf\">\n";
				echo "<img src=\"inc/drawrrd.php?dv=$ud&if%5B%5D=$ui&s=s&t=err\"></a>\n";
			}
			if(in_array("firstseen",$col)){
				$fs       = date("j.M G:i:s",$n[4]);
				echo "<td bgcolor=#$fc>$fs</td>";
			}
			if(in_array("lastseen",$col)){
				$ls       = date("j.M G:i:s",$n[5]);
				echo "<td bgcolor=#$lc>$ls</td>";
			}
			if(in_array("ssh",$col)){
				echo "<td><a href=ssh://$ip><img src=\"img/16/lokc.png\"></a>\n";
				echo CheckTCP($ip,'22','') ."</td>";
			}
			if(in_array("tel",$col)){
				echo "<td><a href=telnet://$ip><img src=\"img/16/loko.png\"></a>\n";
				echo CheckTCP($ip,'23','') ."</td>";
			}
			if(in_array("www",$col)){
				echo "<td><a href=http://$ip target=window><img src=\"img/16/glob.png\"></a>\n";
				echo CheckTCP($ip,'80'," \r\n\r\n") ."</td>";
			}
			if(in_array("nbt",$col)){
				echo "<td><img src=\"img/16/nwin.png\">\n";
				echo NbtStat($ip) ."</td>";
			}
			echo "</tr>\n";
		}
		@DbFreeResult($res);
	}else{
		print @DbError($link);
	}
	?>
</table>
<table class="content" >
<tr class="<?=$modgroup[$self]?>2"><td><?=$row?> Nodes (<?=$query?>)</td></tr>
</table>
	<?
}
include_once ("inc/footer.php");
?>
