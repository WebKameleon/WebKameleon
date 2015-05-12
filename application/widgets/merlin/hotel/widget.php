<?php

require_once __DIR__.'/../widget.php';

class merlin_hotelWidget extends merlinWidget
{
    /**
     * @var string
     */
    public $name = 'merlin/hotel';

    
    public function run()
    {
        $merlin=$this->merlin;
        $merlin_hotelWidget=array();

	$this->next_sign=$this->mode>1?'&':'?';
	$costxt=$this->webtd['costxt'];
        include __DIR__.'/../include/hotel.php';    
        foreach ($merlin_hotelWidget AS $k=>$v) $this->$k=$v;
        
        parent::run();
    }


}
