<?php
/**
 * @author Radosław Szczepaniak <radoslaw.szczepaniak@gammanet.pl>
 */

class embeddedWidget extends Widget
{
    public $name = 'embedded';

    
    
    public function run()
    {
        $this->data['body'] = GN_Smekta::smektuj($this->data['body'],$this->webtd);
        return parent::run();
    }
}