<?php

class indexController extends Controller
{
    
    protected $menu_cycle;
    protected $google_translate_from=false;
    protected $modification_date;
    
    
    protected function init()
    {
        $this->google_translate_from = isset($_GET['google_translate_from']) ? $_GET['google_translate_from'] : false;
        parent::init();

    }    
    
    public function finish(&$data, $output)
    {
        parent::finish($data, $output);

        if (Bootstrap::$main->session('editmode') == PAGE_MODE_PREVIEW && is_object(Bootstrap::$main->tokens) ) {
            Bootstrap::$main->tokens->loadJQuery = true;
        }
    }

    public function get($mayadd=true)
    {
        $page = 0 + $this->_getPageId();

        if ($page == -1) return $this->add();

        $content = $this->getPage($page, Bootstrap::$main->session('editmode'));

        if (!isset($content['data']['page']['sid'])) {
            $content['page_id'] = $page;
            if ($mayadd && $this->_hasParam('add')) {
                return $this->add($page);
            }

            $content['referlang']=Bootstrap::$main->session('referlang');
            $content['page_not_found']=1;
            $content['requested_page_id']=$page;            
            $content['ref_menu'] = $this->_getParam('ref_menu');
            $content['referer'] = $this->_getParam('referer');
            return $content;
        }

        
        $this->setViewTemplate($content['template']);
        $this->viewLayout = false;

        $data = $content['data'];

        if (!strlen($data['page']['file_name']) || $data['page']['file_name'] == '.') {
            Bootstrap::$main->error(ERROR_WARNING, 'This page has no filename - it is not recommended by google', array(), $link = 'index/filename/' . $data['page']['id']);
        }

        $modelWeblink = new weblinkModel;
        $data['menu_list'] = $modelWeblink->getMenuList();

        $clipboard = Bootstrap::$main->session('clipboard');
        $data['clipboard'] = !empty($clipboard);
     
        return $data;
    }
    
    public function postRender(&$html)
    {
        if ($this->google_translate_from) $html=Html::find_translate_font_in_tags($html);
        return $html;
    }

    protected function device($w, $h)
    {
        $page = $this->id;
        $webpage = new webpageModel();
        $wp = $webpage->getOne($page);

        Bootstrap::$main->hidetopbar(1);

        return array(
            'template_images' => Bootstrap::$main->session('template_images'),
            'page' => $wp,
            'width' => $w,
            'height' => $h
        );
    }
    
    
    public function ipad()
    {
        return $this->device(783,1038);
    }
    
    public function ipadh()
    {
        return $this->device(1038,783);
    }
        
    public function phone()
    {
        return $this->device(337,497);
    }
    
    public function phoneh()
    {
        return $this->device(497,337);
    }

    public function edit()
    {
        $page = abs(0 + $this->id);
        $webpage = new webpageModel();
        $wp = $webpage->getOne($page);
        
        $ret = array();

        $users=new acluserModel();
        $pages=new aclpageModel();
        
        $server=Bootstrap::$main->session('server');
        
        if (isset($_POST['acl']) && $server['owner'])
        {
 
            if (!empty($_POST['acl']['new']['user']) && !empty($_POST['acl']['new']['pass'])) {
  
                $_POST['acl']['new']['user']=trim($_POST['acl']['new']['user']);
                $_POST['acl']['new']['pass']=trim($_POST['acl']['new']['pass']);
                
                if ($_POST['acl']['new']['user'] && $_POST['acl']['new']['pass'] && !$users->find_by_username($_POST['acl']['new']['user']))
                {
                    $users->username = $_POST['acl']['new']['user'];
                    $users->password = $_POST['acl']['new']['pass'];
                    $users->save();
                    $user = $_POST['acl']['new']['user'];
                    $_POST['acl'][$user]['ok']=1;
                    $_POST['acl'][$user]['pass']=$_POST['acl']['new']['pass'];
                }   
            }
            
            foreach($_POST['acl'] AS $k=>$user)
            {
                $u=$users->find_by_username($k);
                
                if ($user['pass'] != $u['password'])
                {
                    $users->load($u);
                    $users->password=$user['pass'];
                    $users->save();
                }
                
                if (isset($user['delete']) && $user['delete'])
                {
                    $users->remove($k);
                }
                
            }
        
            
            $allusers=$users->getall();
            
            foreach ($allusers AS $user)
            {
                $chkp=$pages->checkUser($user['username'],$page);
            
                if ($_POST['acl'][$user['username']]['ok'])
                {
                    if (!$chkp) {
                        $acl=new aclpageModel();
                        $acl->username=$user['username'];
                        $acl->page=$page;
                        $acl->ok=1;
                        $acl->save();
                    
                        $chkp['ok']=1;
                    }
                    if (!$chkp['ok']) {
                        if ($chkp['page']==$page) {
                            $acl=new aclpageModel($chkp['id']);
                        } else {
                            $acl=new aclpageModel();
                        }

                        $acl->username=$user['username'];
                        $acl->page=$page;
                        $acl->ok=1;
                        $acl->save();
                    }
                    
                } else {
                    if (!$chkp) {
                        $acl=new aclpageModel();
                        $acl->username=$user['username'];
                        $acl->page=$page;
                        $acl->ok=0;
                        $acl->save();
                    
                        $chkp['ok']=0;
                    }
                    if ($chkp['ok']) {
                        if ($chkp['page']==$page) {
                            $acl=new aclpageModel($chkp['id']);
                        } else {
                            $acl=new aclpageModel();
                        }
                        
                        $acl->username=$user['username'];
                        $acl->page=$page;                        
                        $acl->ok=0;
                        $acl->save();
                    }
                    
                }
                
            
            }
            
        }

        if (!empty($_POST['page']['sid']) && $_POST['page']['sid'] == $wp['sid']) {
            if ($webpage->checkRight($page)) {
                if (isset($_POST['page']['prev_prev'])) {
                    if ($_POST['page']['prev_prev'] != $_POST['page']['prev']) {
                        $_POST['page']['tree'] = '';
                    }
                    unset($_POST['page']['prev_prev']);
                }
                if (!empty($_POST['page']['bgcolor']) && $_POST['page']['bgcolor'][0]=='#') $_POST['page']['bgcolor']=substr($_POST['page']['bgcolor'],1);
                
                
                $file_name_must_be_changed = $_POST['page']['file_name']==$webpage->file_name && ($webpage->id >0 || !$webpage->file_name) && ($_POST['page']['title']!=$webpage->title || $_POST['page']['title_short']!=$webpage->title_short || $_POST['page']['prev']!=$webpage->prev);
                
                
                foreach ($_POST['page'] AS $k => $v) {
                    if (!strlen($v)) $v = null;
                    $webpage->$k = $v;
                }

                if ($webpage->id == 0) $webpage->prev = -1;

                if (isset($_POST['d_xml']))
                    $webpage->d_xml = base64_encode(serialize($_POST['d_xml']));

                if ($pages->getAuthUsers($page))
                {
                    $file_name=explode('.',$webpage->file_name);
                    if (strtolower(end($file_name))!='php')
                    {
                        $file_name[count($file_name)-1]='php';
                        $webpage->file_name=implode('.',$file_name);
                    }
                }

                
                
                $webpage->save();

                if (!$webpage->file_name || $file_name_must_be_changed) $this->filename($webpage->data(), false);
                
                
                if (strlen($webpage->file_name)==1)
                {
                    $webpage->file_name=null;
                    $webpage->save();
                }

                $this->redirect('index/get/' . $wp['id']);
            } else {

            }
        }

        $ret['page'] = $wp;
        $ret['tokens'] = Bootstrap::$main->tokens;
        $ret['d_xml'] = Bootstrap::$main->kameleon->get_user_variables('webpage', $wp);

        $weblink = new weblinkModel;
        $ret['menu_list'] = $weblink->getMenuList();
        
        
        $ret['users'] = $users->getall();
        
        if (is_array($ret['users'])) foreach($ret['users'] AS &$user)
        {
            $chkp=$pages->checkUser($user['username'],$page);
            if ($chkp) $user['ok']=$chkp['ok'];
        }
        $ret['config']=$this->getConfigChangeStyles('page');
        return $ret;
    }

