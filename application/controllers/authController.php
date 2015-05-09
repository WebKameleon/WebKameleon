<?php

class authController extends Controller
{
    
    public function get()
    {
        
        
        $oauth2 = Bootstrap::$main->getConfig('oauth2');

        $scopes="openid profile email";
        $forscope='';
        
        if ($scope=Bootstrap::$main->session('forscope'))
        {
            if (isset($oauth2['scopes'][$scope]))
            {
                $scopes.=' '.$oauth2['scopes'][$scope];
                $forscope=$scope;
            }
        }
        
        $uri = 'http://' . $_SERVER['HTTP_HOST'] . Bootstrap::$main->getRoot() . 'auth/get_token';
        $realm = 'http://' . $_SERVER['HTTP_HOST'] . Bootstrap::$main->getRoot() . 'auth';
        

        $prompt = $this->_getParam('prompt')?:'none'; 
        
        if (isset($_GET['state']) && $_GET['state']==Bootstrap::$main->session('oauth2_state'))
        {
            if (isset($_GET['code']))
            {
                $data = array(
                    'code' => $_GET['code'],
                    'client_id' => $oauth2['client_id'],
                    'client_secret' => $oauth2['client_secret'],
                    'redirect_uri' => $uri,
                    'grant_type' => 'authorization_code'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response_token = curl_exec ($ch);
                
                $token=json_decode($response_token,true);
                              
                if (isset($token['refresh_token']) && strlen($token['refresh_token']))
                {
                    $token['created']=time();
                    $response_token=json_encode($token);
                }
  
                $token_to_update = isset($token['refresh_token']) && strlen($token['refresh_token']) ? json_encode(array($forscope=>$response_token)) : null;
   
   
                if (isset($token['access_token']))
                {
                    
                    $authstate=new authstateModel($_GET['state']);
                    $authstate->nd_complete=Bootstrap::$main->now;
                    $authstate->save();
                    
                    curl_setopt($ch, CURLOPT_URL,'https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$token['access_token']);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, null);
                    
                    $auth = json_decode(curl_exec ($ch),true);
                    
                    if (isset($auth['given_name'])) $auth['first_name'] = $auth['given_name'];
                    if (isset($auth['family_name'])) $auth['last_name'] = $auth['family_name'];
                    
                    
                    if (isset($auth['id']))
                    {
                        Bootstrap::$main->session('auth', $auth);
                        
                        if ($this->authorize($auth,$token_to_update))
                        {

                            $this->getServers(true);
            
                            $user = Bootstrap::$main->session('user');
                            $r    = Bootstrap::$main->session('redirect')?:Bootstrap::$main->getRoot();
                            
                            
                            $authstate->nd_user_joined = $user['nlicense_agreement_date'];
                            $authstate->save();
                            
                            $user_model=new userModel($user['username']);
                            
                            
                            if ($user_model->login_time() < 300 && !$user_model->notips) Bootstrap::$main->settips();
                            
                            
            
                            $this->redirect($r == Bootstrap::$main->getRoot() ? ($user['after_login_redirect'] ? 'index' : 'wizard') : $r);                    
                        }
                        else
                        {
                            $this->redirect(Bootstrap::$main->getRoot());
                        }
                    }
                    else
                    {
                        if (isset($auth['error']))
                        {
                            Bootstrap::$main->error(ERROR_ERROR,'Google responds %s',array($auth['error']['message']));
                        }
                        $this->redirect('public');
                    }
                    
                }               
                else
                {
                    $this->redirect('public');
                }
                
            }
            elseif (isset($_GET['error']) && $_GET['error']=='immediate_failed') {
                $this->redirect('auth?prompt=select_account');
                //$this->redirect('auth?prompt=consent');
            }
            else
            {
                $this->redirect('public/scopes');
            }
        }
        elseif (isset($_GET['state'])) {
            $this->redirect('public');
        }
        else {        
        
            $state=md5(rand(90000,1000000).time());
            
            $authstate=new authstateModel();
            $authstate->state=$state;
            $authstate->nd_create=Bootstrap::$main->now;
            $authstate->ip=$_SERVER['REMOTE_ADDR'];
            $authstate->save();
            
            Bootstrap::$main->session('oauth2_state',$state);
            $url='https://accounts.google.com/o/oauth2/auth?client_id='.urlencode($oauth2['client_id']);
            $url.='&response_type=code';
            $url.='&scope='.urlencode($scopes);
            $url.='&redirect_uri='.urlencode($uri);
            $url.='&openid.realm='.urlencode($realm);
            $url.='&state='.$state;
            $url.='&prompt='.$prompt;
            $url.='&access_type=offline';
              
            header('Location: ' . $url);
            die();
        }
    }
    
    
    
    public function get_token()
    {
        if (!Bootstrap::$main->session('user')) return $this->get();
        
        if ($this->_hasParam('return_url')) Bootstrap::$main->session('redirect', base64_decode($this->_getParam('return_url')));        
        
        Google::getUserClient(null,true,$this->id);
        $this->redirect(Bootstrap::$main->session('redirect') ?: Bootstrap::$main->getRoot().'index/get/'.Bootstrap::$main->session('referpage')); 
    }
    
    

    /**
     * @param array $auth
     * @return bool
     */
    public function authorize($auth,$access_token=null)
    {
        
        
        if (isset($auth['email'])) {
            $auth['email']=strtolower($auth['email']);
            $userModel = new userModel;
            $user = $userModel->getByEmail($auth['email']);

            if (!$user) {
                $user = $userModel->addUser($auth['email'], $auth['name']);
                
                $userModel->from_campaign = isset($_COOKIE['_wk']) && $_COOKIE['_wk'] ? $_COOKIE['_wk'] : Bootstrap::$main->session('campaign');
                $userModel->save();
                
                $data=$user;
                Bootstrap::$main->session('user', $user);
                $data['him']=$auth['email'];
                $global=Bootstrap::$main->getConfig('global');
                
                if ($global['admin']!=$auth['email']) Observer::observe('welcome', $data);
            }
            
            $userModel->load($user);
            
            if ($access_token) $userModel->access_token=$access_token;
            if (!$userModel->ulang) $userModel->ulang = $auth['locale'];
            
            $userModel->photo=$auth['picture'];
            $userModel->link=$auth['link'];
            $userModel->fullname=$auth['name'];
        
            $userModel->save();
            $user=$userModel->data();
            
            Bootstrap::$main->session('user', $user);
            Bootstrap::$main->session('ulang', $user['ulang']);
            Bootstrap::$main->session('lang', $user['ulang']);

            
            
            return true;
        }
        return false;
    }

    public function getServers($requery = false, $id=0)
    {

        $servers = Bootstrap::$main->session('servers');
        if ($servers && count($servers) && !$requery) return $servers;
        $userModel = new userModel(Bootstrap::$main->session('user'), false);

        $servers = Bootstrap::$main->session('servers', $userModel->servers());
        Bootstrap::$main->session('trash', $userModel->servers(true));

        
        
        if (is_array($servers) && (!Bootstrap::$main->session('server') || !count(Bootstrap::$main->session('server')) || $id)) {
            foreach ($servers AS $server) {
                if ($server['nd_expire'] && $server['nd_expire'] < time()) continue;

                if ($id==$server['id'] || !Bootstrap::$main->session('server') || !count(Bootstrap::$main->session('server'))) {
                    Bootstrap::$main->session('server', $server);
                    break;
                }
            }

        }

        return $servers;
    }

    public function login($ip, $server = null)
    {
        $user=Bootstrap::$main->session('user');
        if (!isset($user['username']) || !$user['username']) return false;
    
        
        $forcenew = $server ? true : false;
        if (!$server) $server = Bootstrap::$main->session('server');
        
                
        if ($server)
        {
        
            if (!isset($server['login_id'])) $forcenew=false;
            
            $s=new serverModel();
            $s->d_xml($server);
            Bootstrap::$main->session('server',$server);
            
            
            $config=Bootstrap::$main->getConfig();
            
            $anonymous_expire_time=isset($config['security']['anonymous_expire'])?$config['security']['anonymous_expire']:$config['security.anonymous_expire'];
            
            
            if ($server['owner'] && $server['anonymous_expire']<time()+($anonymous_expire_time/2)) {
                $s=new serverModel($server['id']);
                
                $s->anonymous_expire=time()+$anonymous_expire_time;
                
                while (true) 
                {
                    $s->anonymous=md5(time()+rand(10000,99999));
                    if (!$s->find_one_by_anonymous($s->anonymous)) break;
                }
                $s->save();
                
                if (is_array($s->data())) foreach($s->data() AS $k=>$v) $server[$k]=$v;
                Bootstrap::$main->session('server',$server);
            }
            
            
    
            if (!isset($server['login_id'])) $server['login_id']=0;
            if (!$server['login_id']) $server['login_id'] = 0+Bootstrap::$main->session('no_server_login_id');
            
            
            $login = new loginModel($server['login_id']);
            if (Bootstrap::$main->now - $login->tout > LOGIN_IDLE_TIME_MINS * 60 || $ip != $login->ip || $forcenew) {
                $login = new loginModel(array(
                    'ip' => $ip,
                    'server' => $server['id'],
                    'username' => $server['username'],
                    'tin' => Bootstrap::$main->now
                ), true);    
            }
            
            $login->tout = Bootstrap::$main->now;
    
            $login->save();
            
            Bootstrap::$main->session('no_server_login_id',$login->id);
    
            
            if ($server['login_id'] != $login->id) {
                $this->getServers(true,$login->server);
            }
    
            $user=new userModel($server['username']);
            
           
            
            if ($user->username && $user->lastserver!=$server['id']) {
                $user->lastserver=$server['id'];
                $user->save();
            }
        }
        else
        {
            $login_id=0+Bootstrap::$main->session('no_server_login_id');
            $login = new loginModel($login_id);
             
            if (Bootstrap::$main->now - $login->tout > LOGIN_IDLE_TIME_MINS * 60 || $ip != $login->ip ) {
                $login = new loginModel(array(
                    'ip' => $ip,
                    'server' => null,
                    'username' => $user['username'],
                    'tin' => Bootstrap::$main->now
                ), true);    
            }
            
            $login->tout = Bootstrap::$main->now;
            $login->save();
            
            Bootstrap::$main->session('no_server_login_id',$login->id);
        }
        
        return $login;

    }



}
