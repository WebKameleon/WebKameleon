<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class slideshowWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'slideshow';

    /**
     * @return array
     */
    public function getJsData()
    {
        $images = isset($this->data['images']) ? json_decode($this->data['images'], true) : array();

        return array(
            'auto'          => count($images) > 1,
            'pager'         => count($images) > 1,
            'speed'         => $this->data['speed'],
            'pause'         => $this->data['pause'],
            'controls'      => false,
            'slideMargin'   => 0,
            'captions'      => true,
            'slideWidth'    => $this->data['width'],
            'startSlide'    => 0,
            'minSlides'     => 1,
            'maxSlides'     => 1,
        );
    }

    public function run()
    {
        Bootstrap::$main->tokens->loadJQuery = true;

        $this->loadCSS('jquery.bxslider.css');
        $this->loadJS('jquery.bxslider.min.js');

        parent::run();
    }


    public function init()
    {
        if ($this->webtd['menu_id'] && $this->webtd['menu_id'] != $this->data['menu_id']) $this->data['menu_id']=$this->webtd['menu_id'];
        parent::init();
        
    }


}