    public function add($page = null)
    {
        $server = Bootstrap::$main->session('server');
        $webpage = new webpageModel();
        $new_webpage = new webpageModel();
        $weblink = new weblinkModel();
        $webtd = new webtdModel();

        $servers = Bootstrap::$main->session('servers');
	
        $server['pages'] = $servers[$server['id']]['pages'];        
        
        
        if ($server['pages']) {
            $ranges=Tools::ranges($server['pages']);    
        
        } else {
            $ranges = array(array(0, 1000), array(1001, 2000), array(2001, 5000), array(5001, 10000), array(10001, 50000), array(50001, 1000000));
        }

        $referer = $this->_hasParam('referer') ? $this->_getParam('referer') : (is_null($page) ? $this->id : ($page > 0 ? 0 : -1));
        $referer_page = empty($referer) ? null : $webpage->getOne($referer);
        
        

        if (is_null($page)) {
            if ($referer && !$referer_page) $page=$referer;
            else $page = $webpage->findFirstEmptyNumber($ranges);
        } else {
            if ($webpage->getOne($page, true)) return;
        }

        if (isset($_GET['tdsid'])) {
            $td = $webtd->find_one_by_sid($_GET['tdsid']);
            if (!$webpage->checkRight($td['page_id'])) $webtd->clear();
        }

        $pages=array();
        if (isset($_GET['ref_menu']) && $_GET['ref_menu']) {
            $menu = explode(':', $_GET['ref_menu']);
            $links = $weblink->getAll($menu[0]);
            
            foreach ($links AS $link) {
                if ($link['page_target'] && !strlen($link['lang_target']) ) {
                    
                    $wp = $webpage->getOne($link['page_target']);
                    
                    $pages[$wp['nd_update']?:$wp['nd_create']] = array('prev'=>$wp['prev'],'type'=>$wp['type']);

                }
                if ($link['pri'] == $menu[1]) {
                    $weblink->load($link);
                    $new_webpage->title = $weblink->alt;
                }
            }
        } elseif (strlen($referer)) {
            $children=$webpage->getChildren($referer);
            
            foreach($children AS $wp) {
                $pages[$wp['nd_update']?:$wp['nd_create']] = array('prev'=>$wp['prev'],'type'=>$wp['type']);
            }
        }

        
        if (count($pages))
        {
            krsort($pages);
            $ak=array_keys($pages);
            $new_webpage->prev = $pages[$ak[0]]['prev'];
            $new_webpage->type = $pages[$ak[0]]['type']; 
        }
        
        if ($this->_hasParam('title'))
            $new_webpage->title = $this->_getParam('title');

        if (!strlen($new_webpage->prev)) {
            if (strlen($referer)) {
                $new_webpage->prev = $referer_page?$referer:0;
                $new_webpage->type = $referer_page?$referer_page['type']:0;
            }
        }


        $server = Bootstrap::$main->session('server');
        $new_webpage->id = $page;
        $new_webpage->server = $server['id'];
        $new_webpage->lang = Bootstrap::$main->session('lang');
        $new_webpage->ver = Bootstrap::$main->session('ver');

        if ($webtd->title) {
            $new_webpage->title = $webtd->title;
            $new_webpage->og_desc = Bootstrap::$main->kameleon->unhtml($webtd->plain);
        }

        if ($webtd->bgimg) {
            $new_webpage->og_image = 'widgets/article/gfx/icon/' . $webtd->bgimg;
        }
        
        if ($new_webpage->id==0) $new_webpage->prev=-1;
        
        if (!$new_webpage->title) {
            $shit_title=true;
            $new_webpage->title = Tools::translate('New page') . ' ' . $page;
        } else $shit_title=false;        
        

        if (!$new_webpage->save())
        {
            $this->redirectBack();
            return;
        }
        

        
            
        if ($weblink->sid) {
            $weblink->page_target = $page;
            $weblink->save();
        }

        if ($webtd->sid) {
            $webtd->more = $page;
            $webtd->save();
        }

        if (!$shit_title)
        {
            $new_webpage = $this->filename($new_webpage->data(),false);
        }        
        
        $this->redirect('index/get/' . $page);

        return $new_webpage;
    }

    public function remove($page = null, $recursive = false)
    {
        if ($page === null) $page = $this->_getPageId();

        $modelWebpage = new webpageModel;
        $modelWebpage->remove($page, $recursive);
        
        $redirect='index/get/'.($modelWebpage->prev>=0?$modelWebpage->prev:0);


        
        $this->redirect($redirect);
    }
    
