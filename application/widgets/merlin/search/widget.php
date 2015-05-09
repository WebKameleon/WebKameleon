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
        $this->loadJS('../bootstrap-datepicker.js');
	$this->loadCSS('../datepicker.css');
        
        $merlin=$this->merlin;
        $merlin_searchWidget=array();
        require_once __DIR__.'/../include/search.php';    
        foreach ($merlin_searchWidget AS $k=>$v) $this->$k=$v;
        
        parent::run();
    }


}
