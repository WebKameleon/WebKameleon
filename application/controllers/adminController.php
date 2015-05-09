<?php

class adminController extends Controller
{
    protected function init()
    {
        $user = Bootstrap::$main->session('user');

        if (!isset($user['admin']) || !$user['admin']) mydie(Tools::translate('Insufficient rights'), Tools::translate('Error'));
    }
    
    
    public function user()
    {
        $user=new userModel($this->id);
        $auth=new authController();
        
        $u=$user->data();
        $u['admin']=1;
        
        Bootstrap::$main->session('user',$u);
        
        $name=explode(' ',$u['fullname']);
        Bootstrap::$main->session('auth',array('email'=>$u['email'],'first_name'=>$name[0],'last_name'=>$name[1]));
        
        
        $auth->getServers(true);
        $this->redirect('index');
    }
    
    
    public function enter()
    {
        
        $s=new serverModel($this->id);
        $server=$s->data();
        
        $user=Bootstrap::$main->session('user');

        $server['owner']=1;
        $server['ftp']=1;
        $server['server']=$server['id'];
        $server['username']=$user['username'];
        $server['login_id']=0;
        $s->d_xml($server);
        
        
        Bootstrap::$main->session('server',$server);
        Bootstrap::$main->setGlobals(true);
        
        
        $this->redirect('index');
    }

    public function get()
    {
        $s = new serverModel();
        $webpage=new webpageModel();
        $weblink=new weblinkModel();
        $webtd=new webtdModel();
        $users=new userModel();
        

        $limit=50;
        
        $q=$this->_getParam('q');
        $u=$this->_getParam('u');

        $page=$this->_getParam('page');
        if ($page) $page--;
        
        $offset=$limit*$page;
        
        if ($u)
        {
            $limit=0;
            $offset=0;
            $page=0;
            $user=new userModel($u);
            $pages=false;
        }
        else
        {
            
            $user=null;
        }
        
        $config=Bootstrap::$main->getConfig();
        $families=$config['template']['families'];
        
        $count=$s->count($q,$u);
        $servers = $s->servers($q,$u,'id DESC',$limit,$offset);

        foreach ($servers AS &$server) {
            if ($this->id == $server['id']) {
                $s->get($this->id);

                foreach ($_GET['set'] AS $k=>$v)
                {
                    switch ($k)
                    {
                        case 'nd_expire':
                        case 'nd_last_payment':
                            $s->$k = Tools::timestamp($v);
                            break;
                        
                        case 'social_template':
                            if (!$v || in_array($v,$families)) $s->$k=$v;
                            break;
                        
                        case 'creator':
                            $u=$users->find_one_by_email($v);
                            if (isset($u['username'])) $s->$k=$u['username'];
                            break;
                        
                        default:
                            if (strstr($k,'price')) $v=str_replace(',','.',$v);
                            $s->$k=$v;
                            
                    }
                }
                
                    
                $s->save();
                $server=$s->data();
            }

            $server['more']=false;            
            
            $more = $s->getUsers($server['id']);

            if (count($more > 1)) {
                foreach ($more AS $i => $m) {
                    if (trim($m['username']) == trim($server['creator'])) unset($more[$i]);
                }

                $server['more'] = $more;
            }
            $server['trash'] = $server['id'] < 0;
            
            $s->get($server['id']);
            $server['creator']=$s->creator();
            
            
            if ($server['social_template'])
            {
                $server['social_template_count']=0+$s->from_social_template_count($server['nazwa']);
            }
            
            $server['time']=$s->login_time();
            $server['webpage'] = $webpage->count($server['id']);
            $server['webtd'] = $webtd->count($server['id']);
            $server['weblink'] = $weblink->count($server['id']);
            
        }
        
        if (!$u)
        {
            $pages=array();
            for ($i=0;$i<ceil($count/$limit);$i++) $pages[]=$i+1;
            if (count($pages)<=1) $pages=null;            
        }

        
        
        
        return array('servers' => $servers,'count'=>$count,'user'=>$user,'limit'=>$limit,'page'=>$page+1,'start'=>$limit*$page,'pages'=>$pages,'families'=>$families,'security'=>$config['security']);
    }
    
