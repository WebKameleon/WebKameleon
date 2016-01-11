<?php
    
    function ___moved($location,$permanently=true)
    {
        if ($permanently) header('HTTP/1.1 301 Moved Permanently');
        header('Location: '.$location);        
        die();
    }
    
    function ___notfound()
    {
        header('HTTP/1.0 404 Not Found');
        die();
    }
    
    function ___check_index($file)
    {
        $debug_str='';
        if (isset($_SERVER['url_changed']) && $_SERVER['url_changed'])
        {
            $debug_str="URL changed to $file";    
        }
        elseif (substr(strtolower(basename($file)),0,6)=='index.')
        {
            $_self=explode('?',$_SERVER['REQUEST_URI']);
            if (!strlen($_self[0])) $_self[0]='/';
            
            if (basename($_self[0]) != basename($file) && substr($_self[0],-1) != '/' )
            {
                $_self[0].='/';
                if (isset($_self[1])) $_self[0].='?'.$_self[1];
                ___moved($_self[0]);
            }
            
            $debug_str="End of ".$_self[0].' = '.substr($_self[0],-1);
        }
        else
        {
            $debug_str="File $file is not an index";
        }
        
        if ($debug_str) return "<!-- Webkameleon for Appengine: $debug_str -->";
    }
    
    function ___script($_script)
    {
        $__d=___check_index($_script);
        chdir(dirname($_script));
        include(getcwd().'/'.basename($_script));
        
        echo $__d;
    }
    
    function ___readfile($_file)
    {
        if (substr($_file,-3)=='.js') Header('Content-type: text/javascript');
        header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
        $__d=___check_index($_file);
        readfile($_file);
        echo $__d;
    }


    function ___regex($self_long,$self,$regex_array)
    {
        $_notfound='';
        $_redirect='';
        $__get=array();


        foreach($regex_array AS $r)
        {
            
            if ($r[0]=='^.*$' || $r[0]=='^.+$' || $r[0]=='*' || $r[0]=='.*' || $r[0]=='.+')
            {
                $_notfound=$r[1];
                continue;
            }
            
            if (substr($r[0],0,8)=='^http://')
            {            
                $self2_long=preg_replace('~'.$r[0].'~',$r[1],$self_long);
                
                if ($self2_long != $self_long)
                {

                    $_redirect=$self2_long;
                    break;
                }
            }
            else
            {
                $self2=preg_replace('~'.$r[0].'~',$r[1],$self);    

                if ($self!=$self2)
                {
                    
                    if (substr($self2,0,7)=='http://')
                    {
                        $_redirect=$self2;
                        break;
                    }
                    
                    
                    if ($pos=strpos($self2,'?'))
                    {
                        $query=substr($self2,$pos+1);
                        $self2=substr($self2,0,$pos);
                        foreach(explode('&',$query) AS $pair)
                        {
                            $pair=explode('=',$pair);
                            if (isset($pair[1])) $__get[urldecode($pair[0])] = urldecode($pair[1]);
                        }   
                    }

                    
                    $self=$self2;
                }
            }
            
        }
        
        
        return array('redirect'=>$_redirect,'get'=>$__get,'self'=>$self,'not_found'=>$_notfound);
        
    }

    if (isset($_only_functions)) return;

    $_self=explode('?',$_SERVER['REQUEST_URI']);
    $self=$_self[0];
    if (!$self) $self='/';

    $memcache = new Memcache;
    $memcache_key = '_req_'.$self;
    
    $page=$memcache->get($memcache_key);
    if ($page) {
        if ($page['f']=='r') die(___readfile($page['v']));
        if ($page['f']=='s') die(___script($page['v']));
    }
    
    $_app=unserialize(file_get_contents(__DIR__.'/_app.ser'));
    
    if (!is_array($_app['s'])) ___notfound();
    
    
    
    $self_long='http://'.$_SERVER['HTTP_HOST'].$self;
    
    if (isset($_app['r']) && is_array($_app['r']))
    {
        $r=___regex($self_long,$self,$_app['r']);
        
        if ($r['redirect'])
        {
            if (!strstr($r['redirect'],'?') && count($_self)>1) $r['redirect'].='?'.$_self[1]; 
            ___moved($r['redirect']);            
        }
        
        if (count($r['get']))
        {
            $_GET = array_merge($r['get'],$_GET);
            $_REQUEST = array_merge($r['get'],$_REQUEST);            
        }
        
        $_notfound=$r['not_found'];
        
        if ($self != $r['self']) $_SERVER['url_changed']=true;
        $self=$r['self'];
    }
    
    $gcsroot='gs://'.$_app['b'];
    
    for ($__i=0;$__i<2;$__i++) {
    
        if (isset($_app['s']['static'][$self]) && file_exists($_app['s']['static'][$self])) {
            $memcache->set($memcache_key,['f'=>'r','v'=>$_app['s']['static'][$self]]);
            die(___readfile($_app['s']['static'][$self]));
        }
        if (isset($_app['s']['static'][$self]) && file_exists($gcsroot.'/'.$_app['s']['static'][$self])) {
            $memcache->set($memcache_key,['f'=>'r','v'=>$gcsroot.'/'.$_app['s']['static'][$self]]);
            die(___readfile($gcsroot.'/'.$_app['s']['static'][$self]));
        }
        if (isset($_app['s']['script'][$self]) && file_exists($_app['s']['script'][$self])) {
            $memcache->set($memcache_key,['f'=>'s','v'=>$_app['s']['script'][$self]]);
            die(___script($_app['s']['script'][$self]));
        }
        
        if ($_notfound)
        {
            $self=$_notfound;
            if (substr($self,0,7)=='http://')
            {
                if (!strstr($self,'?') && count($_self)>1) $self.='?'.$_self[1]; 
                ___moved($self);
            }            
            
        }
    }

    $gcsfile=$gcsroot.$self;
    if (file_exists($gcsfile)) {
        $f=explode('.',strtolower($gcsfile));
        $ext=end($f);
        $mime=unserialize(file_get_contents(__DIR__.'/_mime.ser'));
        $ct=isset($mime[$ext])?$mime[$ext]:"application/$ext";
        Header("Content-type: $ct");
        readfile($gcsfile);
        die();
    }

    
    ___notfound();