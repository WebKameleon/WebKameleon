<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class gshareWidget extends Widget
{
    /**
     * @var string
     */
    public $name = 'gshare';

    /**
     * @var string
     */
    public $parent = 'gplusone';

    /**
     * @var int
     */
    public $icon_size;

    public function run()
    {
        $this->icon_size = $this->getIconSize();

        parent::run();
    }

    /**
     * @return int
     */
    public function getIconSize()
    {
        if ($this->data['size'] == 'small')
            return 16;

        if ($this->data['size'] == 'large')
            return 64;

        return 32;
    }
}