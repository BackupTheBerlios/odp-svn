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

// get config in .htaccess protected area and perhaps get the "secret config dir"
	require "config/config.php";
	if ( isset($secretconfigdir) ){
		require "$secretconfigdir/config.php";
	}
//
	require "include/libsession.php";
//
	require "include/libaaa.php";
	require "include/libwwwstuff.php";
//
	start_session_start();
//
	if ( authenticated() ){

		$pageid = getpageid();
		if ( ! isset($pageid)){
			$pageid = 'help.php';
		}
		if ( autherized( getuserid(), $pageid ) ){
//
// include page with call backs for various routines
			$page_include = "include/" . $pageid;
			if ( file_exists( $page_include) ){
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
//
		}else{ 
// 
// just a quick text for test ...
			echo getuserid() . "Not Authorized\n";
		}
	}
	else{
		$reqpage=$_SESSION['reqpage'];
		if ( authenticate() ){
			html_header("nocache",$reqpage,2);
			splash("Welcome " . getuserid() . " to " . $reqpage);
			html_footer();
		}else{
			html_header("nocache");
                	splash( "Any user pass will work at the moment"  );
                	form_login();
			html_footer();
		}
	}

// That's all folks ...
?>
