#!/usr/bin/perl
#
#   perl do "/path/to/perl_module.pl";
#
#
#   use snmpd -f to debug ...
#
#  Owen Brotherwood, DK 2007
#  Based on original perl module example
#  GNU General Public License V3
#

# running from snmpd.conf  or command line
if (!defined($regat)) {
        $regat = $ARGV[0];          # Register at this OID
        $mibdata = $ARGV[1];        # File for data Name_for_Values$delimTType_of_Values$delimTValue1$delimVValue2$delimV
        $delimT = $ARGV[2];         # Delimeter between Name $delimT Type $delimV Values
        $delimV = $ARGV[3];         # Delimeter between Values
}

# For print STDERR which can be seen when using -f option for snmpd
$debugging = 1;
$verbose = 0;

use NetSNMP::OID (':all');
use NetSNMP::agent (':all');
use NetSNMP::ASN (':all');

BEGIN {
    print STDERR "Starting " . $0;
}

sub my_snmp_handler {
    my ($handler, $registration_info, $request_info, $requests) = @_;
    my $request;
    my %my_oid = ();

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
                @value = split(/$delimV/, $index_values);
                $index = 1;
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
                print STDERR $oid if ($debugging);
                if ($request_info->getMode() == MODE_GET) {
# easy to get
                        print STDERR ":GET" if ($debugging);
                        if (exists $oid_value{$oid}) {
                                print STDERR "->$oid_value{$oid}\n" if ($debugging);
                                $request->setValue($oid_type{$oid}, $oid_value{$oid});
                        }else{
                                print STDERR " No value ...\n";
                        }
                }elsif ($request_info->getMode() == MODE_GETNEXT) {
# long way to walk
                        print STDERR ":GETNEXT" if($debugging);
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
                        }else {
                                print STDERR "Hit by a truck whilst walking ...\n";
                        }
                }
        }
}
#--------------------------------
# Standard Example from here ...
#--------------------------------
sub shut_it_down {
  $running = 0;
}


        print STDERR " loaded ok\n";
        print STDERR "Parameters:$regat, $mibdata, $delimT, $delimV\n";
# if we're not embedded, this will get auto-set below to 1
        $subagent = 0;
# where we are going to hook onto
        my $regoid = new NetSNMP::OID($regat);
        print STDERR "Registering at " . $regoid . " (" . $regat .")\n" if ($debugging);

        if (!$agent) {
                $agent = new NetSNMP::agent('Name' => 'test', # reads test.conf
                                'AgentX' => 1);   # make us a subagent
                $subagent = 1;
                print STDERR "started us as a subagent ($agent)\n"
}

        $agent->register('myname',$regoid, \&my_snmp_handler);


        if ($subagent) {
# We need to perform a loop here waiting for snmp requests.  We
# aren't doing anything else here, but we could.
                $SIG{'INT'} = \&shut_it_down;
                $SIG{'QUIT'} = \&shut_it_down;
                $running = 1;
                while($running) {
                        $agent->agent_check_and_process(1);  # 1 = block
                        print STDERR "mainloop excercised\n" if ($debugging);
                }
                $agent->shutdown();
        }


