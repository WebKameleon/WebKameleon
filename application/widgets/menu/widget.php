<?php

class menuWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'menu';
    public $crop = true;


    public function run()
    {
        $this->checkLinks();
        return parent::run();
    }

}