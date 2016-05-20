<?php
class editController extends Controller
{
    public static function replace_plain($plain)
    {
        $plain = preg_replace('#'.Bootstrap::$main->getRoot().'uimages/[0-9]+/[0-9]+#', UIMAGES_TOKEN, $plain);
        $plain = preg_replace('#'.Bootstrap::$main->getRoot().'ufiles/[0-9]+\-att#', UFILES_TOKEN, $plain);        
        
        $plain = preg_replace('#/*uimages/[0-9]+/[0-9]+#', UIMAGES_TOKEN, $plain);
        $plain = preg_replace('#/*ufiles/[0-9]+\-att#', UFILES_TOKEN, $plain);
        
        
        $start=INSIDELINE_TOKEN.'begin';
        $end=INSIDELINE_TOKEN.'end';
        
        $plain=preg_replace('~(<a href="'.UFILES_TOKEN.'[^"]+\.mp3">[^<]+</a>)~i',$start.'\\1'.$end,$plain);
        
        
        while (($pos=strpos($plain,$start))!==false) {
            $endpos=strpos($plain,$end);
            
            $a=substr($plain,$pos,$endpos-$pos).$end;
            
            $pos=strpos($a,'href="');
            $href=substr($a,$pos+6);
            $pos=strpos($href,'">');
            $inside=substr($href,$pos+2);
            $inside=substr($inside,0,strlen($inside)-strlen($end)-4);
            $href=substr($href,0,$pos);
            
            if ($inside==basename($href)) {
                $newa='<audio controls="" src="'.$href.'">'.$inside.'</audio>';
            } else {
                $newa='<a href="'.$href.'">'.$inside.'</a>';
            }
    
            $plain=str_replace($a,$newa,$plain);
        }
        
        
        return $plain;
    }
    
    
    public function get()
    {
        $ret = Bootstrap::$main->session();
        $root = Bootstrap::$main->getRoot();
        $webtd = new webtdModel($this->id);
        $webcat=new webcatModel();
        $config = Bootstrap::$main->getConfig();
        $page = isset($_GET['page']) ? $_GET['page'] + 0 : 0;

        if (!$webtd->checkRight()) return;

        
        
        
        $ret['page_id'] = $page;

        if (isset($_POST) && !empty($_POST)) {

            if (isset($_POST['td'])) {

                if (isset($_POST['td']['plain']))
                {
                    $_POST['td']['plain']=self::replace_plain($_POST['td']['plain']);
                }
                if (isset($_POST['td']['menu_id']) && $_POST['td']['menu_id'] == -1) {
                    $weblink = new weblinkModel;
                    $_POST['td']['menu_id'] = $weblink->get_new_menu_id();
                }                
                
                if (isset($_POST['td']['valid_from']))
                    $webtd->nd_valid_from = strtotime($_POST['td']['valid_from']);

                if (isset($_POST['td']['valid_to']))
                    $webtd->nd_valid_to = strtotime($_POST['td']['valid_to']);
                    

                if (isset($_POST['td']['custom_date']) && $_POST['td']['custom_date'])
                    $webtd->nd_custom_date = strtotime($_POST['td']['custom_date']);
                else
                    $webtd->nd_custom_date = Bootstrap::$main->now;
                    

                if (isset($_POST['td']['custom_date_end']) && $_POST['td']['custom_date_end'])
                    $webtd->nd_custom_date_end = strtotime($_POST['td']['custom_date_end']);
                else
                    $webtd->nd_custom_date_end = 0;
                    
                    
                foreach ($webtd->data() AS $k => $v) {
                    if ($k != $webtd->getKey() && isset($_POST['td'][$k])) {
                        $webtd->$k = $_POST['td'][$k];
                    }
                }
            }
            
            if (isset($_POST['d_xml'])) {
                $webtd->d_xml = base64_encode(serialize($_POST['d_xml']));
            }

            if (isset($_POST['td']['restore_plain']) && $_POST['td']['restore_plain']) {
                $dir=Bootstrap::$main->session('ufiles_path').'/.html';
                $plain_file=$dir.'/'.trim($_POST['td']['restore_plain']);
                
                if (file_exists($plain_file)) $webtd->plain = file_get_contents($plain_file);
            }
            
            
            $webtd->save();
            
            
            if (isset($_POST['td']['save_plain']) && $_POST['td']['save_plain']) {
                $title=$webtd->title;
                if (!$title) $title='module_'.$page.'_'.$webtd->sid;
                
                $title=Bootstrap::$main->kameleon->str_to_url($title,-1);
                $title.='.html';
                
                
                $dir=Bootstrap::$main->session('ufiles_path').'/.html';
                if (!file_exists($dir)) mkdir($dir,0755,true);
                
                file_put_contents("$dir/$title",$webtd->plain);
            }
            
            
            if (isset($_POST['cat'])) {
                
                
                foreach ($_POST['cat'] AS $cat=>$checked)
                {
                    if ($cat=='__new__')
                    {
                        $cat=trim($checked);
                        if (strlen($cat)) $webcat->add($webtd->server,$webtd->sid,$cat);
                    }
                    else
                    {
                        if ($webcat->hasCat($webtd->server,$webtd->sid,$cat) && !$checked) $webcat->del($webtd->server,$webtd->sid,$cat);
                        if (!$webcat->hasCat($webtd->server,$webtd->sid,$cat) && $checked) $webcat->add($webtd->server,$webtd->sid,$cat);
                    }
                    
                }
                                
            }

            if ($webtd->has_widget()) {
                $webtd->update_widget(isset($_POST[$webtd->widget])?$_POST[$webtd->widget]:null);
            }

            
            $redirect = 'index/get/' . $page;
            if (isset($_GET['hash'])) {
                $redirect .= '#' . $_GET['hash'];
            }
            
            
            
            
            
            $this->redirect($redirect);
            return;
        } else {
            if ($webtd->sid) Tools::activity('webtd',$webtd->sid);
        }

        $td = $webtd->data();

        
        
        $ret['cats']=$webcat->getCats($td['server'],$td['sid']);
        
        $td['title'] = str_replace("\"", "&quot;", $td['title']);
    
        //$td['plain'] = str_replace(array("<", ">"), array("&lt;", "&gt;"), $td['plain']);
        
        
        $td['plain'] = str_replace(UFILES_TOKEN, substr(Bootstrap::$main->session('ufiles'), strlen($root)), $td['plain']);
        $td['plain'] = str_replace(UIMAGES_TOKEN, substr(Bootstrap::$main->session('uimages'), strlen($root)), $td['plain']);
        $td['plain'] = str_replace('textarea', 'safetextarea', $td['plain']);
        
        $td['plain2'] = str_replace(array('\\',"'","\n","\r"), array('\\\\',"\'",'\\n',''), $td['plain']);
        
        
        
        if (!$this->_hasParam('dontfollow')) {
            $widget_has_edit=false;
            if ($webtd->has_widget()) {
                $widget = $webtd->get_widget($this->_getParam('page'));
                if ($widgetEditView = $widget->getEditView()) {
                    $widget->edit();
                    $this->setViewTemplate($widgetEditView);
                    $widget_has_edit=true;
                } else {
                    $widget->edit();
                }
                $widgetClass = get_class($widget);
        
                $ret[$widgetClass] = $ret['widgetData'] = $widget->toArray();
            }
            if ($webtd->menu_id && !$widget_has_edit) {
                $this->redirect('menu/get/' . $webtd->menu_id . '?setrefersid='.$this->id);
                return;
            }
        }

        $ret['td'] = $td;

        $weblink = new weblinkModel;
        $ret['menu_list'] = $weblink->getMenuList();
      
        $ret['tokens'] = Bootstrap::$main->tokens;
        //$ret['user_classes'] = $webtd->getUserClasses();
        $ret['d_xml'] = Bootstrap::$main->kameleon->get_user_variables('webtd', $td);

        $ret['levels']=$config['level']['body'];

        if ($td['page_id'] % 100 == $ret['server']['header']) $ret['levels'] = $config['level']['header'];
        if ($td['page_id'] % 100 == $ret['server']['footer']) $ret['levels'] = $config['level']['footer'];

        $css_files = array();
        if (isset($config['template']['css_files']) && is_array($config['template']['css_files'])) {
            foreach ($config['template']['css_files'] as $cssFile) {
                if (strpos($cssFile,'/'))
                    $css_files[] = 'template/'.$cssFile;
                else 
                    $css_files[] = $ret['template_images'] . '/' . $cssFile;
            }
        }
        $css_files[] = Bootstrap::$main->getRoot() . 'skins/kameleon/ckeditor.css';

        $ret['css_files'] = json_encode($css_files);
        
        $ret['config']=$this->getConfigChangeStyles('box');
        $ret['config_inline']=$this->getConfigChangeStyles('inline');
        
        return $ret;
    }

