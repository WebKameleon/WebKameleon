<?php

class ajaxController extends Controller
{


    public function module_delete ()
    {
        $edit=new editController(null,$_GET['tdsid']);
        $edit->remove();
        return array('status'=>1);
    }


    public function module_drag()
    {

        if ($this->_hasParam('kolejka') && $this->_hasParam('level') && $this->_hasParam('tdsid')) {
            $levelek = $this->_getParam('level');
            $kolejka = explode(';', $this->_getParam('kolejka'));
            list ($sidek) = explode(',', $this->_getParam('tdsid'));
            $server = Bootstrap::$main->session('server');

            $pri = 1;
            foreach ($kolejka as $kolej) {
                list ($sid) = explode(',', $kolej);

                $webtd = new webtdModel($sid);
                if ($server['id'] != $webtd->server || !$webtd->checkRight())
                    continue;

                $webtd->pri = $pri++;

                if ($sid == $sidek)
                    $webtd->level = $levelek;

                $webtd->save();
            }
        }
        return array('status' => 1);

        /*if (strlen($_GET['kolejka']) && strlen($_GET['level']) && strlen($_GET['tdsid'])) {
            $kolejka = $_GET["kolejka"];
            $levelek = $_GET["level"];
            $tdsid = explode(",", $_GET["tdsid"]);
            $sidek = $tdsid[0];

            $kolej = explode(";", substr($kolejka, 0, -1));
            $sidy = array();
            $primy = array();

            for ($i = 0; $i < sizeof($kolej); $i++) {
                $ktmp = explode(",", $kolej[$i]);
                $sidy[$i] = $ktmp[0];
                $primy[$i] = $ktmp[1];
            }
            sort($primy);

            $server = Bootstrap::$main->session('server');
            for ($i = 0; $i < sizeof($kolej); $i++) {
                $webtd = new webtdModel($sidy[$i]);
                if ($server['id'] != $webtd->server || !$webtd->checkRight()) continue;

                $webtd->pri = $primy[$i];
                if ($sidy[$i] == $sidek) $webtd->level = $levelek;
                $webtd->save();
            }
        }

        return array('status' => 1);*/
    }

    public function dropmenu_load_lang()
    {
        //{"items":[{"title":"Polski (pl)","img":"skins\/kameleon\/img\/lang\/pl.png","href":"\/googleon\/index.php?setlang=pl&page=3","onclick":"","css":""},{"title":"Angielski","img":"skins\/kameleon\/img\/lang\/en.png","href":"\/googleon\/index.php?setlang=en&page=3","onclick":"","css":""}],"status":1}
        $items = array();
        $items2 = array();
        
        $page = isset($_GET['page']) ? $_GET['page'] : 0;
        $globals = Bootstrap::$main->globals();
        $langs_used = Bootstrap::$main->session('langs_used');
        
        foreach (Bootstrap::$main->getConfig('langs') AS $lang) {
            $item = array();

            $item['html'] = '<span class="flag flag-' . $lang . '"></span>';
            if (!in_array($lang, $langs_used)) {
                $item['html'].='<b>'.$this->trans('Create').'</b> '.$this->trans($lang);
                $item['href'] = Bootstrap::$main->getRoot().'index/get/0?setLang=' . $lang.'&setreferlang='.Bootstrap::$main->session('lang');
            } else {
                
                $item['href'] = '?setLang=' . $lang;
                $item['html'].=$this->trans($lang);
            }
            
            $item['title'] = $this->trans($lang);
            

            if (!in_array($lang, $langs_used)) {
                $item['class'] = 'inactive';
            }
            
            $item['id'] = $lang;

            if (in_array($lang, $langs_used))
                $items[Tools::translate($lang)] = $item;
            else
                $items2[Tools::translate($lang)] = $item;
        }

        ksort($items);
        ksort($items2);
        
        
        $items = array_merge($items, $items2);
        
        return (array(
            'items' => $items,
            'status' => 1
        ));

    }

    public function dropmenu_load_bookmark()
    {
        $webfav = new webfavModel();

        $page = isset($_GET['page']) ? $_GET['page'] + 0 : 0;
        $globals = Bootstrap::$main->globals();

        $bookmarks = $webfav->getBookmarks($globals['server']['id'], $globals['user']['username'], $globals['lang']);

        $items = array();

        $met = false;
        if (is_array($bookmarks)) {
            foreach ($bookmarks AS $bookmark) {
                $item = array();

                $item['title'] = $item['html'] = $this->shorten_title($bookmark['title_short'], $bookmark['title']);
                $item['href'] = $globals['root'] . 'index/get/' . $bookmark['id'];

                if ($bookmark['id'] == $page)
                    $met = true;

                $items[] = $item;
            }
        }

        $tmp = array();
        $tmp['title'] = $tmp['html'] = $this->trans($met ? 'Remove from bookmarks' : 'Add to bookmarks');
        $tmp['onclick'] = 'km_bookmark(' . $page . ')';
        $tmp['class'] = 'km_bookmark_' . ($met ? 'delete' : 'add');

        $items[] = $tmp;

        return (array(
            'items' => $items,
            'status' => 1
        ));
    }

    public function module_add()
    {

        $type_id = isset($_GET['type_id']) ? $_GET['type_id'] + 0 : 0;
        $page_id = isset($_GET['page_id']) ? $_GET['page_id'] + 0 : 0;

        $webtd = new webtdModel();

        $error=null;
        if ($webtd->checkRight($page_id)) {
            $td=$webtd->add($page_id, $type_id);
            $status = $td ? 1 : 0;
            if (!$status)
            {
                $error=Bootstrap::$main->tokens->error();
            }
            else
            {
                Bootstrap::$main->session('new_sid',$td['sid']);
            }
        } else {
            $status = 0;
            $td=null;
        }

        return array('status' => $status,'td'=>$td, 'error'=>$error);
    }
    
    
    public function bookmark()
    {
        $page = isset($_GET['page']) ? $_GET['page'] + 0 : 0;
        $webfav = new webfavModel();

        $globals = Bootstrap::$main->globals();

        $webfav->addRemove($page, $globals['server']['id'], $globals['user']['username'], $globals['lang']);

        return array('status' => 1);
    }

