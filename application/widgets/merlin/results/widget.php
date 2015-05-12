<?php

require_once __DIR__.'/../widget.php';

class merlin_resultsWidget extends merlinWidget
{
    /**
     * @var string
     */
    public $name = 'merlin/results';

    
    public function run()
    {
        $merlin=$this->merlin;
        $merlin_resultsWidget=array();
        $type=isset($this->data['type'])?$this->data['type']:'';
        $size=$this->webtd['size'];
	$this->next_sign=$this->mode>1?'&':'?';
	$costxt=$this->webtd['costxt'];
        include __DIR__.'/../include/results.php';    
        foreach ($merlin_resultsWidget AS $k=>$v) $this->$k=$v;
        
        parent::run();
    }


}
