<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gplusWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'gplus';

    /**
     * @var string
     */
    public $parent = 'gplusone';

    public function run()
    {
        $this->loadJS('https://apis.google.com/js/plusone.js');

        parent::run();
    }
}