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
	$pageid = getpageid();
	if ( ! isset($pageid)){
		$pageid = 'index.php';
	}
	if ( autherized( getuserid(), $pageid ) ){
//
// include page with call backs for various routines
		$page_include = "include/" . $pageid;
		$config_include = "condfig/" . $pageid;
		if ( file_exists( $page_include) ){
			if ( file_exists( $config_include) ){
				require $config_include;
			}
			require $page_include;
               	 	$page_var = page_init();
    	        	page_html_header($page_var);
          		page_domenu($page_var);
                	page_main($page_var);
                	page_html_footer($page_var);
                	page_cleanup($page_var);
		}else{
			echo "sorry, haven't got that far";
		}
	}else{ 
		$reqpage=$_SESSION['SCRIPT_URI'];
               	form_login($reqpage);
	}
// That's all folks ...
?>
