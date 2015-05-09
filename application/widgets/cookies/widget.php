<?php
class cookiesWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'cookies';

 
    

    public function run()
    {
        
        $this->data['info']=str_replace($this->data['policy'],'<a href="'.Bootstrap::$main->tokens->page_href($this->webtd['more']).'">'.$this->data['policy'].'</a>',$this->data['info']);
        Bootstrap::$main->tokens->loadJQuery = true;
        $this->loadJS('cookies.js');
        $this->loadJS('jquery.cookie.js');
        return parent::run();
    }
    
    public function init()
    {
        if (!isset($this->data['policy'])) $this->data['policy']=Tools::translate('cookie policy');
        if (!isset($this->data['info'])) $this->data['info']=Tools::translate('_cookies_info');
        if (!isset($this->data['button'])) $this->data['button']='OK';
        if (!isset($this->data['display_mode'])) $this->data['display_mode']=0;
        
        
        parent::init();
    }
    
}