    public function dropmenu_load_server()
    {
        $servers = Bootstrap::$main->session('servers');

        $items = array();
        $root = Bootstrap::$main->getRoot();
        foreach ($servers AS $server) {
            $item = array();

            $item['title'] = $item['html'] = $server['nazwa_long']?:$server['nazwa'];
            $item['href'] = $root . 'index/get?setServer=' . trim($server['nazwa']) . '&_ts=' . Bootstrap::$main->now;
            $item['css'] = '';
            $item['onclick'] = '';
            $item['img'] = '';

            $path = $server['paths']['ufiles_path'] . '/.root/favicon.ico';

            if (file_exists($path)) {
                $item['img'] = 'data:image/ico;base64,' . base64_encode(file_get_contents($path));
            }

            $items[] = $item;
        }

        sort($items);

        return (array(
            'items' => $items,
            'status' => 1
        ));

        //{"items":[{"title":"","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=","onclick":"","css":""},{"title":"ala","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=ala","onclick":"","css":""},{"title":"alf1","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=alf1","onclick":"","css":""},{"title":"alf2","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=alf2","onclick":"","css":""},{"title":"eccotravel","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=eccotravel","onclick":"","css":""},{"title":"fajne_dupcie","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=fajne_dupcie","onclick":"","css":""},{"title":"kredka","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=kredka","onclick":"","css":""},{"title":"oknonet","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=oknonet","onclick":"","css":""},{"title":"sklep_fakro","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=sklep_fakro","onclick":"","css":""},{"title":"tab_01","img":"root\/favicon.ico","href":"\/googleon\/index.php?_ts=1373286488&SetServer=tab_01","onclick":"","css":""}],"status":1}
    }

    public function page_sitemap_visible()
    {
        $webpage = new webpageModel($_GET['pagesid']);
        if (!$webpage->checkRight()) {
            return (array(
                'status' => 0,
                'nositemap' => $webpage->nositemap
            ));
        }

        $webpage->nositemap = $webpage->nositemap ? 0 : 1;
        $webpage->save();

        return (array(
            'status' => 1,
            'nositemap' => $webpage->nositemap
        ));
    }

    public function page_visible()
    {
        $webpage = new webpageModel($_GET['pagesid']);
        if (!$webpage->checkRight()) {
            return (array(
                'status' => 0,
                'hidden' => $webpage->nositemap
            ));
        }

        $webpage->hidden = $webpage->hidden ? 0 : 1;
        $webpage->save();
        
        if ($webpage->hidden && $webpage->file_name)
        {
            $webpagetrash=new webpagetrashModel();
            $webpagetrash->server=$webpage->server;
            $webpagetrash->page_id=$webpage->id;
            $webpagetrash->ver=$webpage->ver;
            $webpagetrash->lang=$webpage->lang;
            $webpagetrash->nd_issue=time();
            $webpagetrash->file_name=$webpage->file_name;
            $webpagetrash->status='N';
            $webpagetrash->save();
          
            
        }

        return (array(
            'status' => 1,
            'hidden' => $webpage->hidden
        ));
    }

    public function module_visible()
    {
        $webtd = new webtdModel($_GET['tdsid']);
        if (!$webtd->checkRight()) {
            return (array(
                'status' => 0,
                'hidden' => $webtd->nositemap
            ));
        }

        $webtd->hidden = $webtd->hidden ? 0 : 1;
        $webtd->save();

        return (array(
            'status' => 1,
            'hidden' => $webtd->hidden
        ));
    }

    public function clipboard()
    {
        
        $items=Bootstrap::$main->session('clipboard');
        $items2=array();
        
        if (is_array($items)) foreach ($items AS $what=>$item)
        {
            foreach($item AS $id=>$v)
            {
                if (!is_array($v)) $v=array(0,$v);
                
                $items2[$v[0].'-'.$what.'-'.$id] = array($what,$id,$v[1]);
            }
        }
        
        krsort($items2);
        
        
        $items=array();
        
        foreach($items2 AS $i)
        {
            if ($i[0]=='mask') $items[$i[0]][$i[1]]=$i[2];
            else $items[$i[0]][]=array('k'=>$i[1],'v'=>$i[2]);
        }
             
        
        return array(
            'items' => $items,
            'title' => $this->trans('Cliboard'),
            'close' => $this->trans('Close'),
            'header' => $this->trans('Header'),
            'footer' => $this->trans('Footer'),
            'page' => $this->trans('Page'),
            'td' => $this->trans('Module'),
            'page_id' => isset($_GET['page']) ? $_GET['page'] + 0 : 0,
            'nothing' => $this->trans('Nothing found in kameleon clipboard'),
        );

    }

    protected function shorten_title($title1, $title2 = null)
    {
        $limit = 30;

        if (trim($title1))
	{
	    $title2shorten=trim($title1);
	} else {
	    $title2shorten=trim($title2);
	}

	if (strlen($title2shorten)<=$limit) return $title2shorten;
	
	while (strlen($title2shorten)>$limit)
	{
	    $a=explode(' ',$title2shorten);
	    if (count($a)==1) return substr($title2shorten,0,$limit);
	    unset($a[count($a)-1]);
	    $title2shorten=implode(' ',$a);
	}
	return $title2shorten.'...';
        
    }