    public function users()
    {
        $users=new userModel();
        $server=new serverModel();
        
        $limit=50;
        $q=$this->_getParam('q');
        $page=$this->_getParam('page');
        if ($page) $page--;
        
        $global=Bootstrap::$main->getConfig('global');
        $circles=Bootstrap::$main->session('circles');
        
        $all=$users->users($q,'nlicense_agreement_date DESC, fullname',$limit,$limit*$page);
        
        
        if ((!$circles || $this->_getParam('circles')) && $global['gplus'])
        {
            $user = new userModel();
            Gplus::$user=$user->getByEmail($global['gplus']);
            
            $people=Gplus::people_list();
            $circles=array();
            
            foreach($people['items'] AS $man) $circles[]=$man['url'];
            Bootstrap::$main->session('circles',$circles);
        }
        

        foreach($all AS $k=>&$person)
        {
            if (!$person['username']) {
                unset($all[$k]);
                continue;
            }
            
            
            $users->get($person['username']);
            $person['servers']=$users->servers();
            
            $person['time']=$users->login_time();
            $person['logins']=$users->logins();
            
            $logged_in=$users->isLoggedIn();
            if ($logged_in)
            {
                $server->get($logged_in);
                $person['logged_in'] = $server->data();
            }
            
            
            if ($person['link'] && $global['gplus_link'])
            {            
                $link=explode('/',$person['link']);
                $link[count($link)-1] = $global['gplus_link'].'/'.$link[count($link)-1];
                $person['link2']=implode('/',$link).'/posts?'.$person['ulang'];
            }
            elseif (!$global['gplus_link'])
            {
                $person['link2']=$person['link'];
            }
            
            $person['circle'] = is_array($circles) && in_array($person['link'],$circles);
        }

        
        
        $count=$users->count($q);
        $pages=array();
        for ($i=0;$i<ceil($count/$limit);$i++) $pages[]=$i+1;
        if (count($pages)<=1) $pages=null;
        
        return array('users'=>$all,'count'=>$count,'limit'=>$limit,'page'=>$page+1,'start'=>$limit*$page,'pages'=>$pages);
    }
    
    public function trans()
    {
        include(APPLICATION_PATH . '/lang/en.php');
        $en=$words;
        
        $words=array();
        $lang=Bootstrap::$main->session('ulang');
        
        
        $dir=APPLICATION_PATH . '/../files'; 
        $file=$dir.'/'.$lang.'.php';
        
        if (file_exists($file)) include($file);
        
        
        if (isset($_POST['t'])) {

            $words=$_POST['t'];
            file_put_contents($file,'<?php');
            foreach ($words AS $k=>$v) {
                if ($k && $v && $k!=$v) file_put_contents($file,"\n\$words['".addslashes($k)."']='".addslashes($v)."';",FILE_APPEND);
            }
        }
        
        
        
        $words2=array();
        
        foreach($words AS $k=>$word) {
            $words2[]=array('key'=>$k,'word'=>$word,'en'=>$en[$k]);
        }
        
        
        

        return array('words'=>$words2,'lang'=>$lang);
    }
    
    
    public function role()
    {
        $role=$this->id;
        $server=Bootstrap::$main->session('server');
        $thisuser=Bootstrap::$main->session('user');
        $user = new userModel($role);
        
        
        $rights=new rightModel();
        $rights->getRight($server['id'],$role);
        
        $hasright = $rights->server;
                
        $rights->server = $server['id'];
        $rights->username = $role;
        
        $cache=Bootstrap::$main->session('admin_users_info');
        
        if (isset($_POST['user'])) {
            if ($thisuser['username']!=$role)
            {
                foreach($_POST['user'] AS $k=>$v) $user->$k=$v;
                $user->save();
            }
            foreach($_POST['right'] AS $k=>$v) $rights->$k=$v;
            $rights->save();

            $this->redirect('index/get/'.Bootstrap::$main->session('referpage'));
        }
        

        $data=$rights->data();
        
        if (!$hasright)
        {
            $data['pages']='-';
            $data['menus']='-';
            $data['proof']='-';
        }
        
        
        $logins=$user->getlogins();
        
        if ($login_id=$this->_getParam('login'))
        {
            $activities=new activityModel();
            
            foreach ($logins AS &$login)
            {
                if ($login['id']==$login_id)
                {
                    $a=$activities->find_by_login_id($login_id);
                    
                    foreach ($a AS &$act)
                    {
                        $model=$act['table_name'].'Model';
                        $obj=new $model($act['table_id']);
                        $act['table']=$obj->data();
                    }
                    $login['activities'] = $a;
                    //mydie($a);
                }
            }
        }
        
        
        return array('right'=>$data,'user'=>$user->data(),'server'=>$server,'total_time'=>$user->login_time(),'logins'=>$logins);
    }
    
}


    