    public function paste($sid=0,$referer=null) {
        $webpage=new webpageModel();
        $oryginal_webpage=new webpageModel($sid?:$this->id);
        
        
        if (!$oryginal_webpage->hasAccess())
        {
            Bootstrap::$main->error(ERROR_ERROR,'No access');
            $this->redirectBack();
            return false;
        }        
        
        
        if (is_null($referer)) $referer = $this->_hasParam('referer') ? $this->_getParam('referer') : (is_null($page) ? $this->id : ($page > 0 ? 0 : -1));
        $referer_page = $webpage->getOne($referer);        
        
        $new_webpage=$this->add();
        
        if (!$new_webpage) return;
        
        if ($referer_page)
        {
            $new_webpage->prev=$referer_page['id'];
        }
        else
        {
            $new_webpage->prev=$oryginal_webpage->prev;
            $new_webpage->type=$oryginal_webpage->type;
            
        }
        
        $new_webpage->save();
        
        foreach($oryginal_webpage->data() AS $k=>$v) {
            if (!in_array($k,array('id','ver','lang','server','next','prev','tree','sid','nd_create','nd_update','nd_ftp','file_name'))) {
                $new_webpage->$k=strlen($v)?$v:null;
            }
        }
        $new_webpage->save();
        
        $this->filename($new_webpage->data(),false);
        
        $webtd=new webtdModel();
        $tds=$webtd->getAll(array($oryginal_webpage->id),$oryginal_webpage->ver,$oryginal_webpage->lang,$oryginal_webpage->server);
        
        
        foreach($tds AS $td) {
            unset($td['sid']);
            unset($td['lang']);
            unset($td['server']);
            unset($td['ver']);
            
            
            $td['page_id']=$new_webpage->id;
            
            $new_webtd = new webtdModel($td,true);
            $new_webtd->save();
            $new_webtd->uniqueid=$new_webtd->uniqueid();
            $new_webtd->save();
        }
        
        
        if ($this->_getParam('wholeTree'))
        {
            $children=$oryginal_webpage->getChildren();
            if (is_array($children)) foreach($children AS $child)
            {
                $this->paste($child['sid'],$new_webpage->id);
            }
        }
        
    }
    
