<?php

include __DIR__ . '/const.php';

class Bootstrap
{
    private static $SESSION_PREFIX = 'kameleon';
    private $session_prefix;
    /**
     * @var Doctrine_Connection
     */
    private $conn;

    /**
     * @var Bootstrap
     */
    public static $main;
    private $ip, $root;
    public $now;
    private $user;
    public $config;
    protected $debug;
    protected $globals;
    protected $path;
    /**
     * @var Tokens
     */
    public $tokens;

    /**
     * @var Translate
     */
    public $translate,$translates;

    /**
     * @var Kameleon
     */
    public $kameleon;

    protected $id;

    public function __construct($conn, $config)
    {
        $this->conn = $conn;
        $this->config = $config;

        $this->session_prefix = self::$SESSION_PREFIX . '_' . md5(__DIR__);

        self::$main = $this;
        $this->now = time();
        $this->ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'0.0.0.0');

	if (isset($_SERVER['REQUEST_URI']))
	{
	    $uri = $_SERVER['REQUEST_URI'];
	    $root = dirname($_SERVER['SCRIPT_NAME']);
    
	    $uri = str_replace($root, '', $uri);
	    if ($root != '/') $root .= '/';
    
	    $this->root = $root;
	}
	else $root='/';
	
	$this->kameleon = new Kameleon($root);
	
