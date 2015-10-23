<?php

//TODO: https://github.com/tkahn/Smooth-Div-Scroll/

class thumbslideWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'thumbslide';

    public $js_options_keys = array(
        'width', 'height', 'thumb_opacity', 'animation_speed', 'effect'
    );




    /**
     * @param string $filename
     * @return string
     */
    public function checkThumb($filename)
    {
        $h=$this->data['thumb_height'];
        $w=0;
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getThumbsPath(), $w, $h, 0777, true);
    }

    public function run()
    {
        require_once __DIR__ . '/../common/widget.php';
        commonWidget::loadFancybox();

        $this->loadJS('thumbslide.js');

        Bootstrap::$main->tokens->loadJQuery = true;

        foreach ($this->webtd['menu'] AS &$menu)
        {
            $imgpath=$this->getThumbsPath().'/'.$menu['img'];
            $s=@getimagesize ($imgpath);
            $menu['w']=$s[0];
        }
 
        
        return parent::run();
    }
}
