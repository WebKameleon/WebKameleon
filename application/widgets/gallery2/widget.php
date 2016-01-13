<?php
/**
 * @author Robson <robert@gammanet.pl>
 */

class gallery2Widget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'gallery2';

    public $js_options_keys = array(
        'width', 'height', 'thumb_opacity', 'animation_speed', 'effect'
    );

    /**
     * @param array $links
     */
    protected function checkLinks(array $links)
    {
        parent::checkLinks($links);

        $max_height = 0;

        foreach ($links as $link) {
            list ( , $height) = getimagesize($this->getImagesPath() . DIRECTORY_SEPARATOR . $link['img']);
            $max_height = max($max_height, $height);
        }

        $this->data['height'] = $max_height;
    }

    /**
     * @return array
     */
    public function getJsData()
    {
        $data = array_intersect_key($this->data, array_flip($this->js_options_keys));
        $slideshow = array();
        foreach ($this->data as $k => $v) {
            if (strpos($k, 'slideshow_') !== false) {
                list (, $name) = explode('_', $k);

                $slideshow[$name] = $v;
            }
        }
        if ($slideshow)
            $data['slideshow'] = $slideshow;

        return $data;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function checkImage($filename)
    {
        $w=$this->data['width'];
        $h=$this->data['width'] * 2;
        
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getImagesPath(), $w, $h, 0777, true);
    }

    /**
     * @param string $filename
     * @return string
     */
    public function checkThumb($filename)
    {
        $h=$this->data['thumb_height'];
        $w=isset($this->data['thumb_width'])?$this->data['thumb_width']+0:0;
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getThumbsPath(), $w, $h, 0777, true);
    }

    public function run()
    {
        require_once __DIR__ . '/../common/widget.php';
        commonWidget::loadFancybox();

        Bootstrap::$main->tokens->loadJQuery = true;

        $this->loadJS('gallery2.js');
        parent::run();
        
        $links=array();
        $images=json_decode($this->data['images'],true);
        $update_required=false;
        foreach($images AS &$image)
        {
            $link = $this->_weblink->get($image['sid']);
            $links[$link['sid']] = $link;
            
            $url=$this->getImagesUrl(). DIRECTORY_SEPARATOR . $link['img'];
            $url=substr(str_replace($this->getUimagesUrl(),'',$url),1);
            if ($url!=$image['url']) {
                $image['url']=$url;
            }
        }
        
        $this->checkLinks($links);
        
        if ($update_required)
        {
            $this->data['images']=json_encode($images);
            $this->save();
        }
        
    }
}
