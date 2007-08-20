<?php
//
// libmisc.php
//

        require_once 'include/libaaa.php';
        require_once 'include/libwwwstuff.php';
//
// get config and perhaps get the secret config dir
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

?>
