<?php

class gmapWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'gmap';

    public function devby2($a, $round = 0)
    {
        return round($a / 2, $round);
    }

    public function run()
    {
        if (!$this->data['link']) {
		$api='//maps.google.com/maps/api/js';
		if (isset($this->data['key']) && strlen(trim($this->data['key'])) ) $api.='?key='.$this->data['key'];
            $this->loadJS($api);
            $this->loadJS('gmap.js');
        }
        parent::run();
    }
    
    
    public function update()
    {
        if (strstr(strtolower($this->data['link']),'iframe'))
        {
            $this->data['link'] = preg_replace('/.+src="([^"]+)".+/i','\1',$this->data['link']);
        
        }
    
        
        return parent::update();
    
    }
}
