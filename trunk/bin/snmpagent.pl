#!/usr/bin/perl 

########################################
#
# 
# Owen Brotherwood, DK 2007 
# Based on original perl module example 
# GNU General Public License V3 
#
#
######################################### 

$program = $0;

if (!defined($regat)) {
	help('No $regat defined');
}

sub help {
	my ($message) = @_;
	print STDERR '
ERROR: ' .  $program . ':' . $message . 
'

Here is some help ...

	This program should be started from snmpd.conf. 

	An example for allowing one to walk /etc/passwd would be when this program is /etc/snmp/snmpagent.pl:
		perl print STDERR \'Perl extentsions:\' . \n\"
		perl $debugging = \'1\';
		perl $verbose = \'1\';
		perl {$regat = \'.1.3.6.1.4.1.8072.999\'; $extenstion = \'1\'; $mibdata = \'/etc/passwd\'; $delimT=\'\'; $delimV=\':\'; do \'/etc/snmp/snmpagent.pl\';}

	Use snmpd -f to see what is going on.

	If $delimT is not \'\', the first two values are comment(for documentation) and type, for example 4.
	If $delimT is \'\', ASN_OCTET_STR (4) is presummed.

	So, with $delimV=\':\' and $delimT=\'=\':
		oidname1=4=value1:value2
		oidname2=4=value3:value4

	The result of a snmpwalk would be:
		NET-SNMP-MIB::netSnmp.999.1.1.1 = STRING: "value1"
		NET-SNMP-MIB::netSnmp.999.1.1.2 = STRING: "value2"
		NET-SNMP-MIB::netSnmp.999.1.1.1 = STRING: "value3"
		NET-SNMP-MIB::netSnmp.999.1.1.1 = STRING: "value4"

	NB: snmptable requires a MIB to work.

	Owen Brotherwood, DK 2007
	GNU General Public License V3
';
	die($message);
}

use NetSNMP::OID (':all');
use NetSNMP::agent (':all');
use NetSNMP::ASN (':all');

sub my_snmp_handler { 
    	my ($handler, $registration_info, $request_info, $requests) = @_; 
    	my $request; 
    	my %my_oid = (); 
	my @mibdata;
	my $ASN_OCTET_STR = 4;
# for this example, wasteful read test data in every time ... 
    	open(MIB,$mibdata); 
    	@mibdata = <MIB>; 
    	close(MIB); 
# we append $extension to $regat for the area which the test data is available
    	$base_oid = new NetSNMP::OID($regat . '.' . $extension); 
# start taking in values 
        undef($prev_oid); 
        $jndex = 1; 
        foreach $line (@mibdata) { 
# fill the hash pipe 
                chomp $line; 
		if ($delimT != ''){
                	($index_name, $index_type, $index_values) = split(/$delimT/, $line); 
		}else{
			$index_values = $line;
			$index_name = $mibdata;
			$index_type = $ASN_OCTET_STR;
		}
                my @value = split(/$delimV/, $index_values); 
                my $index = 1; 
                foreach $mibit (@value) { 
                        $this_oid = new NetSNMP::OID($base_oid . '.' . $jndex . '.' . $index); 
                        $oid_type{$this_oid} = $index_type;
                        $oid_value{$this_oid} = $mibit; 
                        $oid_index{$this_oid} = $index; 
                        $oid_jndex{$this_oid} = $jndex; 
                        if (defined($prev_oid)){ 
                                $oid_next{$prev_oid} = $this_oid; 
                        } 
                        $prev_oid = $this_oid; 
                        print STDERR "Loading $this_oid $oid_type{$this_oid}::$oid_value{$this_oid}  \n" if ($verbose); 
                        $index++; 
                } 
                $jndex++; 
        } 
	$mjndex = $jndex;
	$mindex = $index;
# fill in some blanks
	for ($jndex = 1; $jndex < $mjndex; $jndex++) {
		$this_oid = new NetSNMP::OID($base_oid . '.' . $jndex);
	        $next_oid = new NetSNMP::OID($this_oid . '.1');
		$oid_next{$this_oid} = $next_oid;
	}
# Now do the request hope it has not timed out
        for ($request = $requests; $request; $request = $request->next()) { 
                $oid = $request->getOID(); 
                print STDERR "$program @ $oid " if ($debugging); 
                if ($request_info->getMode() == MODE_GET) { 
# easy to get 
                        print STDERR " GET " if ($debugging); 
                        if (exists $oid_value{$oid}) { 
                                print STDERR "->$oid_value{$oid}\n" if ($debugging); 
                                $request->setValue($oid_type{$oid}, $oid_value{$oid}); 
                        }else{ 
                                print STDERR " No value ...\n"; 
                        } 
                }elsif ($request_info->getMode() == MODE_GETNEXT) { 
# long way to walk 
                        print STDERR " GETNEXT " if ($debugging); 
                        if (defined($oid_next{$oid})) { 
                                $next_oid = $oid_next{$oid}; 
                                $type_oid = $oid_type{$next_oid}; 
                                $value_oid = $oid_value{$next_oid}; 
                                $request->setOID($next_oid); 
                                $request->setValue($type_oid, $value_oid); 
                        }elsif ($oid <= $base_oid) { 
				$next_oid = new NetSNMP::OID($base_oid . '.1.1');
				$type_oid = $oid_type{$next_oid};
				$value_oid = $oid_value{$next_oid};
                                $request->setOID($next_oid); 
                                $request->setValue($type_oid, $value_oid); 
                        }else{
				print STDERR "Hit by a truck whilst walking ...\n" if ($debugging);
			}
                } 
        } 
} 

{
        if (!$agent) {
               help('No $agent defined');
        }

	print STDERR "$0 @ $regat ($extension) using $mibdata ($delimV) ($delimT)\n";

        my $regoid = new NetSNMP::OID($regat); 

        $agent->register($program, $regoid, \&my_snmp_handler);
	print STDERR $program . " Goodbye cruel world, I leave you with my_snmp_handler\n";
}
########################################################################
