<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class scrollWidget extends imageWidget
{
    public $name = 'scroll';

    public $js_options_keys = array(
        'auto', 'speed', 'circular', 'visible', 'scroll'
    );

    /**
     * @return string
     */
    public function getImagesUrl()
    {
        return $this->getGfxUrl() . '/' . $this->thumbDir;
    }

    /**
     * @return array
     */
    public function getJsData()
    {
        return array_intersect_key($this->data, array_flip($this->js_options_keys));
    }

    public function run()
    {
        $this->loadJS('jquery.jcarousellite.min.js');
        $this->loadJS('scroll.js');

        Bootstrap::$main->tokens->loadJQuery = true;

        parent::run();
    }
}