<?php

class anonymousController extends indexController
{
    private $shouldBeRerendered=false;

    public function __call($name, $args)
    {
    
        if (strlen($name)!=32) return $this->tryMedia($name); 
        $server=new serverModel();
        $s=$server->find_one_by_anonymous($name);
        
    
        if (!$s) return $this->tryMedia($name);
        if ($s['anonymous_expire']<time()) mydie('This link expired on '.date('Y-m-d H:i',$s['anonymous_expire']));
            
        $path=explode('/',$this->fullpath);
        
        
        
        Bootstrap::$main->session('expire',$s['anonymous_expire']);
        $s['anonymous']=1;
        Bootstrap::$main->session('server',$s);
        Bootstrap::$main->setServer($s['id'],false,false);
        Bootstrap::$main->setGlobals();
        Bootstrap::$main->session('editmode',PAGE_MODE_PREVIEW);
        Bootstrap::$main->hidetopbar(1);
        Bootstrap::$main->session('ulang','en');
        Bootstrap::$main->session('lang',(isset($path[3]) && strlen($path[3])==2)?$path[3]:$s['lang']);
        Bootstrap::$main->session('ver',(isset($path[4]) && $path[4]+0>0)?$path[4]:$s['ver']);
        
        
        
        if (!Bootstrap::$main->session('auth')) Bootstrap::$main->session('auth',array('first_name'=>'Anonymous','last_name'=>'Anonymous','email'=>'anonymous@google.com'));
        if (!Bootstrap::$main->session('user') || true) Bootstrap::$main->session('user',array('username'=>'anonymous','skin'=>'kameleon','ulang'=>'en','access_token'=>'-','anonymous'=>1));
        
        $s['owner']=0;
        $s['login_id']=0;
        $s['server']=$s['id'];
        $s['username']='anonumous';
        
        Bootstrap::$main->session('redirect', false);
        Bootstrap::$main->session('server',$s);
        Bootstrap::$main->session('servers',array($s));
        
         
        $auth=new authController();
        $login=$auth->login($_SERVER['REMOTE_ADDR']);
         
        $s['login_id']=$login->id;
        Bootstrap::$main->session('server',$s);
         
         
        if ($path[2]=='wkz') return $this->wkz($s);
        
        $page=0+(isset($path[2])?$path[2]:0);
        if ($page<0) $page=0;
        
        $this->id=$page;
        $_GET['page']=$page;
        
        $this->shouldBeRerendered = true;
        return $this->get();
        
    }
    
    public function postRender(&$data)
    {
        if (!$this->shouldBeRerendered) return $data;
        
        $root=Bootstrap::$main->getRoot();
        return preg_replace('#(=["\']'.$root.')([^/][^"\']+)#','\\1anonymous/'.session_id().'/\\2',$data);
    }    
    
    
    protected function tryMedia($name)
    {
        session_write_close();
        session_id($name);
        session_start();
     
           
        if (!count($_SESSION)) return;
        
        
        if (Bootstrap::$main->session('expire')<time()) mydie('This link expired on '.date('Y-m-d H:i',Bootstrap::$main->session('expire')));
        
        $path=explode('/',$this->fullpath);
        
        
        
        if (isset($path[2]) && strlen($path[2]) ) {
            unset($path[0]);
            unset($path[1]);
            
            $controller_name=$path[2].'Controller';
        
            if (class_exists($controller_name))
            {
                $action=isset($path[3])?$path[3]:'get';
                $id=isset($path[4])?$path[4]:null;
                
                $controller=new $controller_name(implode('/',$path),$id);
                
                Bootstrap::$main->setGlobals();               
                $ret=$controller->$action();
            
                $this->setViewTemplate($controller->getViewTemplate());
                return $ret;
            }
        }
        
    }
    
    
    protected function wkz($s)
    {
        $wizard=new wizardController();
        
        
        
        Bootstrap::$main->session('server',$s);
        
        $file=$wizard->export(true);
        
        //mydie($s,$file);
        $id=basename($file);
        $wizard=new wizardController(null,$id);
        $wizard->export();
        die(); //just in case
    }
    
}