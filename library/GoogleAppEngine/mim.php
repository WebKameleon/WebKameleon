<?php

    $mime=file('/etc/mime.types');
    
    $ser=[];
    
    $preamble=true;
    
    foreach ($mime AS $m)
    {
        if (strlen($m) && $m[0]=='#' && $preamble) continue;
        if (strlen($m) && $m[0]!='#' && $preamble) $preamble=false;
        
        
        $m=str_replace(['#'],'',$m);
        $m=explode(' ',trim(preg_replace("/\s+/"," ",$m)));
        
        if (count($m)==1) continue;
        
        for ($i=1;$i<count($m);$i++) $ser[$m[$i]]=$m[0];
    }
    
    
    file_put_contents('mime.ser',serialize($ser));