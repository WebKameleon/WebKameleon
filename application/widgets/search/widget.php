<?php

class searchWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'search';

    public function edit()
    {
        require_once __DIR__ . '/../common/widget.php';
        commonWidget::loadFancybox();
        
        if (!isset($this->data['cx']) || !$this->data['cx'] )
        {
            $session=Bootstrap::$main->session();
            $this->data['cx']= $session['search_cx'][$session['server']['id']];
        }
        
        parent::edit();
    }
    
    
    public function run()
    {
        $page=$this->webpage['id'];
        if (!isset($_SERVER['search_widget'][$page])) $this->data['load_script']=1;
        $_SERVER['search_widget'][$page]=true;
        
        //$this->loadJS('//www.google.com/jsapi');
        parent::run();
    }
    
    
    public function update()
    {
        $session=Bootstrap::$main->session();
        $session['search_cx'][$session['server']['id']] = $this->data['cx'];
        Bootstrap::$main->session('search_cx',$session['search_cx']);
        parent::update();
    }
}
