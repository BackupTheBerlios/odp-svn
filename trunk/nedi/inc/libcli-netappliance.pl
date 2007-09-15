#============================================================================
#
# Program: libcli-sshnet.pl
# Programmer: Remo Rickli, dcr
#
# -> Net::Telnet/Net::SSH::Perl based Functions <-
#
# Needs quite some perl libs, but works well on OBSD4.  Rather limited at this stage:
#
# Ubuntu install hint
#$ wget http://search.cpan.org/CPAN/authors/id/D/DB/DBROBINS/Net-SSH-Perl-1.30.tar.gz
#$ tar zxvf Net-SSH-Perl-1.30.tar.gz
#$ cd Net-SSH-Perl-1.30
#$ perl Makefile.PL && make
#sudo checkinstall -D --pkgname="Marc-Net-SSH-Perl" --pkgversion="1.30" make install
 
# SSH doesn't support enable at this stage (only 1 command per session)
# Foundry only tested with simple telnet pw/en configs
# HP Procurve is nasty due to lots of escape characters. Only simple telnet pw/en tested as well.
#
# GetCfg is a dummy function, with that there's no need to touch libmisc.pl in case more
# devices are added. This will also help for a complete rewrite (e.g. with DIS)
#
#============================================================================
package cli;
use Net::Telnet::Cisco;

use vars qw($sshnetok);

eval 'use Net::SSH::Perl';
eval 'use Net::Appliance::Session';
if ($@){
	$sshnetok = 0;
	print "Net::SSH::Perl not available\n" if $main::opt{d};
}else{
	$sshnetok = 1;
	print "Net::SSH::Perl loaded\n" if $main::opt{d};
}
if ($@){
	$netapsess = 0;
	print "Net::Appliance::Session not available\n" if $main::opt{d};
}else{
	$netapsess = 1;
	print "Net::Appliance::Session loaded\n" if $main::opt{d};
}

print "\n****** Verbose Option given !! ******\n" if $main::opt{v};

# original my $prompt = '/(?m:^[\w.-]+\s?(?:\(config[^\)]*\))?\s?[\$#>]\s?(?:\(enable\))?\s*$)/';
my $prompt = '/.+?[#>]\s?(?:\(enable\)\s*)?$/';

#============================================================================
# HP managed to request the "any key" before login on certain devices. This is only a dirty
# fix since I'm too stupid to make autodetection work !@#$!@$#
#============================================================================
sub Kickit{
#	select(undef, undef, undef, 0.4);							# Wait 200ms to get whole banner
#	my $banner = ${$_[0]->buffer};
#	print"\n$banner\n";
#
#	if($banner =~ /Press any key to continue/){
#		print $_[0]->print("");
#		print"Tk";
#	}

	if( $main::dev{$_[0]}{ty} =~ /^J90/){
		print $_[1]->print("");
		print"Tk";
	}
}

