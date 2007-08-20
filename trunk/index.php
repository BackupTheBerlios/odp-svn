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
	require_once 'include/libindex.php';
//
	global $defaultpage;
	$pageid = getpageid();
	if ( ! isset($pageid)){
		$pageid = $defaultpage;
	}

        $page_include = "include/" . $pageid;
        $config_include = "config/" . $pageid;

        if ( file_exists( $page_include) ){
       		if ( file_exists( $config_include) ){
                	require $config_include;
                }
		global $pageauth;
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
