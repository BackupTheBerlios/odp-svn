<?php
	$logintxt='Any Username will do to autherize ...';
	$tabtag = "cellspacing=1 cellpadding=6 border=0 width=100%";

// colours
        $bg1 = "99AACC";
	$bg2 = "99BBDD";

        $footertxt='the text on the foot';
// default page to start with
	$defaultpage='index.php';
// JS Menu 
        $JSMainMenu='config/JSMainMenu.php';
        $JSUserMenu='config/JSUserMenu.php';

// defaults
        $mnuimg="bomb.png";
        $mnutxt="None";

	$JSMainC[0] = array ( 'label' => 'Secret', 'auth' => 'admin', 'icon' => 'fogr.png',
			'members' => array (
				'secret.php',
			),
		);


        $JSUserC[0] = array ( 'label' => '', 'auth' => 'none', 'icon' => 'user.png', 
			'members' => array (
                                'logout.php',
                        ),
                );


	
        $JSUserC[1] = array ( 'label' => '', 'auth' => 'none', 'icon' => 'ring.png',
                        'members' => array (
                                'about.php',
                                'help.php',
                                'debug.php',
                        ),
                );
        $JSDefiM['about.php'] 	= 	array ( 'label' => 'Logout', 	'auth' => 'none', 	'icon' => 'exit.png',);
	$JSDefiM['help.php'] 	= 	array ( 'label' => 'Click Me',	'auth' => 'admin', 	'icon' => 'flop.png',);
        $JSDefiM['secret.php'] 	= 	array ( 'label' => 'Click Me',	'auth' => 'admin', 	'icon' => 'flop.png',);
        $JSDefiM['logout.php'] 	= 	array ( 'label' => 'Logout', 	'auth' => 'none',	'icon' => 'exit.png',);
?>