    public function getPage($page,$mode=PAGE_MODE_PURE,$sid=-1)
    {
        Bootstrap::$main->tokens->reset();
        Widget::$loaded_widgets = array();

        $webpage=new webpageModel($sid>=0?$sid:null);

        $template_cache=array();
        $config=Bootstrap::$main->getConfig();
        $wp=$sid>=0?$webpage->data():$webpage->getOne($page);
        $type = $wp['type'] ? :0;
        $page=$wp['id'];

        $webpage->setMain();
        
        $this->modification_date=$webpage->nd_update;
    
        $readonly=0;
        $include_php=false;
        
        $webpage->d_xml($wp);

        if (!$webpage->checkRight()) {
            $mode=PAGE_MODE_PREVIEW;
            $readonly=1;
        }
            
        $d_xml_a=unserialize(base64_decode($wp['d_xml']));
        if (is_array($d_xml_a)) $wp=array_merge($d_xml_a,$wp);
    
        
        $template_path=Bootstrap::$main->session('template');        
    
        $template = isset($config['webpage']['type'][$type]['filename']) ? $config['webpage']['type'][$type]['filename'] : (file_exists($template_path.'/html/page.'.$type.'.html') ? 'page.'.$type.'.html' : 'page.0.html');
        
        $ret['template']=$template_path.'/html/'.$template;        
        $ret['data'] = $levels = GN_Smekta::struktura($ret['template'],'^level[0-9]+',dirname($ret['template']));

        /*
        $onlythistype=true;
        foreach ($ret['data'] AS $part) {
            foreach ($part AS $level_name=>$l) {
                if (substr($level_name,-6)=="notype") {
                    $onlythistype=false;
                    break 2;
                }
            }
        }
        */
        $onlythistype=false;
        
        
        $ret['data']['template_images']=Bootstrap::$main->session('template_images');
        $ret['data']['template_path']=Bootstrap::$main->session('template_path');
        $ret['data']['template_media']=Bootstrap::$main->session('template_media');
        
        $ret['data']['template_dir']=Bootstrap::$main->session('template_dir');
        
        
        $ret['data']['uimages']=Bootstrap::$main->session('uimages');
        $ret['data']['ufiles']=Bootstrap::$main->session('ufiles');
        $ret['data']['translate']=Bootstrap::$main->translate;
        $ret['data']['translates']=Bootstrap::$main->translates;
        
        $ret['data']['page']=$wp;
        $ret['data']['tokens']=Bootstrap::$main->tokens;
        $ret['data']['root']=Bootstrap::$main->getRoot();
        $ret['data']['mode']=$mode;
        $ret['data']['kameleon']=Bootstrap::$main->kameleon;
        
        $ret['data']['noConflictParam']='true';
        $ret['data']['readonly'] = $readonly;
        
        
        $ret['data']['tokens']->webpage=$wp;
        $ret['data']['tokens']->mode=$mode;
        $ret['data']['tokens']->page=$page;
        
        $ret['data']['currentUser'] = new userModel();
        $ret['data']['currentUser'] = $ret['data']['currentUser']->getCurrent();
        
        if ($google_translate_next=$this->_getParam('google_translate_next'))
        {
            $google_translate_next=explode(',',$google_translate_next);
            $ret['data']['google_translate_next']=$google_translate_next[1];
            $ret['data']['google_translate_lang']=$google_translate_next[0];
            
        }
       
        $server=new serverModel($wp['server']);
        $ret['data']['creator']=$server->creator();
       

        $trees=$webpage->trees($wp['id'],$wp['ver']);
        
        
        if (isset($trees['notype'])) {
            $tree=implode(':',$trees['notype']);
        }
        
        
        $acl=new aclpageModel();
        $ret['data']['acl']=$acl->getAuthUsers($page);
        $ret['data']['aclfreepage']=0;
        
        if ($ret['data']['acl'] && isset($trees['notype']))
        {
            for ($i=count($trees['notype'])-1; $i>=0; $i--)
            {
                $aclfree=$acl->getAuthUsers($trees['notype'][$i]);
                if (!$aclfree)
                {
                    $ret['data']['aclfreepage']=$trees['notype'][$i];
                    break;
                }
            }
            $include_php=true;
        }
        
        
        $header=$webpage->header;
        $footer=$webpage->footer;
        
        if ($config['header_footer']['multi']) {
            $header-=$wp['type']*$config['header_footer']['multi_step'];
            $footer-=$wp['type']*$config['header_footer']['multi_step'];    
        }

        
        $pages=array($page,$header,$footer);
        
        $ret['data']['page']['header_id']=$header;
        $ret['data']['page']['footer_id']=$footer;
        
        
        $default_body_level=$config['default']['level']['body'];
        if (isset($config['webpage']['type'][$wp['type']]['level'])) $default_body_level=$config['webpage']['type'][$wp['type']]['level'];
        
    
        $treename=$onlythistype?'type':'notype';
        if (isset($trees[$treename]) && is_array($trees[$treename])) $pages=array_merge($pages,$trees[$treename]);
    
        
    
        $webtd=new webtdModel();
        $all=$webtd->getAll($pages,$wp['ver'],null,0,$mode);

        foreach ($all AS $td) {
            $token2=null;
            $index=null;
            
            if ($mode<=PAGE_MODE_PREVIEW && $td['hidden']) continue;
            
            
            if ($td['page_id']<0) {
                $token1=$td['page_id']==$header ? 'header':'footer';
                $token2='level'.$td['level'];
            } else {
                $token1='body';
                if ($td['page_id']==$wp['id']) {
                    $token2='level'.$td['level'];
                    $index=$td['pri'];
                } else {
                    $page_id=$td['page_id'];
                    
                    
                    $lev='level'.$td['level'].'_inherited_up_notype';
                    if (isset($ret['data'][$token1][$lev]) && isset($trees['notype'])) {                        
                        $inx=count($trees['notype']) - array_search($page_id,$trees['notype']);
                        if (strlen($inx)) {
                            $index=1000*$inx + $td['pri'];
                            $token2=$lev;
                        }
                    }
                    $lev='level'.$td['level'].'_inherited_up_type';
                    if (isset($ret['data'][$token1][$lev]) && isset($trees['type'])) {                        
                        $inx=count($trees['type']) - array_search($page_id,$trees['type']);
                        if (strlen($inx)) {
                            $index=1000*$inx + $td['pri'];
                            $token2=$lev;
                        }
                    }
                    $lev='level'.$td['level'].'_inherited_down_notype';
                    if (isset($ret['data'][$token1][$lev]) && isset($trees['notype'])) {                        
                        $inx=count($trees['notype']) - array_search($page_id,$trees['notype']);
                        if (strlen($inx)) {
                            $index=-1000*$inx + $td['pri'];
                            $token2=$lev;
                        }
                    }
                    $lev='level'.$td['level'].'_inherited_down_type';
                    if (isset($ret['data'][$token1][$lev]) && isset($trees['type'])) {                        
                        $inx=count($trees['type']) - array_search($page_id,$trees['type']);
                        if (strlen($inx)) {
                            $index=-1000*$inx + $td['pri'];
                            $token2=$lev;
                        }
                    }
                    
                    if (isset($td['contents_repeat']) && $td['contents_repeat']) {
                        $token2='level'.$td['level'];
                        $inx=count($trees['notype']) - array_search($page_id,$trees['notype']);
                        $index=$td['contents_repeat']*1000*$inx + $td['pri'];
        
                    }
                    
                }    
            }
            
            
            if (!is_null($token2)) {
                
                $td['lost'] = false;
                if (!isset($levels[$token1][$token2]))
                {
                    if ($mode==PAGE_MODE_EDIT && $td['page_id']>=0 && $td['page_id']==$wp['id'] || $mode==PAGE_MODE_EDITHF && $td['page_id']<0)
                    {
                        $token2='lost';
                        if (!isset($levels[$token1][$token2])) $levels[$token1][$token2]=array();
                        $index=null;
                        
                        if (isset($td['level']) && isset($config['level'][$token1][$td['level']]) && $config['level'][$token1][$td['level']])
                            $td['lost'] = $config['level'][$token1][$td['level']];
                        else
                            $td['lost']='?';
                    }
                    else
                    {
                        $token2='lost.but.not.shown';
                    }
    
                }
                
                if (is_null($index)) $levels[$token1][$token2][]=$td;
                else
                {
                    while(isset($levels[$token1][$token2][$index])) $index++;
                    $levels[$token1][$token2][$index]=$td;
                }
                
                if (!$td['lost'] && $td['nd_update']>$this->modification_date) $this->modification_date=$td['nd_update'];
               
            } 
        }

       
        
        
        $available_levels=array();
        
        $lostpri=1;
        

        foreach (array('header','body','footer') AS $pagepart_no=>$pagepart) {
            if (isset($levels[$pagepart])) {
             
             
                $next_td=null;
                $next_lev=null;
                foreach($levels[$pagepart] AS $levelname=>$level) {
                    
                    if (is_array($level)) foreach ($level AS $ti=>$td) {
                        if ($td['hidden']) continue;
                        
                        if (!is_null($next_td)) {
                            $levels[$pagepart][$next_lev][$next_td]['next_td_title']=$td['title'];
                            $levels[$pagepart][$next_lev][$next_td]['next_td_sid']=$td['sid'];
                        } 
                        $next_td=$ti;
                        $next_lev=$levelname;
                        
                    }
                }
                
                
                
                foreach($levels[$pagepart] AS $levelname=>$level) {
                    $levelno=str_replace('level','',$levelname);
                    if ($levelno) $available_levels[$pagepart][$levelno]=1;
                    
                    $html='';
                    if (is_array($level)) ksort($level);
                    
                    $level_count = count($level);
                    $level_i = 0;
                
                    
                    if (is_array($level)) foreach ($level AS $td) {
                        $type=$td['type']+0;
                        
                        if ($td['html'] && !$td['staticinclude']) $include_php=true;
                                                
                    
                        if ($level_i++ == 0) $td['first_child'] = 1;
                        if ($level_i == $level_count) $td['last_child'] = 1;
                        
                        
                        
                        $td['default_level'] = ($td['page_id']>=0 && $td['level']==$default_body_level);
                        
                        $html_path=$template_path.'/html';

                        if (strlen($td['img']) && file_exists(Bootstrap::$main->session('uimages_path').'/'.$td['img'])) {
                            list($td['img_w'],$td['img_h']) = getimagesize(Bootstrap::$main->session('uimages_path').'/'.$td['img']);
                        }
                        if (strlen($td['bgimg']) && file_exists(Bootstrap::$main->session('uimages_path').'/'.$td['bgimg'])) {
                            list($td['bgimg_w'],$td['bgimg_h']) = getimagesize(Bootstrap::$main->session('uimages_path').'/'.$td['bgimg']);
                        } 
                        
                        if ($td['lost']) $td['pri']=$lostpri++;

                        $td['more_link']=$td['more']?Bootstrap::$main->kameleon->href('','',$td['more'],$page,$mode) : Bootstrap::$main->kameleon->href('','',$page,$page,$mode); 
                        $td['next_link']=$td['next']?Bootstrap::$main->kameleon->href('','',$td['next'],$page,$mode) : Bootstrap::$main->kameleon->href('','',$page,$page,$mode);
                        $td['self_link']=Bootstrap::$main->kameleon->href('','',$page,$page,$mode);
                                               
                        $td=$this->_process_td($td,$mode,$page,$tree,$this->google_translate_from);
                        
                        if ($td['nd_valid_from'] || $td['nd_valid_to']) $include_php=true;

                        $webtd->d_xml($td);

                        if ($td['html']) $debug_id=Debugger::debug(null,'td('.$td['html'].')');
                        
                        $td['body']=$pagepart=='body';
                        $td['_page_id']=str_replace('-','_',$td['page_id']);
                        $td['pagepart']=$pagepart_no;
                        
                        $td['readonly']=false;
                        if ($pagepart=='body') {
                            if ($page!=$td['page_id']) {
                                $td['readonly']='<a href="'.Bootstrap::$main->kameleon->href('','',$td['page_id'],$page,$mode).'">'.Tools::translate('Repeated from page').': '.$td['page_id'].'</a>';
                            }
                        }
                        

                        if ($td['more']==$page) $td['more']=0;
                        
                        if ($td['nd_valid_from'] || $td['nd_valid_to']) $td['valid']=1;

                        if ($wp['nd_ftp'] && $td['nd_update'] > $wp['nd_ftp'] ) $td['need_ftp']=1;

                        if ($td['widget'] && Widget::exists($td['widget'])) {
                            $widget = Widget::factoryWebtd($td,$wp['id']);
                            $widget->webpage = &$wp;
                            $widget->mode = $mode;
                            $widget->run();
                            
                            if (isset($config['webtd']['type'][$type])) $td['widget_name']=Tools::translate($config['webtd']['type'][$type]['name']);
                            else {
                                foreach ($config['webtd']['type'] AS $w)
                                {
                                    if ($w['widget']==$td['widget'])
                                    {
                                        $td['widget_name']=Tools::translate($w['name']);
                                        break;
                                    }
                                }
                            }

                            if (isset($config['webtd']['type'][$type]['filename']) && file_exists($html_path.'/'.$config['webtd']['type'][$type]['filename']) ) {
                                $template_td=$config['webtd']['type'][$type]['filename'];
                            } else {
                                $templ_td = $widget->default_html($html_path);
                                $template_td=basename($templ_td);
                                $html_path=dirname($templ_td);
                                
                            }
                            $td[get_class($widget)] = $widget->toArray();
                        } else {
                            $template_td = isset($config['webtd']['type'][$type]['filename']) ? $config['webtd']['type'][$type]['filename'] : (file_exists($html_path.'/td.'.$type.'.html') ? 'td.'.$type.'.html' : 'td.0.html');                    
                        }

                        $template_cache_token=$html_path.'/'.$template_td;
                        if(!isset($template_cache[$template_cache_token])) {
                            $template_cache[$template_cache_token] = file_exists($template_cache_token) ? file_get_contents($template_cache_token) : ''; 
                        }
                        
                        if ($td['ob'] & 1 ) $td['ob_start']=true;
                        if ($td['ob'] & 2 ) $td['ob_end']=true;
                        
                        $td['__index__']=$level_i;
                        
                        Bootstrap::$main->tokens->webtd=$td;
                        
                        $td=array_merge($ret['data'],$td);
                        $td['page']=$wp;
                
                        
                        $html.=$td['tokens']->ob(
                                GN_Smekta::smektuj(
                                    $this->addKameleonTags($template_cache[$template_cache_token],$mode,'td'),
                                    $td,
                                    false,
                                    array($html_path,VIEW_PATH.'/replace',VIEW_PATH.'/scripts'),
                                    $template_cache_token
                                )
                        );
                        
                        
                        if ($td['html']) Debugger::debug($debug_id);
                        
                    }
                    
                    $levelno=preg_replace('/[^0-9]/','',$levelname);
                    $name=isset($config['level'][$pagepart][$levelno]) ? $config['level'][$pagepart][$levelno] : Tools::translate('Level').' '.$levelno;
                    $ret['data'][$pagepart][$levelname] = trim($this->addKameleonTags($html,$mode,'level',array('name'=>$name,'part'=>$pagepart,'level'=>$levelno, 'inherited'=>strstr($levelname,'inherited')?1:0)));
                
                }
            }
        }

        
        $ret['data']['config'] = Bootstrap::$main->getConfig();        
        
        foreach($ret['data']['config']['level'] AS $pagepart=>&$levels)
        {
            if (!isset($available_levels[$pagepart])) unset($ret['data']['config']['level'][$pagepart]);
            else {
                foreach(array_keys($levels) AS $key)
                {
                    if (!isset($available_levels[$pagepart][$key])) unset($ret['data']['config']['level'][$pagepart][$key]);
                }
            }
        }
    
        
    
        if ($include_php && Bootstrap::$main->now-$webpage->nd_update>0 && (substr($webpage->file_name,-5)=='.html' || substr($webpage->file_name,-4)=='.htm') )
        {
            $webpage->file_name=preg_replace('/.htm[l]*$/','.php',$webpage->file_name);
            $webpage->save();
        }
        
        if ($this->modification_date > $webpage->nd_update)
        {
            $webpage->nd_update = $this->modification_date;
            $webpage->save();
        }
        
        if (is_array($wp)) foreach ($wp AS $k=>$v) $ret['data']['page'][$k]=$v;
    
        $ret['data']['php'] = substr(strtolower($webpage->file_name),-4)=='.php';
        return $ret;
    }

