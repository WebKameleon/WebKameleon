<?php

require_once __DIR__.'/../widget.php';

class merlin_searchWidget extends merlinWidget
{
    /**
     * @var string
     */
    public $name = 'merlin/search';

    
    
  

    public function run()
    {
        
	$this->loadJS('../picker.js');
	$this->loadJS('../picker.date.js');
	$this->loadJS('../picker.time.js');
	//$this->loadJS('../legacy.js');
	$this->loadJS('../translations/pl_PL.js');
	
	$this->loadCSS('../themes/default.css');
	$this->loadCSS('../themes/default.date.css');
	
	
	
        $merlin=$this->merlin;
        $merlin_searchWidget=array();
	$costxt=$this->webtd['costxt'];
        include __DIR__.'/../include/search.php';    
        foreach ($merlin_searchWidget AS $k=>$v) $this->$k=$v;
        
        parent::run();
    }


}
