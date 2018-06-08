<?php
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='off') {
        Header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        die();
    }

    $bucket=$_SERVER['SERVER_NAME'];

    
    $file='gs://'.$bucket.$_SERVER['REQUEST_URI'];
    if ($pos=strpos($file,'?')) $file=substr($file,0,$pos);
    $memcache = new Memcache;
    $memcache_key = md5($file);
    
    $mimes = [
        'txt'=>'text/plain',
        'html'=>'text/',
        'php'=>'text/html',
        'jpg'=>'image/',
        'ico'=>'image/',
        'gif'=>'image/',
        'jpeg'=>'image/',
        'pdf'=>'application/',
        'css'=>'text/',
        'js'=>'text/javascript',
        'mp4'=>'video/'
    ];
    
    function __finish7812($f,$e,$m,$c=false) {
        Header('Content-type: '.$m);
        if ($e=='php') {
            include($f);
            return '';
        }
        else {
            $hours=24;
            if($e=='html') $hours=2;
            Header('expires: '.date('r',time()+3600*$hours));
            if (!$c) $c=file_get_contents($f);
            return $c;
        }
    }
    
    
    
    $m=$memcache->get($memcache_key);
    if ($m) die(__finish7812($m[0],$m[1],$m[2],$m[3]));
    
    
    $search = (substr($file,-1)=='/') ? [$file.'index.html',$file.'index.php'] : [$file,$file.'/index.html',$file.'/index.php'];
    
    foreach($search AS $f) {
        if (!file_exists($f)) continue;
        $info=pathinfo($f);
        $ext=strtolower($info['extension']);
        $mime = isset($mimes[$ext]) ? $mimes[$ext] : 'application/';
        if (substr($mime,-1)=='/') $mime.=$ext;
        $c=__finish7812($f,$ext,$mime);
        $memcache->set($memcache_key,[$f,$ext,$mime,$c]);
        die($c);
    }
    
    header("HTTP/1.0 404 Not Found");
    
    