    public function setup_properties()
    {
        $data = Bootstrap::$main->session('server');
        $keys = array('szablon', 'http_url', 'analytics', 'lang');
        $server = new serverModel($data);

        foreach ($keys as $option) {
            if (isset($_POST['server'][$option])) {
                $server->$option = $_POST['server'][$option];
            }
        }

        if (isset($_POST['server']['logo'])) {
            $server->option('logo', $_POST['server']['logo']);
        }
        
        if (isset($_POST['server']['mourning'])) {
            $server->option('mourning', $_POST['server']['mourning']);
        }        
        
        if (isset($_POST['server']['background'])) {
            $server->option('background', $_POST['server']['background']);
        }        

        if (isset($_POST['server']['bgcolor'])) {
            $server->option('bgcolor', preg_replace('/[^0-9a-f]/i','',$_POST['server']['bgcolor']));
        }        

        Bootstrap::$main->session('server', $server->save());
        Bootstrap::$main->setGlobals(true);

        $this->redirectBack();
    }
    
    
    protected function add_google_translate_from_tag($html,$obj,$id,$field)
    {
        return '<font class="km_translate" rel="'.$obj.','.$id.','.$field.'">'.$html.'</font>';
    }

    public function _process_td($td,$mode,$page,$tree,$google_translate_from=false) {
        
        
        if ($td['nd_valid_from'] && $td['nd_valid_from']<Bootstrap::$main->now) $td['nd_valid_from']=0;
        if ($td['nd_valid_to'] && $td['nd_valid_to']<Bootstrap::$main->now) $td['nd_valid_to']=0;        
        
        
        if ($td['menu_id']) {
            $this->menu_cycle=array();
            $td['menu']=$this->_getMenu($td['menu_id'],$mode,$page,$tree,$google_translate_from);
            if (count($td['menu'])) $td['name'] = $td['menu'][0]['name'];
        }
        
        if ($page!=$td['page_id'] && $td['page_id']>=0) $google_translate_from=false;
        
        if (!$google_translate_from) $td['plain']=Bootstrap::$main->kameleon->include_plain($td['plain'],$mode);
        
        
        $td['plain']=preg_replace('#/*uimages/[0-9]+/[0-9]+#',Bootstrap::$main->session('uimages'),$td['plain']);
        $td['plain']=str_replace(UIMAGES_TOKEN,Bootstrap::$main->session('uimages'),$td['plain']);

        $td['plain']=preg_replace('#/*ufiles/[0-9]+\-att#',Bootstrap::$main->session('ufiles'),$td['plain']);
        $td['plain']=str_replace(UFILES_TOKEN,Bootstrap::$main->session('ufiles'),$td['plain']);
    
    
        $start=INSIDELINE_TOKEN.'begin';
        $end=INSIDELINE_TOKEN.'end';

        $td['plain']=preg_replace('/(<form[^>]*>)/i',$start.'\\1'.$end,$td['plain']);


        while (($pos=strpos($td['plain'],$start))!==false) {
            $endpos=strpos($td['plain'],$end);
            
            $form=substr($td['plain'],$pos,$endpos-$pos).$end;
                        
            $newform=substr($form,strlen($start),strlen($form)-strlen($start)-strlen($end));
            
            
            
            if (!stripos($newform,'action="'))
            {
                $newform=str_replace('>',' action="'.$td['next_link'].'">',$newform);
            }

            $td['plain']=str_replace($form,$newform,$td['plain']);
        }        
        
        
        if (!$google_translate_from) {
            $start=INSIDELINE_TOKEN.'begin';
            $end=INSIDELINE_TOKEN.'end';
    
            $td['plain']=preg_replace('/kameleon:link\(([a-z0-9:]+),*([a-z0-9_=\{\}]*)\)/',$start.'\\1'.INSIDELINE_TOKEN.'\\2'.$end,$td['plain']);
            $td['plain']=preg_replace('/kameleon:inside_link\(([a-z0-9:]+),*([a-z0-9_=\{\}]*)\)/',$start.'\\1'.INSIDELINE_TOKEN.'\\2'.$end,$td['plain']);
            
            
            
            while ($pos=strpos($td['plain'],$start)) {
                $plain=substr($td['plain'],$pos);
                $endpos=strpos($plain,$end);
                if (!$endpos) break;
                
                $plain=substr($plain,0,$endpos+strlen($end));
                
                $plain2=$plain;
                
                $plain2=str_replace($start,'',$plain2);
                $plain2=str_replace($end,'',$plain2);
                
                $_link=explode(INSIDELINE_TOKEN,$plain2);
                     
                
                $link=Bootstrap::$main->kameleon->href('',$_link[1],$_link[0],$page,$mode);
                $td['plain']=str_replace($plain,$link,$td['plain']);
      
            }
            
            
            $start=RMEDIA_TOKEN.'begin';
            $end=RMEDIA_TOKEN.'end';
            
            $td['plain']=preg_replace('#media/get/([^"\'\/\n \t]+)#',$start.'\\1'.$end,$td['plain']);
            
            
            while ($pos=strpos($td['plain'],$start)) {
                $plain=substr($td['plain'],$pos);
                $endpos=strpos($plain,$end);
                if (!$endpos) break;
                
                $plain=substr($plain,0,$endpos+strlen($end));
                
                $target=$plain;
                
                $target=str_replace($start,'',$target);
                $target=str_replace($end,'',$target);
                
                
                if ($mode) $rmedia=Bootstrap::$main->getRoot().'media/get/'.$target;
                else {
                    
                    $gallery=new galleryController();
                    $webmedia=new webmediaModel();               
                    $info=$gallery->info($target,$webmedia->getOwner($target));
                    
                    $rmedia=Bootstrap::$main->session('media').'/'.$info['name'];
                    
                    if (strstr($info['mime'],'svg')) $rmedia.='.svg';
                }
                
                
                $td['plain']=str_replace($plain,$rmedia,$td['plain']);
      
            }
            
            $start=IMG_TOKEN.'begin';
            $end=IMG_TOKEN.'end';
            
            $td['plain']=preg_replace('#(<img [^>]+>)#i',$start.'\\1'.$end,$td['plain']);
            
            $title=$td['title'];
            if (!$title)
            {
                $webpage=Bootstrap::$main->tokens->webpage;
                $title=$webpage['title'];
            }
            

            while (true) {
                $pos=strpos($td['plain'],$start);
                if ($pos===false) break;
            
                
                $plain=substr($td['plain'],$pos);
                $endpos=strpos($plain,$end);
                

                
                if (!$endpos) break;
                
                $plain=substr($plain,0,$endpos+strlen($end));
                
                $target=$plain;
                
                $target=str_replace($start,'',$target);
                $target=str_replace($end,'',$target);
                
                $alt=strpos(strtolower($target),'alt=');
                
                
                if ($alt) {
                    
                    $q=substr($target,$alt+4,1);
                    if ($q!=' ')
                    {
                        $end2=strpos(substr($target,$alt+5),$q);
                        
                        $alttag=substr($target,$alt,$end2+6);
                    
                        if (strlen($alttag)<8)
                        {
                            $target=str_replace($alttag,'alt="'.addslashes($title).'"',$target);
                        }
                    }
                    
                    
                } else {
                    $target=substr($target,0,4).' alt="'.addslashes($title).'"'.substr($target,4);
                }
                
                
                
                $td['plain']=str_replace($plain,$target,$td['plain']);
      
            }
            
            
        }
          
        
        if ($google_translate_from && $td['lang']!=$td['olang']) {
            
            foreach (array('plain','title') AS $token) {
                if ($td[$token]) {
                    $td[$token]=$this->add_google_translate_from_tag($td[$token],'td',$td['sid'],$token);
                    
                }    
            }
            
        }
        
        
        return $td;
    }
    