#============================================================================
# Find login, if device is compatible for mac-address-table or config retrieval
#============================================================================
sub PrepDev{
print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***  SUB: [libcli-sshnet] PrepDev   ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};

	my $us    = "";
	my $nok   = 2;										#  clibad=2 means very true ;-)
	my $pnmap = 1;										# port not mapped -> ssh will be tried...
	my $na    = $_[0];
	my $op    = $_[1];
	my @users = @misc::users;

	if($op eq "fwd" and $main::dev{$na}{os} !~ /^IOS/){					# Only IOS has support for mac-address stuff
		return $nok;
	}
	if(defined $main::dev{$na}{cp} and $main::dev{$na}{cp} != 0){				# Undef -> it's new, 0 -> set to be prepd
		if(!$main::dev{$na}{us}){							# Do we have a  user?
print "RealPort exist but no User\n\n" if $main::opt{v};
			print "Pu";
			return $nok;								# No user but a real port -> failed before
		}elsif(exists $misc::login{$main::dev{$na}{us}}){				# OK if in nedi.conf
			return 0;								# Lets use that then (clibad=false)
		}else{
			print "Pc";								# User not in nedi.conf -> Prep
		}
	}
	if(defined $misc::map{$main::dev{$na}{ip}}{cp}){					# Disable SSH upon telnet port map
print "Disable SSH upon telnet port map\n" if $main::opt{v};
		$main::dev{$na}{cp} = $misc::map{$main::dev{$na}{ip}}{cp};
		print "M$main::dev{$na}{cp}" if $main::opt{d};
		$pnmap = 0;
	}else{
		$main::dev{$na}{cp} = 23;
	}
	if($main::dev{$na}{os} eq "Cat1900"){
print "Trying Cat1900" if $main::opt{v};
		do {
			$us = shift (@users);
			print " P:$us" if $main::opt{d};
			my $session = Net::Telnet::Cisco->new(	Host	=> $main::dev{$na}{ip},
								Port	=> $main::dev{$na}{cp},
								Prompt  => $prompt,
								Timeout => $misc::timeout,
								Errmode	=> 'return'
								);
		
			if( defined($session) ){
				if( $session->waitfor('/Enter Selection:.*$/') ){
					$session->print("k");
					if ($session->enable( $misc::login{$us}{pw} ) ){
						$nok = 0;
					}else{
						print "Te";
					}
				}
				$session->close;
			}else{
				print "Tc";
				return $nok;
			}
		} while ($#users ne "-1" and $nok);						# And stop on ok or we ran out of logins
	}elsif( $main::dev{$na}{os} =~ /^(IOS|CatOS|Ironware|ProCurve)/){
		do {
			$us = shift (@users);
			print " P:$us" if $main::opt{d};
			if($sshnetok and $pnmap){
				if( $main::dev{$_[0]}{os} =~ /^(CatOS|IOS)/ ){ # use Net::Appliance only on Cisco-Devices
					eval {
						my $ssh = Net::Appliance::Session->new(Host => $main::dev{$na}{ip}, Transport => 'SSH');
						$ssh->connect( Name => $us, Password => $misc::login{$us}{pw} );
						$ssh->input_log(*STDOUT) if $main::opt{v};
						my($stdout, $stderr, $exit)=$ssh->cmd('show privilege');
						if ($stderr) {
							print "Hl";
						}else{
							$nok = 0;
							$main::dev{$na}{cp} = 22;
						}
						$ssh->close;
					};
				}else{
				eval {
					my $ssh = Net::SSH::Perl->new($main::dev{$na}{ip}, "BatchMode yes", "RhostsAuthentication no", protocol => 2 );
						$ssh->login($us, $misc::login{$us}{pw});
						my ($stdout, $stderr, $exit) = $ssh->cmd("exit");
						if ($stderr) {
							print "Hl";
						}else{
							$nok = 0;
							$main::dev{$na}{cp} = 22;
						}	
					};
				}
			}else{
				$@ = "Hs";
			}
			print $@ if $main::opt{d};
			if ($@){
			print "Trying Net::Telnet::Cisco\n" if $main::opt{v};
				my $session = Net::Telnet::Cisco->new(	Host	=> $main::dev{$na}{ip},
									Port	=> $main::dev{$na}{cp},
									Prompt  => $prompt,
									Timeout	=> $misc::timeout,
									Errmode	=> 'return'
									);
				if(defined $session){
					Kickit($_[0],$session);
					if( $session->login($us,$misc::login{$us}{pw}) ){
						if ($misc::login{$us}{en}){
							$session->enable($misc::login{$us}{en});
						}
						if ($session->is_enabled){			# Make sure we are enabled now		
						print "Telnet-Session is an enables session \n" if $main::opt{v};
							$nok = 0;
						}else{
							print "Te";
						}
					}else{
						print "Tl";
					}
					$session->close;
				}else{
					print "Tc";
					return $nok;
				}
			}
		} while ($#users ne "-1" and $nok);						# And stop once a user worked or we ran out of them.
	}else{
		$main::dev{$na}{cp} = 0;							# Clear port to indicate unsupported device
		return $nok;
	}
	if($nok){
		print "Tu";
	}else{
		print ":$main::dev{$na}{cp} " if $main::opt{d};
		$main::dev{$na}{us} = $us;
	}
	return $nok;
}

#============================================================================
# Get Ios mac address table.
#============================================================================
sub BridgeFwd{

print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***    SUB: [libcli-sshnet] BridgeFwd ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};


	my $line = "";
	my $nspo = 0;
	my @cam  = ();
	my $cmd  = "sh mac-address-table dyn";
	my $cmd2  = "sh port-security addr";							# Thanks Duane Walker 7/2007
	my $cap  = 0;

	if($main::dev{$_[0]}{os} eq "IOS-wl"){							# Cisco WLan specific...
		$cap = 1;									# Needed to avoid using counters as vlans
		$cmd = 'sh bridge | exclude \*\*\*';						# Work around aged (***) forwarding entries
	}
	print " F:$main::dev{$_[0]}{us}:$main::dev{$_[0]}{cp} " if $main::opt{d};

	if( $main::dev{$_[0]}{cp} == 22 ){

		print "\nUsing Client port ssh\n" if $main::opt{v};
		if( $main::dev{$_[0]}{os} =~ IOS){
			print "\nOpen host with Net::Appliance::Session using:$main::dev{$_[0]}{us}\n" if $main::opt{v};
			eval {
				my $ssh = Net::Appliance::Session->new(Host => $main::dev{$_[0]}{ip}, Transport => 'SSH');
				$ssh->connect(Name => $main::dev{$_[0]}{us}, Password => $misc::login{$main::dev{$_[0]}{us}}{pw});
				$ssh->input_log(*STDOUT) if $main::opt{v};
				my ($cmd_out) = $ssh->cmd('show privilege'); # findout if an enabled account
				if ( $cmd_out =~ /is 15/ ) {  # Are we in enabled Mode
					print "He";
				 }else{ # Noooo, ok we are going to try to enter it
					print "Hl";
					$ssh->begin_privileged($misc::login{$main::dev{$_[0]}{us}}{en});
						my $cmd_out = $ssh->cmd('show privilege');
						if ( $cmd_out =~ /is 15/ ) {
							print "He";
						}
				}
				my ($stdout, $stderr, $exit) = $ssh->cmd($cmd);
				@cam = split("\n", $stdout);
				$ssh->close;
			};
		}else{
			print "\nOpen host with Net:SSH:Perl\n" if $main::opt{v};
			eval {
				my $ssh = Net::SSH::Perl->new($main::dev{$_[0]}{ip},);
				$ssh->login($main::dev{$_[0]}{us}, $misc::login{$main::dev{$_[0]}{us}}{pw}, debug => 1, "BatchMode yes" );
											# DEBUG-Option turned on, to figure out
											# why ssh takes so looong to pull infos
				my ($stdout, $stderr, $exit) = $ssh->cmd($cmd);
				@cam = split("\n", $stdout);
			};
		}
			
		if ($@){
			print "Ho";
			return 2;
		}
	}else{
print "\nUsing Client port telnet\n" if $main::opt{v};
		my $session = Net::Telnet::Cisco->new(	Host	=> $main::dev{$_[0]}{ip},
							Port	=> $main::dev{$_[0]}{cp},
							Prompt  => $prompt,
							#Input_log  => "input.log",
							#output_log  => "output.log",
							Timeout	=> $misc::timeout,
							Errmode	=> 'return'
							);
		if( defined($session) ){
			if( $session->login( $main::dev{$_[0]}{us}, $misc::login{$main::dev{$_[0]}{us}}{pw} ) ){
				if ( $misc::login{$main::dev{$_[0]}{us}}{en} ){
					if (!$session->enable( $misc::login{$main::dev{$_[0]}{us}}{en} ) ){
						$session->close;
						print "Te";
						return 2;
					}
				}
				$session->cmd("terminal len 0");
				@cam = $session->cmd($cmd);
				push @cam, $session->cmd($cmd2) if ($misc::getfwd eq 's' and !$cap);	# Thanks Duane Walker 7/2007
				$session->close;
			}else{
				$session->close;
				print "Tl";
				return 2;
			}
			$session->close;
		}else{
			print "Tc";
			return 2;
		}
	}
	foreach my $l (@cam){
		if ($l =~ /\s+(dynamic|forward|secure(dynamic|sticky))\s+/i){			# (secure) Thanks to Duane Walker 7/2007
			my $mc = "";
			my $po = "";
			my $vl = "";
			my @mactab = split (/\s+/,$l);
			foreach my $col (@mactab){
				if ($col =~ /^(Gi|Fa|Do|Po|Vi)/){$po = &misc::Shif($col)}
				elsif ($col =~ /^[0-9|a-f]{4}\./){$mc = $col}			
				elsif (!$cap and $col =~ /^[0-9]{1,4}$/ and !$vl){$vl = $col}	# Only use this, if it's not a Cisco AP
			}
			if( exists($misc::portprop{$_[0]}{$po}) ){				# Make sure IFidx exists before using IF
				if($po =~ /\.[0-9]/){						# Does it look like a subinterface?
					my @subpo = split(/\./,$po);
					$vl = $subpo[1];
					if($misc::portprop{$_[0]}{$subpo[0]}{upl}){$misc::portprop{$_[0]}{$po}{upl} = 1}	# inherit uplink metric on subinterface
				}
				$mc =~ s/\.//g;

				if ($vl !~ /$misc::ignoredvlans/){
					if ($po =~ /^.EC-|^Po[0-9]|channel/){
						$misc::portprop{$_[0]}{$po}{chn} = 1;
					}
					$misc::portprop{$_[0]}{$po}{pop}++;
					$misc::portnew{$mc}{$_[0]}{po} = $po;
					$misc::portnew{$mc}{$_[0]}{vl} = $vl;
					print "\n FWC:$mc on $po vl$vl" if $main::opt{v};
					$nspo++;
				}
			}
		}
	}
	print " f$nspo ";
	return 0;
}