	$this->session('db_name',$config['db.dbname']);
    }

    public function lang()
    {
	
	if ($_l=$this->session('ulang')) return $_l;
	
        $langs = $this->getConfig('langs');

        // break up string into pieces (languages and q factors)
	$alang=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'en';
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',$alang , $matches);

        if (count($matches[1])) {
            $tmp = array_combine($matches[1], $matches[4]);

            foreach ($tmp as $lang => $val) {
                if ($val === '')
                    $tmp[$lang] = 1;
            }

            arsort($tmp, SORT_NUMERIC);

            $lang = substr(key($tmp), 0, 2);
            if (in_array($lang, $langs)) return $lang;
        }
	

        return 'en';
    }

    public function globals($name = 'index')
    {
        $user = $this->session('user');
        $skin = isset($user['skin'])?trim($user['skin']):'kameleon';

        if (empty($skin)) $skin = 'kameleon';

	
        $clearCache = $this->session('clearCache');
        $this->session('clearCache', 0);
	
	$_ruri=explode('?',$_SERVER['REQUEST_URI']);
	$request_uri_no_get=$_ruri[0];

        $globals = array('root' => $this->root, 'lang' => $this->session('lang'), 'ver' => $this->session('ver'),
			 'skin' => $skin, 'auth' => $this->session('auth'), 'translate' => $this->translate,
			 'translates' => $this->translates, 'user' => $user, 'config' => $this->config,
			 'langs' => $this->config['langs'], 'kameleon' => $this->kameleon,
			 'title' => $this->translate->trans($name), 'hidetopbar' => $this->session('hidetopbar'),
			 'editmode' => $this->session('editmode'), 'server' => $this->session('server'),
			 'servers' => $this->session('servers'), 'ulang' => $this->session('ulang'),
			 'clipboard' => is_array($this->session('clipboard')), 'clearCache' => $clearCache,
			 'tokens' => $this->tokens,'path'=>$this->session('path'), 'langs_used'=>$this->session('langs_used'),
			 'now'=>$this->now,'others'=>$this->others(),'noConflictParam'=>'','referpage'=>$this->session('referpage'),
			 'time_delta'=>$this->session('time_delta'), 'tips'=>$this->session('tips'),'request_uri_no_get'=>$request_uri_no_get);

        if (isset($this->config['global'])) $globals = array_merge($globals, $this->config['global']);

        foreach ($_SERVER AS $k => $v) if (!isset($globals[$k])) $globals[$k] = $v;
	foreach ($_GET AS $k => $v) if (!isset($globals[$k])) $globals[$k] = $v;
	
	unset($globals['config']['oauth2']);
	unset($globals['config']['payment']);
	if (isset($globals['config']['default']['ftp_pass'])) unset($globals['config']['default']['ftp_pass']);
	
	

	if (isset($globals['server']['analytics']))
	{
	    $globals['server']['analytics'] = GN_Smekta::smektuj($globals['server']['analytics'],array('lang' => $this->session('lang'), 'ver' => $this->session('ver')));
	}
	
	$globals['mayOpenGallery'] = (isset($globals['server']['owner']) && $globals['server']['owner']) || $this->config['files']['editorMayOpenGallery'];
	
        return $globals;
    }

    public function run()
    {
	

        $part = substr($_SERVER['REQUEST_URI'], strlen($this->root));
        if ($pos = strpos($part, '?')) $part = substr($part, 0, $pos);
        $this->path = $part;
        $parts = explode('/', $part);

        if (!strlen($parts[0])) $parts[0] = 'index';

        $this->user = $this->session('user');

        $NO_TEMPLATE = in_array($parts[0], array('auth', 'ajax', 'logout', 'wizard', 'anonymous','user','thumb','payment','public','scopes'));
	$NO_LOGIN = in_array($parts[0], array('ajax', 'logout', 'template', 'anonymous','images','uimages','media','thumb'));

        $debug = Debugger::debug(null, implode('/', $parts));
        if ($debug) GN_Smekta::set_debug_fun(array('Debugger', 'debug'));

	
	if ($parts[0] != 'public') {
	    if (is_null($this->user) && $parts[0] != 'auth' && $parts[0] != 'anonymous') {
		$parts[0] = 'public';
		$parts[1] = 'get';
		if (!$this->session('ulang')) $this->logout();
		if (!isset($_GET['setuLang'])) $this->session('redirect', $_SERVER['REQUEST_URI']);
		if (isset($_GET['state'])) $this->session('forscope','drive');
		if (isset($_GET) && count($_GET)>0 && !isset($_GET['setuLang']) && !isset($_GET['setcampaign']) ) $this->redirect('auth');
	    } elseif ($parts[0] != 'anonymous') {		
		$authController = new authController();
		
		$servers = $authController->getServers();
		//$s=is_array($servers) && count($servers)>0 ? current($servers) : null;
		
		if (!$NO_LOGIN) $authController->login($this->ip);

		$this->session('ulang', isset($this->user['ulang'])?$this->user['ulang']:$this->lang());
    
		if (isset($_GET['setServer'])) $this->setServer($_GET['setServer']);
    
		$this->setGlobals();
    
		if (!$servers && !$NO_TEMPLATE) {
		    $parts[0] = 'wizard';
		    $parts[1] = 'get';
		    $NO_TEMPLATE = true;
		}
    	
		if ($r = $this->session('redirect') && $parts[0] != 'auth') {
		    $this->session('redirect', false);
		}		
		
	    } else {
		if (strlen($parts[1]) < 20) $parts[1] = md5(0);
		$_GET = array();
		$_POST = array();
		$_REQUEST = array();
	    }
	}
	
	
	if ($parts[0]=='public') {
	    $this->config2array();
	    
	    $this->tokens = new Tokens();
	    $this->tokens->init($this->root);
	}
	

        if (!$this->session('ulang')) {
            $this->session('ulang', $this->lang());
        }

	
	
        if (!$this->session('lang')) {
	    $server=$this->session('server');
            if (isset($server['lang'])) $this->session('lang', $server['lang']);
	}
        

        if (is_array($_GET)) foreach ($_GET AS $k => $v) if (method_exists($this, $k)) $this->$k($v);

        $this->translate = new Translate($this->session('ulang')?:'en');
	$this->translates = new Translate($this->session('lang')?:'en');
	
        if (!isset($parts[1]) || !strlen($parts[1])) $parts[1] = 'get';

        if (isset($parts[2])) $this->id = $parts[2];

        $controller_name = $parts[0] . 'Controller';

        if (!class_exists($controller_name)) {
            header('HTTP/1.0 404 Not Found');
            die;
        }
	
	
	//mydie($user);
        /**
         * @var Controller $controller
         */
        $controller = new $controller_name(implode('/', $parts), isset($parts[2]) ? $parts[2] : null);
        $controller->setViewTemplate(strtolower($parts[0] . '.' . $parts[1]));

        if ($this->session('user') && !$this->session('template') && !$NO_TEMPLATE) $this->redirect('wizard');

        if ($this->session('user') && !$this->session('template')) {
            $this->config2array();
        }

        $action = $parts[1];

        ob_start();

        $controller_data = $controller->$action();
        $this->debug[$controller_name] = ob_get_contents();

	$r = $controller->redirect();
	//if ($r) mydie($r,$controller_name);
        if ($r) $this->redirect($r);

        @ob_end_clean();

        $controller->finish($controller_data, $this->debug[$controller_name]);

        $globals = $this->globals($parts[0] . '.' . $parts[1]);

	
        if (is_array($controller_data)) {
            $controller_data = array_merge($globals, $controller_data);
        } else {
            $controller_data = $globals;
        }

	$controller_data['controller_name']=$parts[0];
	$controller_data['controller_action']=$parts[1];
	
	
	
        $template = $controller->getViewTemplate();
        $layout = $controller->getViewLayout();

        if ($template[0] != '/') {
            $template = APPLICATION_PATH . '/views/scripts/' . $template . '.html';
        }

        $ret = '';
        if (file_exists($template)) {
            $html = file_get_contents($template);
            if ($layout) {
                if (file_exists($layout)) {
                    $html = str_replace('{template}', $html, file_get_contents($layout));
                }
            } else {
                $html = $controller->addKameleonTags($html, $this->session('editmode'));
            }

            $controller_data['__debugger__'] = Debugger::debug($debug);

            $ret = GN_Smekta::smektuj($html, $controller_data, false, array(dirname($template), VIEW_PATH . '/scripts', VIEW_PATH . '/replace'), $template);
        }

	
	$global=$this->getConfig('global');
	
	if (isset($global['charset']) && $global['charset'] && !headers_sent()) Header('Content-Type: text/html; charset='.$global['charset']);
	
        return $controller->postRender($ret);
    }

    public function setDebug($debug)
    {
		//mydie($debug);
        $this->session('debug', $debug);
    }

    public function getDebug()
    {
        if (!$this->session('debug')) return false;

        return true;
    }

    public function debug($k = null, $v = null)
    {

        if (!$this->session('debug')) return;

        if (!is_null($k)) {
            $this->debug[$k][] = $v;

            return;
        }

        $debug = $this->debug;
        foreach ($debug AS $k => $v) {
            if (empty($v)) unset($debug[$k]); else $debug[$k] = explode("\n", $v);
        }
        $debug['session'] = $this->session();

//        $jdebug = str_replace("'", "\\'", json_encode($debug));
//        $jdebug = str_replace("\\n", "\\\n", $jdebug);

        $jdebug = json_encode($debug);

	$ret='';
	$user=$this->session('user');
	
	if ($user['admin'])
	{
	    $ret = '<script>';
	    $ret .= "console && console.log && console.log($jdebug, 'kameleoDebug');";
	    $ret .= '</script>';
	}
	
        return $ret;
    }

    public function session($key = null, $val = null)
    {

        if (is_null($key)) return isset($_SESSION[$this->session_prefix]) ? $_SESSION[$this->session_prefix] : array();
        if (is_null($val)) {
            return isset($_SESSION[$this->session_prefix][$key]) ? $_SESSION[$this->session_prefix][$key] : null;
        }
        if ($val !== false) $_SESSION[$this->session_prefix][$key] = $val; else unset($_SESSION[$this->session_prefix][$key]);

        return $val;
    }

    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return Doctrine_Connection
     */
    public function getConn()
    {
	if (!is_object($this->conn))
	{
	    $this->conn=Doctrine_Manager::connection($this->conn);
	}
	
	
        return $this->conn;
    }

    public function getConfig($what = null)
    {
        if ($what && isset($this->config[$what])) return $this->config[$what];
		if ($what) {
			$config=$this->config;
			
			foreach (explode('.',$what) AS $w)
			{
				if (isset($config[$w])) $config=$config[$w];
			}
			return $config;
		}

        return $this->config;
    }

    public function getServer()
    {
        return $this->session('server');
    }

    public function logout($redirect=null)
    {
	$user=$this->session('user');
	if (isset($user['username']) && $user['username']) {
	    $u=new userModel($user['username']);
	    $u->lastserver=null;
	    $u->save();
	}
	
        unset($_SESSION[$this->session_prefix]);
	if ($redirect) $this->redirect($redirect);
    }

    public function setGlobals($clear = false, $dontchangelanguage=false)
    {
        $tokenClassName = 'Tokens';

        $server = $this->session('server');
        if ($server) {

            if (!$this->session('ver')) $this->session('ver', $server['ver']);
            if (!$this->session('lang')) $this->session('lang', $this->session('ulang'));
            if (!$this->session('lang') || ($clear && !$dontchangelanguage) || !$this->session('template')) $this->session('lang', $server['lang']);
            $ver = $this->session('ver');
            $lang = $this->session('lang');

            if (is_null($this->session('editmode'))) {
                $this->session('editmode', PAGE_MODE_EDIT);
            }

            if ($clear || !$this->session('template')) {

                $paths = Tools::getPaths($server, $ver);

                $this->session('template', $paths['template_path']);
                $this->session('template_path', $paths['template_path']);
                $this->session('template_media', $paths['template_media_path']);

                $this->session('uincludes', $paths['uincludes']);
                $this->session('uincludes_ajax', $paths['uincludes_ajax']);

                $this->session('uimages', $paths['uimages']);
                $this->session('uimages_path', $paths['uimages_path']);

                $this->session('ufiles', $paths['ufiles']);
                $this->session('ufiles_path', $paths['ufiles_path']);
            }

            $this->session('template_images', $this->root . 'images');
		$this->session('template_dir', $this->root . 'template');

            if ($path = $this->session('template')) {

                if (file_exists($path . '/config.ini')) {
                    $template_config = parse_ini_file($path . '/config.ini');
                    foreach ($template_config AS $k => $v) if (substr($k, 0, 9) == 'security.') unset($template_config[$k]);
                    $this->config = array_merge($this->config, $template_config);
                }

                $this->config2array();
		unset($this->config['db']);
		

                if ($this->config['security']['allow_template_tokens'] && file_exists($path . '/' . $this->config['template']['token_class'] . '.php')) {
                    if (!class_exists($this->config['template']['token_class']))
					{
						require_once $path . '/' . $this->config['template']['token_class'] . '.php';
					}
					if (class_exists($this->config['template']['token_class'])) {
                        $tokenClassName = $this->config['template']['token_class'];
                    }
                }
            }

            $this->setPath($lang, $ver);

        }

        $this->config2array();

        $this->tokens = new $tokenClassName();
        $this->tokens->init($this->root);

        if (isset($_SERVER['REQUEST_URI'])) $this->session('request_uri', $_SERVER['REQUEST_URI']);
    
		if (!$this->session('langs_used') || !count($this->session('langs_used')) || $clear ) {
			$webpage = new webpageModel();
			$langs=$webpage->langs();
			
			foreach($langs AS $i=>$lang) if (!$lang) unset($langs[$i]);
			
			//if ($this->session('lang') && !in_array($this->session('lang'),$langs)) $langs[]=$this->session('lang');
			$this->session('langs_used',$langs);
			
		}
	

    }

    public function setPath($lang, $ver=0)
    {

        $path = array();
	if (!$ver) $ver=$this->session('ver');
	$server=$this->session('server');
        foreach ($this->config['path'] AS $k => $v) {
            $path[$k] = GN_Smekta::smektuj($v, get_defined_vars());
        }

	$this->session('path',$path);
    }

    public function config2array($config=null)
    {
        $c = array();

		$server=$this->session('server');
		
	
        foreach ($config?:$this->config AS $k => $v) {
            $k = str_replace('.', "']['", $k);
            $k = "\$c['" . $k . "']";
            eval($k . '=$v;');
        }
		
		if (isset($server['trust']) && $server['trust']) {
			$c['security']['allow_td_module_execute'] = $c['security']['allow_template_tokens'] = true;
		}
	
		if ($config) return $c;
	
        $this->config = $c;
        $this->webtd_widgets();
        return $this->config;
    }

    protected function webtd_widgets()
    {

        if (!isset($this->config['webtd']['type']) || !is_array($this->config['webtd']['type'])) return;

        $widgets = $this->config['webtd']['type'];
	
	
	
	ksort($widgets);
	
	
	foreach ($widgets AS $type => $data)
	{
	    if (!isset($data['parent']) && $type<20000) $widgets[$type]['parent']='Template defined';
	}
	
        
        $this->config['webtd']['type'] = $widgets;

        $names = array();
        $menu = array();

	$user=new userModel();
	$user=$user->getCurrent();
	
        foreach ($widgets as $type => $data) {
            if (!isset($data['name'])) continue;
	    
	    if (isset($data['admin']) && $data['admin'] && !$user->admin) continue;
	    if (isset($data['admin']) && $data['admin']) $data['admin_only']=true; 
	    
	    
	    if (isset($data['scope']) && $user->hasAccess($data['scope']))
	    {
		$data['scope']='';
	    }
	    
            //if (isset($names[$data['name']])) {echo $type.$data['name'];continue;}
            $names[$data['name']] = true;
            if (isset($data['parent'])) {
                $menu[$data['parent']]['name'] = $data['parent'];
                $menu[$data['parent']]['children'][$type] = $data;
            } else {
                $menu[$type] = $data;
            }
        }

        $this->config['widgets_menu'] = $menu;
    }

    protected function seteditmode($editmode)
    {
        if ($editmode) {
            $this->session('editmode', PAGE_MODE_EDIT);
        } else {
            $this->session('editmode', PAGE_MODE_PREVIEW);
        }

        $this->redirect($this->path);
    }

    public function setServer($server, $auth_stuff = true, $redirect = true)
    {
        $servers = $this->session('servers');
        if (is_array($servers)) {
            foreach ($servers AS $id => $s) {
                if (trim($s['nazwa']) == $server || $id == $server) {

                    if ($s['nd_expire'] && $s['nd_expire'] < time()) {
                        $this->error(ERROR_ERROR, 'Server %s expired on %s', array($s['nazwa'], $this->kameleon->datetime($s['nd_expire'])));

                        return;
                    }
		    
		    Tools::activity('server',$s['id'],'E');

                    if ($auth_stuff) {
                        $authController = new authController();
                        $authController->login($this->ip, $s);
                        $authController->getServers(true,$id);
                    }
                    foreach (array('ver', 'lang', 'template') AS $k) {
                        $this->session($k, false);
                    }

                    $this->setGlobals(true);
                    $this->session('clearCache', 1);
                    if ($redirect) $this->redirect('index');
                    break;
                }
            }
        }
    }

    public function redirect($r, $get = null)
    {
	
        if (substr($r, 0, 4) != 'http' && $r[0] != '/') {
            $r = $this->root . $r;
        }
        if (is_array($get)) {
            foreach ($get AS $k => $v) {
                if (!strlen($v)) unset($get[$k]); else $get[$k] = urlencode($k) . '=' . urlencode($v);
            }
            if (count($get)) {
                $r .= strstr($r, '?') ? '&' : '?';
                $r .= implode('&', $get);
            }
        }

	
	
        if (!headers_sent()) Header('Location: ' . $r);
	else {
	    echo "<script>location.href='$r';</script>";
	}
        die();
    }

    public function setreferpage($page)
    {
        $this->session('referpage', $page);
    }

    public function setreferlang($lang)
    {
        $this->session('referlang', $lang);
    }    
    
    
    public function setrefersid($sid)
    {
        $this->session('refersid', $sid);
    }

    public function hidetopbar($hidetopbar)
    {
        $this->session('hidetopbar', $hidetopbar);
    }

    protected function switcheditmode($switcheditmode)
    {
        $neweditmode = $this->session('editmode') == PAGE_MODE_EDIT ? PAGE_MODE_EDITHF : PAGE_MODE_EDIT;
        $this->session('editmode', $neweditmode);

        $this->redirect($this->path);
    }

    /**
     * @param int $type
     * @param string $error
     * @param array $params
     * @param string $link
     */
    public function error($type = 0, $error = false, $params = array(), $link = '')
    {
        $this->session('error', $error);
        $this->session('error_type', $type);
        $this->session('error_params', $params);
        $this->session('error_link', $link);
    }
    
    public function isError()
    {
	return $this->session('error');
    }

    /**
     * @return string
     */
    public function getServerHttpUrl()
    {
        $server = $this->session('server');

        if ($server['http_url']) {
            if (strpos($server['http_url'], 'http') === 0) {
                return $server['http_url'];
            }

            return 'http://' . $server['http_url'];
        }

        return 'http://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
	if (!$lang){
	    $server=$this->session('server');
	    $lang=$server['lang'];
	}
        $this->session('lang', $lang);
	$this->setGlobals(true,true);
    }

    
    public function setuLang($lang)
    {
        $this->session('ulang', $lang);
	$this->redirect($this->getRoot());
    }    
    
    /**
     * @return string
     */
    public function getKameleonUrl()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . $this->getRoot();
    }
    
    
    protected function others()
    {
	$others=$this->session('_others');
	
	if ( !isset($others['timestamp']) || $others['timestamp']+5 < $this->now)
	{
	    $people = (isset($others['people'])) ? $others['people'] : array(); 
	    $users=new userModel();
	    $user=$this->session('user');
	    $server=$this->session('server');
	    
	    if (isset($server['id']) && $server['id'] && isset($user['username']) && $user['username'])
	    {
		
		$people2 = $users->getAllLoggedIn($server['id'],$user['username']);
		
		$ret=array();
		
		foreach($people2 AS $person)
		{
		    $key=$person['username'];
		    if (!isset($people[$key]['color']))
		    {
			$person['color']='#';
			for ($i=0;$i<6;$i++) $person['color'].=rand(0,9);
			
		    } else $person['color'] = $people[$key]['color'];
		    
		    unset($person['access_token']);
		    
		        
		    $ret[$key]=$person;
		}
		
		$others['people']=$ret;
		$others['timestamp']=$this->now;
		$others['system']=$users->getAllLoggedIn(0,$user['username']);
		if ($others['system']) $others['system']++;
	    }
	    
	    if (!isset($others['color']))
	    {
		$others['color']='#';
		for ($i=0;$i<6;$i++) $others['color'].=rand(0,9);
	    }
	    
	    $others=$this->session('_others',$others);
	    
	}

	return $others;
	
    }
    
    public function reminder($servers)
    {
	$this->config2array();
	$global=$this->getConfig('global');
	
	$_SERVER['HTTP_HOST']=$global['http_host'];
	$this->root=$global['http_root'];
	
	foreach ($servers AS $server) {
	    
	    $data=$server;
	    if (!$data['email']) continue;
	    $data['him']=$data['email'];
	    
	    $days=floor(($server['nd_expire']-time())/(24*3600));
	    $data['days']=$days;
	    
	    $data['kameleon']=$this->kameleon;
	    $data['tokens']=$this->tokens;
	    
	    unset($data['access_token']);
	    
	    Observer::observe('reminder', $data,$server['ulang']?:'en',null,$days); 
	}
    }
    
    protected function UserKnowsHowToEditHeaderFooter($v)
    {
	$v+=0;
	$u=$this->session('user');
	$user=new userModel($u['username']);
	$user->knows_hf = $v;
	$user->save();
	
	$u['knows_hf']=$v;
	$this->session('user',$u);
	
	$this->redirect('index');
    }
    
    
    
    public function closeConn()
    {
	if (is_object($this->conn)) $this->conn->close();
    }

    public function settips($tips=null)
    {
	if (is_null($tips)) {
	    $tips=isset($this->config['default']['tips'])?$this->config['default']['tips']:$this->config['default.tips'];
	}
	
	while($pos=strpos($tips,'-'))
	{
	    $before=preg_replace('/[^0-9]*([0-9]+)$/','#\1',substr($tips,0,$pos));
	    $before=substr($before,strpos($before,'#')+1);
	    
	    $after=preg_replace('/^([0-9]+)[^0-9]*/','\1#',substr($tips,$pos+1));
	    $after=substr($after,0,strpos($after,'#'));
	    
	    $tips=substr($tips,0,$pos-strlen($before)) . implode(',',range($before,$after)) . substr($tips,$pos+1+strlen($after));
	}
	
	$this->session('tips',$tips);
	
	if (!strlen($tips))
	{
	    $user=new userModel();
	    $user->getCurrent();
	    $user->notips=1;
	    $user->save();
	}
	
	$_ruri=explode('?',$_SERVER['REQUEST_URI']);
	$request_uri_no_get=$_ruri[0];
	
	if (isset($_GET['settips'])) $this->redirect($request_uri_no_get);
    }
    
    
    public function setcampaign($campaign)
    {
	$this->session('campaign',$campaign);
	
	setcookie('_wk',$campaign, time()+3600*24*60); 
	if (!isset($_COOKIE['_wk']) && $this->config['security']['first_click_redirect'])
	{
	    header('Location: '.$this->config['security']['first_click_redirect']);
	    die();
	}
	
	$_ruri=explode('?',$_SERVER['REQUEST_URI']);
	$request_uri_no_get=$_ruri[0];
	$this->redirect($request_uri_no_get);

    }
    
    
    public function people_reminder($people)
    {
	$users=new userModel();
	$server=new serverModel();
	$ftp=new ftpModel();
	$this->config2array();
	$global=$this->getConfig('global');
	
	$_SERVER['HTTP_HOST']=$global['http_host'];
	$this->root=$global['http_root'];
	
	foreach ($people AS $person) {
	    
	    $data=$person;
	    if (!$data['email']) continue;
	    $data['him']=$data['email'];
	    
	    
	    $days=floor((time()-$data['nlicense_agreement_date'])/(24*3600));
	    $data['days']=$days;
	    
	    $data['kameleon']=$this->kameleon;
	    $data['tokens']=$this->tokens;
	    
	    unset($data['access_token']);
	    
	    $servers=$server->getForUser($data['username']);
	    
	    if (count($servers)>1) continue;
	    
	    
	    if (count($servers)==0 && $data['nd_last_expire']< $this->now - 14*24*3600 )
	    {
		$users->get($data['username']);
		$users->nd_last_expire = null;
		$users->save();
	    }
	    
	    
	    $firstname=$data['fullname'];
	    if ($pos=strpos($firstname,' ')) $firstname=trim(substr($firstname,0,$pos));
	    $data['firstname'] = $firstname;
	    
	    $data['pl_she'] = strtolower(substr($firstname,-1)) == 'a';
	    
	    $login_time = $users->login_time($data['username']);
	    if ($login_time < 300 || count($servers)==0)
	    {
		
		$data['login_time']=ceil($login_time/60);
		Observer::observe('reminder-shorttime', $data,$data['ulang']?:'en',null,$days);
	    }
	    
	    if (count($servers)==0)
	    {
		Observer::observe('reminder-nowebsite', $data,$data['ulang']?:'en',null,$days);
	    }
	    else
	    {
		$ftps=$ftp->getLast($servers[0]['id'],'',1);
		
		foreach($servers[0] AS $k=>$v) if (!isset($data[$k])) $data[$k]=$v;
		
		if (!count($ftps))
		{
		    Observer::observe('reminder-noftp', $data,$data['ulang']?:'en',null,$days);
		}
		else
		{
		    if (!$data['map_url']) Observer::observe('reminder-nomap', $data,$data['ulang']?:'en',null,$days);
		}
	    }
	     
	}
    }    
    
}