    protected function _getMenu($menu_id,$mode,$page,$tree,$google_translate_from=false) {
        
        if (isset($this->menu_cycle[$menu_id])) return null;
        $this->menu_cycle[$menu_id]=true;
        
        
        
        $weblink=new weblinkModel();
        $html_path=Bootstrap::$main->session('template').'/html';
        $config=Bootstrap::$main->getConfig();
        
        $links=$weblink->getAll($menu_id,$mode);
        
        
        $lp=1;
        if (is_array($links)) foreach ($links AS $i=>$link) {
            
            if ($link['nd_update']>$this->modification_date) $this->modification_date=$link['nd_update'];
                        
            $links[$i]['lp']=$lp++;
            if ($i==0) {
                $links[$i]['class']=$links[$i]['class']?$links[$i]['class'].' first_child':'first_child';
                $links[$i]['first_child']=true;
            }
            else $links[$i]['first_child']=false;
            if ($i==count($links)-1) {
                $links[$i]['class']=$links[$i]['class']?$links[$i]['class'].' last_child':'last_child';
                $links[$i]['last_child']=true;
            } else $links[$i]['last_child']=false;
            
            if (strlen($link['page_target']) && !$link['lang_target']) {
                if ($link['page_target']==$page || ($link['page_target']>0 && in_array($link['page_target'],explode(':',$tree))) ) {
                    $links[$i]['class']=$links[$i]['class']?$links[$i]['class'].' active':'active';
                    $links[$i]['active']=1;
                } else $links[$i]['active']=0;
            }
            
            $weblink->d_xml($links[$i]);
            
            if ($link['submenu_id']) {
                $links[$i]['menu']=$this->_getMenu($link['submenu_id'],$mode,$page,$tree,$google_translate_from);
            } else {
                $links[$i]['menu']=false;
            }
            
            
            if (!strlen(trim($link['href'])) || $link['href'][0]=='#') {
                if ($mode>PAGE_MODE_PREVIEW){
                    if ($links[$i]['variables']) $links[$i]['variables'].='&';
                    $links[$i]['variables'].='ref_menu='.$link['menu_id'].':'.$link['pri'];
                }
                if (strlen($links[$i]['ufile_target'])) {
                    $links[$i]['href']=Bootstrap::$main->session('ufiles').'/'.$links[$i]['ufile_target'];
                }
                else $links[$i]['href']=Bootstrap::$main->kameleon->href(trim($link['href']),$links[$i]['variables'],$link['lang_target'].':'.$link['page_target'],$page,$mode);
            }
            
       
            
            
            $type=$links[$i]['type']+0;
            
            if (isset($config['weblink']['type'][$type]['filename']) && file_exists($html_path.'/'.$config['weblink']['type'][$type]['filename']))
            {
                $links[$i]['html']=GN_Smekta::smektuj($html_path.'/'.$config['weblink']['type'][$type]['filename'],$links[$i]);
            }
            
            
            if ($google_translate_from && $link['lang']!=$link['olang']) {
                
                foreach (array('alt','alt_title','description','titlea','titleb','titlec') AS $token) {
                    if ($link[$token]) {
                        $links[$i][$token]=$this->add_google_translate_from_tag($link[$token],'link',$link['sid'],$token);
                    }    
                }
                            
            }
            
            foreach (['alt','alt_title','description','titlea','titleb','titlec'] AS $f)
                $links[$i][$f]=str_replace('"','&quot;',$link[$f]);
            
            
        }
                       
        return $links;
    }