    public function remove()
    {
        $webtd = new webtdModel($this->id);
        if ($webtd->checkRight()) {
            if ($webtd->has_widget()) {
                $widget = $webtd->get_widget();
                $widget->delete();
            }
            $webtd->remove($this->id);
        }
        $this->redirectBack();
    }

    public function move_down()
    {
        $webtd = new webtdModel($this->id);
        if ($webtd->checkRight()) {
            $webtd->move(0, 1);
        }
        $this->redirectBack('kameleon_td'.$webtd->sid);
    }

    public function move_up()
    {
        $webtd = new webtdModel($this->id);
        if ($webtd->checkRight()) {
            $webtd->move(0, -1);
        }
        $this->redirectBack('kameleon_td'.$webtd->sid);
    }

    public function set_level()
    {
        $webtd = new webtdModel($this->id);
        if ($this->_getParam('level')+0>0) {
            
            $webtd->level = $this->_getParam('level');
            $webtd->pri=$webtd->next_pri();
            $webtd->save();
        }
        
        $this->redirectBack('kameleon_td'.$webtd->sid);
    }

    public function set_menu()
    {
        $webtd = new webtdModel($this->id);
        $menu_id = $this->_getParam('menu_id');
        if ($new = (strtolower($menu_id) == 'new')) {
            $weblinkModel = new weblinkModel;
            $menu_id = $weblinkModel->get_new_menu_id();
        }
        $webtd->menu_id = $menu_id;
        $webtd->save();
        if ($new)
            $this->redirect('menu/get/' . $menu_id . '?setrefersid=' . $this->id . '&setreferpage=' . $this->_getParam('page'));
        else
            $this->redirectBack('kameleon_td'.$webtd->sid);
    }

