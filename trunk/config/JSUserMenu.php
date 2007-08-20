<?php
?>
var usermenu = [
        [
                '<img src=./img/16/user.png>',' <?=getuserid()?>',null,null,null,
                        _cmSplit,
                        ['<img src=./img/16/exit.png>','Logout','index.php?pageid=logout.php',null,null],
        ],
        _cmSplit,
        [
                '<img src=./img/16/ring.png>','',null,null,null,
                        ['<img src=./img/16/wglb.png>','Developement','index.php?pageid=help.php',null,null],
                        ['<img src=./img/16/user.png>','User','index.php?pageid=help.php',null,null],
                        _cmSplit,
                        ['<img src=./img/16/wglb.png>','Debug','index.php?pageid=debug.php',null,null],
                        ['<img src=./img/16/idea.png>','About ...',null,null,null],
        ],
];
<?php
?>
