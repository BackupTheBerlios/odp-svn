#!/usr/bin/perl 

########################################
# 
# perl do "/path/to/perl_module.pl"; 
# 
# - use snmpd -f to debug ... 
# 
# Owen Brotherwood, DK 2007 
# Based on original perl module example 
# GNU General Public License V3 
#
######################################### 

# running from snmpd.conf  or command line
if (defined($my_regat)) {
	$regat = $my_regat;
	$mibdata = $my_mibdata;
#	my $delimT = $my_delimT;
#	my $delimV = $my_delimV;
}else{
        my $regat = $ARGV[0];          # Register at this OID
        my $mibdata = $ARGV[1];        # File for data Name_for_Values$delimTType_of_Values$delimTValue1$delimVValue2$delimV
        my $delimT = $ARGV[2];         # Delimeter between Name $delimT Type $delimV Values
        my $delimV = $ARGV[3];         # Delimeter between Values
}

$program = $0;

use NetSNMP::OID (':all'); 
use NetSNMP::agent (':all'); 
use NetSNMP::ASN (':all'); 

sub my_snmp_handler { 
    	my ($handler, $registration_info, $request_info, $requests) = @_; 
    	my $request; 
    	my %my_oid = (); 
	my @mibdata;
	

# for this example, wasteful read test data in every time ... 
    	open(MIB,$mibdata); 
    	@mibdata = <MIB>; 
    	close(MIB); 
# we append .1 to $regat for the area which the test data is available
    	$base_oid = new NetSNMP::OID($regat . '.1'); 
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
			$index_name = 'Unknown';
			$index_type = '4';
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
                        print STDERR " GETNEXT " if($debugging); 
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
                die "Should be embedded, run from snmpd.conf...\n";
        }

	print STDERR "$0 @ $regat using $mibdata ($delimV) ($delimT)\n";

        my $regoid = new NetSNMP::OID($regat); 

        $agent->register($program, $regoid, \&my_snmp_handler);
}
########################################################################