#============================================================================
# Wrapper to get the proper config
#============================================================================
sub Config{

print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***     SUB: [libcli-sshnet] Config  -->> Guessing Operating System ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};

	print " B:$main::dev{$_[0]}{us}:$main::dev{$_[0]}{cp} " if $main::opt{d};
	print " B:$main::dev{$_[0]}{us}:$main::dev{$_[0]}{cp} " if $main::opt{v};

	if($main::dev{$_[0]}{os} eq "Cat1900"){
		print "----------------------------------------> Getting Cat1900 Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchC19Cfg($_[0]) );
	}elsif($main::dev{$_[0]}{os} eq "Ironware"){
		print "----------------------------------------> Getting Ironware Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh run","skip-page-display") );
	}elsif($main::dev{$_[0]}{os} eq "ProCurve"){
		print "----------------------------------------> Getting ProCurve Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh run","no page") );
	}elsif($main::dev{$_[0]}{os} eq "CatOS"){
		print "----------------------------------------> Getting CatOS Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh conf","set length 0") );
	}elsif($main::dev{$_[0]}{os} eq "IOS-fw"){
		print "----------------------------------------> Getting IOS-fw Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh run","pager 0") );
	}elsif($main::dev{$_[0]}{os} eq "IOS"){
		print "----------------------------------------> Getting IOS Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh run","pager 0") );
	}else{
		print "----------------------------------------> Getting ????? Config \n\n" if $main::opt{v};
		&db::BackupCfg( $_[0], &cli::FetchCfg($_[0],"sh run","terminal length 0") );
	}
}

