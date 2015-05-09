<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class flashWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'flash';

    public function run()
    {
        $this->loadJS('swfobject.js');

        parent::run();
    }
}