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

	$JSMainC[0] = array (
			'name' => 'Secret',
			'auth' => 'admin',
			'icon' => 'fogr.png',
			'members' => array (
				'secret.php'
			),
		);

	$JSMainM['secret.php'] = array (
			'name' => 'Click Me',
			'auth' => 'admin',
			'icon' => 'flop.png',
		);

echo '<pre>';
print_r($JSMainC);
print_r($JSMainM);
$sz = count($JSMainC);
echo "size of " . $sz;

echo '</pre>';

?>
