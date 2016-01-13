<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class galleryWidget extends imageWidget
{
    /**
     * @var string
     */
    public $name = 'gallery';

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
        $h=$this->data['width'] * 2;
        return Tools::check_image($filename, $this->getUimagesPath(), $this->getImagesPath(), $this->data['width'], $h, 0777, true);
    }

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

        $this->loadCSS('jquery.ad-gallery.css');
        $this->loadJS('jquery.ad-gallery.js');
        $this->loadJS('ad-gallery.js');

        Bootstrap::$main->tokens->loadJQuery = true;

        parent::run();
    }
}
