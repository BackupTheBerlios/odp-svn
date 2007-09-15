#!/usr/bin/perl
#============================================================================
# Program: nedi.pl
# Programmer: Remo Rickli
#
# DATE		COMMENT
#----------------------------------------------------------------------------
# 07/09/04	v1.0.a initial merged (0.8 and 0.9) version 
# 21/12/04	v1.0.e first alpha^17 version.
# 22/02/05	v1.0.p alpha^5 version.
# 27/04/05	v1.0.s alpha^2 version (1 timestamp per discovery for coherence).
# 30/03/06	v1.0.w rrd integration, .def philosopy, monitoring 		(RC1)
# 30/06/06	v1.0.w system rrd, modules, monitoring, discovery		(RC2)
# 3/11/06		v1.0.w  1st SSH implementation, link mgmt, defgen	(RC3)
# 15/12/06	v1.0.w Cleanup and bugfixes. RRDs based on 1h interval 	(RC4-Xmas edition)
# 21/03/07	v1.0.w More cleanup, -I and -N, nodetrack, rel IF counters	(Relase)
# 16/04/07	v1.0  device names now BINARY (is case sensitive), new  (route based) discovery, stock module management.
#============================================================================
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#============================================================================
# Visit http://www.nedi.ch for more information.
#============================================================================

use strict;
use Getopt::Std;
 
use vars qw($p $now $nediconf $cdp $lldp $oui);
use vars qw(%nod %dev %int %mod %link %vlan %opt %net %usr); 

getopts('AbcdDiIlLNort:u:vw:y',\%opt) or &Help();

$p = $0;
$p =~ s/(.*)\/(.*)/$1/;
if($0 eq $p){$p = "."};
$now = time;
require "$p/inc/libmisc.pl";									# Use the miscellaneous nedi library
&misc::ReadConf();
require "$p/inc/libsnmp.pl";									# Use the SNMP function library
require "$p/inc/libcli-netssh.pl";								# Use the Net::SSH::Perl CLI library
#require "$p/inc/libcli-netappliance.pl";							# Use the Net::Appl;iance::Session CLI library
require "$p/inc/libmon.pl";									# Use the WEB functions for webdevs.
require "$p/inc/libweb.pl";									# Use the Monitoring lib for notifications.
require "$p/inc/lib" . lc($misc::backend) . ".pl" || die "Backend error ($misc::backend)!";

if($opt{u}){
	$misc::seedlist = "$opt{u}";
}else{
	$misc::seedlist = "seedlist";
}
# Disable buffering so we can see what's going on right away.
select(STDOUT); $| = 1;

# -------------------------------------------------------------------
# This is the debug mode, using previousely saved vars instead of discovering...
# -------------------------------------------------------------------
if ($opt{D}){
	print &misc::ReadOUIs();
	print &db::ReadDev();
	&misc::RetrVar();
# Functions to be debugged go here
#	&db::UnStock();
	&misc::BuildArp() if(defined $misc::arpwatch);						# Needs to be built before Links!
	&misc::Link();

#	print &db::ReadNod();
#	&misc::BuildNod();
#	&misc::RetireNod();

#	&db::WriteDev();
#	&db::WriteVlan();
#	&db::WriteInt();
#	&db::WriteNet();
#	&db::WriteLink();
#	&db::WriteNod();

	die "\n=== Debugging ended! ===\n";
}
# -------------------------------------------------------------------

if ($opt{w}) {
	print &db::WlanUp();
}elsif($opt{i}) {
	&db::InitDB();
}elsif($opt{y}) {
	&ShowDefs();
}else{
	@misc::doneid  = ();
	@misc::doneip  = ();
	@misc::donenam = ();

	print &misc::ReadOUIs();
	print &db::ReadDev();
	print &db::ReadLink('type','S');						# Read static links to avoid doubles

	my $nseed = &misc::InitSeeds();

	print "CDP-"   if($opt{c});
	print "LLDP-"  if($opt{l});
	print "OUI-"   if($opt{o});
	print "Route-" if($opt{r});
	print "Discovery with $nseed seed(s) on ". localtime($now)."\n";
	print "===============================================================================\n";
	print "Device				Status				     Todo/Done\n";
	print "-------------------------------------------------------------------------------\n";
	while ($#misc::todo ne "-1"){
		my $start = time;
		my $id    = shift(@misc::todo);
		my $name  = &misc::Discover($id);
		push (@misc::doneid,$id);
		push (@misc::doneip,$misc::doip{$id});
		push (@misc::donenam, $name) if ($name);
		my $dur = time - $start;
		print " ${dur}sec" if $main::opt{d};
		printf ("%4d/%d\n",scalar(@misc::todo),scalar(@misc::donenam) );
	}
	print "-------------------------------------------------------------------------------\n";
	if (scalar @misc::donenam){
		&misc::StorVar() if ($opt{d});
		&misc::BuildArp() if(defined $misc::arpwatch);					# Needs to be built before Links!
		&misc::Link();
	
		print &db::ReadNod();
		&misc::BuildNod();
		&misc::RetireNod();
	
		die "Only testing, nothing written!" if $opt{t};
		
		print &db::UnStock();
		print &db::WriteDev();
		
		print &db::WriteInt($opt{A});
		print &db::WriteVlan($opt{A});
		print &db::WriteMod($opt{A});

		print &db::WriteLink($opt{A}) if (!$opt{L});
		print &db::WriteNet();

		print &db::WriteNod();
	}else{
		print "Nothing discovered, nothing written...\n";
	}
}