    public function copy()
    {
        $what = $_GET['what'];
        $id = $_GET['id'];

        switch ($what) {
            case 'page':
                $webpage = new webpageModel($id + 0);
                if (!$webpage->checkRight()) return array('status' => 0);
                $txt = $this->shorten_title($webpage->title_short, $webpage->title);
                $txt .= " [" . $webpage->id . "]";
                $info = $this->trans('Page was copied to kameleon clipboard');
                break;

            case 'mask':
                $webtd = new webtdModel();
                $td = $webtd->find_one_by_uniqueid($id);
                $webtd->load($td);
                if (!$webtd->checkRight()) return array('status' => 0);
                $txt = $this->shorten_title($webtd->title) . " [" . $webtd->page_id . "]";
                $info = $this->trans('Mask was copied to kameleon clipboard');
                break;

            case 'td':
                $webtd = new webtdModel($id + 0);
                if (!$webtd->checkRight()) return array('status' => 0);
                $txt = $this->shorten_title($webtd->title,$webtd->widget);
                if ($webtd->page_id>=0) $txt.=" [" . $webtd->page_id . "]";
                else {
                    $server=Bootstrap::$main->session('server');
                    if ($server['header']==$webtd->page_id%100) $txt.=' ['.Tools::translate('Header').']';
                    if ($server['footer']==$webtd->page_id%100) $txt.=' ['.Tools::translate('Footer').']';
                }
                $info = $this->trans('Module was copied to kameleon clipboard');
                break;

            default:
                return array('status' => 0);
        }

        $clipboard = Bootstrap::$main->session('clipboard');
        if (!is_array($clipboard)) $clipboard = array();
        $clipboard[$what][$id] = array(time(),$txt);
        Bootstrap::$main->session('clipboard', $clipboard);

        return array(
            'status' => 1,
            'info' => $info,
	    'txt' => $txt
        );
    }

    public function share_load()
    {
        $owner  = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');

        $keys  = array_flip(array('email', 'fullname', 'username', 'is_owner', 'can_ftp', 'can_edit', 'can_view'));
        $users = array();

        $modelServer = new serverModel;

        foreach ($modelServer->getUsers($server['id']) as $user) {
            $users[] = array_intersect_key($user, $keys);
        }
        return array(
            'status' => 1,
            'owner' => $owner['username'],
            'users' => $users
        );
    }

    public function share_role()
    {
        $owner  = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');
        $roles  = array('is_owner', 'can_ftp', 'can_edit', 'can_view');

        if (($username = $this->_getParam('username')) && $username != $owner['username'] && ($role = $this->_getParam('role')) && in_array($role, $roles)) {
            $modelServer = new serverModel;
            $modelServer->changeUser($username, $role == 'is_owner', $role == 'can_edit', $role == 'can_ftp', $server['server']);
        }
        return array(
            'status' => 1
        );
    }

    public function share_autocomplete()
    {
        $me = Bootstrap::$main->session('user');

        $users = array();
        if ($term  = $this->_getParam('term')) {
            $modelUser = new userModel;
            foreach ($modelUser->friends($me['username'], $term) as $user) {
                $users[] = array(
                    'value' => $user['username'],
                    'label' => $user['fullname'] . ' (' . $user['email'] . ')'
                );
            }
        }
        return array(
            'status' => 1,
            'users' => $users
        );
    }

    public function share_add()
    {
        $server      = Bootstrap::$main->session('server');
        $modelUser   = new userModel;
        $modelServer = new serverModel;

        if ($term = $this->_getParam('term')) {
            foreach (preg_split('/[\s,;:]+/', $term) as $t) {
                if ($email = filter_var(strtolower(trim($t)), FILTER_VALIDATE_EMAIL)) {
                    $user = $modelUser->find_one_by_email($email);
                    if ($user == null)
                        $user = $modelUser->addUser($email, "");

                    if ($user) {
                        $modelServer->addUser($user['username'], 0, 1, 0, $server['id']);

                        $me = Bootstrap::$main->session('user');
                        Observer::observe('share_server', array(
                            'me' => $me['email'],
                            'him' => $email,
                            'server' => $server
                        ));
                    }
                }
            }

        }

        if (($username = $this->_getParam('username')) && $modelUser->find_one($username)) {
            $modelServer->addUser($username, 0, 1, 0, $server['server']);
        }

        return array(
            'status' => 1
        );
    }

    public function share_delete()
    {
        $owner  = Bootstrap::$main->session('user');
        $server = Bootstrap::$main->session('server');

        if (($username = $this->_getParam('username')) && $username != $owner['username']) {

            $modelServer = new serverModel;
            $modelServer->deleteUser($username, $server['server']);
        }
        return array(
            'status' => 1
        );
    }

    public function tree_add_page()
    {
        $controller = new indexController;
        $controller->add();
        return array(
            'status' => 1
        );
    }

    public function tree_remove_page()
    {
        $referer = $this->_getParam('referer');
        $controller = new indexController;
        $controller->remove($referer, $this->_getParam('whole_tree'));

        
        if (!$referer && $this->_getParam('whole_tree'))
        {
            $server=Bootstrap::$main->session('server');
            $lang=Bootstrap::$main->session('lang');
            
            $redirect='';
            
            if ($server['lang']!=$lang)
            {
                $redirect=Bootstrap::$main->getRoot().'index/get/0?setLang='.$server['lang'];
            }
            else
            {
                $serverModel=new serverModel($server['id']);
                $serverModel->trash();
                $redirect=Bootstrap::$main->getRoot().'wizard';
            }
        }  
        
        
        return array(
            'status' => 1,
            'redirect'=>$redirect
        );
    }

    public function tree_rename_page()
    {
        $referer = $this->_getParam('referer');
        $page = new webpageModel;
        $page->getOne($referer);
        $page->title = $this->_getParam('title');
        $page->save();
        
        $index=new indexController();
        $index->filename($page->data(), false);
        
        return array(
            'status' => 1
        );
    }

    public function tree_change_visibility()
    {
        $referer = $this->_getParam('referer');
        $page = new webpageModel;
        $page->changeProperty($referer, 'hidden', null, !!$this->_getParam('whole_tree'));
        return array(
            'status' => 1
        );
    }

    public function tree_change_nositemap()
    {
        $referer = $this->_getParam('referer');
        $page = new webpageModel;
        $page->changeProperty($referer, 'nositemap', null, !!$this->_getParam('whole_tree'));
        return array(
            'status' => 1
        );
    }

