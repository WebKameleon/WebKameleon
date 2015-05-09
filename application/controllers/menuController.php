<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class menuController extends Controller
{
    /**
     * @var weblinkModel
     */
    protected $_weblinkModel;

    protected function init()
    {
        parent::init();

        $this->_weblinkModel = new weblinkModel;
    }

    public function get($menu_id = null)
    {
        $data = Bootstrap::$main->session();
        $data['page_id'] = $this->_getParam('page');

        $menu = array();

        if ($menu_id == null) $menu_id = $this->id;
        if (!$menu_id > 0)  $data['refersid'] = 0;
        if ($menu_id == -1)
        {
            $menu_id = $this->_weblinkModel->get_new_menu_id();
            $this->redirect('menu/get/'.$menu_id);
            return;
        }
        if ($menu_id == null) $menu_id = -1;

        $menu['id'] = $menu_id;
        $menu['list'] = $this->_weblinkModel->getMenuList();
        $menu['links'] = $this->_weblinkModel->getAll($menu_id);

        if (isset($menu['links'][0]['name'])) {
            $menu['name'] = $menu['links'][0]['name'];
        } else if ($this->_hasParam('name')) {
            $menu['name'] = $this->_getParam('name');
        } else {
            $menu['name'] = $this->trans('New menu');
        }

        $data['menu_may_copy'] = $menu_id>0 && count($menu['links'])==0;

        
        $data['menu'] = $menu;
        
        
        if ($this->_hasParam('setrefersid') || $this->_hasParam('setreferpage'))
        {
            unset($data['_return_url']);
            Bootstrap::$main->session('_return_url','');
            
        }
        
        if ($this->_hasParam('return_url')) {
            $data['_return_url'] = Bootstrap::$main->session('_return_url',base64_decode($this->_getParam('return_url')));
        }
        
        
        

        return $data;
    }

    public function edit_link()
    {
        $data = Bootstrap::$main->session();

        $link = new weblinkModel($this->id);

        if ($this->_hasParam('link')) {
            $linkData = $this->_getParam('link');
            foreach ($link->data() as $k => $v) {
                if ($k != $link->getKey() && isset($linkData[$k])) {
                    $link->$k = $linkData[$k];
                }
            }
            
            if ($link->submenu_id==-1) $link->submenu_id=$link->get_new_menu_id();
            
            if (isset($_POST['d_xml'])) {
                $link->d_xml = base64_encode(serialize($_POST['d_xml']));
            }
            $link->save();
            $this->redirect('menu/get/' . $link->menu_id);
            return;
        }

        if ($this->_hasParam('return_url')) {
            $link->return_url = base64_decode($this->_getParam('return_url'));
        }

        $data['link'] = $link;
        $data['d_xml'] = Bootstrap::$main->kameleon->get_user_variables('weblink', $link->data());
        $data['menu_list'] = $link->getMenuList();

        $data['config']=$this->getConfigChangeStyles('menu');
        
        return $data;
    }

    public function copy()
    {
        $menu_id = $this->_weblinkModel->copy_menu(
            $this->_getParam('menu_src'), $this->id?:null, $this->id?null:$this->_getParam('name')
        );
        $this->redirect('menu/get/' . $menu_id);
    }

    public function add_link()
    {
        $link=$this->_weblinkModel->add_link(
            $this->id, $this->_getParam('name', $this->trans('New menu')), $this->_getParam('alt', $this->trans('New link'))
        );
        if ($refersid=Bootstrap::$main->session('refersid'))
        {
            $webtd=new webtdModel($refersid);
            $weblink=new weblinkModel($link['sid']);
            $weblink->type=$webtd->type;
            $weblink->save();
        }

        $this->redirectBack();
    }

    public function remove_links()
    {
        $this->_weblinkModel->remove_links($this->id);
        $this->redirect('menu');
    }
}