#============================================================================
# Fetch  Config and return it in an array.
# Parameters:
# 1. Target Device
# 2. Command to show configuration
# 3. Command to disable page breaks
# 4. Send a <CR> to get a login prompt [0|1]
#============================================================================
sub FetchCfg{



print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***     SUB: [libcli-sshnet] FetchCfg (Fetching Config and return it in an array) ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};

	my $go  = 0;
	my $cl	= 0;
	my @run = ();
	my @cfg = ();

print "*$main::dev{$_[0]}{ty}*" if $main::opt{v};
	if( $main::dev{$_[0]}{cp} == 22 ){
print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***     SUB: [libcli-sshnet] FetchCfg trying SSH ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};
			if( $main::dev{$_[0]}{ty} =~ /^Cat/ ){
				print "Using Net::Appliance::Session with: Host:$main::dev{$_[0]}{ip} * Name: $main::dev{$_[0]}{us} * Password:$misc::login{$main::dev{$_[0]}{us}}{pw} \n" if $main::opt{v};
				eval {
					my $ssh = Net::Appliance::Session->new(Host => $main::dev{$_[0]}{ip}, Transport => 'SSH');
					$ssh->connect( Name => $main::dev{$_[0]}{us}, Password => $misc::login{$main::dev{$_[0]}{us}}{pw});
					$ssh->input_log(*STDOUT) if $main::opt{v};
					my ($cmd_out) = $ssh->cmd('show privilege');
					if ( $cmd_out =~ /is 15/ ) {
						print "He";
					}else{
						print "Hl";
						$ssh->begin_privileged($misc::login{$main::dev{$_[0]}{us}}{en});
						my $cmd_out = $ssh->cmd('show privilege');
						if ( $cmd_out =~ /is 15/ ) {
							print "He";
						}
					}
					@run =  $ssh->cmd($_[1]);
					$ssh->close;
				};
			}else{
				print "Using Net::SSH::Perl\n" if $main::opt{v};
				eval {
					my $ssh = Net::SSH::Perl->new($main::dev{$_[0]}{ip});
					$ssh->login($main::dev{$_[0]}{us}, $misc::login{$main::dev{$_[0]}{us}}{pw});
					my ($stdout, $stderr, $exit) = $ssh->cmd($_[1]);
					@run = split("\n", $stdout);
				};
			}
		if ($@){
			print "Ho";
			return "SSH failed!";
		}
	}else{
print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***     SUB: [libcli-sshnet] FetchCfg trying TELNET ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};
		my $session = Net::Telnet::Cisco->new(	Host	=> $main::dev{$_[0]}{ip},
							Port	=> $main::dev{$_[0]}{cp},
							Prompt  => $prompt,
							Timeout => ($misc::timeout),		# Increase timeout to build config.
							#Input_log  => "input.log",
							#output_log  => "output.log",
							Errmode	=> 'return'
						  	);
		if( defined($session) ){
			Kickit($_[0],$session);
			if( $session->login($main::dev{$_[0]}{us},$misc::login{$main::dev{$_[0]}{us}}{pw}) ){
				if ($misc::login{$main::dev{$_[0]}{us}}{en}){
					$session->enable($misc::login{$main::dev{$_[0]}{us}}{en});
				}
				if ($session->is_enabled){					# Make sure we are enabled now		
					$session->cmd($_[2]) if $_[2];
					$session->timeout($misc::timeout * 30);			
					@run = $session->cmd($_[1]);
					$session->close;
				}else{
					print "Te";
				}
			}else{
				$session->close;
				print "Tl";
				return "Login $main::dev{$_[0]}{us} failed!\n";
			}
		}else{
			print "Tc";
			return "Telnet failed!";
		}
	}
	$endcfg = 0;
	foreach my $line (@run){
		$line =~ s/[\n\r]//g;
		if ($line =~ /^(Running|Current|PIX|FWSM|ASA)\s|^begin$/){$go = 1}
		if ([$line] and $endcfg ){$go = 0}						# if $line is empty and one line before end appeared
		if ($line =~ /^end/ ){ $endcfg = 1 }						# then kill the last empty line, it'll cause problems
		if ($go){									# with the diff GUI at the Webpage, showing a change
			print "\n CFG:$line" if $main::opt{v};					# of configuration, where really nothing has been changed.
			push @cfg,$line;
			$cl++;
		}
	}
	print "\n" if $main::opt{v};
	if( defined($cfg[$#cfg]) ){ 
		if($cfg[$#cfg] eq "" ){pop @cfg}						# Remove empty line at the end.
	}else{											# Empty config can't be good...
		push @cfg,"Empty config!";
	}
	print "Bf";
	print "-$cl" if $main::opt{d};
	return @cfg;
}

#============================================================================
# Get Catalyst 1900 Config and return it in an array.
#============================================================================
sub FetchC19Cfg{

print "\n*********************************************************************************************************\n" if $main::opt{v};
print "\n***    SUB: [libcli-sshnet] FetchC19Cfg   Get CAT1900 Config  ***\n" if $main::opt{v};
print "\n*********************************************************************************************************\n" if $main::opt{v};

	my @cfg = ();
	my $cl	= 0;

	my $session = Net::Telnet::Cisco->new(	Host	=> $main::dev{$_[0]}{ip},
						Port	=> $main::dev{$_[0]}{cp},
						Prompt  => $prompt,
						Timeout => ($misc::timeout + 10),		# Add 10 seconds to build config.
						Errmode	=> 'return'
					  	);
	
	if( defined($session) ){
		if( $session->waitfor('/Enter Selection:.*$/') ){
			$session->print("k");
			if ($session->enable( $misc::login{$main::dev{$_[0]}{us}}{pw} ) ){
				my @run = $session->cmd("show run");
			
				shift @run;							# Trim & Remove Pagebreaks
				shift @run;
				foreach my $line (@run){
					if ($line !~ /--More--|^$/){
						$line =~ s/\r|\n//g;
						push @cfg,$line;
						$cl++;
					}		
				}
				print "Bf";
				print "-$cl" if $main::opt{d};
			} else {
				print "Te";
				return "Enable failed!\n";
			}
		}else{
			print "To";
			return "Menu timeout!\n";
		}
		$session->close;
		return @cfg;
	}else{
		print "Tc";
		return "Telnet failed!";
	}
}
