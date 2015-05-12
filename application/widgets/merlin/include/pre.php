<?php

    require_once __DIR__.'/fun.php';

    parse_str($costxt);

    require_once __DIR__.'/Merlin.php';
    $merlin=new MERLIN($login,$pass,$operator);

    
    
    
    $merlin_q=array('dest'=>'','dep'=>'');
    foreach ($_GET AS $k=>$v)
    {
        if (substr($k,0,6)=='merlin') $merlin_q[substr($k,7)]=$v;
    }