    public function setup_properties()
    {
        $server = Bootstrap::$main->session('server');

        $szablon = trim($server['szablon']);
        
        $template_media_path = Bootstrap::$main->session('template_media_path');
        
        $forbidden=$szablon[0]=='.';
        
        return array(
            'status' => 1,
            'template' => $szablon,
            'templates_list' => $template_media_path||$forbidden?array():Tools::list_templates(
                realpath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $szablon . DIRECTORY_SEPARATOR . '..')
            ),
            'logo' => isset($server['logo']) ? $server['logo'] : '',
            'background' => isset($server['background']) ? $server['background'] : '',
            'bgcolor' => isset($server['bgcolor']) ? $server['bgcolor'] : '',
            'lang' => $server['lang'],
            'http_url' => $server['http_url'],
            'analytics' => $server['analytics'],
            'mourning' => isset($server['mourning']) ? $server['mourning'] : 0,
        );
    }

    public function setup_list_analytics()
    {
        $analytics = array();
        $keys = array_flip(array('id', 'name', 'websiteUrl'));
        $service = Google::getAnalyticsService();

        $ret=1;
        
        try {
            $accounts = $service->management_accounts->listManagementAccounts();
            foreach ($accounts['items'] as $account) {
                $webproperties = $service->management_webproperties->listManagementWebproperties($account['id']);
                
                foreach ($webproperties->getitems() as $webproperty) {
                    
                    $analytics[] = array_intersect_key((array)$webproperty, $keys);
                }
            }
        } catch (Google_ServiceException $e) {
            
            $user=new userModel();
            $user->getCurrent();
            $ret=$user->hasAccess('analytics')+0;
        }

        return array(
            'status' => $ret,
            'analytics_list' => $analytics
        );
    }

    public function wizard_list_templates()
    {
        $ret = Tools::get_templates();
        $ret['status'] = 1;
        return $ret;
    }

    /**
     * @param string $name
     * @param string $template
     * @param string $URL
     * @param array $data
     * @return array
     */
    public function wizard_create($name = null, $template = null, $URL = null, $data=null)
    {
        
        $user=new userModel();
        $user->getCurrent();
        
        
        if ($name == null)
            $name = $this->_getParam('name');

        if ($template == null)
            $template = $this->_getParam('template');

        if ($URL == null)
            $URL = $this->_getParam('url');


        if ($template) $this->template_activity($template,'N');

        $error = '';

        $src_server=null;
       
        if ($URL) {
            // URL
            if (filter_var($URL, FILTER_VALIDATE_URL) === false) {
                $error = 'Invalid URL';
            } else {
                file_put_contents($filename = Tools::get_tmp_filename(), file_get_contents($URL));
            }
        } else if ($template) {
            if (strpos($template, 'local:') === 0) {
                // lokalny plik *.wkz
                $filename = realpath(APPLICATION_PATH . '/../files/' . substr($template, 6));
            
            } else if (strpos($template, 'drive:') === 0) {
                // z google drive
                ini_set('memory_limit', '2048M');
                $filename = Tools::get_tmp_filename();
                file_put_contents($filename, Google::getContent(substr($template, 6)));
                
            } else if ($template=='.default') {
                // ok - default
                $filename = realpath(APPLICATION_PATH . '/templates/.default/.template.wkz');
            
            } else if (file_exists($tmp = realpath(APPLICATION_PATH . '/templates/' . $template . '/.template.wkz'))) {
                $filename = $tmp;
                
            } else if (strpos($template, 'media/')===0 && file_exists(MEDIA_PATH.'/'.substr($template,6))) {
                $serverModel = new serverModel();
                $src_server=$serverModel->find_one_by_nazwa(substr($template,6));
                
                if (is_array($src_server) && !$src_server['social_template'])
                {
                    $userServers=$user->servers();
                        
                    foreach($userServers AS $s1)
                    {
                        if ($s1['id'] == $src_server['id'])
                        {
                            $src_server['social_template'] = $src_server['nazwa'];
                            break;
                        }
                    }
                }
                
                if (!$src_server || !is_array($src_server) || !$src_server['social_template'] )
                {
                    $src_server=null;
                }
                
                
            } else {
                $error = 'Invalid template';  
            } 
        } else {
            $error = 'Please choose template';
        }

        //musi być nowy, bo wcześniej się już dane poustawiały
        $serverModel = new serverModel();

        if (!$error) {
            
            $server = $serverModel->add($name,$src_server);
            $wizard = new wizardController();
            
            if (!$server) {
                $error = Bootstrap::$main->tokens->error();
            } else if (isset($filename)) {                
                $auth = new authController;
                $auth->getServers(true,$server->id); 
                
                $s=Bootstrap::$main->session('server');
                
                
                $result = $wizard->import($server->id, $filename);
                if (!$result) {
                    $error = Bootstrap::$main->tokens->error();
                    $serverModel->remove($server->id);
                }          
            } else if (!is_null($src_server) || !is_null($data) ) {
                
                               
                $server->social_template_price_en = $src_server['social_template_price_en'];
                $server->social_template_price_pl = $src_server['social_template_price_pl'];
                $server->from_social_template = $src_server['nazwa'];
                $server->save();
                
                $auth = new authController();
                $auth->getServers(true,$server->id);
                
                $webpage = new webpageModel();
                $webpage->server = $src_server['id'];
                $webpage->import($server->id, $data['webpage']?:$webpage->export());                
                
                $weblink = new weblinkModel();
                $weblink->server = $src_server['id'];
                $weblink->import($server->id, $data['weblink']?:$weblink->export());
                
                $webtd = new webtdModel();
                $webtd->server = $src_server['id'];
                $webtd->import($server->id, $data['webtd']?:$webtd->export());
                
                $wizard->setPrimaryLang($server);
                
            } else {
                $server->szablon = $template;
                $server->save();
            }


        }

        return array(
            'status' => $error ? 0 : 1,
            'error'  => $error ? strip_tags($this->trans($error)) : null,
            'data'   => is_object($server)?$server->data():array(),
        );
    }

    /**
     * @param int $id
     * @param string $name
     * @return array
     */
    public function wizard_rename($id = null, $name = null)
    {
        if ($id == null)
            $id = $this->id;

        if ($name == null)
            $name = $this->_getParam('name');

        $status = 0;
        if ($id && trim($name)) {
            $server = new serverModel($id);

            $server->nazwa_long = str_replace(array('<','>'),array('',''),trim($name));
            $server->save();

            $auth = new authController();
            $auth->getServers(true);

            $status = 1;
        }
        return array(
            'status' => $status
        );
    }

    /**
     * @param int $id
     * @return array
     */
    public function wizard_trash($id = null, $servers = null)
    {
        $status = 0;

        if ($id == null)
            $id = $this->id;

        if ($id) {

            if ($servers == null)
                $servers = Bootstrap::$main->session('servers');

            foreach ($servers AS $server) {
                if ($server['id'] == $id && $server['owner']) {
                    $serverModel = new serverModel;
                    $serverModel->trash($id);

                    $auth = new authController();
                    $auth->getServers(true);

                    $status = 1;

                    break;
                }
            }

        }

        return array(
            'status' => $status
        );
    }

    /**
     * @param int $id
     * @return array
     */
    public function wizard_untrash($id = null)
    {
        return $this->wizard_trash($id, Bootstrap::$main->session('trash'));
    }

    /**
     * @param int $id
     * @return array
     */
    public function wizard_remove($id = null)
    {
        if ($id == null)
            $id = $this->id;

        $status = 0;

        if ($id) {
            $servers = Bootstrap::$main->session('trash');
            
            foreach ($servers AS $server) {
                if (abs($server['id']) == abs($id) && $server['owner']) {
                    $serverModel = new serverModel;
                    $status = $serverModel->remove($id);
                    if ($status) {
                        $auth = new authController();
                        $auth->getServers(true);

                        $id = abs($id);
                        
                        $server['nazwa']=trim($server['nazwa']);
			if ($server['nazwa']) Tools::rm_r(MEDIA_PATH . '/'.$server['nazwa']);

                        if ($id) Tools::rm_r(MEDIA_PATH . '/uimages/' . $id);
                        if ($id) Tools::rm_r(MEDIA_PATH . '/ufiles/' . $id . '-att');
                        if ($server['nazwa']) Tools::rm_r(MEDIA_PATH . '/uincludes/' . $server['nazwa']);
                    }
                    break;
                }
            }
        }

        return array(
            'is_empty' => count(Bootstrap::$main->session('trash')) == 0,
            'status' => $status
        );
    }

    public function wizard_unshare($id = null)
    {
        if ($id == null)
            $id = $this->id;

        $status = 0;
        if ($id) {

            $me      = Bootstrap::$main->session('user');
            $servers = Bootstrap::$main->session('servers');

            foreach ($servers AS $server) {
                if ($server['server'] == $id && !$server['owner']) {
                    $serverModel = new serverModel;
                    $serverModel->deleteUser($me['username'], $id);

                    $auth = new authController();
                    $auth->getServers(true);

                    $status = 1;

                    break;
                }
            }
        }

        return array(
            'status' => $status
        );
    }

    public function menu_change_name()
    {
        $weblink = new weblinkModel;

        $weblink->menu_change_name($this->id, $this->_getParam('name'));
        return array(
            'status' =>  1
        );
    }

    public function link_change_property()
    {
        $status = 0;
        $weblink = new weblinkModel($this->id);

        if (($property = $this->_getParam('prop')) && $property != $weblink->getKey()) {
            $weblink->$property = $this->_getParam('val');
            
            $status = $weblink->save() ? 1 : 0;

        }

        return array(
            'status' => $status
        );
    }

    public function link_change_visibility()
    {
        $weblink = new weblinkModel($this->id);
        $weblink->hidden = $weblink->hidden ? 0 : 1;
        $res=$weblink->save();

        return array(
            'status' => $res?1:0,
            'hidden' => $weblink->hidden,
            'title' => $this->trans($weblink->hidden ? 'Link hidden' : 'Link visible')
        );
    }

    public function link_remove()
    {
        $weblink = new weblinkModel;
        $res=$weblink->remove($this->id);

        return array(
            'status' => $res?1:0
        );
    }

    public function menu_reorder_links()
    {
        $status = 0;
        if ($this->_hasParam('order')) {
            $pri = 1;
            
            $status = 1;
            foreach (explode(':', $this->_getParam('order')) as $sid) {
                $link = new weblinkModel($sid);
                $link->pri = $pri++;
                if (!$link->save()) $status=0;
            }
            
        }

        return array(
            'status' => $status
        );
    }

    public function widget_option()
    {
        $webtd = new webtdModel($this->id);
        $status = 0;
        if ($webtd->has_widget()
        &&  $this->_hasParam('widget')
        &&  $this->_getParam('widget') == $webtd->widget
        &&  $this->_hasParam('option')
        &&  $this->_hasParam('value')) {
            
            $option = $this->_getParam('option');
            $widgetData = unserialize(base64_decode($webtd->widget_data));
            $widgetData[$option] = $this->_getParam('value');
            $webtd->widget_data = base64_encode(serialize($widgetData));
            if ($webtd->save())
            {
                $widget = $webtd->get_widget();
                $widget->update();
                $status = 1;
            }
        }

        return array(
            'status' => $status
        );
    }

    /**
     * @return Google_YouTubeService
     */
    protected function getYouTubeService()
    {
        static $youtubeService;
        if ($youtubeService == null) {
            $youtubeService = Google::getYouTubeService();
        }
        return $youtubeService;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function youtube_list_channels(array $filters = null)
    {
        $channels = array();
        $service = $this->getYouTubeService();

        if (empty($filters)) {
            $filters = array();

            if ($this->id) {
                $filters = array('id' => $this->id);
            } else {
                $filters = array('mine' => true);
            }

            $filters['maxResults'] = self::YOUTUBE_CHANNELS_MAX_RESULTS;
        }

        $listChannels = $service->channels->listChannels('snippet,contentDetails', $filters);

        
        
        foreach ($listChannels->getItems() as $channel) {
            
            $channels[] = array(
                'id' => $channel->getId(),
                'title' => $channel->getSnippet()->getTitle(),
                'description' => $channel->getSnippet()->getDescription(),
                'tmb' => $channel->getSnippet()->getThumbnails()->getDefault()->getUrl(),
                'related_playlists' => $channel->getContentDetails()->getRelatedPlaylists()
            );
        }

        return array(
            'status' => 1,
            'channels' => $channels
        );
    }

    /**
     * @param array $filters
     * @return array
     */
    public function youtube_list_playlists(array $filters = null)
    {
        $playlists = array();
        $service = $this->getYouTubeService();

        if (empty($filters)) {
            $filters = array();

            if ($this->id) {
                $filters = array('id' => $this->id);
            } else if ($this->_hasParam('channel')) {
                $filters = array('channelId' => $this->_getParam('channel'));
            } else {
                $filters = array('mine' => true);
            }

            $filters['maxResults'] = self::YOUTUBE_PLAYLISTS_MAX_RESULTS;
        }

        $listPlaylists = $service->playlists->listPlaylists('snippet,contentDetails', $filters);

        foreach ($listPlaylists['items'] as $playlist) {
            $playlists[] = array(
                'id' => $playlist['id'],
                'playlist_id' => $playlist['id'],
                'channel_id' => $playlist['snippet']['channelId'],
                'channel_title' => $playlist['snippet']['channelTitle'],
                'title' => $playlist['snippet']['title'],
                'description' => $playlist['snippet']['description'],
                'tmb' => $playlist['snippet']['thumbnails']['default']['url']
            );
        }

        return array(
            'status' => 1,
            'playlists' => $playlists
        );
    }

    /**
     * @param $video
     * @return array
     */
    protected function youtube_video_data( $video)
    {
        $snippet=$video->getSnippet();
        
        if ($video->getKind() == 'youtube#playlistItem') {
            $video_id = $snippet->getResourceId()->getVideoId();
        } else {
            $video_id = $video->getId();
        }

        $tmb=$snippet->getThumbnails();
        
        return array(
            'id' => $video_id,
            'title' => $snippet->getTitle(),
            'description' => $snippet->getDescription(),
            'tmb' => is_object($tmb)? $tmb->getDefault()->getUrl():''
        );
    }

    const YOUTUBE_VIDEO_MAX_RESULTS     = 30;
    const YOUTUBE_PLAYLISTS_MAX_RESULTS = 10;
    const YOUTUBE_CHANNELS_MAX_RESULTS  = 10;

    /**
     * @param $playlist_id
     * @return array
     */
    protected function youtube_playlist_items($playlist_id)
    {
        $service = $this->getYouTubeService();
        $videos = array();
        $listPlaylistItems = $service->playlistItems->listPlaylistItems('snippet', array('playlistId' => $playlist_id, 'maxResults' => self::YOUTUBE_VIDEO_MAX_RESULTS));

        
        foreach ($listPlaylistItems->getItems() as $video) {
            $videos[] = $this->youtube_video_data($video);
        }

        return $videos;
    }

    public function youtube_list_videos()
    {
        $videos = array();
        $service = $this->getYouTubeService();

        if ($this->_hasParam('mine')) {
            $channels_list = $this->youtube_list_channels(array('mine' => true));
        } else if ($this->_hasParam('channel')) {
            $channels_list = $this->youtube_list_channels(array('id' => $this->_getParam('channel')));
        } else if ($this->_hasParam('playlist')) {
            $videos = $this->youtube_playlist_items($this->_getParam('playlist'));
        } else {
            $listVideos = $service->videos->listVideos('snippet', array('chart' => 'mostPopular', 'maxResults' => self::YOUTUBE_VIDEO_MAX_RESULTS));

           
            foreach ($listVideos->getItems() as $video) {
                
                $videos[] = $this->youtube_video_data($video);
            }
        }

        if (isset($channels_list)) {
                    
            foreach ($channels_list['channels'] as $channel) {
                $videos = array_merge($videos, $this->youtube_playlist_items($channel['related_playlists']->uploads));
            }
        }
//        die('<pre>' . print_r($videos, 1) . PHP_EOL);
        return array(
            'status' => 1,
            'videos' => array_slice($videos, 0, self::YOUTUBE_VIDEO_MAX_RESULTS)
        );
    }

    public function gdrive_files($query = null)
    {
        if ($query == null) $query = $this->_getParam('q');

        $drive = Google::getDriveService();

        $list = $drive->files->listFiles(array(
            'q' => $query
        ));

        return array(
            'status' => 1,
            'items' => $list['items']
        );
    }

    /**
     * @param array $data
     * @param $output
     */
    public function finish($data, $output)
    {
        Header('Content-type: application/json');

        if ($output && is_array($data) && !isset($data['output'])) $data['output'] = $output;

        die(json_encode($data));
    }

    /**
     * @param string $name
     * @param string $value
     * @return array
     */
    public function set_cookie($name = null, $value = null)
    {
        if ($name == null)
            $name = $this->_getParam('name');

        if ($value == null)
            $value = $this->_getParam('value');

        $cookie = Bootstrap::$main->session('cookie');
        if ($cookie == null)
            $cookie = array();

        $cookie[$name] = $value;
        Bootstrap::$main->session('cookie', $cookie);

        return array(
            'status' => 1
        );
    }

    /**
     * @param string $name
     * @return array
     */
    public function get_cookie($name = null)
    {
        if ($name == null)
            $name = $this->_getParam('name');

        $cookie = Bootstrap::$main->session('cookie');

        return array(
            'status' => 1,
            'value' => @$cookie[$name] ? : null
        );
    }

    public function translate()
    {
        $webpage=null;
        foreach($_POST AS $k=>$v)
        {
            $a=explode(',',$k);
            
            $classname='web'.$a[0].'Model';
            $obj=new $classname($a[1]);
            $atr=$a[2];
            if ($atr=='plain') {
                $v=editController::replace_plain($v);
            }
            
            $v=str_replace(array('<font>','</font>'),'',$v);
            
            $obj->$atr = $v;
            $obj->olang=Bootstrap::$main->session('lang');
            $obj->save();
            
            if (is_null($webpage) && $a[0]=='page') $webpage=$obj;
        }
        
        if (!is_null($webpage))
        {
            $index=new indexController();
            $index->filename($webpage->data(), false);            
        }
        
        $webpage=new webpageModel();
        
        $next=$webpage->next_to_translate($this->id);
        if (!$next)
        {
            $wp=$webpage->getOne($this->id);
            if (strlen($wp['prev']) && $wp['prev']>=0) $next=$webpage->next_to_translate($wp['prev']);
        }
        
        if (!$next) $next=0+$webpage->next_to_translate();
        
        
        return array(
            'status'=>1,
            'next'=>$next
        );
    }    
    
    
    public function webtd()
    {
        $webtd=new webtdModel($this->id);
        return $webtd->data();
    }
    
    
    public function time_delta()
    {
        if (isset($_GET['d']))
        {
            $d=explode(':',$_GET['d']);
            if (count($d)>1) $d[count($d)-1]=substr(end($d),0,2);
            $d=implode(':',$d);
            $t=strtotime($d);
            
            $time_delta = $t-Bootstrap::$main->now;
            if ($time_delta==0) $time_delta=1;
            Bootstrap::$main->session('time_delta',$time_delta);
        }
    }
    
    
    public function appengie_regex_test()
    {
        $_only_functions=true;
        include_once (__DIR__.'/../../library/GoogleAppEngine/_app.php');
        
        $self_long=$_POST['url'];
        $self=preg_replace('~^http://~','',$self_long);
        $pos=strpos($self,'/');
        if ($pos) $self=substr($self,$pos+1);
        else $self='/';
        if (!$self) $self='/';
        
        $result=array('start'=>$self_long);
        
        $n=0;
        while(true)
        {
            if ($n==10) break; //zabezpieczenie
            
            $n++;
            $r=___regex($self_long,$self,$_POST['regex']);
            
            if ($r['redirect'])
            {
                $result['redirect'.$n]=$r['redirect'];
                $self_long=$r['redirect'];
                
                $self=preg_replace('~^http://~','',$self_long);
                $pos=strpos($self,'/');
                if ($pos) $self=substr($self,$pos+1);
                else $self='/';
                if (!$self) $self='/';
                
                continue;
            }
            
            if ($r['self']!=$self) $result['change']=$r['self'];
            
            if (count($r['get'])) $result['_GET']=$r['get'];
            
            break;
        }
        
        
        return $result;
    }
    
    
    public function tip_done()
    {
        $tips=Bootstrap::$main->session('tips');
        
        
        if (strlen($tips))
        {
            switch ($this->id)
            {
                case 0:
                    Bootstrap::$main->settips('');
                    break;
                
                case 4:
                    $tips=explode(',',$tips);
                    $k=array_search(1,$tips);
                    if (strlen($k)) unset($tips[$k]);
                    $tips=implode(',',$tips);
                    Bootstrap::$main->session('tips',$tips);                    
                    break;
                
                case 6:
                    $tips=explode(',',$tips);
                    $k=array_search($this->id,$tips);
                    if (strlen($k)) unset($tips[$k]);
                    $tips=implode(',',$tips);
                    Bootstrap::$main->session('tips',$tips);                    
                    break;

                case 8:
                    Bootstrap::$main->settips('0,10000000000');
                    break;
                
            }
            
            
        }
        
        return array('tips'=>$tips);
    }
    
    
    public function template_activity($media=null,$type='R')
    {
        if (is_null($media)) $media=$this->id;
        if (substr($media,0,7)=='base64:') $media=base64_decode(substr($media,7));
        if (substr($media,0,6)=='media/') $media=substr($media,6);
        
        $server=new serverModel();
        $s=$server->find_one_by_nazwa($media);
        
        if (!$s['id']) return;
        
        if (isset($_GET['type'])) $type=$_GET['type'];
             
        Tools::activity('server',$s['id'],$type);
        
    }
    
    public function newsletter_nop()
    {
        return array('status'=>'pending');
    }
    
    public function newsletter()
    {
        
        $res=array();
        
        session_write_close();
        
        ini_set('display_errors',false);
        ini_set('max_execution_time',0);
        header('Location: '.Bootstrap::$main->getRoot().'ajax/newsletter_nop');
        flush();
        ob_end_flush();
        
        $user=Bootstrap::$main->session('user');
        $observersent = new observersentModel();
        
        while(true)
        {
            
            if ($this->id + 0 != $this->id || !$this->id)
            {
                $res['status']=false;
                break;
            }
            
            $webtd=new webtdModel($this->id);
            $server=Bootstrap::$main->session('server');
            if ($server['id']!=$webtd->server)
            {
                $res['status']=false;
                break;
            }
            
            
            $msg=$webtd->plain;
            $data=unserialize(base64_decode($webtd->widget_data));
            
            $plain=$msg;    
            while ($pos=strpos($plain,UIMAGES_TOKEN))
            {
                $plain=substr($plain,$pos+strlen(UIMAGES_TOKEN)+1);
                
                $pos_1=strpos($plain,'"');
                $pos_2=strpos($plain,"'");
                
                if ($pos_1 && $pos_2) $pos=min($pos_1,$pos_2);
                elseif ($pos_1) $pos=$pos_1;
                else $pos=$pos_2;
                
                
                $img=substr($plain,0,$pos);
                
                if (isset($data['imgs'][$img]['mediaUrl']))
                {
                    $msg=str_replace(UIMAGES_TOKEN.'/'.$img,$data['imgs'][$img]['mediaUrl'],$msg);
                }
            
            }
            
            
            if (isset($data['a']) && count($data['a']))
            {
                $webpage=new webpageModel();
                $webpage->getOne(0);
                $webpage->setMain();
                $server=Bootstrap::$main->session('server');
                $url=$server['http_url'];
                if (substr($url,-1)!='/') $url.='/';
                
                $client=Google::getUserClient(null,false,'newsletter');
                $service = Google::getUrlshortenerService($client);
                
                $sent=0;
                
                foreach($data['a'] AS $href=>$goo)
                {
                    $href2=$href;   
                    if (!$goo)
                    {

                        if (substr($href2,0,13)=='kameleon:link')
                        {
                            $href2=substr($href2,14,strlen($href2)-15);
                            $href2=explode(',',$href2);
                            if (!isset($href2[1])) $href2[1]='';
                            
                            $href2=$url.Bootstrap::$main->kameleon->href('',$href2[1],$href2[0],'',PAGE_MODE_PURE);
                        }
                    
                        if (!strstr($href2,'goo.gl')) {
                            $google_url = new Google_Service_Urlshortener_Url();
                            $google_url->longUrl=$href2;
                        
                          
                            $goo_href=$service->url->insert($google_url);
                            
                        
                            if (isset($goo_href['id']))
                            {
                                $href2=$goo_href['id'];
                                $data['a'][$href]=$href2;
                            }
                        }
                    } else {
                        $href2=$goo;
                    }
             
                    $msg=str_replace('"'.$href.'"','"'.$href2.'"',$msg);
                    
                }
                
                
                $webtd->widget_data = base64_encode(serialize($data));
                $webtd->save();
                
            }
            
            

            $to=$this->_getParam('to');
            
            if ($to && strstr($to,'@'))
            {
                $tos=explode(',',$to);
                $i=1;
                foreach ($tos AS $to)
                {
                    $webtd->web20 = serialize(array('status'=>Tools::translate('Sending'),'current'=>$i,'total'=>count($tos)));
                    $webtd->save();
                    $res['status'] = Gmail::send($to,$msg,$webtd->title,$user['email'],true);
                    if ($res['status']) $sent++;
            
                }
            }
            
            $spid=$this->_getParam('spid');
            $sheet=$this->_getParam('sheet');
            
            
            $tos=array();
            if ($spid && $sheet)
            {
                
                $webtd->web20 = serialize(array('status'=>Tools::translate('Retrieving spreadsheet data')));
                $webtd->save();                
                $people=Spreadsheet::getWorksheet($spid,$sheet);
                
                if (count($people))
                {
                    $header=$people[0];
                    $mail=-1;
                    foreach ($header AS $i=>$name)
                    {
                        if (strstr(strtolower($name),'mail'))
                        {
                            $mail=$i;
                            break;
                        }
                    }
                    
                    if ($mail==-1)
                    {
                        $status=Tools::translate('Column with e-mail not found');
                        $webtd->web20 = serialize(array('status'=>$status));
                        $webtd->save();
                        $res['status']=false;
                        $res['msg']=$status;
                        break;
                    }
                    
                
                    for ($i=1;$i<count($people);$i++)
                    {
                        if (!strstr($people[$i][$mail],'@')) continue;
                        
                        $a=array();
                        foreach ($header AS $j=>$name)
                        {
                            if (!$name) continue;
                            $a[$name] = $people[$i][$j];
                        }
                        $tos[$people[$i][$mail]]=$a;
                    }
                    
                    
                    $webtd->web20 = serialize(array('status'=>Tools::translate('Sending'),'current'=>0,'total'=>count($tos)));
                    $webtd->save();
                    
                    $i=1;
                    
                    $event='kmw-newsletter-'.$webtd->sid;
                    foreach ($tos AS $to=>$vars)
                    {
                        $to=strtolower($to);
                        
                        if ($observersent->getSent($to,$event)) continue;
    
    
                        if (Gmail::send($to,GN_Smekta::smektuj($msg,$vars),GN_Smekta::smektuj($webtd->title,$vars),$user['email'],true))
                        {
                            $observersent2=new observersentModel();
                            $observersent2->email=$to;
                            $observersent2->event=$event;
                            $observersent2->nd_sent=time();
                            $observersent2->save();
                            $sent++;
                        }
                        

                        $webtd->web20 = serialize(array('status'=>Tools::translate('Sending'),'current'=>($i++),'total'=>count($tos)));
                        $webtd->save();

                    }
                    
                    
                } else {
                    $status=Tools::translate('Spreadsheet not found');
                    $webtd->web20 = serialize(array('status'=>$status));
                    $webtd->save();
                    $res['status']=false;
                    $res['msg']=$status;
                    break;
                }
                
                
            }
            

            $webtd->web20 = serialize(array('status'=>Tools::translate('Complete').': '.$sent.' '.Tools::translate('messages')));
            $webtd->save();
            
            $res['status']=true;
            $res['msg']=Tools::translate('Complete');
            
            break;
        }
        
        
        
        $web20=unserialize($webtd->web20);
        $web20['end']=true;
        $webtd->web20=serialize($web20);
        $webtd->save();
        return $res;
    }

    
    public function sheets()
    {
        session_write_close();
        
        return Spreadsheet::listWorksheets($this->id);
    }
    
    public function newsletter_status()
    {
        
        $res=array();
        
        while(true)
        {
            
            if ($this->id + 0 != $this->id || !$this->id)
            {
                $res['status']=false;
                break;
            }
            
            $webtd=new webtdModel($this->id);
            $server=Bootstrap::$main->session('server');
            if ($server['id']!=$webtd->server)
            {
                $res['status']=false;
                break;
            }
        
        
            $res['status']=true;
            $res['data']=unserialize($webtd->web20);
        
            if (!isset($res['data']['end'])) $res['data']['end'] = false;
            break;
        }
        
        
        return $res;
    }
    

}