    protected function _getPageId()
    {
        return isset($_REQUEST['page']) && $_REQUEST['page'] >= 0 ? $_REQUEST['page'] : $this->id ? : 0;
    }
    
    
    public function filename($wp=null,$deep=true) {
        $webpage=new webpageModel();
        if (is_null($wp)) $wp=$webpage->getOne($this->_getPageId());
        

        
        $i=0;
        while (true)
        {
            $filename=$webpage->createFilename($wp,$i);
            if (!$webpage->filenameCount($filename,$wp['id'])) break;
            if ($wp['id']==0) break;
            $i++;
        }
        
        $webpage->load($wp);
        $webpage->file_name=$filename;
        $webpage->save();


        
        if ($deep) {
            $children=$webpage->getAllByPrev($wp['id']);
            if (is_array($children)) foreach($children AS $child) $this->filename($child,$deep);
        }

        $this->redirect('index/get/'.$wp['id']);
    
        
    
        return $webpage;
    }
    
    
    public function copyfrom()
    {
        $olang=$this->id;
        $langs=Bootstrap::$main->session('langs_used');
        $lang=Bootstrap::$main->session('lang');
        $server=Bootstrap::$main->session('server');
        
        
        
        if (in_array($olang,$langs) ) {
            $webpage=new webpageModel();
            $webtd=new webtdModel();
            $weblink=new weblinkModel();
        
            $webpage->begin();
            
            $page=$webpage->export($olang);
            $td=$webtd->export($olang);
            $link=$weblink->export($olang);
            
            $webpage->import($server['id'],$page,$lang);
            $webtd->import($server['id'],$td,$lang);
            $weblink->import($server['id'],$link,$lang);
        
        
            if (Bootstrap::$main->isError()) {
                $webpage->rollback();
            } else {
                $webpage->commit();
            }
            
        }
        Bootstrap::$main->setGlobals(true);
        
        $this->redirect('index');
    }
    
    
    public function appengine()
    {
        $server=Bootstrap::$main->session('server');
    
        $user=new userModel();
        $user->getCurrent();
        if (!$user->hasAccess('appengine'))
        {
            $this->redirect('scopes/appengine');
            return;
        }
        
        if (trim($server['creator'])!=trim($user->username))
        {
            Bootstrap::$main->error(ERROR_ERROR,'Only the website creator may manage Google App Engine');
            
            $this->redirect('index/get');
        }        
        
    
        if (isset($_POST['appengine']))
        {
            $s=new serverModel($server['id']);
            
            $s->appengine_id= $_POST['appengine']['appengine_id'];
            $s->appengine_ver= $_POST['appengine']['appengine_ver'];
            $s->appengine_pre= $_POST['appengine']['appengine_pre'];
            
            $appengine_scripts=array();
            foreach($_POST['appengine']['appengine_scripts'] AS $script)
            {

                if (isset($script['filename'])) {
                    $file=$script['filename'];
                    if ($script['login']) $file.=':'.$script['login'];
                    $appengine_scripts[]=$file;
                }
            }
            $s->appengine_scripts = implode("\n",$appengine_scripts);
            
            
            $appengine_cron=array();
            foreach($_POST['appengine']['appengine_cron']['url'] AS $i=>$url)
            {
                if (trim($url) && trim($_POST['appengine']['appengine_cron']['when'][$i]))
                {
                    $appengine_cron[]=trim($_POST['appengine']['appengine_cron']['desc'][$i]) . '|' .trim($url) .'|' . trim($_POST['appengine']['appengine_cron']['when'][$i]);
                }
                
            }
            
            $s->appengine_cron = implode("\n",$appengine_cron);
 
            $appengine_rewrite=array();
 
            foreach($_POST['appengine']['appengine_rewrite']['regex'] AS $i=>$regex)
            {
                if (trim($regex) && trim($_POST['appengine']['appengine_rewrite']['dest'][$i]))
                {
                    $appengine_rewrite[]=trim($regex) .'~' . trim($_POST['appengine']['appengine_rewrite']['dest'][$i]);
                }
                
            }
            
            
            
            
            $s->appengine_rewrite = implode("\n",$appengine_rewrite);
            $s->resitemap=1;
            
            $s->save();
            
            foreach($s->data() AS $k=>$v) $server[$k]=$v;
            
            
            $server=Bootstrap::$main->session('server',$server);
        }
        
        $scripts=Tools::scandir(Bootstrap::$main->session('uincludes'));
    
        $_appengine_scripts=explode("\n",$server['appengine_scripts']);
        $appengine_scripts=array();
        
        foreach($_appengine_scripts AS $as)
        {
            $as=explode(':',$as);
            $appengine_scripts[$as[0]] = isset($as[1])?$as[1]:'';    
        }
    
        foreach ($scripts AS &$script)
        {
            $script['checked'] = in_array($script['file'],array_keys($appengine_scripts));
            if ($script['checked']) $script['login'] = $appengine_scripts[$script['file']];
        }
    
    
        $cron=array();
        
        if (trim($server['appengine_cron'])) foreach(explode("\n",$server['appengine_cron']) AS $c)
        {
            $c=explode('|',$c);
            
            $cron[]=array('desc'=>$c[0],'url'=>$c[1],'when'=>$c[2]);
                    
        }
    
        $rewrite=array();
        
        if (trim($server['appengine_rewrite'])) foreach(explode("\n",$server['appengine_rewrite']) AS $c)
        {
            $c=explode('~',$c);
            $rewrite[]=array('regex'=>$c[0],'dest'=>$c[1]);
        }
    
    
    
        return array('scripts'=>$scripts,'cron'=>$cron,'rewrite'=>$rewrite);
    }
    
    
    public function gcs()
    {
        $user=new userModel();
        $user->getCurrent();
        if (!$user->hasAccess('gcs'))
        {
            $this->redirect('scopes/gcs');
            return;
        }        
        
        
        $server=Bootstrap::$main->session('server');
        
        
        if (trim($server['creator'])!=trim($user->username))
        {
            Bootstrap::$main->error(ERROR_ERROR,'Only the website creator may manage Google Cloud Storage');
            
            $this->redirect('index/get');
        }
        
        if (isset($_POST['gcs']))
        {
            $s=new serverModel($server['id']);
            
            $s->gcs_bucket= $_POST['gcs']['gcs_bucket'];
            $s->gcs_website= $_POST['gcs']['gcs_website'];
            
            $s->save();
            
            $server['gcs_bucket']=$s->gcs_bucket;
            $server['gcs_website']=$s->gcs_website;
            
            $server=Bootstrap::$main->session('server',$server);
            
            
            
            $client=Google::getUserClient(null,false,'gcs');
            $service = Google::getStorageService($client);
            

            
            $langs=Bootstrap::$main->session('langs_used');
            
            $buckets=array();
            foreach($langs AS $lang)
            {
                $project=GN_Smekta::smektuj($_POST['gcs']['gcs_bucket'],get_defined_vars());
                $bucket=GN_Smekta::smektuj($_POST['gcs']['gcs_website'],get_defined_vars());
                
                if (!$bucket) continue;
                
                if (isset($buckets[$bucket])) continue;   
           
                $b=null;
                try {
                    $b = $service->buckets->get($bucket);   
                } catch (Exception $e) {
                    
                    if ($project)
                    {
                        try {
                            $gb=new Google_Bucket();
                            $gb->name = $bucket;
                            $b=$service->buckets->insert($project,$gb);
                        } catch (Exception $e) {
                            $buckets[$bucket] = $e->getMessage();
                        }
                        
                    }
                    else $buckets[$bucket] = $e->getMessage();
                }
                
                
                if (!is_null($b))
                {
                    $dacl=$service->defaultObjectAccessControls->listDefaultObjectAccessControls($b['name']);
                    
                    $public_reader=false;
                
                    foreach ($dacl['items'] AS $item)
                    {
                        if ($item['entity']=='allUsers') $public_reader=true;
                    }
                    
                    if (!$public_reader)
                    {
                        //$acl=new Google_ObjectAccessControl();
                        $acl=new Google_Service_Storage_ObjectAccessControl();
                        $acl->setEntity('allUsers');
                        $acl->setRole('READER');
                        $service->defaultObjectAccessControls->insert($b['name'],$acl);
                    }
                    
                    if (!isset($b['website']))
                    {
                        $gb=new Google_Service_Storage_Bucket($b);
                        $w=new Google_Service_Storage_BucketWebsite();
                        
                        $w->setMainPageSuffix('index.html');
                        $w->setNotFoundPage('err404.html');
                        
                        $gb->setWebsite($w);
                        
                        try {
                            $www=$service->buckets->update($b['name'],$gb);
                            $buckets[$bucket] = 'Website set, dont forget to CNAME it to <b>c.storage.googleapis.com</b>';
                        } catch (Exception $e) {
                            $buckets[$bucket] = $e->getMessage();
                        }
                        
                    }
                    else
                    {
                        $buckets[$bucket] = ' should be CNAME of <b>c.storage.googleapis.com</b>';
                    }
                }
            
            
                    
            }
            
            return array('buckets'=>$buckets);
            
        }
    }

    
    public function plain()
    {
        if ($this->id + 0 != $this->id || !$this->id) die();
        
        $webtd=new webtdModel($this->id);
        $server=Bootstrap::$main->session('server');
        if ($server['id']!=$webtd->server) die();
        
        
        $td=$this->_process_td($webtd->data(),2,$webtd->page_id,'');
        
        die($td['plain']);
        
        
    }
}