#===================================================================
# List supported device types
#===================================================================
sub ShowDefs {
	print "Supported Devices ---------------------------------------------------------\n";
	chdir("$p/sysobj");
	my @defs = glob("*.def");
	foreach my $df (sort @defs){
		open F, $df or print "couldn't open $df\n" && return;
		while (<F>) {
			chomp;
			next unless /^Type/o;
			$_ =~ s/^Type\s//;
			printf ("%-40s %s \n",$_,$df );
		}
	}
}
#===================================================================
# Display some help
#===================================================================
sub Help {
	print "\n";
	print "usage: nedi.pl [-i|-D|-t|-w|-y|] <more option(s)>\n";
	print "Discovery Options (can be combined, default is static) --------------------\n";
	print "-b	backup running configs\n";
	print "-c	CDP discovery\n";
	print "-l 	LLDP discovery\n";
	print "-o	OUI discovery (based on ARP chache entries of the above)\n";
	print "-r	route table discovery (on L3 devices)\n";
	print "-s	GONE! check nedi.conf for getfwd...\n";
	print "-u<file>	use specified seedlist\n";
	print "-A 	append to networks, links, vlans, interfaces and modules tables\n";
	print "-I 	don't try to find best suited IP addresses for devices\n";
	print "-L 	don't touch links, so you can maintain them manually\n";
	print "-N 	don't exclude devices from nodes\n";
	print "Other Options -------------------------------------------------------------\n";
	print "-i	initialize database and start all over\n";
	print "-w<dir>	add Kismet csv files in directory to WLAN database\n";
	print "-t<ip>	test IP only, but don't write anything\n";
	print "-d/D	store internal variables and print debug info/debug mode (check lines 66-86)\n";
	print "-v	verbose output\n";
	print "-y	show supported devices based on .def files (in sysobj)\n\n";
	print "\nOutput Legend -----------------------------------------------------------\n";
	print "Statistics (lower case letters):\n";
	print "i#	Interfaces\n";
	print "p#	IF IP addresses\n";
	print "a#	ARP entries\n";
	print "f#	Forwarding entries\n";
	print "m#	Modules\n";
	print "v#	Vlans\n";
	print "[clro]#/#	(CDP,LLDP,route or OUI) queueing (added/done already)\n";
	print "b#	border hits\n";
	print "\nWarnings (upper case letters):\n";
	print "Ax	Addresses (i=IF IP, m=IF mask, a=arptable, n=no IF)\n";
	print "Bx	Backup configs (f=fetched, n=new, u=updated)\n";
	print "Fx(#)	Forwarding table (i=IF, p=Port, #=vlan)\n";
	print "Ix	Interface (d=desc, n=name, t=type, s=speed, m=mac, a=admin status,\n";
	print "		h(in)/H(out)=HC octet,o/O=octet,e/E=error, l=alias, x=duplex, v=vlan)\n";
	print "Hx	SSH (s=no ssh libs or port mapped, c=connect, l=login, u=no user, o=other\n";
	print "M#..	Mapping IP or telnet port according to config\n";
	print "Mx	Modules (t=slot, d=desc, c=class, h=hw, f=fw, s=sw, n=SN, m=model)\n";
	print "Qx	Queueing (c=CPD, l=LLDP, r=route, 0=IP is 0.0.0.0, s=seeing itself, d=desc filter, v=voip)\n";
	print "Px	CLI preparing (u=no user, c=no credentials\n";
	print "Rx	RRD (d=mkdir, u=update, s=create sys, i=create IF,n=IF name)\n";
	print "Sx	SNMP (c=connect, n=SN, B=Bootimage,u=CPU util, m=CPUmem,i=IOmem,t=Temp)\n";
	print "Tx	Telnet (c=connect,e=enable, k=needed kick, l=login, u=no user, o=other\n";
	print "Vx	VTP or Vlan (d=VTP domain, m=VTP mode, n=Vl name)\n";
	print "---------------------------------------------------------------------------\n";
	die "NeDi 1.0-rc4 11.Sep 2007\n";
}
