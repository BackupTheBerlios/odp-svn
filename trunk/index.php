<?php
//==========================================================================
//
// State machine for NeDi ...?
// by Owen Brotherwood, oxo
//
// todo: 
//	a lot ...
//
//==========================================================================

//
        require_once 'include/libsession.php';
//
	start_session_start();
//
        require_once 'include/libaaa.php';
        require_once 'include/libwwwstuff.php';
//
        require_once 'config/config.php';

        if ( isset($configdir) ){
                require $configdir . "/config.php";
        }else{
                $configdir = 'config';
        }
//
//
//
        $lang = getlang();
        if ( isset($lang) ){
                if ( file_exists("$configdir/$lang/config.php") ){
                        require "$configdir/$lang/config.php";
                }
        }
//

//
	$pageid = getpageid();
	if ( ! isset($pageid)){
		$pageid = $defaultpage;
	}

        $page_include = "include/" . $pageid;
        $config_include = $configdir . "/" . $pageid;

        if ( file_exists( $page_include) ){
       		if ( file_exists( $config_include) ){
                	require $config_include;
                }
		if ( autherized( getuserid(), $pageauth ) ){
			require $page_include;
               	 	$page_var = page_init();
    	        	page_html_header($page_var);
                	page_main($page_var);
                	page_html_footer($page_var);
                	page_cleanup($page_var);
		}else{ 
               		login();
		}
	}
// That's all folks ...
?>