    public function set_repeat()
    {
        $webtd = new webtdModel($this->id);
        $webtd->contents_repeat = $this->_getParam('repeat');
        $webtd->save();
        $this->redirectBack('kameleon_td'.$webtd->sid);
    }

    public function paste()
    {
        $webtd=new webtdModel($this->id);
        
        if (!$webtd->hasAccess())
        {
            Bootstrap::$main->error(ERROR_ERROR,'No access');
            $this->redirectBack();
            return false;
        }
        
        $data=$webtd->data();
        unset($data['sid']);
        unset($data['server']);
        unset($data['ver']);
        unset($data['lang']);
        
        unset($data['autor']);
        unset($data['nd_create']);
        
        
        $data['page_id']=isset($_GET['hf'])?$_GET['hf']:$_GET['page'];
        $data['pri']=$webtd->next_pri($data['page_id'],$data['level']);
        $data['uniqueid']=$webtd->uniqueid();
        $data['trash']=0;
        
        if ($this->_getParam('menu_copy') && $data['menu_id'])
        {
            $weblink=new weblinkModel();
            $data['menu_id'] = $weblink->copy_menu($data['menu_id'],null,null,true,$webtd->lang,$webtd->ver,$webtd->server);
        }
        
        $new_webtd=new webtdModel($data);
        if (!$new_webtd->save())
        {
            $this->redirectBack();
            return;            
        }
        
        Bootstrap::$main->session('new_sid',$new_webtd->sid);
        
        $this->redirect('index/get/'.$_GET['page'],'kameleon_td'.$new_webtd->sid);

    }
    
    
    
    public function copy_template()
    {
        $webtd=Bootstrap::$main->getConfig('webtd');
        $template_path=Bootstrap::$main->session('template_path').'/html';
        $template_media=Bootstrap::$main->session('template_media');
        
        if (!$template_media) return;

        $widgets=array();
        
        
        foreach($webtd['type'] AS $type)
        {
            if (isset($type['widget']) && $type['widget']) $widgets[]=$type['widget'];
        }
        
        
        if (isset($_POST['copy_template']))
        {
            foreach(array_keys($_POST['copy_template']) AS $w)
            {
                if (in_array($w,$widgets))
                {
                    $widget=APPLICATION_PATH.'/widgets/'.$w.'/default.html';
                    if (file_exists($widget))
                    {
                        $dest=$template_path.'/widget_'.$w.'.html';
                        if (file_exists($dest)) $dest=$template_path.'/_widget_'.$w.'.html';
                        
                        copy($widget,$dest);
                    }
                }
                
            }
        }
        
        $widgets=array();
        
        
        foreach($webtd['type'] AS $type)
        {
            if (!isset($type['widget']) || !$type['widget']) continue;
        
        
            $widgets[Tools::translate($type['name'])]=array('name'=>Tools::translate($type['name']),'widget'=>$type['widget'],'exists'=>file_exists($template_path.'/widget_'.$type['widget'].'.html'));
        }
        
        ksort($widgets);
        

        
        
        return array('widgets'=>$widgets);
    }
}
